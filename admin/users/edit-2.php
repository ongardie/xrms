<?php
/**
 * admin/users/edit-2.php - Save changes from user edit
 *
 * Admin changes a user
 *
 * $Id: edit-2.php,v 1.12 2004/12/30 19:06:58 braverock Exp $
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

$gmt_offset = (strlen($gmt_offset) > 0) ? $gmt_offset : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM users WHERE user_id = $edit_user_id";
$rst = $con->execute($sql);

$rec = array();

$rec['role_id']         = $role_id;
$rec['user_contact_id'] = $user_contact_id;
$rec['last_name']       = $last_name;
$rec['first_names']     = $first_names;
$rec['username']        = $new_username;
$rec['email']           = $email;
$rec['gmt_offset']      = $gmt_offset;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$rst = $con->execute($upd);

if(!$rst) {
    db_error_handler($con, $upd);
}

add_audit_item($con, $session_user_id, 'updated', 'users', $edit_user_id, 1);

$con->close();

if ( $_SESSION['role_short_name'] == 'Admin' ) {
  header("Location: some.php");
}else{
header("Location: self.php?msg=saved");
}

/**
 *$Log: edit-2.php,v $
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