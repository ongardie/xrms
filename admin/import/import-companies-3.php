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
 * The second page, import-companies-2.php, allows the user to preview the data,
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
 * $Id: import-companies-3.php,v 1.27 2004/08/25 14:18:45 neildogg Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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

$session_user_id = session_check( 'Admin' );

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
            //Set defaults for INSERT
            $rec = array();
            $rec['user_id'] = $user_id;
            $rec['crm_status_id'] = $crm_status_id;
            $rec['company_source_id'] = $company_source_id;
            $rec['industry_id'] = $industry_id;
            $rec['account_status_id'] = $account_status_id;
            $rec['rating_id'] = $rating_id;
            $rec['entered_at'] = $entered_at;
            $rec['entered_by'] = $entered_by;
            $rec['company_name'] = $company_name;

            importMessage("Created company '$company_name'");
        } else {
            //Empty array for UPDATE
            $rec = array();

            importMessage("Updated company '$company_name'");
        }

        $rec['company_record_status'] = 'a';
        $rec['last_modified_at'] = $last_modified_at;
        $rec['last_modified_by'] = $last_modified_by;

        if ($legal_name) {
            $rec['legal_name'] = $legal_name;
        }
        if ($company_website) {
            $rec['url'] = $company_website;
        }
        if ($company_taxid) {
            $rec['tax_id'] = $company_taxid;
        }
        if ($extref1) {
            $rec['extref1'] = $extref1;
        }
        if ($extref2) {
            $rec['extref2'] = $extref2;
        }
        if ($extref3) {
            $rec['extref3'] = $extref3;
        }
        if ($company_custom1) {
            $rec['custom1'] = $company_custom1;
        }
        if ($company_custom2) {
            $rec['custom2'] = $company_custom2;
        }
        if ($company_custom3) {
            $rec['custom3'] = $company_custom3;
        }
        if ($company_custom4) {
            $rec['custom4'] = $company_custom4;
        }
        if ($employees) {
            $rec['employees'] = $employees;
        }
        if ($revenue) {
            $rec['revenue'] = $revenue;
        }
        if ($credit_limit) {
            $rec['credit_limit'] = $credit_limit;
        }
        if ($terms) {
            $rec['terms'] = $terms;
        }
        if ($company_profile) {
            $rec['profile'] = $company_profile;
        }
        if ($company_code) {
            $rec['company_code'] = $company_code;
        }
        //set phone numbers only if the company didn't already exist
        if (!$company_id) {
            if ($company_phone) {
                $rec['phone'] = $company_phone;
            }
            if ($company_phone2) {
                $rec['phone2'] = $company_phone2;
            }
            if ($company_fax) {
                $rec['fax'] = $company_fax;
            }
        }
        if ($company_id) {
            //UPDATE
            $sql = "SELECT * FROM companies WHERE company_id = $company_id";
            $rst = $con->execute($sql);

            $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
            debugSql($upd);
            if (strlen($upd)>0) {
                $con->execute($upd);
            }
        } else {
            //INSERT
            $tbl = 'companies';
            $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
            debugSql($ins);
            $con->execute($ins);
        }

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
                $rec = array();
                $rec['company_code'] = 'C' . $company_id;
            } else {
                $rec = array();
                $rec['company_code'] = $company_code;
            }

            $sql = "SELECT * FROM companies WHERE company_id = $company_id";
            $rst = $con->execute($sql);
            $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
            debugSql($upd);
            if (strlen($upd)>0) {
                $con->execute($upd);
            }
        }

        //check to see if we need to insert a division
        if (strlen($division_name) > 0) {
            $rec = array();
            $rec['division_name'] = $division_name;

            $tbl = 'company_division';
            $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
            debugSql($ins);
            $con->execute($ins);

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
                $rec = array();
                $rec['company_id'] = $company_id;
                $rec['address_name'] = $address_name;
                $rec['line1'] = $address_line1;
                $rec['line2'] = $address_line2;
                $rec['city'] = $address_city;
                $rec['province'] = $address_state;
                $rec['address_body'] = $address_body;
                $rec['use_pretty_address'] = $address_use_pretty_address;
                $rec['postal_code'] = $address_postal_code;
                $rec['country_id'] = $address_country;

                $tbl = 'addresses';
                $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
                debugSql($ins);
                $con->execute($ins);
                
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

                importMessage("Imported address '$address_line1'");
                $address_id = $con->insert_id();
            }
        else {
            importFailedMessage("Did not import address '$address_line1'");
        }
            // if we don't have a default address, set them now
            // this is kind of naive first through the post choosing, but oh well
            if (!$default_address_id  && $address_id) {
                $sql = "SELECT * FROM companies WHERE company_id = $company_id";
                $rst = $con->execute($sql);

                $rec = array();
                $rec['default_primary_address'] = $address_id;
                $rec['default_billing_address'] = $address_id;
                $rec['default_shipping_address'] = $address_id;
                $rec['default_payment_address'] = $address_id;

                $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
                debugSql($upd);
                if (strlen($upd)>0) {
                    $con->execute($upd);
                }
                $default_address_id = $address_id;
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
            $rec = array();
            $rec['company_id'] = $company_id;
            $rec['first_names'] = $contact_first_names;
            $rec['last_name'] = $contact_last_name;
            $rec['entered_at'] = $entered_at;
            $rec['entered_by'] = $entered_by;
            $rec['last_modified_at'] = $last_modified_at;
            $rec['last_modified_by'] = $last_modified_by;

            if ($address_id) {
                $rec['address_id'] = $address_id;
            } else {
                $rec['address_id'] = $default_address_id;
            }
            if ($division_id){
                $rec['division_id'] = $division_id;
            }
            if ($contact_work_phone){
                $rec['work_phone'] = $contact_work_phone;
            }
            if ($contact_home_phone){
                $rec['home_phone'] = $contact_home_phone;
            }
            if ($contact_fax){
                $rec['fax'] = $contact_fax;
            }
            if ($contact_email){
                $rec['email'] = $contact_email;
            }
            if ($contact_salutation){
                $rec['salutation'] = $contact_salutation;
            }
            if ($contact_date_of_birth){
                $rec['date_of_birth'] = $contact_date_of_birth;
            }
            if ($contact_summary){
                $rec['summary'] = $contact_summary;
            }
            if ($contact_title){
                $rec['title'] = $contact_title;
            }
            if ($contact_description){
                $rec['description'] = $contact_description;
            }
            if ($contact_cell_phone){
                $rec['cell_phone'] = $contact_cell_phone;
            }
            if ($contact_aol){
                $rec['aol_name'] = $contact_aol;
            }
            if ($contact_yahoo){
                $rec['yahoo_name'] = $contact_yahoo;
            }
            if ($contact_msn){
                $rec['msn_name'] = $contact_msn;
            }
            if ($contact_interests){
                $rec['interests'] = $contact_interests;
            }
            if ($contact_custom1){
                $rec['custom1'] = $contact_custom1;
            }
            if ($contact_custom2){
                $rec['custom2'] = $contact_custom2;
            }
            if ($contact_custom3){
                $rec['custom3'] = $contact_custom3;
            }
            if ($contact_custom4){
                $rec['custom4'] = $contact_custom4;
            }
            if ($contact_profile){
                $rec['profile'] = $contact_profile;
            }
            if ($gender){
                $rec['gender'] = $gender;
            }

            $tbl = 'contacts';
            $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
            debugSql($ins);
            $con->execute($ins);

            importMessage("Updated contact '$contact_first_names $contact_last_name'");

        } //end insert contact
    else {
        importFailedMessage("Did not update contact '$contact_first_names $contact_last_name'");
    }

        //set the category if we got one
        if ($category_id) {
            //should add an is_numeric check and other logic here
            $rec = array();
            $rec['category_id'] = $category_id;
            $rec['on_what_table'] = 'companies';
            $rec['on_what_id'] = $company_id;

            $tbl = 'entity_category_map';
            $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
            debugSql($ins);
            $con->execute($ins);
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
 * Revision 1.27  2004/08/25 14:18:45  neildogg
 * - Daylight savings now applied to all new addresses
 *
 * Revision 1.26  2004/07/27 18:25:22  braverock
 * - fixed problem where GetUpdateSql function may return an empty string, and don't try to execute an empty query.
 *   - resolves SF bug 998856
 *
 * Revision 1.25  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.24  2004/07/08 22:16:28  introspectshun
 * - Now uses GetInsertSQL and GetUpdateSQL
 *
 * Revision 1.23  2004/07/07 22:18:32  braverock
 * - minor improvements to import process
 *
 * Revision 1.22  2004/05/21 12:24:27  braverock
 * - assign contact address_id to the company default address if no address is imported with the contact
 *
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