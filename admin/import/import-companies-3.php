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
 * @author Brian Peterson
 * @author Chris Woofter
 *
 * @todo put more feedback into the company import process
 * @todo add numeric checks for some of the category import id's
 *
 * $Id: import-companies-3.php,v 1.21 2004/05/06 19:24:56 braverock Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

/**
 * function importFailedMessage - debug function for import
 *
 * @author Mark Spoorendonk
 *
 * @param string  $str
 * @param optional boolean $success default=false
 *
 * @todo Add adodb error handler here... pass &$con as a parameter by reference
 */
function importFailedMessage($str) {
    return importMessage($str, false);
}

/**
 * function debugSQL - debug function for import
 *
 * @author Mark Spoorendonk
 *
 * @param string  $sql
 */
function debugSql($sql) {
    return; // comment out this line for debuging
    echo "<code><pre>";
    print_r($sql);
    echo "</pre></code>\n";
}

/**
 * function importMessage - debug function for import
 *
 * @author Mark Spoorendonk
 *
 * @param string  $str
 * @param optional boolean $success default=true
 */
function importMessage($str, $success=true) {
    return; // comment out this line for debuging
    $color="#ffb0b0"; // red
    if($success) $color="#b0ffb0"; // green
    echo "<div style=\"background-color: $color\">$str</div>\n";
}

$session_user_id = session_check();

