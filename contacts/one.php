<?php
/**
 * One Contact Page
 *
 * This page allows for the viewing of the details for a single contact.
 *
 * @todo break the parts of the contact details qey into seperate queries
 *       to make the entire process more resilient.
 *
 * $Id: one.php,v 1.100 2006/06/15 21:32:59 vanmer Exp $
 */
require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once('../activities/activities-widget.php');

$contact_id = $_GET['contact_id'];
global $on_what_id;
$on_what_id=$contact_id;

$session_user_id = session_check();

// make sure $msg is never undefined
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

global $con;

$con = get_xrms_dbconnection();

$form_name = 'One_Contact';

// make sure $accounting_rows is defined
if ( !isset($accounting_rows) ) {
  $accounting_rows = '';
}
//call the accounting hook
$accounting_rows = do_hook_function('contact_accounting_inline_display', $accounting_rows);

// make sure $contact_buttons is defined
if ( !isset($contact_buttons) ) {
  $contact_buttons = '';
}
//call the one_contact_buttons hook
$contact_buttons = do_hook_function('one_contact_buttons', $contact_buttons);

update_recent_items($con, $session_user_id, "contacts", $contact_id);

// Get the Contact Record
$rst = get_contact($con, $contact_id, $return_rst = true);
if ($rst) {

    // Instantiating variables for each contact field, so that custom fields
    // added to the contacts table are accessible to plugin code without
    // an extra read from database.
    foreach ($rst->fields as $contact_field => $contact_field_value ) {
        $$contact_field = $contact_field_value;
    }

    $profile = str_replace ("\n","<br>\n",htmlspecialchars($profile));
    $work_phone = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']);
    $work_phone_ext = $rst->fields['work_phone_ext'];
    if (trim($work_phone_ext)) {
            $work_phone_ext_display = '&nbsp;' . _("x") . $work_phone_ext;
    }
    $cell_phone = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['cell_phone']);
    $home_phone = get_formatted_phone($con, $rst->fields['home_address_id'], $rst->fields['home_phone']);
    $fax = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['fax']);
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];

    $rst->close();
} else {
    //$msg="Problem";
}


switch ($gender) {
    case 'f':
        $gender = _("Female");
        break;
    case 'm':
        $gender = _("Male");
        break;
    case 'u':
        $gender = _("Unknown");
        break;
    default:
        $gender = _("Unknown");
        break;
}

if ( $address_id ) {
  $business_address = get_formatted_address($con, $address_id);
} else {
  $business_address = '';
}

if ( $home_address_id ) {
  $home_address = get_formatted_address($con, $home_address_id);
} else {
  $home_address .= '';
}
$sql_opportunity_types = "
SELECT
  opportunity_status_pretty_name, opportunity_status_id
FROM opportunity_statuses
WHERE opportunity_status_record_status = 'a'
ORDER by sort_order
";
$rst = $con->execute($sql_opportunity_types);
$opportunity_status_rows = $rst->GetMenu2('opportunity_status_id', null, true);

// New Activities Widget
$return_url = "/contacts/one.php?contact_id=$contact_id";

$new_activity_widget = GetNewActivityWidget($con, $session_user_id, $return_url, null, null, $company_id, $contact_id);


// Begin Activities Widget

// Pass search terms to GetActivitiesWidget
$search_terms = array();


$extra_where =" AND ((a.contact_id = $contact_id) OR ((activity_participants.contact_id = $contact_id) AND (activity_participants.ap_record_status = 'a'))) AND a.activity_record_status = 'a'";

$default_columns = array('title', 'owner', 'type', 'activity_about', 'scheduled', 'due');

$activities_widget =  GetActivitiesWidget($con, $search_terms, $form_name, _('Activities'), $session_user_id, $return_url, $extra_where, null, $default_columns);

// End Activities Widget


// division
$division_row = '';
if ($division_id != '') {
    $division_sql = "select division_id, division_name from company_division where division_id = $division_id";
    $div_rst = $con->execute($division_sql);

    if ($div_rst->NumRows() > 0) {
        $division_row .= '<input type=hidden name=division_id value='.$div_rst->fields['division_id'].'>'
                       .  $div_rst->fields['division_name'];
    }
} //end division select

