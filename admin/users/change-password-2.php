<?php
/**
 * admin/users/change-password-2.php - Save new password
 *
 * Check that new password entries are identical
 * Then save in the database.
 *
 * $Id: change-password-2.php,v 1.3 2004/03/12 16:20:58 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$user_id = $_POST['user_id'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

if ($password == $confirm_password) {
    $password = md5($password);
    
    $con = &adonewconnection($xrms_db_dbtype);
    $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
    
    $sql = "update users set password = " . $con->qstr($password, get_magic_quotes_gpc()) . " where user_id = $user_id";
    
    $con->execute($sql);
    
    $con->close();
    
    header("Location: " . $http_site_root . "/admin/routing.php");
} else {
    header("Location: change-password.php?msg=password_no_match");
}

/**
 *$Log: change-password-2.php,v $
 *Revision 1.3  2004/03/12 16:20:58  maulani
 *- correct redirect URL
 *
 *Revision 1.2  2004/03/12 15:37:07  maulani
 *- Require new passwords be entered twice for validation
 *- Add phpdoc
 *
 *
 */
?>
