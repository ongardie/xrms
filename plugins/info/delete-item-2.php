<?php
/**
 * Insert item details into the database
 *
 * $Id: delete-item-2.php,v 1.3 2005/01/08 07:55:55 gpowers Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

require_once('info.inc');

$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check();

$msg = $_GET['msg'];

# Always retrieve, and pass on, server and company ID
$info_id = $_GET['info_id'];
$company_id = $_GET['company_id'];
$info_type_id = $_GET['info_type_id'];
$return_url = $_GET['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$sql = "UPDATE info SET info_record_status = 'd' WHERE info_id = " . $info_id;
$con->execute($sql);

$con->close();

header("Location: " . $http_site_root . $return_url");

?>