if (get_system_parameter($con, 'Display Item Technical Details') == 'y') {
    $history_text = '<tr> <td class=widget_content colspan=2>';
    $history_text .= _("Contact ID:") . '  ' . $contact_id ;
    $history_text .= '</td> </tr>';
} else {
$history_text = '';
}


/**** BUILD THE FORMER COMPANIES SIDEBAR ****/
$former_sidebar_form_id='FormerContactCompany';
$former_sidebar_header=_("Former Companies");
$former_sql = "SELECT ".$con->concat($con->qstr("<a href=\"$http_site_root/companies/one.php?company_id="),'former_company_id',"'\">'",'company_name',"'</a>'")." as LINK, companychange_at FROM contact_former_companies JOIN companies ON companies.company_id=former_company_id WHERE contact_id=$contact_id";
$columns = array();
$columns[] = array('name' => _("Company"), 'index_sql' => 'LINK');
$columns[] = array('name' => _("Date"), 'index_sql' => 'companychange_at', 'default_sort'=>'desc');

$colspan = count($columns);
$pager = new GUP_Pager($con, $former_sql, null,$former_sidebar_header, $former_sidebar_form_id, 'FormerCompaniesSidebarPager', $columns, false, true);
$former_action=$http_site_root.current_page();
$former_rows.="<div id=FormerCompanies><form name=\"$former_sidebar_form_id\" action=\"$former_action\" method=POST><input type=hidden name=contact_id value=$contact_id>";
$former_rows.=$pager->Render($cases_sidebar_rows_per_page);
$former_rows.="</form></div>";
/**** END FORMER COMPANIES SIDEBAR ****/


/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the sidebars
$on_what_table = 'contacts';
$on_what_id = $contact_id;

//include the categories sidebar
require_once($include_directory . 'categories-sidebar.php');

//include the Cases sidebar
$case_limit_sql = "and cases.".make_singular($on_what_table)."_id = $on_what_id";
require_once( $include_locations_location . 'cases/sidebar.php');

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.".make_singular($on_what_table)."_id = $on_what_id";
require_once( $include_locations_location . 'opportunities/sidebar.php');

//include the contacts-companies sidebar
$relationships = array('contacts' => $contact_id, 'companies' => $company_id);
require_once( $include_locations_location . 'relationships/sidebar.php');

//include the files sidebar
require_once( $include_locations_location . 'files/sidebar.php');

//include the notes sidebar
require_once( $include_locations_location . 'notes/sidebar.php');

// make sure $sidebar_rows_top is defined
if ( !isset($sidebar_rows_top) ) {
  $sidebar_rows_top = '';
}

//call the sidebar top hook
$sidebar_rows_top = do_hook_function('contact_sidebar_top', $sidebar_rows_top);

// make sure $sidebar_rows_bottom is defined
if ( !isset($sidebar_rows_bottom) ) {
  $sidebar_rows_bottom = '';
}

//call the sidebar bottom hook
$sidebar_rows_bottom = do_hook_function('contact_sidebar_bottom', $sidebar_rows_bottom);

/** End of the sidebar includes **/
/*********************************/

add_audit_item($con, $session_user_id, 'viewed', 'contacts', $contact_id, 3);

$page_title = _("Contact Details").': '.$salutation.' '.$first_names . ' ' . $last_name;
start_page($page_title, true, $msg);

?>

<object classid="clsid:FB7199AB-79BF-11d2-8D94-0000F875C541" codetype="application/x-oleobject" id="objMessengerApp" width="0" height="0"></object>

<script language="JavaScript" type="text/javascript">
<!--

function openMsnSession(strIMAddress) {
    objMessengerApp.LaunchIMUI(strIMAddress);
}

