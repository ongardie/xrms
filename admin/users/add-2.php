<?php
/**
 * commit a new user to the Database
 *
 * $Id: add-2.php,v 1.13 2006/01/02 22:09:39 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-users.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$role_id = $_POST['role_id'];
$new_username = $_POST['new_username'];
$password = $_POST['password'];
$last_name = $_POST['last_name'];
$first_names = $_POST['first_names'];
$email = $_POST['email'];
$gmt_offset = $_POST['gmt_offset'];
if (array_key_exists('allowed_p', $_POST)) $enabled=true;
else $enabled=false;

$con = get_xrms_dbconnection();
$error_msg='';
$user_id=add_xrms_user($con, $new_username, $password, $role_id, $first_names, $last_name, $email, $gmt_offset, $enabled, false, $error_msg);
$con->close();
if (!$user_id) {
    $msg="Failed to add user: $error_msg.";
    header("Location: some.php?msg=$msg&role_id=$role_id&new_username=$new_username&last_name=$last_name&first_names=$first_names&email=$email&gmt_offset=$gmt_offset&allowed_p=$enabled");
    exit;
}

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.13  2006/01/02 22:09:39  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.12  2005/09/26 01:20:03  vanmer
 * - added parameters to inputs for new user in some.php, so that passed in values will be displayed
 * - added passback of entered parameters from add-2.php if error occurs when adding the user
 *
 * Revision 1.11  2005/05/18 05:51:24  vanmer
 * - changed to call add user function with silent parameter, error message variable
 *
 * Revision 1.10  2005/02/10 23:45:05  vanmer
 * -altered to use new add_xrms_user function
 * -altered to use enabled flag to set user account status
 *
 * Revision 1.9  2005/01/13 17:56:13  vanmer
 * - added new ACL code to user management section
 *
 * Revision 1.8  2004/12/30 19:06:26  braverock
 * - add db_error_handler
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.7  2004/07/16 23:51:38  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.6  2004/07/15 22:23:53  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.5  2004/06/14 22:50:14  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
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