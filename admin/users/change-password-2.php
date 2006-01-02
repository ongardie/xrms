<?php
/**
 * admin/users/change-password-2.php - Save new password
 *
 * Check that new password entries are identical
 * Then save in the database.
 *
 * $Id: change-password-2.php,v 1.13 2006/01/02 22:09:39 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

//
// become Admin aware - Don't accept the user to edit from the URL
// or from POST for non-Admin types.
//
if (check_user_role(false, $session_user_id, 'Administrator') AND array_key_exists('edit_user_id',$_POST) AND $_POST['edit_user_id']) {
  $edit_user_id = $_POST['edit_user_id'];
} else {
  $edit_user_id = $session_user_id;
}

$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password == $confirm_password) {
    $password = md5($password);

    $con = get_xrms_dbconnection();

    $sql = "SELECT * FROM users WHERE user_id = $edit_user_id";

    $rst = $con->execute($sql);
    if ($rst) {
        $rec = array();
        $rec['password'] = $password;

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        if (strlen($upd)){
            $upd_rst= $con->execute($upd);

            if (!$upd_rst) { db_error_handler($con, $upd); }

            add_audit_item($con, $session_user_id, 'change password', 'users', $edit_user_id, 1);

            $con->close();

            header("Location: " . $http_site_root . "/admin/routing.php?msg=saved");
        } else {
            $con->close();

            $msg = urlencode(_("There was a problem with the user ID. Password not Changed."));
            header("Location: change-password.php?msg=$msg");
        }
    } else { // no result set on finding the user
        db_error_handler ($con, $sql);

        $con->close();
    }

} else {
    header("Location: change-password.php?msg=password_no_match");
}

/**
 *$Log: change-password-2.php,v $
 *Revision 1.13  2006/01/02 22:09:39  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.12  2005/09/15 16:08:38  vanmer
 *- added code to ensure that even if user is an administrator, they can still change their own password as well
 *
 *Revision 1.11  2005/07/22 15:45:24  braverock
 *- add additional result set error handling and more informative error msgs
 *- remove trailing whitespace
 *
 *Revision 1.10  2005/05/31 20:28:59  vanmer
 *- changed to use new ACL role check instead of older deprecated role system
 *
 *Revision 1.9  2004/07/20 12:45:21  cpsource
 *- Allow non-Admin users to change their passwords, but do so
 *  in a secure manner.
 *
 *Revision 1.8  2004/07/16 23:51:38  cpsource
 *- require session_check ( 'Admin' )
 *
 *Revision 1.7  2004/06/14 22:50:14  introspectshun
 *- Add adodb-params.php include for multi-db compatibility.
 *- Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 *Revision 1.6  2004/05/13 16:36:46  braverock
 *- modified to work safely even when register_globals=on
 *  (!?! == dumb administrators ?!?)
 *- changed $user_id to $edit_user_id to avoid security collisions
 *  - fixes multiple reports of user role switching on user edits.
 *
 *Revision 1.5  2004/05/10 13:07:20  maulani
 *- Add level to audit trail
 *- Clean up audit trail text
 *
 *Revision 1.4  2004/03/12 16:34:31  maulani
 *- Add audit trail
 *- Add phpdoc
 *
 *Revision 1.2  2004/03/12 15:37:07  maulani
 *- Require new passwords be entered twice for validation
 *- Add phpdoc
 */
?>