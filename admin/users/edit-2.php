<?php
/**
 * admin/users/edit-2.php - Save changes from user edit
 *
 * Admin changes a user
 *
 * $Id: edit-2.php,v 1.6 2004/05/17 17:23:43 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$edit_user_id = $_POST['edit_user_id'];
$role_id = $_POST['role_id'];
$new_username = $_POST['new_username'];
$last_name = $_POST['last_name'];
$first_names = $_POST['first_names'];
$email = $_POST['email'];
$gmt_offset = $_POST['gmt_offset'];

$gmt_offset = (strlen($gmt_offset) > 0) ? $gmt_offset : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update users set role_id = $role_id, last_name = " . $con->qstr($last_name, get_magic_quotes_gpc()) . ", first_names = " . $con->qstr($first_names, get_magic_quotes_gpc()) . ", username = " . $con->qstr($new_username, get_magic_quotes_gpc()) . ", email = " . $con->qstr($email, get_magic_quotes_gpc()) . ", gmt_offset = $gmt_offset where user_id = $edit_user_id";
$con->execute($sql);

add_audit_item($con, $session_user_id, 'updated', 'users', $edit_user_id, 1);

$con->close();

header("Location: some.php");

/**
 *$Log: edit-2.php,v $
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
 *
 *
 *
 */
?>