<?php
/**
 * Logout
 *
 * $Id: logout.php,v 1.3 2004/07/13 18:27:58 cpsource Exp $
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

//
// Note: session_start is not needed here, as session_check()
// guarantees we have a valid session
//session_start();

session_unset();
session_destroy();

header("Location: {$http_site_root}/login.php");

?>
