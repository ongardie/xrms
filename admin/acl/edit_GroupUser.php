<?php
/**
 * admin/users/edit-2.php - Save changes from user edit
 *
 * Admin changes a user
 *
 * $Id: edit_GroupUser.php,v 1.1 2005/06/07 20:20:25 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

getGlobalVar($role_id, 'role_id');
getGlobalVar($return_url, 'return_url');
$edit_user_id    = $_POST['edit_user_id'];
$user_contact_id = $_POST['user_contact_id'];
$role_id         = $_POST['role_id'];

getGlobalVar($userAction, 'userAction');
if (!$userAction) { $userAction='editUser'; }

switch ($userAction) {
    case 'deleteRole':
        getGlobalVar($edit_user_id, 'edit_user_id');
        getGlobalVar($GroupUser_id,'GroupUser_id');
        $role_id=$_GET['role_id'];
        getGlobalVar($group, 'group');
            if (!$group) {
                $group="Users";
            }
            if (delete_user_group(false, $GroupUser_id, $group, $edit_user_id, $role_id)) {
                $msg="Deleted role $role_id for user $edit_user_id in group $group successfully";
            } else {
                $msg="Failed to delete role $role_id for user $edit_user_id in group $group";
            }
            if (!$return_url) { $return_url="one.php?edit_user_id=$edit_user_id&msg=$msg"; }
            Header("Location: $return_url");
            exit();       
    break;
    case 'addRole':
            getGlobalVar($edit_user_id, 'edit_user_id');
            $role_id=$_POST['role_id'];
            getGlobalVar($group, 'group');
            if (!$group) {
                $group="Users";
            }
            $ret=add_user_group(false, $group, $edit_user_id, $role_id);
            if (!is_array($ret)) { 
                $msg= _("Failed to add user to role in group.");
            } else {
                $msg = _("Added user to role in group successfully");
            }
            if (!$return_url) { $return_url="one.php?edit_user_id=$edit_user_id&msg=$msg"; }
            Header("Location: $return_url");
            exit();       
    default:
            $msg="No Action Selected";
            if (!$return_url) { $return_url="one.php?edit_user_id=$edit_user_id&msg=$msg"; }
            Header("Location: $return_url");
            exit();       
    break;
}
/**
  * $Log: edit_GroupUser.php,v $
  * Revision 1.1  2005/06/07 20:20:25  vanmer
  * - added new interface to GroupUsers, splitting out child groups
  * - added new interface for adding child groups/managing them
  * - added handler for deleting users from roles in groups
  * - added link to new group management pages
  *
**/
?>