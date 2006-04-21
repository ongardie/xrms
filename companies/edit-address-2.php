<?php
/**
 * Database updates for Edit address for a company
 *
 * $Id: edit-address-2.php,v 1.13 2006/04/21 22:05:02 braverock Exp $
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
$address_type = $_POST['address_type'];
$use_pretty_address = $_POST['use_pretty_address'];

$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

$con = get_xrms_dbconnection();
// $con->debug=1;

$rec = array();
$rec['country_id'] = $country_id;
$rec['line1'] = $line1;
$rec['line2'] = $line2;
$rec['city'] = $city;
$rec['province'] = $province;
$rec['postal_code'] = $postal_code;
$rec['address_type'] = $address_type;
$rec['address_name'] = $address_name;
$rec['address_body'] = $address_body;
$rec['use_pretty_address'] = $use_pretty_address;

$result = update_address($con, $rec, false, get_magic_quotes_gpc());
if ($result) {
    add_audit_item($con, $session_user_id, 'updated', 'addresses', $address_id, 1);
} else {
    $msg=urlencode(_("Updating Address Failed"));
    header("Location: addresses.php?msg=$msg&company_id=$company_id");
}

$param = array( $_POST, $result, $rec);
do_hook_function('company_edit_address_2', $param);

$con->close();

header("Location: addresses.php?msg=saved&company_id=$company_id");

/**
 * $Log: edit-address-2.php,v $
 * Revision 1.13  2006/04/21 22:05:02  braverock
 * - update to use update_address fn
 *
 * Revision 1.11  2006/04/21 20:26:07  braverock
 * - modify to use addresses API
 *
 * Revision 1.10  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.9  2005/06/15 18:37:53  ycreddy
 * Added a plugin hook 'company_edit_address_2'
 *
 * Revision 1.8  2005/04/11 02:06:48  maulani
 * - Add address type.  RFE 862049 (maulani)
 *
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