//-->
</script>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Contact Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <!-- Contact Details left Column -->
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Name"); ?></td>
                                    <td class=clear><?php  echo $salutation .' '. $first_names.' '.$last_name; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Gender"); ?></td>
                                    <td class=clear><?php  echo $gender; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Summary"); ?></td>
                                    <td class=clear><?php  echo $summary; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Title"); ?></td>
                                    <td class=clear><?php  echo $title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Description"); ?></td>
                                    <td class=clear><?php  echo $description; ?></td>
                                </tr>
                                <?php if (trim($date_of_birth)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Date of Birth"); ?></td>
                                    <td class=clear><?php  echo $date_of_birth; ?></td>
                                </tr>
                                <?php
                                      };  //end if date_of_birth
                                      if (trim($tax_id)) {
                                ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Tax ID"); ?></td>
                                    <td class=clear><?php  echo $tax_id; ?></td>
                                </tr>
                                <?php }; ?>
                                <tr>
                                    <td class=sublabel><?php echo _("E-Mail"); ?></td>

                                    <td class=clear>
                                    <a href='mailto:<?php echo $email."' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&on_what_id=$contact_id&contact_id=$contact_id&company_id=$company_id&email=$email&activity_title=email to $first_names $last_name&return_url=/contacts/one.php?contact_id=$contact_id";?>'">
                                    <?php echo htmlspecialchars($email); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Work Phone"); ?></td>
                                    <td class=clear><?php  echo $work_phone . $work_phone_ext_display; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Home Phone"); ?></td>
                                    <td class=clear><?php  echo $home_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Cell Phone"); ?></td>
                                    <td class=clear><?php  echo $cell_phone; ?></td>
                                </tr>
                                <?php if (trim($fax)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Fax"); ?></td>
                                    <td class=clear><?php  echo $fax; ?></td>
                                </tr>
                                <?php
                                      }; //end if fax
                                      if (trim($interests)) {
                                ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Interests"); ?></td>
                                    <td class=clear><?php  echo $interests; ?></td>
                                </tr>
                                <?php
                                      }; //end if interests
                                ?>

                                <?php do_hook('one_contact_left'); ?>
                                </table>
                            </td>

                            <!-- Contact Details Right Column -->
                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td class=sublabel><?php echo _("Company"); ?></td>
                                    <td class=clear><a href="<?php  echo $http_site_root?>/companies/one.php?company_id=<?php echo $company_id;; ?>"><?php echo $company_name; ?></a> (<?php echo $company_code;?>)</td>
                                </tr>
                                <?php if (trim($division_row)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Division"); ?></td>
                                    <td class=clear><?php  echo $division_row; ?></td>
                                </tr>
                                <?php }; ?>
                                <!-- accounting plugin -->
                                <?php echo $accounting_rows; ?>
                                <!-- // These rows should be either removed or moved into an accounting plugin
                                <tr>
                                    <td class=sublabel><?php echo _("Account Owner"); ?></td>
                                    <td class=clear><?php  echo $account_owner; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("CRM Status"); ?></td>
                                    <td class=clear><?php  echo $crm_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Account Status"); ?></td>
                                    <td class=clear><?php  echo $account_status_display_html; ?></td>
                                </tr>
                                // end of accounting rows commented out
                                -->
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Business Address"); ?></td>
                                    <td class=clear><?php echo $business_address ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Home Address"); ?></td>
                                    <td class=clear><?php echo $home_address ?></td>
                                </tr>
                                <?php if (trim($custom1)) { ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _($contact_custom1_label); ?></td>
                                    <td class=clear><?php  echo $custom1; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($custom2)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($contact_custom2_label); ?></td>
                                    <td class=clear><?php  echo $custom2; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($custom3)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($contact_custom3_label); ?></td>
                                    <td class=clear><?php  echo $custom3; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php if (trim($custom4)) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _($contact_custom4_label); ?></td>
                                    <td class=clear><?php  echo $custom4; ?></td>
                                </tr>
                                <?php }; ?>
                                <?php do_hook('one_contact_right'); ?>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                    <td class=clear><?php  echo $entered_at; ?> by <?php echo $entered_by_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                    <td class=clear><?php  echo $last_modified_at; ?> by <?php echo $last_modified_by_username; ?></td>
                                </tr>
                                <?php if ($owner_username) { ?>
                                <tr>
                                    <td class=sublabel><?php echo _("Account Owner"); ?></td>
                                    <td class=clear><?php echo $owner_username; ?></td>
                                </tr>
				<?php }; ?>
                            </table>

                            </td>
                        </tr>
                    </table>

                    <p id="profile" class="hidden"><?php if(strlen($profile) >= 500) { echo substr($profile, 0, 500); ?><span><?php echo substr($profile, 500); ?></span><a href="#" onclick="document.getElementById('profile').className = (document.getElementById('profile').className == '') ? 'hidden' : ''; return false">...</a><?php } else { echo $profile; } ?></p>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <?php echo render_edit_button("Edit", 'button', "javascript: location.href='edit.php?contact_id=$contact_id';"); ?>
                    <input class=button type=button value="<?php echo _("Clone"); ?>" onclick="javascript: location.href='new.php?clone_id=<?php echo $contact_id ?>';">
                    <?php echo $contact_buttons; ?>
                </td>
            </tr>
           <?php  echo $history_text; ?>
        </table>
        <?php echo $new_activity_widget; ?>

        <form name="<?php echo $form_name; ?>" method=post>
            <?php
                // activity pager
                echo $pager_columns_selects;
                echo $activities_widget['content'];
                echo $activities_widget['sidebar'];
                echo $activities_widget['js'];
            ?>
        </form>

        <?php do_hook('contact_content_bottom', $contact_id); ?>

    </div>

    <!-- right column //-->
    <div id="Sidebar">

        <!-- top sidebar plugins //-->
        <?php echo $sidebar_rows_top; ?>

        <!-- categories //-->
        <?php echo $category_rows; ?>

        <!-- opportunities //-->
        <?php echo $opportunity_rows; ?>

        <!-- cases //-->
        <?php echo $case_rows; ?>

        <!-- relationship links //-->
        <?php echo $relationship_link_rows; ?>

        <!-- notes //-->
        <?php echo $note_rows; ?>

        <!-- files //-->
        <?php echo $file_rows; ?>

        <!-- former companies sidebar //-->
        <?php echo $former_rows; ?>

        <!-- bottom sidebar plugins //-->
        <?php echo $sidebar_rows_bottom; ?>

    </div>

