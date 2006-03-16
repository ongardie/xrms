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
 *
 * $Id: import-companies-2.php,v 1.12 2006/03/16 07:56:19 ongardie Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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

move_uploaded_file($_FILES['file1']['tmp_name'], $tmp_upload_directory . 'companies-to-import.txt');

$page_title = _("Preview Data");

start_page($page_title, true, $msg);
?>
<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <form action="import-companies-3.php" method="post">
        <input type=hidden name=file_format value="<?php echo $file_format; ?>">
        <input type=hidden name=delimiter value="<?php echo $delimiter; ?>">
        <input type=hidden name=user_id value="<?php echo $user_id; ?>">
        <input type=hidden name=category_id value="<?php echo $category_id; ?>">
        <input type=hidden name=crm_status_id value="<?php echo $crm_status_id; ?>">
        <input type=hidden name=company_source_id value="<?php echo $company_source_id; ?>">
        <input type=hidden name=industry_id value="<?php echo $industry_id; ?>">
        <input type=hidden name=account_status_id value="<?php echo $account_status_id; ?>">
        <input type=hidden name=rating_id value="<?php echo $rating_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=64><?php echo _("Preview Data"); ?></td>
            </tr>

        <tr>
            <!-- base company info //-->
           <td class=widget_header colspan=4><?php echo _("Company"); ?></td>

            <!-- contact info //-->
           <td class=widget_header colspan=23><?php echo _("Contact Info"); ?></td>

            <!-- address info //-->
           <td class=widget_header colspan=9><?php echo _("Address"); ?></td>

	    <!-- secondary address info //-->
           <td class=widget_header colspan=9><?php echo _("Secondary Address"); ?></td>

            <!-- address info //-->
           <td class=widget_header colspan=19><?php echo _("Additional Company Info"); ?></td>
        </tr>
       <tr>
           <td class=widget_content><?php echo _("Row Number"); ?></td>

           <!-- base company info //-->
           <td class=widget_content><?php echo _("Company ID"); ?></td>
           <td class=widget_content><?php echo _("Company Name"); ?></td>
           <td class=widget_content><?php echo _("Division Name"); ?></td>

           <!-- contact info //-->
           <td class=widget_content><?php echo _("Contact ID"); ?></td>
           <td class=widget_content><?php echo _("First Names"); ?></td>
           <td class=widget_content><?php echo _("Last Name"); ?></td>
           <td class=widget_content><?php echo _("Email"); ?></td>
           <td class=widget_content><?php echo _("Work Phone"); ?></td>
           <td class=widget_content><?php echo _("Cell Phone"); ?></td>
           <td class=widget_content><?php echo _("Home Phone"); ?></td>
           <td class=widget_content><?php echo _("Fax"); ?></td>
           <td class=widget_content><?php echo _("Division"); ?></td>
           <td class=widget_content><?php echo _("Salutation"); ?></td>
           <td class=widget_content><?php echo _("Date of Birth"); ?></td>
           <td class=widget_content><?php echo _("Summary"); ?></td>
           <td class=widget_content><?php echo _("Title"); ?></td>
           <td class=widget_content><?php echo _("Description"); ?></td>
           <td class=widget_content><?php echo _("AOL"); ?></td>
           <td class=widget_content><?php echo _("Yahoo"); ?></td>
           <td class=widget_content><?php echo _("MSN"); ?></td>
           <td class=widget_content><?php echo _("Interests"); ?></td>
           <td class=widget_content><?php echo _("Custom 1"); ?></td>
           <td class=widget_content><?php echo _("Custom 2"); ?></td>
           <td class=widget_content><?php echo _("Custom 3"); ?></td>
           <td class=widget_content><?php echo _("Custom 4"); ?></td>
           <td class=widget_content><?php echo _("Profile"); ?></td>

           <!-- address info //-->
           <td class=widget_content><?php echo _("Address Name"); ?></td>
           <td class=widget_content><?php echo _("Line 1"); ?></td>
           <td class=widget_content><?php echo _("Line 2"); ?></td>
           <td class=widget_content><?php echo _("City"); ?></td>
           <td class=widget_content><?php echo _("State"); ?></td>
           <td class=widget_content><?php echo _("Postal Code"); ?></td>
           <td class=widget_content><?php echo _("Country"); ?></td>
           <td class=widget_content><?php echo _("Address Body"); ?></td>
           <td class=widget_content><?php echo _("Use Pretty Address"); ?></td>

	   <!-- secondary address info //-->
           <td class=widget_content><?php echo _("Secondary Address Name"); ?></td>
           <td class=widget_content><?php echo _("Line 1"); ?></td>
           <td class=widget_content><?php echo _("Line 2"); ?></td>
           <td class=widget_content><?php echo _("City"); ?></td>
           <td class=widget_content><?php echo _("State"); ?></td>
           <td class=widget_content><?php echo _("Postal Code"); ?></td>
           <td class=widget_content><?php echo _("Country"); ?></td>
           <td class=widget_content><?php echo _("Address Body"); ?></td>
           <td class=widget_content><?php echo _("Use Pretty Address"); ?></td>

           <!-- extra company info //-->
           <td class=widget_content><?php echo _("Code"); ?></td>
           <td class=widget_content><?php echo _("Phone"); ?></td>
           <td class=widget_content><?php echo _("Alt. Phone"); ?></td>
           <td class=widget_content><?php echo _("Fax"); ?></td>
           <td class=widget_content><?php echo _("Website"); ?></td>
           <td class=widget_content><?php echo _("Legal Name"); ?></td>
           <td class=widget_content><?php echo _("Tax ID"); ?></td>
           <td class=widget_content><?php echo _("External Ref 1"); ?></td>
           <td class=widget_content><?php echo _("External Ref 2"); ?></td>
           <td class=widget_content><?php echo _("External Ref 3"); ?></td>
           <td class=widget_content><?php echo _("Custom 1"); ?></td>
           <td class=widget_content><?php echo _("Custom 2"); ?></td>
           <td class=widget_content><?php echo _("Custom 3"); ?></td>
           <td class=widget_content><?php echo _("Custom 4"); ?></td>
           <td class=widget_content><?php echo _("No. Employees"); ?></td>
           <td class=widget_content><?php echo _("Revenue"); ?></td>
           <td class=widget_content><?php echo _("Credit Limit"); ?></td>
           <td class=widget_content><?php echo _("Terms"); ?></td>
           <td class=widget_content><?php echo _("Profile/Notes"); ?></td>

       </tr>
