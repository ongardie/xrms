<?php
/**
 * admin/users/self-2.php - Save changes from user edit
 *
 * Save the changes from a user-level self-change
 *
 * $Id: self-2.php,v 1.8 2006/01/02 22:09:39 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$edit_user_id = $_POST['edit_user_id'];
$last_name    = $_POST['last_name'];
$first_names  = $_POST['first_names'];
$email        = $_POST['email'];
$gmt_offset   = $_POST['gmt_offset'];

$gmt_offset = (strlen($gmt_offset) > 0) ? $gmt_offset : 0;

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM users WHERE user_id = $edit_user_id";
$rst = $con->execute($sql);

$rec = array();

$rec['last_name']   = $last_name;
$rec['first_names'] = $first_names;
$rec['email']       = $email;
$rec['gmt_offset']  = $gmt_offset;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'updated', 'users', $edit_user_id, 1);

$con->close();

header("Location: " . $http_site_root . "/private/home.php?msg=saved");

/**
 *$Log: self-2.php,v $
 *Revision 1.8  2006/01/02 22:09:39  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.7  2004/07/20 11:40:06  cpsource
 *- Fixed multiple errors
 *   misc undefined variables being used, g....
 *   non Admin users could end up at some.php and effect other users
 *   made self.php goto self-2.php instead of edit-2.php
 *   non Admin users can now admin their own user name only.
 *   added a successful update promit to private/index.php
 *
 *Revision 1.6  2004/07/16 23:51:38  cpsource
 *- require session_check ( 'Admin' )
 *
 *Revision 1.5  2004/06/14 22:50:14  introspectshun
 *- Add adodb-params.php include for multi-db compatibility.
 *- Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 *Revision 1.4  2004/05/13 16:36:46  braverock
 *- modified to work safely even when register_globals=on
 *  (!?! == dumb administrators ?!?)
 *- changed $user_id to $edit_user_id to avoid security collisions
 *  - fixes multiple reports of user role switching on user edits.
 *
 *Revision 1.3  2004/05/10 20:54:31  maulani
 *- Fix bug 951490.  Unprivileged users will now return to the home screen
 *  after modifying their user records.
 *
 *Revision 1.2  2004/05/10 13:07:20  maulani
 *- Add level to audit trail
 *- Clean up audit trail text
 *
 *Revision 1.1  2004/03/12 16:34:31  maulani
 *- Add audit trail
 *- Add phpdoc
 */
?>