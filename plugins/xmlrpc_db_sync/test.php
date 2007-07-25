<?php
#error_reporting(8); // show all warnings (for debugging) 
error_reporting(1); // error reporting set to fatal only 
define("XMLRPC_DEBUG", 1);
include "xmlrpc.inc";
require_once('../../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-companies.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-addresses.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug=1;
//$result= XMLRPC_request('betty.userland.com', '/RPC2', 'examples.getStateName', array(XMLRPC_prepare(41)));
//var_dump($result);
//var_dump( $XMLRPC_debug_print);
//$result= XMLRPC_request('127.0.0.1', '/xrms/plugins/xmlrpc_db_sync/xmlrpc/server.php', 'HelloWorld', array(XMLRPC_prepare(41)));
//var_dump($result);
//var_dump( XMLRPC_debug_print());
//var_dump(XMLRPC_prepare(41));
/*
$data=array(XMLRPC_prepare("Nic"),XMLRPC_prepare("Lowe"),XMLRPC_prepare("niclowe@hotmail.com"));
$result= XMLRPC_request('127.0.0.1', '/xrms/plugins/xmlrpc_db_sync/xmlrpc/server.php', 'xrms_find_contact', $data);
var_dump($result);
var_dump( XMLRPC_debug_print());
*/
/*
$data=array(XMLRPC_prepare("Bushwood Components"));
$result= XMLRPC_request('127.0.0.1', '/xrms/plugins/xmlrpc_db_sync/xmlrpc/server.php', 'xrms_find_company', $data);
var_dump($result);
var_dump( XMLRPC_debug_print());


$data=array(XMLRPC_prepare("contact_id"),XMLRPC_prepare("1"),XMLRPC_prepare("contacts"),XMLRPC_prepare("email"));
$result= XMLRPC_request('127.0.0.1', '/xrms/plugins/xmlrpc_db_sync/xmlrpc/server.php', 'xrms_export', $data);
var_dump($result);
var_dump( XMLRPC_debug_print());

$data=array(XMLRPC_prepare("company_id"),XMLRPC_prepare("4"),XMLRPC_prepare("contacts"),XMLRPC_prepare("email"));
$result= XMLRPC_request('127.0.0.1', '/xrms/plugins/xmlrpc_db_sync/xmlrpc/server.php', 'xrms_export', $data);
var_dump($result);
var_dump( XMLRPC_debug_print());
*/
/*
$company_data['line1']="74 Lennox Street";
$company_data['postal_code']="2042";
$company_data['city']="Newtown";
$company_data['province']="NSW";
//var_dump($company_data);

$address_data=find_address($con,$company_data);
//echo $address_data['address_id'];
*/


//Tests thje prphibited sql functionality
include "xrms_api.php";
$sql="ALTER THIS TABLE";
var_dump(contains_prohibited_sql_strings($sql));
$sql="UPDATE THIS TABLE";
var_dump(contains_prohibited_sql_strings($sql));



?>