</div>

<script language="JavaScript" type="text/javascript">
function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}
</script>

<?php

$con->close();

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.100  2006/06/15 21:32:59  vanmer
 * - added owner to the UI for a contact
 *
 * Revision 1.99  2006/04/28 15:20:49  braverock
 * - use get_contact API
 *
 * Revision 1.98  2006/03/21 02:59:51  ongardie
 * - Added contact_content_bottom plugin hook.
 *
 * Revision 1.97  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.96  2005/09/25 05:42:06  vanmer
 * - removed IM field references from all contact pages (now handled by plugin)
 * - added custom field hook for contacts new.php
 *
 * Revision 1.95  2005/08/15 19:58:15  braverock
 * - clean up minor whitespace issues
 *
 * Revision 1.94  2005/08/04 19:19:20  vanmer
 * - added sidebar to track contact's former companies
 *
 * Revision 1.93  2005/07/24 20:40:08  maulani
 * - Add display of contact_id in production for troubleshooting purposes
 *
 * Revision 1.92  2005/07/07 16:33:12  daturaarutad
 * removed Calendar.setup code (it moved to activities-widget.php)
 *
 * Revision 1.91  2005/07/07 03:43:38  daturaarutad
 * updated to use new activities-widget functions
 *
 * Revision 1.90  2005/06/29 20:55:33  daturaarutad
 * add default column "due" to activities widget
 *
 * Revision 1.89  2005/06/29 17:21:06  daturaarutad
 * updated activities widget to use GetActivitiesWidget()
 *
 * Revision 1.88  2005/06/07 20:12:45  braverock
 * - added additional checks to not display seldom used fields
 * - commented out accounting rows - should probably be moved to a plugin
 * - moved right column hook to before created/updated data
 *
 * Revision 1.87  2005/05/16 21:30:22  vanmer
 * - added tax_id handling to contacts pages
 *
 * Revision 1.86  2005/05/09 19:54:34  ycreddy
 * Added trim check on work_phone_ext
 *
 * Revision 1.85  2005/05/06 00:14:24  daturaarutad
 * added ability to clone contacts
 *
 * Revision 1.84  2005/05/04 14:27:29  braverock
 * - change Activity 'Title' to 'Summary' for consistency
 *
 * Revision 1.83  2005/05/04 13:34:31  braverock
 * - remove spurious 'About' column from new Activity row
 * - change Start to 'Scheduled' for consistenct of activity start time labels
 *
 * Revision 1.82  2005/05/03 23:10:33  braverock
 * - change Name display to $salutation.' '.$first_names.' '.$last_name
 *
 * Revision 1.81  2005/04/26 17:28:04  gpowers
 * - added Extension ("x") to contact work phone
 * - removed non-digits from phone numbers in edit-2's, new-2's
 * - updated work phone display to include Extension
 *
 * Revision 1.80  2005/04/23 17:47:47  vanmer
 * - Added activities to list for contacts who are listed in the activity_participants table
 * - Changed activity sql to use left outer joins for all secondary tables
 *
 * Revision 1.79  2005/04/19 21:14:59  neildogg
 * - Profile bug if short
 *
 * Revision 1.78  2005/04/19 21:10:10  neildogg
 * - Contact profile shrunken by default. Can be enlarged
 *
 * Revision 1.77  2005/04/07 14:09:43  maulani
 * - Change use of username to use actual name
 *   From RFE 933629 by sdavey
 *
 * Revision 1.76  2005/04/05 18:02:09  ycreddy
 * added assignment for entered_by and last_modified_by that use names different from column names
 *
 * Revision 1.75  2005/04/04 18:11:40  ycreddy
 * Instantiated variable for each contact field for plugin access without an extra read and also moved ->close() to the end of the page for plugin use without creating a new DB connection
 *
 * Revision 1.74  2005/03/22 21:55:12  gpowers
 * - moved up one_contact_buttons hook
 *   - it's now called before the db connection is closed
 *   - now it's in the same area as the company_accounting hook
 *
 * Revision 1.73  2005/03/22 00:06:51  braverock
 * - add trim around if checks in details table to decide whether to print empty data
 *
 * Revision 1.72  2005/03/21 13:40:55  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.71  2005/03/18 20:53:32  gpowers
 * - added hooks for inline info plugin
 *
 * Revision 1.70  2005/03/15 22:50:06  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.69  2005/03/15 21:58:37  daturaarutad
 * fixed Mail Merge for activities pager
 *
 * Revision 1.68  2005/03/14 18:45:58  daturaarutad
 * added default_sort to On column of activities pager
 *
 * Revision 1.67  2005/03/07 16:48:53  daturaarutad
 * updated to speed up sql sorts in the pager using sql_sort_column
 *
 * Revision 1.66  2005/02/25 03:39:57  daturaarutad
 * updated to use GUP_Pager for activities listing
 *
 * Revision 1.65  2005/02/18 14:12:32  braverock
 * - remove double assignment of $contact_id
 *   - patch supplied by Keith Edmunds
 *
 * Revision 1.64  2005/02/14 21:44:11  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.63  2005/02/11 12:26:16  braverock
 * - define contact_sidebar_bottom hook
 *
 * Revision 1.62  2005/02/10 21:16:50  maulani
 * - Add audit trail entries
 *
 * Revision 1.61  2005/02/10 19:41:28  braverock
 * - add missing comma
 *
 * Revision 1.60  2005/02/10 04:35:19  braverock
 * - add display support for home_address_id
 * - hide seldom used and custom fields if they don't have values
 *
 * Revision 1.59  2005/01/26 22:43:03  vanmer
 * - altered SQL query to allow activities table to appear directly before LEFT OUTER JOIN
 *
 * Revision 1.58  2005/01/22 15:21:47  braverock
 * - fixed double handlnig of $http_site_root on mailto link
 * � Resolves SF Bug #1106989 using patch reported by fu22ba55
 *
 * Revision 1.57  2005/01/22 15:07:25  braverock
 * - add sort order to activity_types menu
 *
 * Revision 1.56  2005/01/22 14:35:59  braverock
 * - fixed mis-assignment of title to $e instead of $title, looks like a cut and paste error
 *   Resolves SF Bug #1106290 reported by fu22ba55
 *
 * Revision 1.55  2005/01/13 18:43:59  vanmer
 * - Basic ACL changes to allow display functionality to be restricted
 *
 * Revision 1.54  2005/01/11 13:36:35  braverock
 * - removed on_what_string hack, changed to use make_singular function
 *
 * Revision 1.53  2005/01/10 23:03:58  neildogg
 * - Because contacts have companies too
 *
 * Revision 1.52  2005/01/10 20:47:48  neildogg
 * - Changed to support new relationship sidebar variable requirement
 *
 * Revision 1.51  2004/12/24 16:20:17  braverock
 * - removed opportunity status code becasue it breaks existing $associate_activities code
 * - fixed formatting problems
 * - reinserting this code will require checking for existing opportunity
 *   checking for existing case
 *   not breaking existing $associate_activities code
 *
 * Revision 1.50  2004/12/20 20:12:15  neildogg
 * - Left join allows empty user
 *
 * Revision 1.49  2004/11/09 00:07:47  gpowers
 * - Corrected display of newlines in profile
 *
 * Revision 1.48  2004/10/26 16:37:55  introspectshun
 * - Centralized category handling as sidebar
 *
 * Revision 1.47  2004/10/21 05:58:18  gpowers
 * - added contact_sidebar_top plugin hook
 *
 * Revision 1.46  2004/09/17 20:04:46  neildogg
 * - Added optional auto creation of opportunity
 *  - from contact screen along with auto
 *  - launching activities on opportunity status
 *
 * Revision 1.45  2004/08/05 15:25:34  braverock
 * - fixed mailto link for activity creation
 *
 * Revision 1.44  2004/08/02 15:56:49  gpowers
 * - removed "Vcard" button
 *   - moved to "Vcard" plugin
 *
 * Revision 1.43  2004/07/25 16:18:20  johnfawcett
 * - unified page title
 *
 * Revision 1.42  2004/07/25 13:37:56  johnfawcett
 * - modified string Acct. to Account to unify across application
 *
 * Revision 1.41  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.40  2004/07/21 21:05:35  neildogg
 * - Added get_formatted_phone
 *
 * Revision 1.39  2004/07/21 15:20:04  introspectshun
 * - Localized strings for i18n/translation support
 * - Removed include of lang file
 *
 * Revision 1.38  2004/07/14 22:12:00  neildogg
 * - Now uses $overall_id
 *
 * Revision 1.37  2004/07/13 15:48:59  cpsource
 * - Get rid of undefined variable usage.
 *
 * Revision 1.36  2004/07/09 15:41:14  neildogg
 * - Uses the new, generic relationship sidebar
 *
 * Revision 1.35  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.34  2004/06/24 20:50:29  gpowers
 * - added one_contact_buttons hook for radtest plugin
 *
 * Revision 1.33  2004/06/21 13:56:44  gpowers
 * - removed extra blank lines
 *
 * Revision 1.32  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.31  2004/06/10 15:26:14  gpowers
 * - removed "Transer" and "Edit Address" buttons. (moved to "Edit" page)
 *
 * Revision 1.30  2004/06/09 19:25:49  gpowers
 * - added "Transfer" button to enable transfer of contact to new company
 *
 * Revision 1.29  2004/06/09 16:51:24  gpowers
 * Added a button to "Edit Address"
 *
 * Revision 1.28  2004/06/04 17:20:30  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.27  2004/06/04 13:50:24  braverock
 * - update email link to improve activity tracking
 *
 * Revision 1.26  2004/06/04 13:24:19  braverock
 * - add default activity title to email link
 *
 * Revision 1.25  2004/05/28 13:57:31  gpowers
 * removed "viewed" audit log entry. this is redundant, as this data is
 * already stored in httpd access logs.
 *
 * Revision 1.24  2004/05/27 20:23:15  gpowers
 * Added "Vcard": Export one contact to a Vcard
 * Patch [ 951084 ] Export VCARD
 * Submitted By: frenchman
 *
 * Revision 1.23  2004/05/21 13:06:10  maulani
 * - Create get_formatted_address function which centralizes the address
 *   formatting code into one routine in utils-misc.
 *
 * Revision 1.22  2004/05/21 12:23:26  braverock
 * - add todo item to break out address query
 *
 * Revision 1.21  2004/05/10 13:07:22  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.20  2004/04/27 15:12:59  gpowers
 * added support for activity times
 *
 * Revision 1.19  2004/04/19 22:19:54  maulani
 * - Adjust table for CSS2 positioning
 *
 * Revision 1.18  2004/04/17 16:03:45  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.17  2004/04/16 22:20:55  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.16  2004/04/12 16:20:38  maulani
 * - Fix bug where check was erroneously made for $rst instead of $rst2
 *
 * Revision 1.15  2004/04/10 16:25:29  braverock
 * - add calendar pop-up to new activity
 *   - apply SF patch 927141 submitted by "s-t"
 *
 * Revision 1.14  2004/03/07 14:07:57  braverock
 * - use centralized side-bar code in advance of i18n conversion
 *
 * Revision 1.13  2004/02/06 22:47:37  maulani
 * Use ends_at to determine if activity is overdue
 *
 * Revision 1.12  2004/01/26 19:13:34  braverock
 * - added company division fields
 * - added phpdoc
 */
?>