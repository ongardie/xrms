<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$address_id = $_POST['address_id'];
$company_id = $_POST['company_id'];
$country_id = $_POST['country_id'];
$address_name = $_POST['address_name'];
$address_body = $_POST['address_body'];
$line1 = $_POST['line1'];
$line2 = $_POST['line2'];
$city = $_POST['city'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];
$use_pretty_address = $_POST['use_pretty_address'];

$use_pretty_address = ($use_pretty_address == 'on') ? "'t'" : "'f'";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update addresses set country_id = $country_id, line1 = " . $con->qstr($line1, get_magic_quotes_gpc()) . ", line2 = " . $con->qstr($line2, get_magic_quotes_gpc()) . ", city = " . $con->qstr($city, get_magic_quotes_gpc()) . ", province = " . $con->qstr($province, get_magic_quotes_gpc()) . ", postal_code = " . $con->qstr($postal_code, get_magic_quotes_gpc()) . ", address_name = " . $con->qstr($address_name, get_magic_quotes_gpc()) . ", address_body = " . $con->qstr($address_body, get_magic_quotes_gpc()) . ", use_pretty_address = $use_pretty_address where address_id = $address_id";
$con->execute($sql);

header("Location: addresses.php?msg=saved&company_id=$company_id");

?>