<?php
/**
 * admin/users/edit-2.php - Save changes from user edit
 *
 * Admin changes a user
 *
 * $Id: edit-2.php,v 1.17 2006/01/02 22:09:39 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$edit_user_id    = $_POST['edit_user_id'];
$user_contact_id = $_POST['user_contact_id'];
$role_id         = $_POST['role_id'];
$new_username    = $_POST['new_username'];
$last_name       = $_POST['last_name'];
$first_names     = $_POST['first_names'];
$email           = $_POST['email'];
$gmt_offset      = $_POST['gmt_offset'];

getGlobalVar($return_url, 'return_url');
getGlobalVar($userAction,'userAction');

if (!$userAction) { $userAction='editUser'; }

switch ($userAction) {
    case 'deleteRole':
        getGlobalVar($edit_user_id, 'edit_user_id');
        $role_id=$_GET['role_id'];
        getGlobalVar($group, 'group');
            if (!$group) {
                $group="Users";
            }
            if (delete_user_group(false, false, $group, $edit_user_id, $role_id)) {
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
           return false;
    break;
    case 'editUser':
        if (array_key_exists('enabled',$_POST)) $enabled=true;
        else $enabled=false;
        $user_record_status = ($enabled) ? 'a' : 'd';
        
        $gmt_offset = (strlen($gmt_offset) > 0) ? $gmt_offset : 0;
        
        $con = get_xrms_dbconnection();
        
        $sql = "SELECT * FROM users WHERE user_id = $edit_user_id";
        $rst = $con->execute($sql);
        if ($rst->fields['role_id']!=$role_id) {
            if (!$group) {
                $group="Users";
            }
            if (!delete_user_group(false, false, $group, $edit_user_id, $rst->fields['role_id'])) echo _("Failed to add user to group.");
            $ret=add_user_group(false, $group, $edit_user_id, $role_id);
            if (!is_array($ret)) echo _("Failed to add user to group.");
        }
        $rec = array();
        
        $rec['role_id']         = $role_id;
        $rec['user_contact_id'] = $user_contact_id;
        $rec['last_name']       = $last_name;
        $rec['first_names']     = $first_names;
        $rec['username']        = $new_username;
        $rec['email']           = $email;
        $rec['gmt_offset']      = $gmt_offset;
        $rec['user_record_status'] = $user_record_status;
        
        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        if ($upd) {
            $rst = $con->execute($upd);
            if(!$rst) {
                db_error_handler($con, $upd);
            }
            
            add_audit_item($con, $session_user_id, 'updated', 'users', $edit_user_id, 1);
        }
        
        $con->close();
       
       if (!$return_url) { $return_url="self.php?msg=saved"; }
        header("Location: $return_url");
        exit;
        
    break;
}    
/**
 *$Log: edit-2.php,v $
 *Revision 1.17  2006/01/02 22:09:39  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.16  2005/07/06 17:19:53  vanmer
 *- allow edit of user data to return to $return_url instead of always going to self.php
 *- removed reference to short_role in session
 *
 *Revision 1.15  2005/05/18 05:51:11  vanmer
 *- altered to change behavior based on userAction parameter
 *- defaults to basic (change user information)
 *- added case for deleting roles from a user
 *- added case for adding role for a user
 *
 *Revision 1.14  2005/02/10 23:48:40  vanmer
 *- added handling of enabling/disabling user accounts
 *
 *Revision 1.13  2005/01/13 17:56:13  vanmer
 *- added new ACL code to user management section
 *
 *Revision 1.12  2004/12/30 19:06:58  braverock
 *- add db_error_handler
 *- patch provided by Ozgur Cayci
 *
 *Revision 1.11  2004/10/13 07:59:16  niclowe
 *fixed bug  1003428
 *
 *Revision 1.10  2004/07/20 11:40:06  cpsource
 *- Fixed multiple errors
 *   misc undefined variables being used, g....
 *   non Admin users could end up at some.php and effect other users
 *   made self.php goto self-2.php instead of edit-2.php
 *   non Admin users can now admin their own user name only.
 *   added a successful update promit to private/index.php
 *
 *Revision 1.9  2004/07/16 23:51:38  cpsource
 *- require session_check ( 'Admin' )
 *
 *Revision 1.8  2004/07/13 18:16:16  neildogg
 *- Add admin support to allow a contact to be tied to the user
 *
 *Revision 1.7  2004/06/14 22:50:14  introspectshun
 *- Add adodb-params.php include for multi-db compatibility.
 *- Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 *Revision 1.6  2004/05/17 17:23:43  braverock
 *- change $username to not conflict when register_globals is on (?!?)
 *  - fixed SF bug 952670 - credit to jmaguire123 and sirjo for troubleshooting
 *
 *Revision 1.5  2004/05/13 16:36:46  braverock
 *- modified to work safely even when register_globals=on
 *  (!?! == dumb administrators ?!?)
 *- changed $user_id to $edit_user_id to avoid security collisions
 *  - fixes multiple reports of user role switching on user edits.
 *
 *Revision 1.4  2004/05/10 13:07:20  maulani
 *- Add level to audit trail
 *- Clean up audit trail text
 *
 *Revision 1.3  2004/03/12 16:34:31  maulani
 *- Add audit trail
 *- Add phpdoc
 */
?>