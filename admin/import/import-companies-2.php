<?php
/**
 * import-companies-2.php - File importer for XRMS
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
 * @author Chris Woofter
 * @author Brian Peterson
 *
 * @todo could better accomodate microsoft Outlook by looking for outlook field names
 *
 * $Id: import-companies-2.php,v 1.4 2004/04/09 22:08:38 braverock Exp $
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

move_uploaded_file($_FILES['file1']['tmp_name'], $tmp_upload_directory . 'companies-to-import.txt');

$page_title = "Preview Data";

start_page($page_title, true, $msg);

echo <<<TILLEND

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <form action="import-companies-3.php" method="post">
        <input type=hidden name=delimiter value="$delimiter">
        <input type=hidden name=user_id value="$user_id">
        <input type=hidden name=category_id value="$category_id">
        <input type=hidden name=crm_status_id value="$crm_status_id">
        <input type=hidden name=company_source_id value="$company_source_id">
        <input type=hidden name=industry_id value="$industry_id">
        <input type=hidden name=account_status_id value="$account_status_id">
        <input type=hidden name=rating_id value="$rating_id">
        <table class=widget cellspacing=1 width=100%>
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

//get the data array
$filearray = CSVtoArray($tmp_upload_directory . 'companies-to-import.txt', true , $delimiter, $enclosure);
// @todo modify CSVtoArray fn to do a strtolower and replace spaces with underscores in array element names
// @todo could better accomodate microsoft outlook by looking for outlook field names

//debug line to view the array
//echo "\n<br><pre>". print_r ($filearray). "\n</pre>";

//fill up our variables from the array, where they exist
foreach ($filearray as $row) {
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
    $default_address_id  = fetch_default_address($con, $company_id);

    //does this division exist?
    $division_id = fetch_division_id($con, $division_name, $company_id);


    //now show the row
    echo <<<TILLEND
       <tr>
           <td class=widget_content>$row_number</td>

           <!-- base company info //-->
           <td class=widget_content>$company_id</td>
           <td class=widget_content>$company_name</td>
           <td class=widget_content>$division_name</td>

           <!-- contact info //-->
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
}; //end foreach, loop back to do the next row in the file

//fclose($handle);
$con->close();

echo <<<TILLEND
            <tr>
                <td class=widget_content><input class=button type=submit value="Import"></td>
            </tr>
        </table>
        </form>

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
 * $Log: import-companies-2.php,v $
 * Revision 1.4  2004/04/09 22:08:38  braverock
 * - allow import of all fields in the XRMS database
 * - integrated patches provided by Olivier Colonna of Fontaine Consulting
 *
 * Revision 1.3  2004/02/10 13:31:44  braverock
 * - change url to 'website'
 * - fixed syntax errror on insert
 *
 * Revision 1.2  2004/02/04 18:39:58  braverock
 * - major update to import functionality
 * - add phpdoc
 *
 */
?>