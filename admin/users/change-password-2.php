<?php
/**
 * admin/users/change-password-2.php - Save new password
 *
 * Check that new password entries are identical
 * Then save in the database.
 *
 * $Id: change-password-2.php,v 1.6 2004/05/13 16:36:46 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$edit_user_id = $_POST['edit_user_id'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password == $confirm_password) {
    $password = md5($password);

    $con = &adonewconnection($xrms_db_dbtype);
    $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

    $sql = "update users set password = " . $con->qstr($password, get_magic_quotes_gpc()) . " where user_id = $edit_user_id";

    $con->execute($sql);

    add_audit_item($con, $session_user_id, 'change password', 'users', $edit_user_id, 1);

    $con->close();

    header("Location: " . $http_site_root . "/admin/routing.php");
} else {
    header("Location: change-password.php?msg=password_no_match");
}

/**
 *$Log: change-password-2.php,v $
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