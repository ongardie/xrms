<?php
/**
 * commit a new user to the Database
 *
 * $Id: add-2.php,v 1.8 2004/12/30 19:06:26 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
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

$gmt_offset = ($gmt_offset < 0) || ($gmt_offset > 0) ? $gmt_offset : 0;
$password = md5($password);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//save to database
$rec = array();
$rec['role_id'] = $role_id;
$rec['last_name'] = $last_name;
$rec['first_names'] = $first_names;
$rec['username'] = $new_username;
$rec['password'] = $password;
$rec['email'] = $email;
$rec['gmt_offset'] = $gmt_offset;
$rec['language'] = 'english';

$tbl = 'users';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$rst = $con->execute($ins);

if(!$rst) {
    db_error_handler($con, $ins);
}

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
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