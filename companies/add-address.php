<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$address_name = $_POST['address_name'];
$address_body = $_POST['address_body'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "INSERT INTO addresses (company_id, address_name, address_body) values ($company_id, " . $con->qstr($address_name, get_magic_quotes_gpc()) . ", " . $con->qstr($address_body, get_magic_quotes_gpc()) . ")";

$con->execute($sql);
$con->close();

header("Location: addresses.php?msg=address_added&company_id=$company_id");

?>
