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
 * $Id: routing.php,v 1.1 2004/03/12 15:46:51 maulani Exp $
 */

//where do we include from
require_once('../include-locations.inc');

//get required common files
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

//check to make sure we are logged on
$session_user_id = session_check();

//make our database connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//$con->debug = 1;

// define our query
$sql = "select r.role_short_name as role
        from roles r, users u
        where u.role_id=r.role_id
        and u.user_id = $session_user_id";

//execute
$rst = $con->execute($sql);

$role = $rst->fields['role'];

//close the connection
$con->close();

//if this is a mailto link, try to open the user's default mail application
if ($role == 'Admin') {
    header("Location: " . $http_site_root . "/admin/index.php");
} elseif ($role == 'Developer') {
    header("Location: " . $http_site_root . "/admin/index.php");
} else {
    header("Location: " . $http_site_root . "/admin/users/self.php");
}

/**
 *$Log: routing.php,v $
 *Revision 1.1  2004/03/12 15:46:51  maulani
 *Temporary change for use until full access control is implemented
 *- Block non-admin users from the administration screen
 *- Allow all users to modify their own user record and password
 *- Add phpdoc
 *
 *
 */
?>