$delimiter = $_POST['delimiter'];
$user_id = $_POST['user_id'];
$crm_status_id = $_POST['crm_status_id'];
$company_source_id = $_POST['company_source_id'];
$industry_id = $_POST['industry_id'];
$account_status_id = $_POST['account_status_id'];
$rating_id = $_POST['rating_id'];
$category_id = $_POST['category_id'];
$file_format=$_POST['file_format'];
$template='import-template-' . $file_format . '.php';

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
    require($template);

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

    //echo "<pre>"; print_r($GLOBALS); echo "</pre>"; // debug

    if ((strlen($contact_first_names) == 0) && (strlen($contact_last_name) == 0)) {
        $contact_last_name   = 'Contact';
        $contact_first_names = 'Default';
        importMessage("Creating default contact");
    }

    if (strlen($company_name) > 0) {
        // start putting together our query
        if (!$company_id) {
            $sql_insert_company = "
            insert into companies set
                user_id = $user_id,
                crm_status_id = $crm_status_id,
                company_source_id= $company_source_id,
                industry_id = " . $con->qstr($industry_id) . ",
                account_status_id = $account_status_id,
                rating_id = $rating_id,
                entered_at = " . $con->qstr($entered_at) . ',
                entered_by = ' . $con->qstr($entered_by) . ',
                company_name = '. $con->qstr($company_name) .',';

            importMessage("Created company '$company_name'");
        } else {
            $sql_insert_company = '
            update companies set ';

            importMessage("Updated company '$company_name'");
        }

        $sql_insert_company .=
            "
            company_record_status = 'a' ,
            last_modified_at = " . $con->qstr($last_modified_at) . ',
            last_modified_by = ' . $con->qstr($last_modified_by);

        if ($legal_name) {
            $sql_insert_company .= ',
            legal_name    = '. $con->qstr($legal_name);
        }
        if ($company_website) {
            $sql_insert_company .= ',
            url           = '. $con->qstr($company_website);
        }
        if ($company_taxid) {
            $sql_insert_company .= ',
            tax_id        = '. $con->qstr($company_taxid);
        }
        if ($extref1) {
            $sql_insert_company .= ',
            extref1       = '. $con->qstr($extref1);
        }
        if ($extref2) {
            $sql_insert_company .= ',
            extref2       = '. $con->qstr($extref2);
        }
        if ($extref3) {
            $sql_insert_company .= ',
            extref3       = '. $con->qstr($extref3);
        }
        if ($company_custom1) {
            $sql_insert_company .= ',
            custom1       = '. $con->qstr($company_custom1);
        }
        if ($company_custom2) {
            $sql_insert_company .= ',
            custom2       = '. $con->qstr($company_custom2);
        }
        if ($company_custom3) {
            $sql_insert_company .= ',
            custom3       = '. $con->qstr($company_custom3);
        }
        if ($company_custom4) {
            $sql_insert_company .= ',
            custom4       = '. $con->qstr($company_custom4);
        }
        if ($employees) {
            $sql_insert_company .= ',
            employees     = '. $con->qstr($employees);
        }
        if ($revenue) {
            $sql_insert_company .= ',
            revenue       = '. $con->qstr($revenue);
        }
        if ($credit_limit) {
            $sql_insert_company .= ',
            credit_limit  = '. $con->qstr($credit_limit);
        }
        if ($terms) {
            $sql_insert_company .= ',
            terms         = '. $con->qstr($terms);
        }
        if ($company_profile) {
            $sql_insert_company .= ',
            profile       = '. $con->qstr($company_profile);
        }
        if ($company_code) {
            $sql_insert_company .= ',
            company_code  = '. $con->qstr($company_code);
        }
        //set phone numbers only if the company didn't already exist
        if (!$company_id) {
            if ($company_phone) {
                $sql_insert_company .= ',
                phone     = '. $con->qstr($company_phone);
            }
            if ($company_phone2) {
                $sql_insert_company .= ',
                phone2    = '. $con->qstr($company_phone2);
            }
            if ($company_fax) {
                $sql_insert_company .= ',
                fax       = '. $con->qstr($company_fax);
            }
        }
        //now set the where clause if the company existed
        if ($company_id) {
            $sql_insert_company .= " where company_id = $company_id";
        }

        debugSql($sql_insert_company);
        $con->execute($sql_insert_company);
        $error='';
        $error = $con->ErrorMsg();
        // figure out where to print this out.
        if ($error) {
            echo "<tr><td class=widget_error colspan=54>"
                 ."<br> Unable to insert/update Company $company_name."
                 ."Please correct this error.<br>"
                 . htmlspecialchars($error)
                 ."<br> I tried to execute: <br>"
                 . htmlspecialchars ($create_instrument)
                 ."</td></tr></table>";
            //now skip to the next record
            continue;
        }

        //create the company code if this is a new company
        if (!$company_id) {
            $company_id = $con->insert_id();
            if (!$company_code) {
            $sql_update_company_code = "update companies set
                                        company_code = " .
                                        $con->qstr('C' . $company_id) .
                                        " where company_id = $company_id";
            } else {
                $sql_update_company_code = "update companies set
                                            company_code = " .
                                            $company_code .
                                            " where company_id = $company_id";
            }
            debugSql($sql_update_company_code);
            $con->execute($sql_update_company_code);
        }

        //check to see if we need to insert a division
        if (strlen($division_name) > 0) {
            $sql_insert_division = 'insert into company_division set
                                    division_name = '. $con->qstr($division_name);
            debugSql($sql_insert_division);
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
                                  line1 = '. $con->qstr($address_line1) .' and
                                  city = '. $con->qstr($address_city) ." and
                                  company_id = $company_id";
            debugSql($sql_check_address);
            $rst = $con->execute($sql_check_address);
            if ($rst) {
                $address_id = $rst->fields['address_id'];
                //should probably echo here to indicate that we didn't import this address
            }
            if (!$address_id and $company_id) {
                //figure out a country, because country is required as well
                if ($address_country) {
                    if (!is_numeric($address_country)){
                        // simplify the country to catalize the matching to existing country
                        // example: The Netherlands -> Netherlands
                        $country_simplified=$address_country;
                        $country_simplified=preg_replace("/\\bthe\\b/i", "", $country_simplified);
                        $country_simplified=preg_replace("/\\bunited\\b/i", "", $country_simplified);
                        $country_simplified=preg_replace("/\\bstates\\b/i", "", $country_simplified);
                        $country_simplified=preg_replace("/\\brepublic\\b/i", "", $country_simplified);
                        $country_simplified=preg_replace("/\\bof\\b/i", "", $country_simplified);
                        $country_simplified=trim($country_simplified);

                        $country_sql = "select country_name, country_id from countries
                            where country_record_status = 'a' and
                            country_name like "
                            . $con->qstr('%' .$country_simplified.'%')
                            . " limit 1";
                        debugSql($country_sql);
                        $addrrst = $con->execute($country_sql);
                        if ($addrrst){
                            $address_country = $addrrst->fields('country_id');
                            $addrrst->close();
                            importMessage("Country found: ".$address_country);
                        } else {
                            $address_country = $default_country_id;
                            importFailedMessage("Failed to get country. Using default country");
                        }
                    }
                } else {
                    $address_country = $default_country_id;
                    importFailedMessage("Country not specified. Using default country");
                }

                //insert the new address
                $sql_insert_address = "insert into addresses set
                                   company_id    = $company_id,
                                   address_name  = ". $con->qstr($address_name) .',
                                   line1         = '. $con->qstr($address_line1) .',
                                   line2         = '. $con->qstr($address_line2) .',
                                   city          = '. $con->qstr($address_city) . ',
                                   province      = '. $con->qstr($address_state) . ',
                                   address_body       = '. $con->qstr($address_body) . ',
                                   use_pretty_address = '. $con->qstr($address_use_pretty_address) . ',
                                   postal_code   = '. $con->qstr($address_postal_code) .',
                                   country_id = '. $con->qstr($address_country);
                debugSql($sql_insert_address);
                $con->execute($sql_insert_address);
                importMessage("Imported address '$address_line1'");
                $address_id = $con->insert_id();
            }
        else {
            importFailedMessage("Did not import address '$address_line1'");
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
                debugSql($sql_update_company_set_address_defaults);
                $con->execute($sql_update_company_set_address_defaults);
            }
        } // end address insert

        //check to see if we should insert a contact
        $sql_check_contact = 'select contact_id, first_names, last_name from contacts where
                              first_names = '. $con->qstr($contact_first_names) . ' and
                              last_name   = '. $con->qstr($contact_last_name) . " and
                              company_id  = $company_id" ;
        debugSql($sql_check_contact);
        $rst = $con->execute($sql_check_contact);
        if ($rst) {
            $contact_id = $rst->fields['contact_id'];
            //should probably echo here to indicate that we didn't import this contact
        }
        if (!$contact_id and $company_id) {
        // doesn't exist, create new one
            $sql_insert_contact = "insert into contacts set
                                       company_id  = $company_id,
                                       first_names = ". $con->qstr($contact_first_names) .',
                                       last_name   =' . $con->qstr($contact_last_name) .',
                                       entered_at  =' . $con->qstr($entered_at) .',
                                       entered_by  =' . $con->qstr($entered_by) .',
                                       last_modified_at = ' . $con->qstr($last_modified_at) . ',
                                       last_modified_by = ' . $con->qstr($last_modified_by);
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
                                       work_phone  = '. $con->qstr($contact_work_phone);
            }
            if ($contact_home_phone){
                $sql_insert_contact .= ',
                                       home_phone  = '. $con->qstr($contact_home_phone);
            }
            if ($contact_fax){
                $sql_insert_contact .= ',
                                       fax         = '. $con->qstr($contact_fax);
            }
            if ($contact_email){
                $sql_insert_contact .= ',
                                       email       = '. $con->qstr($contact_email);
            }
            if ($contact_salutation){
                $sql_insert_contact .= ',
                                       salutation       = '. $con->qstr($contact_salutation);
            }
            if ($contact_date_of_birth){
                $sql_insert_contact .= ',
                                       date_of_birth       = '. $con->qstr($contact_date_of_birth);
            }
            if ($contact_summary){
                $sql_insert_contact .= ',
                                       summary       = '. $con->qstr($contact_summary);
            }
            if ($contact_title){
                $sql_insert_contact .= ',
                                       title       = '. $con->qstr($contact_title);
            }
            if ($contact_description){
                $sql_insert_contact .= ',
                                       description       = '. $con->qstr($contact_description);
            }
            if ($contact_cell_phone){
                $sql_insert_contact .= ',
                                       cell_phone       = '. $con->qstr($contact_cell_phone);
            }
            if ($contact_aol){
                $sql_insert_contact .= ',
                                       aol_name       = '. $con->qstr($contact_aol);
            }
            if ($contact_yahoo){
                $sql_insert_contact .= ',
                                       yahoo_name       = '. $con->qstr($contact_yahoo);
            }
            if ($contact_msn){
                $sql_insert_contact .= ',
                                       msn_name       = '. $con->qstr($contact_msn);
            }
            if ($contact_interests){
                $sql_insert_contact .= ',
                                       interests       = '. $con->qstr($contact_interests);
            }
            if ($contact_custom1){
                $sql_insert_contact .= ',
                                       custom1       = '. $con->qstr($contact_custom1);
            }
            if ($contact_custom2){
                $sql_insert_contact .= ',
                                       custom2       = '. $con->qstr($contact_custom2);
            }
            if ($contact_custom3){
                $sql_insert_contact .= ',
                                       custom3       = '. $con->qstr($contact_custom3);
            }
            if ($contact_custom4){
                $sql_insert_contact .= ',
                                       custom4       = '. $con->qstr($contact_custom4);
            }
            if ($contact_profile){
                $sql_insert_contact .= ',
                                       profile       = '. $con->qstr($contact_profile);
            }
            debugSql($sql_insert_contact);
            $con->execute($sql_insert_contact);
            importMessage("Updated contact '$contact_first_names $contact_last_name'");
        } //end insert contact
    else {
        importFailedMessage("Did not update contact '$contact_first_names $contact_last_name'");
    }

        //set the category if we got one
        if ($category_id) {
            //should add an is_numeric check and other logic here

            $sql_insert_category_into_the_companies = "insert into entity_category_map set
                                                        category_id = $category_id,
                                                        on_what_table = 'companies',
                                                        on_what_id = $company_id";
            debugSql($sql_insert_category_into_the_companies);
            $con->execute($sql_insert_category_into_the_companies);
        }

    } // end company_name insert/update check


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
<form action=../data_clean.php method=get>
    <input class=button type=submit value="Run Data Cleanup">
</form>

TILLEND;

end_page();

/**
 * $Log: import-companies-3.php,v $
 * Revision 1.21  2004/05/06 19:24:56  braverock
 * - fixed $country_sql patch
 * - fixed code fomatting
 * - added phpdoc for functions
 * - added todo items
 *
 * Revision 1.20  2004/05/06 19:07:40  braverock
 * - added additional debug information on import scripts
 *   - aaplied SF patch 947578 supplied by Marc Spoorendonk (grmbl)
 *   @todo: add adodb error handlers to SQL queries
 *
 * Revision 1.19  2004/05/03 13:41:44  braverock
 * - missing comma in address insert
 *   fixes bug reported by Stephan in Germany
 *
 * Revision 1.18  2004/04/21 05:08:36  braverock
 * - remove get_magic_quotes_gpc() from qstr param list,
 *   it is unecessary in this context, and actively harmful
 *    see: http://phplens.com/adodb/reference.functions.qstr.html
 *
 * Revision 1.17  2004/04/21 05:02:48  braverock
 * - add data cleanup button to end of import process
 *
 * Revision 1.16  2004/04/19 19:36:16  braverock
 * - fix syntax of address_country checks
 *
 * Revision 1.15  2004/04/19 14:21:54  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
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