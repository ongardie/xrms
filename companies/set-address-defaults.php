<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$default_primary_address = $_POST['default_primary_address'];
$default_billing_address = $_POST['default_billing_address'];
$default_shipping_address = $_POST['default_shipping_address'];
$default_payment_address = $_POST['default_payment_address'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update companies set default_primary_address = $default_primary_address, default_billing_address = $default_billing_address, default_shipping_address = $default_shipping_address, default_payment_address = $default_payment_address where company_id = $company_id";

$con->execute($sql);

header("Location: addresses.php?msg=saved&company_id=$company_id");

?>
