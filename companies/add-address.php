<?php
/**
 * Add an address
 *
 * $Id: add-address.php,v 1.8 2004/07/07 21:53:13 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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
$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

//save to database
$rec = array();
$rec['company_id'] = $company_id;
$rec['country_id'] = $country_id;
$rec['address_name'] = $address_name;
$rec['line1'] = $line1;
$rec['line2'] = $line2;
$rec['city'] = $city;
$rec['province'] = $province;
$rec['postal_code'] = $postal_code;
$rec['address_body'] = $address_body;
$rec['use_pretty_address'] = $use_pretty_address;

$tbl = 'addresses';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$address_id = $con->insert_id();

add_audit_item($con, $session_user_id, 'created', 'addresses', $address_id, 1);

$con->execute($sql);
$con->close();

header("Location: addresses.php?msg=address_added&company_id=$company_id");

/**
 * $Log: add-address.php,v $
 * Revision 1.8  2004/07/07 21:53:13  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.7  2004/06/16 19:22:36  gpowers
 * - removed double quoting from t/f
 *   - did not work with MySQL
 *
 * Revision 1.6  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
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
