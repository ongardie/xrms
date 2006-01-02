<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];

$default_primary_address = isset($_POST['default_primary_address']) ? $_POST['default_primary_address'] : '';
if (!$default_primary_address) {$default_primary_address=0;}

$default_billing_address = isset($_POST['default_billing_address']) ? $_POST['default_billing_address'] : '';
if (!$default_billing_address) {$default_billing_address=0;}

$default_shipping_address = isset($_POST['default_shipping_address']) ? $_POST['default_shipping_address'] : '';
if (!$default_shipping_address) {$default_shipping_address=0;}

$default_payment_address = isset($_POST['default_payment_address']) ? $_POST['default_payment_address'] : '';
if (!$default_payment_address) {$default_payment_address=0;}

$con = get_xrms_dbconnection();

// $con->debug=1;

$sql = "SELECT * FROM companies WHERE company_id = $company_id";
$rst = $con->execute($sql);

$rec = array();
$rec['default_primary_address'] = $default_primary_address;
$rec['default_billing_address'] = $default_billing_address;
$rec['default_shipping_address'] = $default_shipping_address;
$rec['default_payment_address'] = $default_payment_address;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

header("Location: addresses.php?msg=saved&company_id=$company_id");

?>