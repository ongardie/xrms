<?php
/**
 * Database updates for Edit address for a company
 *
 * $Id: edit-address-2.php,v 1.7 2004/06/16 22:09:10 introspectshun Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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

$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug=1;

$sql = "SELECT * FROM addresses WHERE address_id = $address_id";
$rst = $con->execute($sql);

$rec = array();
$rec['country_id'] = $country_id;
$rec['line1'] = $line1;
$rec['line2'] = $line2;
$rec['city'] = $city;
$rec['province'] = $province;
$rec['postal_code'] = $postal_code;
$rec['address_name'] = $address_name;
$rec['address_body'] = $address_body;
$rec['use_pretty_address'] = $use_pretty_address;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: addresses.php?msg=saved&company_id=$company_id");

/**
 * $Log: edit-address-2.php,v $
 * Revision 1.7  2004/06/16 22:09:10  introspectshun
 * - removed double quoting from t/f
 *
 * Revision 1.6  2004/06/16 18:30:22  gpowers
 * - added (commented out) debug line
 *
 * Revision 1.5  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.4  2004/06/09 17:39:51  gpowers
 * - added $Id and $Log tags
 *
*/
?>
