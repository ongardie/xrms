<?php
/**
 * Add an address
 *
 * $Id: add-address.php,v 1.5 2004/05/10 13:09:14 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$country_id = $_POST['country_id'];
$address_name = $_POST['address_name'];
$line1 = $_POST['line1'];
$line2 = $_POST['line2'];
$city = $_POST['city'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];
$address_body = $_POST['address_body'];
$use_pretty_address = $_POST['use_pretty_address'];

$address_name = (strlen($address_name) > 0) ? $address_name : '[address]';
$use_pretty_address = ($use_pretty_address == 'on') ? "'t'" : "'f'";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body, use_pretty_address) values ($company_id, $country_id, " . $con->qstr($address_name, get_magic_quotes_gpc()) . ", " . $con->qstr($line1, get_magic_quotes_gpc()) . ", " . $con->qstr($line2, get_magic_quotes_gpc()) . ", " . $con->qstr($city, get_magic_quotes_gpc()) . ", " . $con->qstr($province, get_magic_quotes_gpc()) . ", " . $con->qstr($postal_code, get_magic_quotes_gpc()) . ", " . $con->qstr($address_body, get_magic_quotes_gpc()) . ", $use_pretty_address)";

$address_id = $con->insert_id();

add_audit_item($con, $session_user_id, 'created', 'addresses', $address_id, 1);

$con->execute($sql);
$con->close();

header("Location: addresses.php?msg=address_added&company_id=$company_id");

/**
 * $Log: add-address.php,v $
 * Revision 1.5  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.4  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 *
 */
?>