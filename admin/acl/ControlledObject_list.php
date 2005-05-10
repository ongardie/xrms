<?php
/**
 * Manage list of ControlledObjects
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: ControlledObject_list.php,v 1.3 2005/05/10 13:28:14 braverock Exp $
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

$page_title = _("Manage Controlled Objects");

// begin sorted columns stuff
getGlobalVar($sort_column, 'sort_column'); 
getGlobalVar($current_sort_column, 'current_sort_column'); 
getGlobalVar($sort_order, 'sort_order'); 
getGlobalVar($current_sort_order, 'current_sort_order'); 
getGlobalVar($ControlledObject_next_page, 'ControlledObject_next_page'); 
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


$sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_ControlledObject.php?form_action=edit&return_url=ControlledObject_list.php&ControlledObject_id="), 'ControlledObject_id', $con->qstr("'\">")) . "AS LINK, ControlledObject_name as 'Object Name', on_what_table as 'Source Table', on_what_field as 'Identifying Field', data_source.data_source_name as 'Data Source' FROM ControlledObject  JOIN data_source ON data_source.data_source_id=ControlledObject.data_source_id order by $order_by";

$css_theme='basic-left';
start_page($page_title);
?>

<script language="JavaScript" type="text/javascript">
<!--

function submitForm(nextPage) {
    document.forms[0].ControlledObject_next_page.value = nextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].ControlledObject_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>


<form method="POST">
<input type=hidden name=use_post_vars value=1>
<input type=hidden name=ControlledObject_next_page value="<?php  echo $ControlledObject_next_page; ?>">
<input type=hidden name=resort value="0">
<input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
<input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
<input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
<input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">



<?php

echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';

$pager = new ADODB_Pager($con, $sql, 'ControlledObject', false, $sort_column-1, $pretty_sort_order);
$pager->Render();

?>
<input type="button" class="button" value="<?php echo _("Add New"); ?>" onclick="javascript: location.href='one_ControlledObject.php?form_action=new&return_url=ControlledObject_list.php'">
</div></div></form>

<?php
end_page();

/**
 * $Log: ControlledObject_list.php,v $
 * Revision 1.3  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.2  2005/02/14 23:42:17  vanmer
 * -requoted strings
 *
 * Revision 1.1  2005/01/13 17:16:14  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.4  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.3  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.2  2004/12/02 06:16:27  ke
 * - Added lookups in foreign key tables to allow lists to display useful information
 *
 * Revision 1.1  2004/12/02 04:12:03  justin
 * initial version
 *
 *
 */
?>
