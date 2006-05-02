<?php
/**
 * View a single Service Case
 *
 * $Id: one.php,v 1.49 2006/05/02 00:51:25 vanmer Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-cases.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-companies.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once('../activities/activities-widget.php');


$case_id = $_GET['case_id'];
$on_what_id=$case_id;
$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$con = get_xrms_dbconnection();
// $con->debug = 1;

$form_name = 'One_Case';

update_recent_items($con, $session_user_id, "cases", $case_id);

//get case details

$case_data=get_case($con, $case_id);
//print_r($case_data);

if ($case_data) {
    $company_id = $case_data['company_id'];
    $contact_id = $case_data['contact_id'];
    $division_id = $case_data['division_id'];

    //get division details
    if ($division_id) {
        $division_data=get_division($con, $division_id);
        $division_name = $division_data['division_name'];
    }


    //get company details
    $company_data=get_company($con, $company_id);
    $company_name = $company_data['company_name'];
    $company_code = $company_data['company_code'];
    $crm_status_display_html = $company_data['crm_status_display_html'];
    $account_status_display_html = $company_data['account_status_display_html'];
    $rating_display_html = $company_data['rating_display_html'];
    $account_owner_username = $company_data['owner_username'];

    //get contact details
    $contact_data=get_contact($con, $contact_id);
    $first_names = $contact_data['first_names'];
    $last_name = $contact_data['last_name'];
    $work_phone = $contact_data['work_phone'];
    $email = $contact_data['email'];

    //pull status, priority and type details
    $case_status_display_html = $case_data['case_status_display_html'];
    $case_priority_display_html = $case_data['case_priority_display_html'];
    $case_priority_id = $case_data['case_priority_id'];
    $case_type_id = $case_data['case_type_id'];
    $case_type_display_html = $case_data['case_type_display_html'];

    //pull case details
    $case_title = $case_data['case_title'];
    $case_description = nl2br($case_data['case_description']);
    $case_owner_username = $case_data['case_owner_username'];
    $entered_at = $con->userdate($case_data['entered_at']);
    $last_modified_at = $con->userdate($case_data['last_modified_at']);
    $entered_by = $case_data['entered_by_username'];
    $last_modified_by = $case_data['last_modified_by_username'];
    $closed_at = $con->userdate($case_data['closed_at']);
    $closed_by = $case_data['closed_by_username'];
}

// get the new activities widget
$return_url = "/cases/one.php?case_id=$case_id"; 

$new_activity_widget = GetNewActivityWidget($con, $session_user_id, $return_url, 'cases', $case_id, $company_id, $contact_id);


// Begin Activities Widget
$form_name = 'OneCase';

$search_terms = array();
$search_terms['on_what_table'] = 'cases';
$search_terms['on_what_id'] = $case_id;


$default_columns = array('title', 'owner', 'type', 'scheduled', 'due');

$activities_widget = GetActivitiesWidget($con, $search_terms, $form_name, _('Activities'), $session_user_id, $return_url, '', '', $default_columns);

// End Activities Widget


/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the siddebars
$on_what_table = 'cases';
$on_what_id = $case_id;

//include the categories sidebar
require_once($include_directory . 'categories-sidebar.php');

//include the files sidebar
require_once("../files/sidebar.php");

//include the notes sidebar
require_once("../notes/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . ", contact_id, address_id FROM contacts WHERE company_id = $company_id AND contact_record_status = 'a' ORDER BY last_name";
$rst = $con->execute($sql);
if ($rst) {
    $address_id = $rst->fields['address_id'];
    $work_phone = get_formatted_phone($con, $rst->fields['address_id'], $work_phone);
    $rst->close();
} else {
   db_error_handler ($con,$sql);
}

add_audit_item($con, $session_user_id, 'viewed', 'cases', $case_id, 3);

$con->close();

$page_title = _("Case #") . $case_id . ": " . $case_title;
start_page($page_title, true, $msg);

?>
<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Case Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Title"); ?></td>
                                    <td class=clear><?php  echo $case_title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Type"); ?></td>
                                    <td class=clear><?php  echo $case_type_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Owner"); ?></td>
                                    <td class=clear><?php  echo $case_owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Status"); ?></td>
                                    <td class=clear>
                                        <?php  echo $case_status_display_html; ?>
                                        <a href="#" onclick="javascript:window.open('case-status-view.php?case_type_id=<?php  echo $case_type_id; ?>');"><?php echo _("Status Definitions"); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Priority"); ?></td>
                                    <td class=clear><?php  echo $case_priority_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                    <td class=clear><?php  echo $entered_at; ?> (<?php  echo $entered_by; ?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                    <td class=clear><?php  echo $last_modified_at; ?> (<?php  echo $last_modified_by; ?>)</td>
                                </tr>
<?php if ($closed_at AND $closed_by) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Closed"); ?></td>
                                    <td class=clear><?php  echo $closed_at; ?> (<?php  echo $closed_by; ?>)</td>
                                </tr>
<?php } ?>
                                </table>
                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Contact"); ?></td>
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/contacts/one.php?contact_id=<?php  echo $contact_id; ?>"><?php  echo $first_names; ?> <?php  echo $last_name; ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Work Phone"); ?></td>
                                    <td class=clear><?php  echo $work_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("E-Mail"); ?></td>
                                    <td class=clear><?php echo "<a href='mailto:$email' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&on_what_id=$case_id&contact_id=$contact_id&on_what_table=cases&activity_title=email RE: $case_title&company_id=$company_id&email=$email&return_url=/cases/one.php?case_id=$case_id'\" >" . htmlspecialchars($email); ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Company"); ?></td>
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>"><?php  echo $company_name; ?></a> (<?php  echo $company_code; ?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Division"); ?></td>
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>&division_id=<?php echo $division_id; ?>"><?php  echo $division_name; ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Account Owner"); ?></td>
                                    <td class=clear><?php  echo $account_owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("CRM Status"); ?></td>
                                    <td class=clear><?php  echo $crm_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Account Status"); ?></td>
                                    <td class=clear><?php  echo $account_status_display_html; ?></td>
                                </tr>
                            </table>

                            </td>
                        </tr>
                    </table>

                    <p><?php  echo $case_description; ?></p>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><?php echo render_edit_button("Edit", 'button',"javascript: location.href='edit.php?case_id=$case_id';"); ?></td>
            </tr>
        </table>

        <?php echo $new_activity_widget; ?>
        <form name="<?php echo $form_name; ?>" method=post>
            <?php
                // activity pager
                echo $activities_widget['content'];
                echo $activities_widget['sidebar'];
                echo $activities_widget['js'];
            ?>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <?php echo $category_rows; ?>

        <!-- notes //-->
        <?php echo $note_rows; ?>

        <!-- files //-->
        <?php echo $file_rows; ?>

    </div>
</div>


<script language="JavaScript" type="text/javascript">
function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}
</script>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.49  2006/05/02 00:51:25  vanmer
 * - changed to use APIs to fetch details about case
 *
 * Revision 1.48  2006/04/28 02:52:51  vanmer
 * - added join on optional closed_by field for cases
 * - added display of closed_by and closed_on user/date
 * - altered display of mailto: href link so as not to confuse the HTML parser in quanta
 *
 * Revision 1.47  2006/01/02 22:47:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.46  2005/07/07 03:38:54  daturaarutad
 * updated to use new activities-widget functions
 *
 * Revision 1.45  2005/06/30 04:42:28  vanmer
 * - automatically set newly created activities on cases to the same priority as the case, if available
 *
 * Revision 1.44  2005/06/29 17:18:16  maulani
 * - Correctly display case status definitions
 *
 * Revision 1.43  2005/05/10 16:19:01  braverock
 * - change Activity 'Title' to 'Summary' for consistency
 *
 * Revision 1.42  2005/05/04 14:27:29  braverock
 * - change Activity 'Title' to 'Summary' for consistency
 *
 * Revision 1.41  2005/05/04 13:36:49  braverock
 * - change Start to 'Scheduled' for consistency of activity start time labels
 *
 * Revision 1.40  2005/03/29 23:52:48  maulani
 * - Add audit trail
 *
 * Revision 1.39  2005/03/21 13:40:54  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.38  2005/03/15 22:41:03  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.37  2005/03/15 21:37:07  daturaarutad
 * fixed Mail Merge for activities pager
 *
 * Revision 1.36  2005/03/15 21:23:23  daturaarutad
 * fixed Mail Merge for activities pager
 *
 * Revision 1.35  2005/03/14 18:52:36  daturaarutad
 * added default_sort to On column of activities pager
 *
 * Revision 1.34  2005/03/07 16:38:12  daturaarutad
 * added sql_sort_column to speed up pager sorting
 *
 * Revision 1.33  2005/02/25 03:41:47  daturaarutad
 * updated to use GUP_Pager for activities listing
 *
 * Revision 1.32  2005/02/14 21:43:14  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.31  2005/02/10 03:24:59  braverock
 * - change order of LEFT OUTER JOIN query for MS SQL server portability
 *
 * Revision 1.30  2005/01/22 15:07:26  braverock
 * - add sort order to activity_types menu
 *
 * Revision 1.29  2005/01/13 18:19:46  vanmer
 * - ACL restriction on activity list
 *
 * Revision 1.28  2005/01/11 13:30:40  braverock
 * - removed on_what_string hack, changed to use standard make_singular function
 *
 * Revision 1.27  2005/01/10 21:49:02  vanmer
 * - fixed javascript popup for status definitions to no longer redirect page
 * - fixed return_url to cases to allow activities to be completed and returned to the case, not the contact
 *
 * Revision 1.26  2005/01/09 17:23:19  vanmer
 * - added javascript needed for marking an activity as done on entry
 * - switched interface buttons from html to calling render_button functions
 * - added commented ACL restriction on activities
 * - changed return_url to use ? instead of %3F, as it was failing on redirect (treated like a URL instead of parameter)
 *
 * Revision 1.25  2005/01/09 16:58:00  braverock
 * - add db_error_handler to all queries
 * - set activity_contact_id correctly for links to Activities
 *
 * Revision 1.24  2005/01/07 01:58:52  braverock
 * - add link to case status pop-up
 *
 * Revision 1.23  2005/01/06 20:54:36  vanmer
 * - moved setup of initial values to above session_check (for ACL)
 * - added division to display of one case, if available
 *
 * Revision 1.22  2004/12/24 16:41:23  braverock
 * - adjusted width of activity title to better manage layout of activity table
 *
 * Revision 1.21  2004/10/26 16:36:26  introspectshun
 * - Centralized category handling as sidebar
 *
 * Revision 1.20  2004/07/30 11:02:14  cpsource
 * - Optionally define msg
 *   set default no_update flag to false in edit-2.php
 *
 * Revision 1.19  2004/07/30 10:20:01  cpsource
 * - Fixed undefines
 *     activity_rows
 *
 * Revision 1.18  2004/07/25 13:35:29  johnfawcett
 * - modified string Acct. to Account to unify across application
 *
 * Revision 1.17  2004/07/25 13:23:05  johnfawcett
 * - removed punctuation form gettext call
 *
 * Revision 1.16  2004/07/21 21:04:29  neildogg
 * - Added get_formatted_phone
 *
 * Revision 1.15  2004/07/16 07:11:17  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.14  2004/06/12 04:08:06  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.13  2004/06/07 18:58:50  gpowers
 * - removed duplicate line
 * - added nl2br() to case description for proper formatting
 *
 * Revision 1.12  2004/05/04 15:30:33  gpowers
 * Changed display of $profile (which was undefined) to $case_description
 *
 * Revision 1.11  2004/04/17 16:02:41  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.10  2004/04/16 22:21:59  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.9  2004/04/10 14:59:47  braverock
 * - display Case Id on Case details screen
 *   - apply SF patch 925619 submitted by Glenn Powers
 *
 * Revision 1.8  2004/03/21 15:25:26  braverock
 * - fixed a bug where there are no contacts for a company.
 *
 * Revision 1.7  2004/03/07 14:07:14  braverock
 * - use centralized side-bar code in advance of i18n conversion
 *
 */
?>
