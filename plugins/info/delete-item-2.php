<?php
/**
 * Insert item details into the database
 *
 * $Id: delete-item-2.php,v 1.4 2005/02/10 13:42:18 braverock Exp $
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

$tbl = 'info_record';
$rec = array();
$rec['info_record_status'] = 'd';

if (!$con->AutoExecute($tbl, $rec, 'UPDATE', "info_id = $info_id")) {
    db_error_handler ($con, $ins);
}

$con->close();

header("Location: " . $http_site_root . $return_url");

?>
