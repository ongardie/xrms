<?php
/**
 * Logout
 *
 * $Id: logout.php,v 1.6 2005/11/27 14:17:20 braverock Exp $
 */

require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
 
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

add_audit_item($con, $session_user_id, 'logout', '', '', 2);

$con->close();

do_hook('logout');

//
// Note: session_start is not needed here, as session_check()
// guarantees we have a valid session
//session_start();

session_unset();
session_destroy();

if ( $msg ) {
  $msg = "?msg=$msg";
}

header("Location: {$http_site_root}/login.php" . $msg);

?>
