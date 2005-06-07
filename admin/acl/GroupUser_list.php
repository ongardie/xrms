<?php
/**
 * Manage list of GroupUsers
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: GroupUser_list.php,v 1.8 2005/06/07 20:20:25 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

$session_user_id = session_check();

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$con = &adonewconnection($xrms_acl_db_dbtype);
$con->connect($xrms_acl_db_server, $xrms_acl_db_username, $xrms_acl_db_password, $xrms_acl_db_dbname);

$page_title = _("Manage Group Users");

$form_name = 'GroupUserPager';

$sql="SELECT " . 
$con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\""._("Edit")."\" onclick=\"javascript: location.href='one_GroupUser.php?form_action=edit&return_url=GroupUser_list.php&GroupUser_id="), 'GroupUser_id', $con->qstr("'\">"),$con->qstr("<input type=\"button\" class=\"button\" value=\""._("Delete") . "\" onclick=\"javascript: location.href='edit_GroupUser.php?userAction=deleteRole&return_url=GroupUser_list.php&GroupUser_id="), 'GroupUser_id', $con->qstr("'\">")) . "AS LINK, Groups.Group_name as 'UserGroup', " . 
$con->Concat('users.last_name', $con->qstr(', '), 'users.first_names') . " AS 'User', " .  
"Role_name as Role FROM GroupUser LEFT OUTER JOIN Groups on Groups.Group_id=GroupUser.Group_id LEFT OUTER JOIN Role on Role.Role_id=GroupUser.Role_id LEFT OUTER JOIN users on users.user_id=GroupUser.user_id WHERE GroupUser.user_id IS NOT NULL";

$user_list = "SELECT " . $con->Concat('users.last_name', $con->qstr(', '), 'users.first_names', $con->qstr(' ('), 'count(GroupUser.GroupUser_id)',$con->qstr(')')) . " AS 'User', GroupUser.user_id FROM GroupUser JOIN users ON users.user_id=GroupUser.user_id WHERE GroupUser.user_id IS NOT NULL GROUP BY GroupUser.user_id";
$user_select=$sql . " AND GroupUser.user_id= XXX-value-XXX";

    $columns = array();
    $columns[] = array('name' => 'Edit', 'index_sql' => 'LINK');
    $columns[] = array('name' => 'User', 'index_sql' => 'User', 'group_query_list'=>$user_list, 'group_query_select'=>$user_select, 'sql_sort_column'=>'GroupUser.user_id');
    $columns[] = array('name' => 'Group', 'index_sql' => 'UserGroup','group_calc'=>true);
    $columns[] = array('name' => 'Role', 'index_sql' => 'Role','group_calc'=>true);


    $default_columns=array('LINK','UserGroup', 'User','Role');
    
    $pager_columns = new Pager_Columns('GroupUserPager', $columns, $default_columns, $form_name);
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');
    $colspan = count($columns);

        $endrows =  "
            <tr>
                <td colspan=$colspan class=widget_content_form_element>
                    $pager_columns_button
                </td>
            </tr>";

   $pager = new GUP_Pager($con, $sql,false, 'Group Users', $form_name, 'GroupUsers', $columns);

    $pager->AddEndRows($endrows);



start_page($page_title);
?>



<form method="POST" name="<?php echo $form_name; ?>">
<?php

echo "<div id='Main'>";
require_once('xrms_acl_nav.php');
echo '<div id=Content>';
echo $pager_columns_selects;
$pager->Render();

?>
<input type="button" class="button" value="<?php echo _("Add New"); ?>" onclick="javascript: location.href='one_GroupUser.php?form_action=new&return_url=GroupUser_list.php'">
</div></div></form>

<?php
end_page();

/**
 * $Log: GroupUser_list.php,v $
 * Revision 1.8  2005/06/07 20:20:25  vanmer
 * - added new interface to GroupUsers, splitting out child groups
 * - added new interface for adding child groups/managing them
 * - added handler for deleting users from roles in groups
 * - added link to new group management pages
 *
 * Revision 1.7  2005/05/10 13:28:14  braverock
 * - localized strings patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.6  2005/03/21 15:56:11  ycreddy
 * Added a quote for AS User to make it compatible with SQL Server
 *
 * Revision 1.5  2005/03/08 21:51:41  daturaarutad
 * fixed query to join user table in order to display user names instead of user ids
 *
 * Revision 1.4  2005/02/15 19:44:49  vanmer
 * - added quotes to reflect sql server needs
 *
 * Revision 1.3  2005/02/14 23:33:05  vanmer
 * - removed unneeded slashes for links
 *
 * Revision 1.2  2005/02/14 23:30:20  vanmer
 * - changed quotes
 *
 * Revision 1.1  2005/01/13 17:16:14  vanmer
 * - Initial Commit for ACL Administration interface
 *
 * Revision 1.7  2004/12/27 23:48:50  ke
 * - adjusted to reflect new stylesheet
 *
 * Revision 1.6  2004/12/02 21:07:15  ke
 * - updated list names to reflect names on one_ page
 *
 * Revision 1.5  2004/12/02 20:53:24  ke
 * - altered to allow display of all entries in table
 *
 * Revision 1.4  2004/12/02 20:34:26  ke
 * - added left outer join to allow all records to display
 *
 * Revision 1.3  2004/12/02 09:34:24  ke
 * - added navigation sidebar to all list pages
 *
 * Revision 1.2  2004/12/02 06:16:27  ke
 * - Added lookups in foreign key tables to allow lists to display useful information
 *
 * Revision 1.1  2004/12/02 04:39:10  ke
 * - Initial revision of group user list and individual page
 *
 *
 */
?>
