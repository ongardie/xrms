<?php
/**
 * Manage list of GroupGroups
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: GroupGroup_list.php,v 1.5 2006/07/13 00:47:20 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

$session_user_id = session_check('Admin');

require_once ($include_directory.'classes/acl/xrms_acl_config.php');

$con = get_acl_dbconnection();


$page_title = _("Manage Group Groups");

$form_name = 'GroupGroupPager';

$sql="SELECT " . 
$con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\""._("Edit")."\" onclick=\"javascript: location.href='one_GroupGroup.php?form_action=edit&return_url=GroupGroup_list.php&GroupUser_id="), 'GroupUser_id', $con->qstr("'\">"),$con->qstr("<input type=\"button\" class=\"button\" value=\""._("Delete") . "\" onclick=\"javascript: location.href='edit_GroupUser.php?userAction=deleteRole&return_url=GroupGroup_list.php&GroupUser_id="), 'GroupUser_id', $con->qstr("'\">")) . "AS LINK,GroupUser_id, Groups.Group_name as 'GroupGroup', CGroup.Group_name as ChildGroup FROM GroupUser LEFT OUTER JOIN Groups on Groups.Group_id=GroupUser.Group_id LEFT OUTER JOIN Groups CGroup on CGroup.Group_id=GroupUser.ChildGroup_id WHERE GroupUser.ChildGroup_id IS NOT NULL";

    $columns = array();
    $columns[] = array('name' => _("Edit"), 'index_sql' => 'LINK');
    $columns[] = array('name' => _("ID"), 'index_sql' => 'GroupUser_id');
    $columns[] = array('name' => _("Group"), 'index_sql' => 'GroupGroup','group_calc'=>true);
    $columns[] = array('name' => _("Child Group"), 'index_sql' => 'ChildGroup','group_calc'=>true);


    $default_columns=array('LINK','GroupGroup', 'ChildGroup');
    
    $pager_columns = new Pager_Columns('GroupGroupPager', $columns, $default_columns, $form_name);
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

   $pager = new GUP_Pager($con, $sql,false, _("Group Groups"), $form_name, 'GroupGroupPager', $columns);

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
<input type="button" class="button" value="<?php echo _("Add New"); ?>" onclick="javascript: location.href='one_GroupGroup.php?form_action=new&return_url=GroupGroup_list.php'">
</div></div></form>

<?php
end_page();

/**
 * $Log: GroupGroup_list.php,v $
 * Revision 1.5  2006/07/13 00:47:20  vanmer
 * - changed all columns/pager combinations to reference the same pager name, to allow saved views to operate properly
 *
 * Revision 1.4  2006/07/09 05:04:03  vanmer
 * - patched ACL interface to check for admin access
 *
 * Revision 1.3  2005/12/12 21:17:20  vanmer
 * - added internationalization calls to strings which were only english
 *
 * Revision 1.2  2005/08/11 22:52:15  vanmer
 * - changed to use ACL dbconnection
 *
 * Revision 1.1  2005/06/07 20:20:25  vanmer
 * - added new interface to GroupUsers, splitting out child groups
 * - added new interface for adding child groups/managing them
 * - added handler for deleting users from roles in groups
 * - added link to new group management pages
 *
 *
 */
?>
