<?php
/**
 * Manage list of RolePermissions
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: RolePermission_list.php,v 1.3 2005/02/15 19:49:35 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

global $http_site_root;

$session_user_id = session_check();

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$con = &adonewconnection($xrms_acl_db_dbtype);
$con->connect($xrms_acl_db_server, $xrms_acl_db_username, $xrms_acl_db_password, $xrms_acl_db_dbname);

$page_title = "Manage Role Permissions";

// begin sorted columns stuff
getGlobalVar($sort_column, 'sort_column'); 
getGlobalVar($current_sort_column, 'current_sort_column'); 
getGlobalVar($sort_order, 'sort_order'); 
getGlobalVar($current_sort_order, 'current_sort_order'); 
getGlobalVar($RolePermission_next_page, 'RolePermission_next_page'); 
getGlobalVar($resort, 'resort'); 

if (!strlen($sort_column) > 0) {
    $sort_column = 1;
		$current_sort_column = $sort_column;
    $sort_order = "asc";
}
    
if (!($sort_column == $current_sort_column)) {
    $sort_order = "asc";
}


$opposite_sort_order = ($sort_order == "asc") ? "desc" : "asc";
$sort_order = (($resort) && ($current_sort_column == $sort_column)) ? $opposite_sort_order : $sort_order;

$ascending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/asc.gif" alt="">';
$descending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/desc.gif" alt="">';
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$order_by = $sort_column;



$order_by .= " $sort_order";
// end sorted columns stuff


$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_RolePermission.php?form_action=edit&return_url=RolePermission_list.php&RolePermission_id="), 'RolePermission_id', $con->qstr("'\">")) . "AS LINK,
Role_name as 'Role', Child.ControlledObject_name as 'Child Object', Parent.ControlledObject_name as 'Parent Object', Scope, Permission_name as 'Permission'
FROM RolePermission JOIN Permission ON Permission.Permission_id=RolePermission.Permission_id 
JOIN Role on Role.Role_id=RolePermission.Role_id
JOIN ControlledObjectRelationship ON ControlledObjectRelationship.CORelationship_id=RolePermission.CORelationship_id
JOIN ControlledObject as Child ON Child.ControlledObject_id=ControlledObjectRelationship.ChildControlledObject_id
LEFT OUTER JOIN ControlledObject as Parent ON Parent.ControlledObject_id=ControlledObjectRelationship.ParentControlledObject_id
order by $order_by";

$css_theme='basic-left';
start_page($page_title);
?>

<script language="JavaScript" type="text/javascript">
<!--

function submitForm(nextPage) {
    document.forms[0].RolePermission_next_page.value = nextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].RolePermission_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>


<form method="POST">
<input type=hidden name=use_post_vars value=1>
<input type=hidden name=RolePermission_next_page value="<?php  echo $RolePermission_next_page; ?>">
<input type=hidden name=resort value="0">
<input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
<input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
<input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
<input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">



<?php

echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';

$pager = new ADODB_Pager($con, $sql, 'RolePermission', false, $sort_column-1, $pretty_sort_order);
$pager->Render();

?>
<input type="button" class="button" value="Add New" onclick="javascript: location.href='one_RolePermission.php?form_action=new&return_url=RolePermission_list.php'">
</div></div></form>

<?php
end_page();

/**
 * $Log: RolePermission_list.php,v $
 * Revision 1.3  2005/02/15 19:49:35  vanmer
 * - changes to reflect new fieldnames
 *
 * Revision 1.2  2005/02/14 23:35:36  vanmer
 * - added qstr and removed single quote slashes
 *
 * Revision 1.1  2005/01/13 17:16:15  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.4  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.3  2004/12/02 21:07:53  ke
 * - updated list to display useful information for roles and object relationships
 *
 * Revision 1.2  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.1  2004/12/02 05:15:17  ke
 * - Initial revision of role permission list and individual page
 *
 * Revision 1.1  2004/12/02 04:39:10  ke
 * - Initial revision of group user list and individual page
 *
 *
 */
?>
