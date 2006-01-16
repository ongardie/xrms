<?php

/**

 * WebForm Plugin - new-form.php

 *

 * @author Nic Lowe

 *

 * @todo add opportunity

 *

 * $Id: new-form.php,v 1.6 2006/01/16 16:00:37 niclowe Exp $

 */

session_start();
$_SESSION['session_user_id'] = $_POST['user_id'];

include_once('vars_webform.inc');
// receives POST's from your web site and imports the company into XRMS, then redirects back to your web site



require_once('../../include-locations.inc');



require_once($include_directory . 'vars.php');

require_once($include_directory . 'utils-interface.php');

require_once($include_directory . 'utils-misc.php');

require_once($include_directory . 'adodb/adodb.inc.php');

require_once($include_directory . 'utils-accounting.php');
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;


//FIRST THING, MAIL THE FORM AND THE RESPONSE JUST IN CASE IT SCREWS UP
$email = $_POST['email'];
//send form to sender
$form_input_data.= "Form Variables input into XRMS\n";
//what data do you put in the email
$data_in_email=array("first_names","last_name","email","company_name","phone","line1","line2","postal_code","city","province","YourMessage");

foreach ($_POST as $key => $val){
if(in_array($key, $data_in_email))$form_input_data.= "$key: $val\n";
}
//send response to sender
$sql="SELECT email_template_title, email_template_body from email_templates where email_template_id=$email_template_id";
$rst=$con->execute($sql);
if($email_respond)mail($email,$rst->fields['email_template_title'],$rst->fields['email_template_body'],"From: $email_template_from");


//RECEIVE THE POSTS INTO THE FORM 
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



//check to see if this company already exists, or if this contact already exists

//if the company exists, then skip inserting a new company

 

//if the contact exists, then skip inserting a new contact

//probably should update company/contact - TODO



$sql="SELECT company_id,default_primary_address FROM companies 
		WHERE
     company_name = '$company_name'
		 and company_record_status <>'d';";

//if the company field is set blank, use a slightly different sql so that you dont add a blank company by accident
if(empty($company_name)){
				$sql="SELECT company_id,default_primary_address FROM companies 
				WHERE
        company_name = CONCAT('$first_names',' ','$last_name')
				and company_record_status <>'d';";
				};


$rst=$con->execute($sql);



if(!$rst->RecordCount()){
	//if you dont have a company name because its a completely
	if($add_person_as_company&&empty($company_name))$company_name=$first_names.' '.$last_name;
  
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


$sql="SELECT contact_id,email, first_names,last_name FROM contacts WHERE

                                                 email = '$email' and

                                                 company_id = '$company_id' and

                                                 first_names = '$first_names' and

                                                 last_name = '$last_name';";

$rst=$con->execute($sql);


if(!$rst->RecordCount()){
//then you dont have a contact who works for this company 


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

$rst->close;



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

//add url to bottom of form_input_data to allow clicking of link inside email client to direct to XRMS to view/edit contact / company
$url_to_company='VIEW LINK: '.$http_site_root.'/companies/one.php?company_id='.$company_id." \n";
$url_to_company.='EDIT LINK: '.$http_site_root.'/companies/edit.php?company_id='.$company_id;
$form_input_data=$form_input_data.$url_to_company;
$subj="$app_title - Contact Us Form Submitted";
if($email_to_admin)mail($email_admin_address,$subj,$form_input_data,"From: $email");


$con->close();



header("Location: $after_adding_new_companies_from_your_web_site_redirect_to_this_page");



/**

 * $Log: new-form.php,v $
 * Revision 1.6  2006/01/16 16:00:37  niclowe
 * fixed errant bug commit
 *
 * Revision 1.4  2004/08/30 05:50:38  niclowe
 * fixed bug where you no session was registered - thereby making the whole form useless (ie it didnt put the data in the database unless you were logged into XRMS - not good for anonymous website forms)
 * Sorry for those who have used this and not realised (it took me a while to figure it out myself..)
 *
 * Revision 1.3  2004/08/15 07:08:27  niclowe
 * Reduced number of form variables (no hidden ones anymore) emailed to admin user.
 *
 * Revision 1.2  2004/07/16 03:34:04  niclowe
 * initial upload
 *
 * Revision 1.1  2004/06/26 14:39:30  braverock
 * - Initial Revision of WebForm Plugin by Nic Lowe
 *   - added phpdoc
 *   - standardized on long php tags
 *

 */

?>