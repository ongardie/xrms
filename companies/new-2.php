<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_name = $_POST['company_name'];
$company_legal_name = $_POST['company_legal_name'];
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

$company_legal_name = (strlen($company_legal_name) > 0) ? $company_legal_name : $company_name;

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

$sql = "insert into companies (crm_status_id, company_source_id, industry_id, user_id, account_status_id, rating_id, company_name, company_legal_name, company_code, phone, phone2, fax, url, employees, revenue, custom1, custom2, custom3, custom4, profile, entered_at, entered_by, last_modified_at, last_modified_by)
        values ($crm_status_id, $company_source_id, $industry_id, $user_id, $account_status_id, $rating_id, " . $con->qstr($company_name, get_magic_quotes_gpc()) . ", " . $con->qstr($company_legal_name, get_magic_quotes_gpc()) . ", " . $con->qstr($company_code, get_magic_quotes_gpc()) . ", " . $con->qstr($phone, get_magic_quotes_gpc()) . ", " . $con->qstr($phone2, get_magic_quotes_gpc()) . ", " . $con->qstr($fax, get_magic_quotes_gpc()) . ", " . $con->qstr($url, get_magic_quotes_gpc()) . ", " . $con->qstr($employees, get_magic_quotes_gpc()) . ", " . $con->qstr($revenue, get_magic_quotes_gpc()) . ", " . $con->qstr($custom1, get_magic_quotes_gpc()) . ", " . $con->qstr($custom2, get_magic_quotes_gpc()) . ", " . $con->qstr($custom3, get_magic_quotes_gpc()) . ", " .$con->qstr($custom4, get_magic_quotes_gpc()) . ", " . $con->qstr($profile, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";
$con->execute($sql);
$company_id = $con->insert_id();

// if no code was provided, set a default company code
if (strlen($company_code) == 0) {
    $company_code = 'C' . $company_id;
    $con->execute("update companies set company_code = " . $con->qstr($company_code, get_magic_quotes_gpc()) . " where company_id = $company_id");
}

// insert an address
$sql = "insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body, use_pretty_address) values ($company_id, $country_id, " . $con->qstr($address_name, get_magic_quotes_gpc()) . ", " . $con->qstr($line1, get_magic_quotes_gpc()) . ", " . $con->qstr($line2, get_magic_quotes_gpc()) . ", " . $con->qstr($city, get_magic_quotes_gpc()) . ", " . $con->qstr($province, get_magic_quotes_gpc()) . ", " . $con->qstr($postal_code, get_magic_quotes_gpc()) . ", " . $con->qstr($address_body, get_magic_quotes_gpc()) . ", $use_pretty_address)";
$con->execute($sql);

// make that address the default, and set the customer and vendor references
$address_id = $con->insert_id();
$con->execute("update companies set default_primary_address = $address_id, default_billing_address = $address_id, default_shipping_address = $address_id, default_payment_address = $address_id where company_id = $company_id");

// insert a contact
$sql_insert_contact = "insert into contacts (company_id, address_id, first_names, last_name, email, work_phone, fax, entered_at, entered_by, last_modified_at, last_modified_by) values ($company_id, $address_id, " . $con->qstr($first_names, get_magic_quotes_gpc()) . ", " . $con->qstr($last_name, get_magic_quotes_gpc()) . ", " . $con->qstr($email, get_magic_quotes_gpc()) . ", " . $con->qstr($phone, get_magic_quotes_gpc()) . ", " . $con->qstr($fax, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";
$con->execute($sql_insert_contact);

if (strlen($accounting_system) > 0) {
    add_accounting_customer($con, $company_id, $company_name, $company_code, $customer_credit_limit, $customer_terms);
    add_accounting_vendor($con, $company_id, $company_name, $company_code, $vendor_credit_limit, $vendor_terms);
}

$con->close();

// redirect
header("Location: one.php?msg=company_added&company_id=$company_id");

?>
