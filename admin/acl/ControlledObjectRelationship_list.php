<?php
/**
 * Manage list of ControlledObjectRelationships
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: ControlledObjectRelationship_list.php,v 1.1 2005/01/13 17:16:13 vanmer Exp $
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

$page_title = "Manage Controlled Object Relationships";

// begin sorted columns stuff
getGlobalVar($sort_column, 'sort_column'); 
getGlobalVar($current_sort_column, 'current_sort_column'); 
getGlobalVar($sort_order, 'sort_order'); 
getGlobalVar($current_sort_order, 'current_sort_order'); 
getGlobalVar($ControlledObjectRelationship_next_page, 'ControlledObjectRelationship_next_page'); 
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


$sql="SELECT " . $con->Concat('"<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href=\'one_ControlledObjectRelationship.php?form_action=edit&return_url=ControlledObjectRelationship_list.php&ControlledObjectRelationship_id="', 'ControlledObjectRelationship_id', '"\'\">"') . "AS LINK, 
ControlledObjectRelationship_id as ID,
Child.ControlledObject_name as 'Child Object', 
Parent.ControlledObject_name as 'Parent Object' 
FROM ControlledObjectRelationship LEFT OUTER JOIN ControlledObject AS Parent ON Parent.ControlledObject_id=ControlledObjectRelationship.ParentControlledObject_id
JOIN ControlledObject AS Child ON Child.ControlledObject_id=ControlledObjectRelationship.ChildControlledObject_id 
order by $order_by";

$css_theme='basic-left';
start_page($page_title);
?>

<script language="JavaScript" type="text/javascript">
<!--

function submitForm(nextPage) {
    document.forms[0].ControlledObjectRelationship_next_page.value = nextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].ControlledObjectRelationship_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>


<form method="POST">
<input type=hidden name=use_post_vars value=1>
<input type=hidden name=ControlledObjectRelationship_next_page value="<?php  echo $ControlledObjectRelationship_next_page; ?>">
<input type=hidden name=resort value="0">
<input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
<input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
<input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
<input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">



<?php

echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';

$pager = new ADODB_Pager($con, $sql, 'ControlledObjectRelationship', false, $sort_column-1, $pretty_sort_order);
$pager->Render();

?>
<input type="button" class="button" value="Add New" onclick="javascript: location.href='one_ControlledObjectRelationship.php?form_action=new&return_url=ControlledObjectRelationship_list.php'">
</div></div></form>

<?php
end_page();

/**
 * $Log: ControlledObjectRelationship_list.php,v $
 * Revision 1.1  2005/01/13 17:16:13  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.5  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.4  2004/12/15 17:58:02  ke
 * - added controlled object relationship id to list
 *
 * Revision 1.3  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.2  2004/12/02 05:58:50  ke
 * - Added lookup for controlled object names in sql query
 *
 * Revision 1.1  2004/12/02 04:19:58  justin
 * initial version
 *
 * Revision 1.1  2004/12/02 04:12:03  justin
 * initial version
 *
 *
 */
?>
