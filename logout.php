<?php
/**
 * Logout
 *
 * $Id: logout.php,v 1.2 2004/05/07 21:30:39 maulani Exp $
 */

require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

add_audit_item($con, $session_user_id, 'logout', '', '', 2);

$con->close();

session_start();
session_unset();
session_destroy();

header("Location: {$http_site_root}/login.php");

?>
