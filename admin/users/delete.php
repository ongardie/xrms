<?php
/**
 * Remove a user form the system
 *
 * $Id: delete.php,v 1.2 2004/05/13 16:36:46 braverock Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$edit_user_id = $_POST['edit_user_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update users set user_record_status = 'd' where user_id = $edit_user_id";
$con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.2  2004/05/13 16:36:46  braverock
 * - modified to work safely even when register_globals=on
 *   (!?! == dumb administrators ?!?)
 * - changed $user_id to $edit_user_id to avoid security collisions
 *   - fixes multiple reports of user role switching on user edits.
 *
 */
?>