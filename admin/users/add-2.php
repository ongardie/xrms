<?php
/**
 * commit a new user to the Database
 *
 * $Id: add-2.php,v 1.4 2004/05/17 17:23:43 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$role_id = $_POST['role_id'];
$new_username = $_POST['new_username'];
$password = $_POST['password'];
$last_name = $_POST['last_name'];
$first_names = $_POST['first_names'];
$email = $_POST['email'];
$gmt_offset = $_POST['gmt_offset'];

$gmt_offset = ($gmt_offset < 0) || ($gmt_offset > 0) ? $gmt_offset : 0;
$password = md5($password);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into users (role_id, last_name, first_names, username, password, email, gmt_offset, language) values ($role_id, " . $con->qstr($last_name, get_magic_quotes_gpc()) . ", " . $con->qstr($first_names, get_magic_quotes_gpc()) . ", " . $con->qstr($new_username, get_magic_quotes_gpc()) . ", " . $con->qstr($password, get_magic_quotes_gpc()) . ", " . $con->qstr($email, get_magic_quotes_gpc()) . ", $gmt_offset, 'english')";
$con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.4  2004/05/17 17:23:43  braverock
 * - change $username to not conflict when register_globals is on (?!?)
 *   - fixed SF bug 952670 - credit to jmaguire123 and sirjo for troubleshooting
 *
 * Revision 1.3  2004/05/13 16:36:39  braverock
 * - modified to work safely even when register_globals=on
 *   (!?! == dumb administrators ?!?)
 * - changed $user_id to $edit_user_id to avoid security collisions
 *   - fixes multiple reports of user role switching on user edits.
 *
 */
?>