<?php
/**
 * companies/new-2.php - Insert the new company, contact, and address into the database.
 *
 * This page actually does the inserts.
 *
 * @todo add more error handling and feedback here
 *
 * $Id: new-2.php,v 1.12 2004/05/10 13:09:14 maulani Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
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

$country_id = $_POST['country_id'];
$address_name = $_POST['address_name'];
$line1 = $_POST['line1'];
$line2 = $_POST['line2'];
$city = $_POST['city'];
$province = $_POST['province'];
$postal_code = $_POST['postal_code'];
$address_body = $_POST['address_body'];
$use_pretty_address = $_POST['use_pretty_address'];

$first_names = $_POST['first_names'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];

$address_name = (strlen($address_name) > 0) ? $address_name : '[address]';
$use_pretty_address = ($use_pretty_address == 'on') ? "'t'" : "'f'";


$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into companies set
                    crm_status_id = $crm_status_id, 
                    company_source_id = $company_source_id,
                    industry_id = $industry_id,
                    user_id = $user_id,
                    account_status_id = $account_status_id,
                    rating_id = $rating_id,
                    company_name = ". $con->qstr($company_name, get_magic_quotes_gpc()). ',
                    legal_name = ' . $con->qstr($legal_name, get_magic_quotes_gpc()) . ',
                    company_code = '. $con->qstr($company_code, get_magic_quotes_gpc()) . ',
                    phone = '. $con->qstr($phone, get_magic_quotes_gpc()) . ',
                    phone2 = '. $con->qstr($phone2, get_magic_quotes_gpc()) .',
                    fax = '. $con->qstr($fax, get_magic_quotes_gpc()) .',
                    url = '. $con->qstr($url, get_magic_quotes_gpc()) .',
                    employees = '. $con->qstr($employees, get_magic_quotes_gpc()) .',
                    revenue = '. $con->qstr($revenue, get_magic_quotes_gpc()) .',
                    custom1 = '. $con->qstr($custom1, get_magic_quotes_gpc()) .',
                    custom2 = '. $con->qstr($custom2, get_magic_quotes_gpc()) .',
                    custom3 = '. $con->qstr($custom3, get_magic_quotes_gpc()) .',
                    custom4 = '. $con->qstr($custom4, get_magic_quotes_gpc()) .',
                    profile = '. $con->qstr($profile, get_magic_quotes_gpc()) .',
                    entered_at = '. $con->dbtimestamp(mktime()) .",
                    entered_by = $session_user_id,
                    last_modified_by = $session_user_id,
                    last_modified_at = " . $con->dbtimestamp(mktime());

$con->execute($sql);
$company_id = $con->insert_id();

// if no code was provided, set a default company code
if (strlen($company_code) == 0) {
    $company_code = 'C' . $company_id;
    $con->execute("update companies set
                          company_code = " . $con->qstr($company_code, get_magic_quotes_gpc()) . "
                          where company_id = $company_id");
}

// insert an address
$sql = "insert into addresses set
               company_id = $company_id,
               country_id = $country_id,
               address_name = " . $con->qstr($address_name, get_magic_quotes_gpc()) . ',
               line1 = '. $con->qstr($line1, get_magic_quotes_gpc()) . ',
               line2 = '. $con->qstr($line2, get_magic_quotes_gpc()) . ',
               city = '. $con->qstr($city, get_magic_quotes_gpc()) . ',
               province = '. $con->qstr($province, get_magic_quotes_gpc()) .',
               postal_code = '. $con->qstr($postal_code, get_magic_quotes_gpc()) . ',
               address_body = '. $con->qstr($address_body, get_magic_quotes_gpc()) .",
               use_pretty_address  = $use_pretty_address";

$con->execute($sql);

// make that address the default, and set the customer and vendor references
$address_id = $con->insert_id();
$con->execute("update companies set default_primary_address = $address_id,
                                    default_billing_address = $address_id,
                                    default_shipping_address = $address_id,
                                    default_payment_address = $address_id
                                    where company_id = $company_id");

// insert a contact
$sql_insert_contact = "insert into contacts set
                              company_id = $company_id,
                              address_id = $address_id,
                              first_names = ". $con->qstr($first_names, get_magic_quotes_gpc()) .',
                              last_name = '. $con->qstr($last_name, get_magic_quotes_gpc()) .',
                              email = '. $con->qstr($email, get_magic_quotes_gpc()) .',
                              work_phone = '. $con->qstr($phone, get_magic_quotes_gpc()) .',
                              fax = '. $con->qstr($fax, get_magic_quotes_gpc()) .',
                              entered_at = '. $con->dbtimestamp(mktime()) .",
                              entered_by = $session_user_id,
                              last_modified_by = $session_user_id,
                              last_modified_at = " . $con->dbtimestamp(mktime());

$con->execute($sql_insert_contact);

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