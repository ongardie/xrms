<?php
/**
 * Database updates for Edit address for a contact
 *
 * $Id: edit-address-2.php,v 1.5 2004/07/07 21:59:47 introspectshun Exp $
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
$contact_id = $_POST['contact_id'];
$country_id = $_POST['country_id'];
$address_name = $_POST['address_name'];
$address_body = $_POST['address_body'];
$line1 = $_POST['line1'];
$line2 = $_POST['line2'];
$city = $_POST['city'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];
$use_pretty_address = $_POST['use_pretty_address'];
$new = $_POST['new'];
$alt_address = $_POST['alt_address'];

$use_pretty_address = ($use_pretty_address == 'on') ? "'t'" : "'f'";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($alt_address) {
    $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_id'] = $alt_address;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
    
    add_audit_item($con, $session_user_id, 'changed address', 'contacts', $contact_id, 1);
} elseif ($address_id && !$new) {
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

    add_audit_item($con, $session_user_id, 'updated', 'addresses', $address_id, 1);

} else {

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

    $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_id'] = $address_id;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    add_audit_item($con, $session_user_id, 'changed address', 'contacts', $contact_id, 1);
}

$con->close();


header("Location: edit-address.php?msg=saved&contact_id=$contact_id");


/**
 * $Log: edit-address-2.php,v $
 * Revision 1.5  2004/07/07 21:59:47  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.4  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.3  2004/06/10 18:07:28  gpowers
 * - added processing for "Use Alternate Address" section
 *
 * Revision 1.2  2004/06/09 17:36:09  gpowers
 * - added $Id: edit-address-2.php,v 1.5 2004/07/07 21:59:47 introspectshun Exp $Log: tags.
 *
 * Revision 1.1  2004/06/09 16:52:14  gpowers
 * - Contact Address Editing
 * - adapted from companies/edit-address.php
 *
 */

?>
