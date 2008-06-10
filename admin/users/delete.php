<?php
/**
 * Remove a user form the system
 *
 * $Id: delete.php,v 1.6 2008/06/10 20:24:17 randym56 Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$edit_user_id = $_POST['edit_user_id'];
if ($edit_user_id <> '1') {
	$set_user_id = isset($_POST['user_id']) ? $_POST['user_id'] : 1;

	$con = get_xrms_dbconnection();

	$sql = "SELECT * FROM users WHERE user_id = $edit_user_id";
	$rst = $con->execute($sql);

	$rec = array();
	$rec['user_record_status'] = 'd';

	$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
	$con->execute($upd);

	//move all contact records to admin user
	$sql = "UPDATE contacts SET user_id = $set_user_id WHERE user_id = $edit_user_id";
	$con->execute($sql);

	//move all company records to admin user
	$sql = "UPDATE companies SET user_id = $set_user_id WHERE user_id = $edit_user_id";
	$con->execute($sql);

	//move all activity records to admin user
	$sql = "UPDATE activities SET user_id = $set_user_id WHERE user_id = $edit_user_id";
	$con->execute($sql);

	$con->close();
	$msg = "User deleted";
	} else $msg = "Cannot Delete User 1";

header("Location: some.php?msg=".$msg);

/**
 * $Log: delete.php,v $
 * Revision 1.6  2008/06/10 20:24:17  randym56
 * - Add ability to deactivate users without setting the 'd'elete flag (set to 'b') so that the records don't get purged.
 * - Add function to move all Company, Contact & Activity records to alternate user when deleting the user to maintain DB integrity.
 *
 * Revision 1.5  2006/01/02 22:09:39  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/07/16 23:51:38  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 22:50:14  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/05/13 16:36:46  braverock
 * - modified to work safely even when register_globals=on
 *   (!?! == dumb administrators ?!?)
 * - changed $user_id to $edit_user_id to avoid security collisions
 *   - fixes multiple reports of user role switching on user edits.
 *
 */
?>
