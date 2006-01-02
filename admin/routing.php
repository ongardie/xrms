<?php
/**
 * admin/routing.php - This page routes users who choose Administration
 *
 * Folks who have a role of User only get to modify themselves
 * Folks who have a role of Admin or Developer get the master page.
 *
 * This is intended as a temporary solution until full access control is introduced
 * in XRMS.
 *
 * $Id: routing.php,v 1.10 2006/01/02 22:38:16 vanmer Exp $
 */

//where do we include from
require_once('../include-locations.inc');

//get required common files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//check to make sure we are logged on
$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

//make our database connection
$con = get_xrms_dbconnection();

//$con->debug = 1;

// close the database connection
$con->close();

$role = $_SESSION['role_short_name'];

if ( $msg ) {
  $msg = "?msg=$msg";
}

//if this is a mailto link, try to open the user's default mail application
if (check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
  header("Location: " . $http_site_root . "/admin/index.php"      . $msg);
} else {
  header("Location: " . $http_site_root . "/admin/users/self.php" . $msg);
}

/**
 *$Log: routing.php,v $
 *Revision 1.10  2006/01/02 22:38:16  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.9  2005/05/18 05:54:55  vanmer
 *- changed to reference ACL for administration routing, instead of SESSION variable
 *
 *Revision 1.8  2004/07/20 12:45:21  cpsource
 *- Allow non-Admin users to change their passwords, but do so
 *  in a secure manner.
 *
 *Revision 1.7  2004/07/20 11:40:05  cpsource
 *- Fixed multiple errors
 *   misc undefined variables being used, g....
 *   non Admin users could end up at some.php and effect other users
 *   made self.php goto self-2.php instead of edit-2.php
 *   non Admin users can now admin their own user name only.
 *   added a successful update promit to private/index.php
 *
 *Revision 1.6  2004/07/20 10:43:16  cpsource
 *- Moved SESSION['role'] to SESSION['role_short_name']
 *  role is now set in login-2.php instead of admin/routing.php
 *  utils-misc.php updated to check session with role_short_name
 *
 *Revision 1.5  2004/07/16 18:52:43  cpsource
 *- Add role check inside of session_check
 *
 *Revision 1.4  2004/07/16 15:13:05  cpsource
 *- Prevent non-Admin's from running admin/index.php
 *
 *Revision 1.3  2004/07/16 12:10:04  cpsource
 *- Add $role from routing to SESSION so that index.php
 *  can check we are Admin or Developer before we
 *  allow users to run admin.
 *
 *  Without this check, someone who's not logged in as anything
 *  can point their browser at xrms/admin/index.php and run
 *  the script.
 *
 *Revision 1.2  2004/06/14 18:13:51  introspectshun
 *- Add adodb-params.php include for multi-db compatibility.
 *- Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 *Revision 1.1  2004/03/12 15:46:51  maulani
 *Temporary change for use until full access control is implemented
 *- Block non-admin users from the administration screen
 *- Allow all users to modify their own user record and password
 *- Add phpdoc
 *
 *
 */
?>