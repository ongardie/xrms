<?php
/**
 * WebForm Plugin - new-form.php
 *
 * @author Nic Lowe
 *
 * @todo integrate template
 * @todo integrate form email
 * @todo add opportunity
 *
 * $Id: new-form.php,v 1.1 2004/06/26 14:39:30 braverock Exp $
 */

//VARIABLES
//Do i mail the form to an administrator?
$email_to_admin=true;
$email_admin_address="nic@newtowncarshare.info";
//Do i respond with a template?
$email_respond=true;
$email_to_address=$_POST['email'];
$email_template_id=1;//blank template
$email_template_from="nic@newtowncarshare.info";
//Do I add an activity automatically? If so, what sort of activity and who by?
$add_activity=true;
$activity_type_id=4;//email from
$activity_status="o";
$activity_title="Website Email";
$activity_description = $_POST['YourMessage'];
$response_time='+2 DAYS';//the time you have in PLAIN ENGLISH to respond to an email
//Do I add an opportunity? If so, what sort and how much etc?
$add_opportunity=true;
$add_opportunity_status_id=1;//new
$add_opportunity_title="Membership";
$add_opportunity_description="Membership";
$add_opportunity_probability="20";
$add_opportunity_size="225";

//mail($email_address,"Response from Newtown CarShare Contact Us Form",$msg2,"From: bruce@newtowncarshare.info");

// receives POST's from your web site and imports the company into XRMS, then redirects back to your web site

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$company_type_id = $_POST['company_type_id'];
$crm_status_id = $_POST['crm_status_id'];
$industry_id = $_POST['industry_id'];
$company_source_id = $_POST['company_source_id'];

//stolen from new-2.php
//simply post to this file, and you'll add a new company

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
$con->debug = 1;

//check to see if this company already exists, or if this contact already exists
//if the company exists, then skip inserting a new company
//if the contact exists, then skip inserting a new contact
//probably should update company/contact - TODO

$sql="SELECT company_id,default_primary_address FROM COMPANIES WHERE
                                                 company_name = '$company_name' and company_record_status <>'d';";
$rst=$con->execute($sql);

if(!$rst->RecordCount()){
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
  if(!isset($address_id)){
                                echo "The address of this company hasnt been loaded - there is a company record, but it will not appear in XRMS";
                                exit;
  }
  $con->execute("update companies set default_primary_address = $address_id,
                                      default_billing_address = $address_id,
                                      default_shipping_address = $address_id,
                                      default_payment_address = $address_id
                                      where company_id = $company_id");
}else{
        //YOU HAVE A PRE_EXISTING COMPANY IN THE DATABASE
        $company_id=$rst->fields['company_id'];
        $address_id=$rst->fields['default_primary_address'];

}
$rst->close();
//CHECK TO SEE IF YOU HAVE A PRE-EXISTING CONTACT WHO works for this company
$sql="SELECT contact_id,email, first_names,last_name FROM CONTACTS WHERE
                                                 email = '$email' and
                                                 company_id = '$company_id' and
                                                 first_names = '$first_names' and
                                                 last_name = '$last_name';";
$rst=$con->execute($sql);

if(!$rst->RecordCount()){

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
  $contact_id=$con->insert_id();
}else{
        //YOU HAVE A PRE_EXISTING CONTACT IN THE DATABASE
        $contact_id=$rst->fields['contact_id'];
}
$rst->close();

// insert an activity
$sql_insert_activity = "insert into activities set
                        activity_type_id = $activity_type_id,
                        user_id = $user_id,
                        company_id = $company_id,
                        contact_id = $contact_id,
                        activity_title = '$activity_title',
                        activity_description = '$activity_description',
                        entered_at = ".$con->dbtimestamp(mktime()).",
                        scheduled_at=".$con->dbtimestamp(mktime()).",
                        ends_at=".$con->dbtimestamp(strtotime($response_time)).",
                        entered_by = $session_user_id;";
if ($add_activity)$con->execute($sql_insert_activity);


if (strlen($accounting_system) > 0) {
    add_accounting_customer($con, $company_id, $company_name, $company_code, $customer_credit_limit, $customer_terms);
    add_accounting_vendor($con, $company_id, $company_name, $company_code, $vendor_credit_limit, $vendor_terms);
}

add_audit_item($con, $session_user_id, 'created', 'companies', $company_id, 1);

$con->close();

//header("Location: $after_adding_new_companies_from_your_web_site_redirect_to_this_page");

/**
 * $Log: new-form.php,v $
 * Revision 1.1  2004/06/26 14:39:30  braverock
 * - Initial Revision of WebForm Plugin by Nic Lowe
 *   - added phpdoc
 *   - standardized on long php tags
 *
 */
?>