<?php
/**
 * companies/new-2.php - Insert the new company, contact, and address into the database.
 *
 * This page actually does the inserts.
 *
 * @todo add more error handling and feedback here
 *
 * $Id: new-2.php,v 1.20 2005/01/09 14:49:56 braverock Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_name = $_POST['company_name'];
$legal_name = $_POST['legal_name'];
$company_code = $_POST['company_code'];
$crm_status_id = $_POST['crm_status_id'];
$user_id = $_POST['user_id'];
$company_source_id = $_POST['company_source_id'];
$industry_id = $_POST['industry_id'];
$phone = $_POST['phone'];
$phone2 = $_POST['phone2'];
$fax = $_POST['fax'];
$url = $_POST['url'];
$employees = $_POST['employees'];
$revenue = $_POST['revenue'];
$profile = $_POST['profile'];
$custom1 = $_POST['custom1'];
$custom2 = $_POST['custom2'];
$custom3 = $_POST['custom3'];
$custom4 = $_POST['custom4'];
$account_status_id = 1;
$rating_id = 1;

$legal_name = (strlen($legal_name) > 0) ? $legal_name : $company_name;

$company_name = (strlen($company_name) > 0) ? $company_name : _("[none]");

$country_id = $_POST['country_id'];
$address_name = $_POST['address_name'];
$line1 = $_POST['line1'];
$line2 = $_POST['line2'];
$city = $_POST['city'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];
$address_body = $_POST['address_body'];
$use_pretty_address = isset($_POST['use_pretty_address']) ? $_POST['use_pretty_address'] : '';

$first_names = $_POST['first_names'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];

$address_name = (strlen($address_name) > 0) ? $address_name : '[address]';
$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";


$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

//save to database
$rec = array();
$rec['crm_status_id'] = $crm_status_id;
$rec['company_source_id'] = $company_source_id;
$rec['industry_id'] = $industry_id;
$rec['user_id'] = $user_id;
$rec['account_status_id'] = $account_status_id;
$rec['rating_id'] = $rating_id;
$rec['company_name'] = $company_name;
$rec['legal_name'] = $legal_name;
$rec['company_code'] = $company_code;
$rec['phone'] = $phone;
$rec['phone2'] = $phone2;
$rec['fax'] = $fax;
$rec['url'] = $url;
$rec['employees'] = $employees;
$rec['revenue'] = $revenue;
$rec['custom1'] = $custom1;
$rec['custom2'] = $custom2;
$rec['custom3'] = $custom3;
$rec['custom4'] = $custom4;
$rec['profile'] = $profile;
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;
$rec['last_modified_by'] = $session_user_id;
$rec['last_modified_at'] = time();

$tbl = 'companies';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$rst = $con->execute($ins);
if (!$rst) {
    db_error_handler ($con, $ins);
}

$company_id = $con->insert_id();

// if no code was provided, set a default company code
if (strlen($company_code) == 0) {
    $company_code = 'C' . $company_id;
    $sql = "SELECT * FROM companies WHERE company_id = $company_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['company_code'] = $company_code;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    if ($upd) {
        $upd_rst = $con->execute($upd);
        if (!$upd_rst) {
            db_error_handler ($con, $upd);
        }
    }
}

// insert an address
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
$adr_rst = $con->execute($ins);
if (!$adr_rst) {
    db_error_handler ($con, $ins);
}


// make that address the default, and set the customer and vendor references
$address_id = $con->insert_id();

if($time_zone_offset = time_zone_offset($con, $address_id)) {
    $sql = 'SELECT *
            FROM addresses
            WHERE address_id=' . $address_id;
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif(!$rst->EOF) {
        $rec = array();
        $rec['daylight_savings_id'] = $time_zone_offset['daylight_savings_id'];
        $rec['offset'] = $time_zone_offset['offset'];

        $upd = $con->getUpdateSQL($rst, $rec, true, get_magic_quotes_gpc());
        $rst = $con->execute($upd);
        if(!$rst) {
            db_error_handler($con, $sql);
        }
    }
} 

$sql = "SELECT * FROM companies WHERE company_id = $company_id";
$rst = $con->execute($sql);
if (!$rst) {
    db_error_handler ($con, $sql);
}

$rec = array();
$rec['default_primary_address'] = $address_id;
$rec['default_billing_address'] = $address_id;
$rec['default_shipping_address'] = $address_id;
$rec['default_payment_address'] = $address_id;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$upd_rst = $con->execute($upd);
if (!$upd_rst) {
    db_error_handler ($con, $upd);
}


// insert a contact
$rec = array();
$rec['company_id'] = $company_id;
$rec['address_id'] = $address_id;
$rec['first_names'] = $first_names;
$rec['last_name'] = $last_name;
$rec['email'] = $email;
$rec['work_phone'] = $phone;
$rec['fax'] = $fax;
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;
$rec['last_modified_by'] = $session_user_id;
$rec['last_modified_at'] = time();

$tbl = 'contacts';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con_rst = $con->execute($ins);
if (!$con_rst) {
    db_error_handler ($con, $ins);
}

if (strlen($accounting_system) > 0) {
    add_accounting_customer($con, $company_id, $company_name, $company_code, $customer_credit_limit, $customer_terms);
    add_accounting_vendor($con, $company_id, $company_name, $company_code, $vendor_credit_limit, $vendor_terms);
}

add_audit_item($con, $session_user_id, 'created', 'companies', $company_id, 1);

$con->close();

// redirect
header("Location: one.php?msg=company_added&company_id=$company_id");

/**
 * $Log: new-2.php,v $
 * Revision 1.20  2005/01/09 14:49:56  braverock
 * - set company_name to [none] if no company name is provided.
 *
 * Revision 1.19  2004/08/25 14:18:56  neildogg
 * - Daylight savings now applied to all new addresses
 *
 * Revision 1.18  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.17  2004/07/16 12:22:36  braverock
 * - fixed bug in quoting of use_pretty_address
 *
 * Revision 1.16  2004/07/16 10:52:14  braverock
 * - add db_error_handler fn calls around all SQL queries for more error feedback
 *
 * Revision 1.15  2004/07/07 21:53:13  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.14  2004/06/12 17:10:24  gpowers
 * - removed DBTimeStamp() calls for compatibility with GetInsertSQL() and
 *   GetUpdateSQL()
 *
 * Revision 1.13  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.12  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.11  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 *
 * Revision 1.10  2004/02/15 02:13:59  maulani
 * remove quotes from integer values in sql
 *
 * Revision 1.9  2004/02/13 16:40:35  maulani
 * Correct field on contact insert
 *
 * Revision 1.8  2004/02/11 15:26:58  braverock
 * - added qstr around some optional fields
 * - changed sql queries to name=value notation for easier debugging
 * - add phpdoc
 *
 */
?>
