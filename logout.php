<?php
/**
 * Logout
 *
 * $Id: logout.php,v 1.7 2006/01/02 23:23:09 vanmer Exp $
 */

require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
 
$con = get_xrms_dbconnection();
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
