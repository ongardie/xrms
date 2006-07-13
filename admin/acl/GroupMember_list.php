<?php
/**
 * Manage list of GroupMembers
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: GroupMember_list.php,v 1.7 2006/07/13 00:47:20 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

getGlobalVar($msg, 'msg');

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$con = get_acl_dbconnection();


$page_title = _("Manage Group Members");
$form_id="GroupMemberList";

$select_sql="SELECT " . $con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\"Edit\" onclick=\"javascript: location.href='one_GroupMember.php?form_action=edit&return_url=GroupMember_list.php&GroupMember_id="), 'GroupMember_id', $con->qstr("'\">")) . "AS LINK, Groups.Group_name as 'GroupName', ControlledObject.ControlledObject_name as 'ControlledObject', GroupMember.*";
 
 $from_sql="FROM GroupMember JOIN Groups ON Groups.Group_id=GroupMember.Group_id JOIN ControlledObject ON ControlledObject.ControlledObject_id=GroupMember.ControlledObject_id";

$sql = "$select_sql $from_sql";

$group_list="SELECT " . $con->Concat('Groups.Group_name', $con->qstr(' ('), 'count(GroupMember.Group_id)',$con->qstr(')')) . " AS 'GroupName', GroupMember.Group_id $from_sql GROUP BY GroupMember.Group_id ORDER BY Groups.Group_name";
$group_select=$sql . " WHERE GroupMember.Group_id= XXX-value-XXX";

$object_list="SELECT " . $con->Concat('ControlledObject.ControlledObject_name', $con->qstr(' ('), 'count(GroupMember.ControlledObject_id)',$con->qstr(')')) . " AS 'COName', GroupMember.ControlledObject_id $from_sql GROUP BY GroupMember.ControlledObject_id ORDER BY ControlledObject.ControlledObject_name";
$object_select=$sql . " WHERE GroupMember.ControlledObject_id= XXX-value-XXX";

$columns = array();
$columns[] = array('name' => _("Action"), 'index_sql' => 'LINK', 'sql_sort_column' => 'GroupMember_id', 'type' => 'url');
$columns[] = array('name' => _("Controlled Object"), 'index_sql' => 'ControlledObject','group_query_list'=>$object_list, 'group_query_select'=>$object_select);
$columns[] = array('name' => _("Group"), 'index_sql' => 'GroupName','group_query_list'=>$group_list, 'group_query_select'=>$group_select);
$columns[] = array('name' => _("Object ID"), 'index_sql' => 'on_what_id');
$columns[] = array('name' => _("Criteria Table"), 'index_sql' => 'criteria_table');
$columns[] = array('name' => _("Criteria Result Field"), 'index_sql' => 'criteria_resultfield');
$columns[] = array('name' => _("Group Member ID"), 'index_sql' => 'GroupMember_id');
$columns[] = array('name' => _("Controlled Object ID"), 'index_sql' => 'ControlledObject_id');
$columns[] = array('name' => _("Group ID"), 'index_sql' => 'Group_id');

$default_columns = array('LINK','GroupName','ControlledObject','on_what_id','criteria_table','criteria_resultfield');

$pager_columns = new Pager_Columns('GroupMembersPager', $columns, $default_columns, $form_id);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');
$colspan=count($columns);


$pager = new GUP_Pager($con, $sql, null,_("Group Members"), $form_id, 'GroupMembersPager', $columns, false);

start_page($page_title, true, $msg);
?>
<form method="POST" name="<?php echo $form_id; ?>">


<?php


echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';


echo $pager_columns_selects;

$endrows="<tr><td colspan=$colspan class=widget_class_form_element>$pager_columns_button
<input type=\"button\" class=\"button\" value=\"".  _("Add New") ."\" onclick=\"javascript: location.href='one_GroupMember.php?form_action=new&return_url=GroupMember_list.php'\"></td></tr>";

$pager->AddEndRows($endrows);

$pager->Render();

?>
</div></div></form>

<?php
end_page();

/**
 * $Log: GroupMember_list.php,v $
 * Revision 1.7  2006/07/13 00:47:20  vanmer
 * - changed all columns/pager combinations to reference the same pager name, to allow saved views to operate properly
 *
 * Revision 1.6  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.5  2005/08/11 22:54:29  vanmer
 * - added msg output to Group Member list
 *
 * Revision 1.4  2005/08/02 00:46:41  vanmer
 * - changed to use new acl wrapper call for database connection
 * - changed to use new GUP_Pager with grouping on objects and groups
 *
 * Revision 1.3  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.2  2005/02/15 00:33:36  vanmer
 * requoted for general use
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
 * Revision 1.1  2004/12/02 04:27:33  ke
 * - Initial revision of group member list and individual page
 *
 *
 */
?>