<?php
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


$con = get_xrms_dbconnection();

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
   $company_id = 0;
   $contact_id = 0;

    //assign array values to variables

    require($template);

    $sql_fetch_company_id = "select comp.company_id,cont.contact_id from companies comp, contacts cont where
                            cont.company_id =  comp.company_id and
                            comp.company_name = '" . addslashes($company_name) ."' and ";
    if ( $contact_first_name = '' )
    {
        $sql_fetch_company_id .= "cont.first_names = '" . addslashes($contact_first_names) . "' and";
    }
    $sql_fetch_company_id .= " cont.last_name = '" . addslashes($contact_last_name) . "' and
                            cont.contact_record_status='a' and
                            comp.company_record_status='a' " ;

    //echo "\n<br><pre> "._("Search Complete").' '. $sql_fetch_company_id . "\n</pre>" ;

    $rst_company_id = $con->execute($sql_fetch_company_id);

    if ( $rst_company_id )
    {
        $company_id = $rst_company_id->fields['company_id'];
        $contact_id = $rst_company_id->fields['contact_id'];

        $rst_company_id->close();
    }
    else
    {
      $company_id = 0;
      $contact_id = 0;
          $sql_fetch_company_id = "select comp.company_id from companies comp where
                                  comp.company_name =  '" . addslashes($company_name) ."' and
                                  comp.company_record_status='a' " ;
    //echo "\n<br><pre> "._("Only Searching for Company") .' '. $sql_fetch_company_id . "\n</pre>" ;

          $rst_company_id = $con->execute($sql_fetch_company_id);

          if ($rst_company_id)
          {
              $company_id = $rst_company_id->fields['company_id'];
              $contact_id = 0;
              $rst_company_id->close();
          }
          else
          {
              $company_id = 0;
              $contact_id = 0;
          }
    }

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
           <!-- address 2 info //-->
           <td class=widget_content>$address2_name</td>
           <td class=widget_content>$address2_line1</td>
           <td class=widget_content>$address2_line2</td>
           <td class=widget_content>$address2_city</td>
           <td class=widget_content>$address2_state</td>
           <td class=widget_content>$address2_postal_code</td>
           <td class=widget_content>$address2_country</td>
           <td class=widget_content>$address2_body</td>
           <td class=widget_content>$address2_use_pretty_address</td>

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
?>
            <tr>
                <td colspan=64 class=widget_content><input class=button type=submit value="<?php echo _("Import"); ?>"></td>
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

<?php
end_page();

/**
 * $Log: import-companies-2.php,v $
 * Revision 1.12  2006/03/16 07:56:19  ongardie
 * - Added support for secondary addresses.
 * - Re-enabled states/provinces.
 *
 * Revision 1.11  2006/01/02 21:50:29  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.10  2005/06/19 13:30:10  braverock
 * - improved localization and multi-line handling w/ addslashes
 * - improved duplicate checking
 * - patches provided by XRMS french translator Jean-Noël Hayart (SF:jnhayart)
 *
 * Revision 1.9  2005/04/15 18:30:20  introspectshun
 * - i18n compliance
 *
 * Revision 1.8  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.7  2004/07/08 22:15:15  introspectshun
 * - Include adodb-params.php
 *
 * Revision 1.6  2004/04/19 14:21:53  braverock
 * - add additional look-ups and tests on import
 * - improve error reporting
 * - revise process to use templates
 *   - makes use of material from SF patch 926925 by Glenn Powers
 *
 * Revision 1.5  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
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