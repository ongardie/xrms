<?php
/**
 * import-companies-3.php - File importer for XRMS
 *
 * The three import-companies files in XRMS allow users or administrators
 * to import new companies and contacts into XRMS
 *
 * The first page, import-companies.php, displays several options that
 * will be common to all imported companies, such as source and initial status,
 * and allows the user to select the file to be imported,
 * and the delimiter to be used.
 *
 * The second page, import-copanies-2.php, allows the user to preview the data,
 * and performs the first round of validation on the data.
 *
 * The third page, import-companies-3.php, actually imports the data and stuffs it
 * into the database.  This is where the final data validation is done,
 * and data is compared against existing data.
 *
 * @author Chris Woofter
 * @author Brian Peterson
 *
 * @todo could better accomodate microsoft Outlook by looking for outlook field names
 *
 * $Id: import-companies-3.php,v 1.14 2004/04/16 22:18:25 maulani Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$delimiter = $_POST['delimiter'];
$user_id = $_POST['user_id'];
$crm_status_id = $_POST['crm_status_id'];
$company_source_id = $_POST['company_source_id'];
$industry_id = $_POST['industry_id'];
$account_status_id = $_POST['account_status_id'];
$rating_id = $_POST['rating_id'];
$category_id = $_POST['category_id'];

$pointer = (strlen($_POST['pointer']) > 0) ? $_POST['pointer'] : 0;

$page_title = "Import Data";

start_page($page_title, true, $msg);

echo <<<TILLEND

<table border=0 cellpadding=0 cellspacing=0 width=100%>
   <tr>
       <table class=widget cellspacing=1>
           <tr>
               <td class=widget_header colspan=54>Preview Data</td>
           </tr>

       <tr>
           <!-- base company info //-->
           <td class=widget_header colspan=4>Company</td>

           <!-- contact info //-->
           <td class=widget_header colspan=22>Contact Info</td>

           <!-- address info //-->
           <td class=widget_header colspan=9>Address</td>

           <!-- address info //-->
           <td class=widget_header colspan=19>Additional Company Info</td>
       </tr>

       <tr>
           <td class=widget_content>Row Number</td>

           <!-- base company info //-->
           <td class=widget_content>Company ID</td>
           <td class=widget_content>Company Name</td>
           <td class=widget_content>Division Name</td>

           <!-- contact info //-->
           <td class=widget_content>Contact ID</td>
           <td class=widget_content>First Names</td>
           <td class=widget_content>Last Name</td>
           <td class=widget_content>Email</td>
           <td class=widget_content>Work Phone</td>
           <td class=widget_content>Cell Phone</td>
           <td class=widget_content>Home Phone</td>
           <td class=widget_content>Fax</td>
           <td class=widget_content>Division</td>
           <td class=widget_content>Salutation</td>
           <td class=widget_content>Date of Birth</td>
           <td class=widget_content>Summary</td>
           <td class=widget_content>Title</td>
           <td class=widget_content>Description</td>
           <td class=widget_content>AOL</td>
           <td class=widget_content>Yahoo</td>
           <td class=widget_content>MSN</td>
           <td class=widget_content>Interests</td>
           <td class=widget_content>Custom 1</td>
           <td class=widget_content>Custom 2</td>
           <td class=widget_content>Custom 3</td>
           <td class=widget_content>Custom 4</td>
           <td class=widget_content>Profile</td>

           <!-- address info //-->
           <td class=widget_content>Address Name</td>
           <td class=widget_content>Line 1</td>
           <td class=widget_content>Line 2</td>
           <td class=widget_content>City</td>
           <td class=widget_content>State</td>
           <td class=widget_content>Postal Code</td>
           <td class=widget_content>Country</td>
           <td class=widget_content>Address Body</td>
           <td class=widget_content>Use Pretty Address</td>

           <!-- extra company info //-->
           <td class=widget_content>Code</td>
           <td class=widget_content>Phone</td>
           <td class=widget_content>Alt. Phone</td>
           <td class=widget_content>Fax</td>
           <td class=widget_content>Website</td>
           <td class=widget_content>Legal Name</td>
           <td class=widget_content>Tax ID</td>
           <td class=widget_content>External Ref 1</td>
           <td class=widget_content>External Ref 1</td>
           <td class=widget_content>External Ref 1</td>
           <td class=widget_content>Custom 1</td>
           <td class=widget_content>Custom 2</td>
           <td class=widget_content>Custom 3</td>
           <td class=widget_content>Custom 4</td>
           <td class=widget_content>No. Employees</td>
           <td class=widget_content>Revenue</td>
           <td class=widget_content>Credit Limit</td>
           <td class=widget_content>Terms</td>
           <td class=widget_content>Profile/Notes</td>

       </tr>
TILLEND;

switch ($delimiter) {
    case 'comma':
        $delimiter = ",";
        break;
    case 'tab':
        $delimiter = "\t";
        break;
    case 'pipe':
        $delimiter = "|";
        break;
    case 'semi-colon':
        $delimiter = ";";
        break;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$row_number = 1;

//this will create a huge ammount of debug data
//$con->debug=1;

$entered_at = date("Y-m-d H:i:s");
$last_modified_at = date("Y-m-d H:i:s");
$entered_by = $session_user_id;
$last_modified_by = $session_user_id;

//get the data array
$filearray = CSVtoArray($tmp_upload_directory . 'companies-to-import.txt', true , $delimiter, $enclosure);
// @todo could better accomodate microsoft outlook by looking for outlook field names

//debug line to view the array
//echo "\n<br><pre>". print_r ($filearray). "\n</pre>";

//fill up our variables from the array, where they exist
foreach ($filearray as $row) {
    //clear our working variables
    $company_id = 0;
    $default_address_id = 0;
    $division_id = 0;
    $address_name = 0;
    $address_id = 0;

    //debug line to view the array
    //echo "\n<br><pre>". print_r ($row). "\n</pre>";


    //assign array values to variables

    //company info
    $company_name        = $row['company_name'];
    $legal_name          = $row['legal_name'];
    $division_name       = $row['division_name'];
    $company_website     = $row['website'];
    $company_taxid       = $row['tax_id'];
    $extref1             = $row['extref1'];
    $extref2             = $row['extref2'];
    $extref3             = $row['extref3'];
    $company_custom1     = $row['company_custom1'];
    $company_custom2     = $row['company_custom2'];
    $company_custom3     = $row['company_custom3'];
    $company_custom4     = $row['company_custom4'];
    $employees           = $row['employees'];
    $revenue             = $row['revenue'];
    $credit_limit        = $row['credit_limit'];
    $terms               = $row['terms'];
    $company_profile     = $row['company_profile'];
    $company_code        = $row['company_code'];
    $company_phone       = $row['phone'];
    $company_phone2      = $row['phone2'];
    $company_fax         = $row['fax'];

    //contact info
    $contact_first_names   = $row['first_name'];
    $contact_last_name     = $row['last_name'];
    $contact_email         = htmlspecialchars($row['email']);
    $contact_work_phone    = $row['work_phone'];
    $contact_home_phone    = $row['home_phone'];
    $contact_fax           = $row['fax'];
    $contact_division      = $row['division'];
    $contact_salutation    = $row['salutation'];
    $contact_date_of_birth = $row['date_of_birth'];
    $contact_summary       = $row['summary'];
    $contact_title         = $row['title'];
    $contact_description   = $row['description'];
    $contact_cell_phone    = $row['cell_phone'];
    $contact_aol           = $row['aol'];
    $contact_yahoo         = $row['yahoo'];
    $contact_msn           = $row['msn'];
    $contact_interests     = $row['interests'];
    $contact_custom1       = $row['contact_custom1'];
    $contact_custom2       = $row['contact_custom2'];
    $contact_custom3       = $row['contact_custom3'];
    $contact_custom4       = $row['contact_custom4'];
    $contact_profile       = $row['contact_profile'];

    //address info
    $address_name               = $row['address_name'];
    $address_line1              = $row['line1'];
    $address_line2              = $row['line2'];
    $address_city               = $row['city'];
    $address_state              = $row['state'];
    $address_postal_code        = $row['postal_code'];
    $address_country            = $row['country'];
    $address_body               = $row['address_body'];
    $address_use_pretty_address = $row['use_pretty_address'];


    // does this company exist,
    $company_id  = fetch_company_id($con, $company_name);
    if (!$company_id) { $company_id=''; };

    // and if so what is its default address...?
    if ($company_id) {
        $default_address_id  = fetch_default_address($con, $company_id);
        if (strlen($division_name) > 0) {
            //does this division exist?
            $division_id = fetch_division_id($con, $division_name, $company_id);
        }
    }

    if ((strlen($contact_first_names) == 0) && (strlen($contact_last_name) == 0)) {
        $contact_last_name   = 'Contact';
        $contact_first_names = 'Default';
    }

    if (strlen($company_name) > 0) {
        // start putting together our query
        if (!$company_id) {
            $sql_insert_company = "
            insert into companies set
                user_id = $user_id,
                crm_status_id = $crm_status_id,
                company_source_id= $company_source_id,
                industry_id = " . $con->qstr($industry_id, get_magic_quotes_gpc()) . ",
                account_status_id = $account_status_id,
                rating_id = $rating_id,
                entered_at = " . $con->qstr($entered_at, get_magic_quotes_gpc()) . ',
                entered_by = ' . $con->qstr($entered_by, get_magic_quotes_gpc()) . ',
                company_name = '. $con->qstr($company_name, get_magic_quotes_gpc()) .',';
        } else {
            $sql_insert_company = '
            update companies set ';
        }

        $sql_insert_company .=
            "
            company_record_status = 'a' ,
            last_modified_at = " . $con->qstr($last_modified_at, get_magic_quotes_gpc()) . ',
            last_modified_by = ' . $con->qstr($last_modified_by, get_magic_quotes_gpc());

        if ($legal_name) {
            $sql_insert_company .= ',
            legal_name    = '. $con->qstr($legal_name, get_magic_quotes_gpc());
        }
        if ($company_website) {
            $sql_insert_company .= ',
            url           = '. $con->qstr($company_website, get_magic_quotes_gpc());
        }
        if ($company_taxid) {
            $sql_insert_company .= ',
            tax_id        = '. $con->qstr($company_taxid, get_magic_quotes_gpc());
        }
        if ($extref1) {
            $sql_insert_company .= ',
            extref1       = '. $con->qstr($extref1, get_magic_quotes_gpc());
        }
        if ($extref2) {
            $sql_insert_company .= ',
            extref2       = '. $con->qstr($extref2, get_magic_quotes_gpc());
        }
        if ($extref3) {
            $sql_insert_company .= ',
            extref3       = '. $con->qstr($extref3, get_magic_quotes_gpc());
        }
        if ($company_custom1) {
            $sql_insert_company .= ',
            custom1       = '. $con->qstr($company_custom1, get_magic_quotes_gpc());
        }
        if ($company_custom2) {
            $sql_insert_company .= ',
            custom2       = '. $con->qstr($company_custom2, get_magic_quotes_gpc());
        }
        if ($company_custom3) {
            $sql_insert_company .= ',
            custom3       = '. $con->qstr($company_custom3, get_magic_quotes_gpc());
        }
        if ($company_custom4) {
            $sql_insert_company .= ',
            custom4       = '. $con->qstr($company_custom4, get_magic_quotes_gpc());
        }
        if ($employees) {
            $sql_insert_company .= ',
            employees     = '. $con->qstr($employees, get_magic_quotes_gpc());
        }
        if ($revenue) {
            $sql_insert_company .= ',
            revenue       = '. $con->qstr($revenue, get_magic_quotes_gpc());
        }
        if ($credit_limit) {
            $sql_insert_company .= ',
            credit_limit  = '. $con->qstr($credit_limit, get_magic_quotes_gpc());
        }
        if ($terms) {
            $sql_insert_company .= ',
            terms         = '. $con->qstr($terms, get_magic_quotes_gpc());
        }
        if ($company_profile) {
            $sql_insert_company .= ',
            profile       = '. $con->qstr($company_profile, get_magic_quotes_gpc());
        }
        if ($company_code) {
            $sql_insert_company .= ',
            company_code  = '. $con->qstr($company_code, get_magic_quotes_gpc());
        }
        //set phone numbers only if the company didn't already exist
        if (!$company_id) {
            if ($company_phone) {
                $sql_insert_company .= ',
                phone     = '. $con->qstr($company_phone, get_magic_quotes_gpc());
            }
            if ($company_phone2) {
                $sql_insert_company .= ',
                phone2    = '. $con->qstr($company_phone2, get_magic_quotes_gpc());
            }
            if ($company_fax) {
                $sql_insert_company .= ',
                fax       = '. $con->qstr($company_fax, get_magic_quotes_gpc());
            }
        }
        //now set the where clause if the company existed
        if ($company_id) {
            $sql_insert_company .= " where company_id = $company_id";
        }

        $con->execute($sql_insert_company);

        //create the company code if this is a new company
        if (!$company_id) {
            $company_id = $con->insert_id();
            if (!$company_code) {
            $sql_update_company_code = "update companies set
                                        company_code = " .
                                        $con->qstr('C' . $company_id, get_magic_quotes_gpc()) .
                                        " where company_id = $company_id";
            } else {
                $sql_update_company_code = "update companies set
                                            company_code = " .
                                            $company_code .
                                            " where company_id = $company_id";
            }
            $con->execute($sql_update_company_code);
        }

        //check to see if we need to insert a division
        if (strlen($division_name) > 0) {
            $sql_insert_division = 'insert into company_division set
                                    division_name = '. $con->qstr($division_name, get_magic_quotes_gpc());
            $con->execute($sql_insert_division);
            $division_id = $con->insert_id();
        }

        //insert new address
        if ($address_city) {
            //city is required, can't think of a simpler requirement

            //if we don't have an address name, assign the city as the name
            if (!$address_name) {$address_name = $address_city;}

            // now check to see if we already have an address that matches line1 and city
            $sql_check_address = 'select address_id from addresses where
                                  line1 = '. $con->qstr($address_line1, get_magic_quotes_gpc()) .' and
                                  city = '. $con->qstr($address_city, get_magic_quotes_gpc()) ." and
                                  company_id = $company_id";
            $rst = $con->execute($sql_check_address);
            if ($rst) {
                $address_id = $rst->fields['address_id'];
                //should probably echo here to indicate that we didn't import this address
            }
            if (!$address_id and $company_id) {
                //insert the new address
                $sql_insert_address = "insert into addresses set
                                   company_id    = $company_id,
                                   address_name  = ". $con->qstr($address_name, get_magic_quotes_gpc()) .',
                                   line1         = '. $con->qstr($address_line1, get_magic_quotes_gpc()) .',
                                   line2         = '. $con->qstr($address_line2, get_magic_quotes_gpc()) .',
                                   city          = '. $con->qstr($address_city, get_magic_quotes_gpc()) . ',
                                   province      = '. $con->qstr($address_state, get_magic_quotes_gpc()) . ',
                                   country_id         = '. $con->qstr($address_country, get_magic_quotes_gpc()) . ',
                                   address_body       = '. $con->qstr($address_body, get_magic_quotes_gpc()) . ',
                                   use_pretty_address = '. $con->qstr($address_use_pretty_address, get_magic_quotes_gpc()) . ',
                                   postal_code   = '. $con->qstr($address_postal_code, get_magic_quotes_gpc());
                $con->execute($sql_insert_address);
                $address_id = $con->insert_id();
            }
            // if we don't have a default address, set them now
            // this is kind of naive first through the post choosing, but oh well
            if (!$default_address_id  && $address_id) {
                $sql_update_company_set_address_defaults = "
                    update companies set
                        default_primary_address = $address_id,
                        default_billing_address = $address_id,
                        default_shipping_address = $address_id,
                        default_payment_address = $address_id
                        where company_id = $company_id";
                $con->execute($sql_update_company_set_address_defaults);
            }
        } // end address insert

        //check to see if we should insert a contact
        $sql_check_contact = 'select contact_id, first_names, last_name from contacts where
                              first_names = '. $con->qstr($contact_first_names, get_magic_quotes_gpc()) . ' and
                              last_name   = '. $con->qstr($contact_last_name, get_magic_quotes_gpc()) . "and
                              company_id  = $company_id" ;
        $rst = $con->execute($sql_check_contact);
        if ($rst) {
            $contact_id = $rst->fields['contact_id'];
            //should probably echo here to indicate that we didn't import this contact
        }
        if (!$contact_id and $company_id) {
            $sql_insert_contact = "insert into contacts set
                                       company_id  = $company_id,
                                       first_names = ". $con->qstr($contact_first_names, get_magic_quotes_gpc()) .',
                                       last_name   =' . $con->qstr($contact_last_name, get_magic_quotes_gpc()) .',
                                       entered_at  =' . $con->qstr($entered_at, get_magic_quotes_gpc()) .',
                                       entered_by  =' . $con->qstr($entered_by, get_magic_quotes_gpc()) .',
                                       last_modified_at = ' . $con->qstr($last_modified_at, get_magic_quotes_gpc()) . ',
                                       last_modified_by = ' . $con->qstr($last_modified_by, get_magic_quotes_gpc());
            if ($address_id) {
                $sql_insert_contact .= ',
                                       address_id  = '. $con->qstr($address_id,  get_magic_quotes_gpc()) ;
            }
            if ($division_id){
                $sql_insert_contact .= ',
                                       division_id = '. $con->qstr($division_id,  get_magic_quotes_gpc());
            }
            if ($contact_work_phone){
                $sql_insert_contact .= ',
                                       work_phone  = '. $con->qstr($contact_work_phone, get_magic_quotes_gpc());
            }
            if ($contact_home_phone){
                $sql_insert_contact .= ',
                                       home_phone  = '. $con->qstr($contact_home_phone, get_magic_quotes_gpc());
            }
            if ($contact_fax){
                $sql_insert_contact .= ',
                                       fax         = '. $con->qstr($contact_fax, get_magic_quotes_gpc());
            }
            if ($contact_email){
                $sql_insert_contact .= ',
                                       email       = '. $con->qstr($contact_email, get_magic_quotes_gpc());
            }
            if ($contact_salutation){
                $sql_insert_contact .= ',
                                       salutation       = '. $con->qstr($contact_salutation, get_magic_quotes_gpc());
            }
            if ($contact_date_of_birth){
                $sql_insert_contact .= ',
                                       date_of_birth       = '. $con->qstr($contact_date_of_birth, get_magic_quotes_gpc());
            }
            if ($contact_summary){
                $sql_insert_contact .= ',
                                       summary       = '. $con->qstr($contact_summary, get_magic_quotes_gpc());
            }
            if ($contact_title){
                $sql_insert_contact .= ',
                                       title       = '. $con->qstr($contact_title, get_magic_quotes_gpc());
            }
            if ($contact_description){
                $sql_insert_contact .= ',
                                       description       = '. $con->qstr($contact_description, get_magic_quotes_gpc());
            }
            if ($contact_cell_phone){
                $sql_insert_contact .= ',
                                       cell_phone       = '. $con->qstr($contact_cell_phone, get_magic_quotes_gpc());
            }
            if ($contact_aol){
                $sql_insert_contact .= ',
                                       aol_name       = '. $con->qstr($contact_aol, get_magic_quotes_gpc());
            }
            if ($contact_yahoo){
                $sql_insert_contact .= ',
                                       yahoo_name       = '. $con->qstr($contact_yahoo, get_magic_quotes_gpc());
            }
            if ($contact_msn){
                $sql_insert_contact .= ',
                                       msn_name       = '. $con->qstr($contact_msn, get_magic_quotes_gpc());
            }
            if ($contact_interests){
                $sql_insert_contact .= ',
                                       interests       = '. $con->qstr($contact_interests, get_magic_quotes_gpc());
            }
            if ($contact_custom1){
                $sql_insert_contact .= ',
                                       custom1       = '. $con->qstr($contact_custom1, get_magic_quotes_gpc());
            }
            if ($contact_custom2){
                $sql_insert_contact .= ',
                                       custom2       = '. $con->qstr($contact_custom2, get_magic_quotes_gpc());
            }
            if ($contact_custom3){
                $sql_insert_contact .= ',
                                       custom3       = '. $con->qstr($contact_custom3, get_magic_quotes_gpc());
            }
            if ($contact_custom4){
                $sql_insert_contact .= ',
                                       custom4       = '. $con->qstr($contact_custom4, get_magic_quotes_gpc());
            }
            if ($contact_profile){
                $sql_insert_contact .= ',
                                       profile       = '. $con->qstr($contact_profile, get_magic_quotes_gpc());
            }
            $con->execute($sql_insert_contact);
        } //end insert contact

        //set the category if we got one
        if ($category_id) {
            $sql_insert_category_into_the_companies = "insert into entity_category_map set
                                                        category_id = $category_id,
                                                        on_what_table = 'companies',
                                                        on_what_id = $company_id";
            $con->execute($sql_insert_category_into_the_companies);
        }

    } //end check for company_name


    //now show the row
    echo <<<TILLEND
       <tr>
           <td class=widget_content>$row_number</td>

           <!-- base company info //-->
           <td class=widget_content>$company_id</td>
           <td class=widget_content>$company_name</td>
           <td class=widget_content>$division_name</td>

           <!-- contact info //-->
           <td class=widget_content>$contact_id</td>
           <td class=widget_content>$contact_first_names</td>
           <td class=widget_content>$contact_last_name</td>
           <td class=widget_content>$contact_email</td>
           <td class=widget_content>$contact_work_phone</td>
           <td class=widget_content>$contact_cell_phone</td>
           <td class=widget_content>$contact_home_phone</td>
           <td class=widget_content>$contact_fax</td>
           <td class=widget_content>$contact_division</td>
           <td class=widget_content>$contact_salutation</td>
           <td class=widget_content>$contact_date_of_birth</td>
           <td class=widget_content>$contact_summary</td>
           <td class=widget_content>$contact_title</td>
           <td class=widget_content>$contact_description</td>
           <td class=widget_content>$contact_aol</td>
           <td class=widget_content>$contact_yahoo</td>
           <td class=widget_content>$contact_msn</td>
           <td class=widget_content>$contact_interests</td>
           <td class=widget_content>$contact_custom1</td>
           <td class=widget_content>$contact_custom2</td>
           <td class=widget_content>$contact_custom3</td>
           <td class=widget_content>$contact_custom4</td>
           <td class=widget_content>$contact_profile</td>

           <!-- address info //-->
           <td class=widget_content>$address_name</td>
           <td class=widget_content>$address_line1</td>
           <td class=widget_content>$address_line2</td>
           <td class=widget_content>$address_city</td>
           <td class=widget_content>$address_state</td>
           <td class=widget_content>$address_postal_code</td>
           <td class=widget_content>$address_country</td>
           <td class=widget_content>$address_body</td>
           <td class=widget_content>$address_use_pretty_address</td>

           <!-- extra company info //-->
           <td class=widget_content>$company_code</td>
           <td class=widget_content>$company_phone</td>
           <td class=widget_content>$company_phone2</td>
           <td class=widget_content>$company_fax</td>
           <td class=widget_content>$company_website</td>
           <td class=widget_content>$legal_name</td>
           <td class=widget_content>$company_taxid</td>
           <td class=widget_content>$extref1</td>
           <td class=widget_content>$extref2</td>
           <td class=widget_content>$extref3</td>
           <td class=widget_content>$company_custom1</td>
           <td class=widget_content>$company_custom2</td>
           <td class=widget_content>$company_custom3</td>
           <td class=widget_content>$company_custom4</td>
           <td class=widget_content>$employees</td>
           <td class=widget_content>$revenue</td>
           <td class=widget_content>$credit_limit</td>
           <td class=widget_content>$terms</td>
           <td class=widget_content>$company_profile</td>

       </tr>

TILLEND;



    $row_number = $row_number + 1;
}; //end foreach, loop back and do the next row.

$con->close();


    echo <<<TILLEND

        </table>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=33% valign=top>

        </td>
    </tr>
</table>

TILLEND;

end_page();

/**
 * $Log: import-companies-3.php,v $
 * Revision 1.14  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.13  2004/04/09 22:08:38  braverock
 * - allow import of all fields in the XRMS database
 * - integrated patches provided by Olivier Colonna of Fontaine Consulting
 *
 * Revision 1.12  2004/02/10 22:45:56  braverock
 * - fixed typo noticed by Beth
 *
 * Revision 1.11  2004/02/10 22:26:49  braverock
 * - fixed syntax error when updating data on existing company
 *
 * Revision 1.10  2004/02/10 17:15:14  braverock
 * - added extra error checknig around phone import
 *
 * Revision 1.9  2004/02/10 16:57:24  braverock
 * - fixed address line 2 insert
 *
 * Revision 1.8  2004/02/10 16:38:13  braverock
 * - fixed error in $sql_insert_company syntax
 *
 * Revision 1.7  2004/02/10 13:43:17  braverock
 * - fixed error in division import
 *
 * Revision 1.6  2004/02/10 13:31:44  braverock
 * - change url to 'website'
 * - fixed syntax errror on insert
 *
 * Revision 1.5  2004/02/04 18:39:58  braverock
 * - major update to import functionality
 * - add phpdoc
 *
 */
?>