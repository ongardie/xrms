<?php
/**
 * One Contact Page
 *
 * This page allows for the viewing of the details for a single contact.
 *
 * @todo break the parts of the contact details qey into seperate queries (e.g. addresses)
 *       to make the entire process more resilient.
 *
 * $Id: contacts_one.php,v 1.2 2005/03/21 13:40:57 maulani Exp $
 */
require_once('../../include-locations.inc');

$include_locations_location = "../../";
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

// make sure $msg is never undefined
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$contact_id = $_GET['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "contacts", $contact_id);

$sql = "select cont.*,
c.company_id, company_name, company_code,
u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as account_owner,
account_status_display_html, crm_status_display_html
from contacts cont, companies c, users u1, users u2, users u3, account_statuses as1, crm_statuses crm
where cont.company_id = c.company_id
and cont.entered_by = u1.user_id
and cont.last_modified_by = u2.user_id
and c.user_id = u3.user_id
and c.account_status_id = as1.account_status_id
and c.crm_status_id = crm.crm_status_id
and contact_id = $contact_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $address_id = $rst->fields['address_id'];
    $company_name = $rst->fields['company_name'];
    $company_code = $rst->fields['company_code'];
    $division_id  = $rst->fields['division_id'];
    $crm_status_display_html = $rst->fields['crm_status_display_html'];
    $account_status_display_html = $rst->fields['account_status_display_html'];
    $account_owner = $rst->fields['account_owner'];
    $last_name = $rst->fields['last_name'];
    $first_names = $rst->fields['first_names'];
    $salutation = $rst->fields['salutation'];
    $date_of_birth = $con->userdate($rst->fields['date_of_birth']);
    $gender = $rst->fields['gender'];
    $summary = $rst->fields['summary'];
    $title = $rst->fields['title'];
    $description = $rst->fields['description'];
    $profile = $rst->fields['profile'];
    $email = $rst->fields['email'];
    $work_phone = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']);
    $cell_phone = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['cell_phone']);
    $home_phone = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['home_phone']);
    $fax = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['fax']);
    $aol_name = $rst->fields['aol_name'];
    $yahoo_name = $rst->fields['yahoo_name'];
    $msn_name = $rst->fields['msn_name'];
    $interests = $rst->fields['interests'];
    $custom1 = $rst->fields['custom1'];
    $custom2 = $rst->fields['custom2'];
    $custom3 = $rst->fields['custom3'];
    $custom4 = $rst->fields['custom4'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];
    $rst->close();
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
  $address_to_display = get_formatted_address($con, $address_id);
} else {
  $address_to_display = '';
}

// most recent activities
$sql_activities = "
SELECT
  a.activity_id, a.activity_title, a.scheduled_at, a.entered_at, a.on_what_table, a.on_what_id,
  a.activity_status, at.activity_type_pretty_name,
  cont.contact_id, cont.first_names AS contact_first_names,
  cont.last_name AS contact_last_name, u.username,
CASE
  WHEN ((a.activity_status = 'o') AND (a.scheduled_at < " . $con->SQLDate('Y-m-d') . ")) THEN 1
  ELSE 0
END AS is_overdue
FROM activity_types at, users u, activities a, contacts cont
WHERE a.contact_id = $contact_id
  AND a.contact_id = cont.contact_id
  AND a.user_id = u.user_id
  AND a.activity_type_id = at.activity_type_id
  AND a.activity_record_status = 'a'
ORDER BY is_overdue DESC, a.scheduled_at DESC, a.entered_at DESC
";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_contact_page);
$activity_rows = '';

if ($rst) {
    while (!$rst->EOF) {

        $open_p = $rst->fields['activity_status'];
        $scheduled_at = $rst->unixtimestamp($rst->fields['scheduled_at']);
        $is_overdue = $rst->fields['is_overdue'];
        $on_what_table = $rst->fields['on_what_table'];
        $on_what_id = $rst->fields['on_what_id'];

        if ($open_p == 'o') {
            if ($is_overdue) {
                $classname = 'overdue_activity';
            } else {
                $classname = 'open_activity';
            }
        } else {
            $classname = 'closed_activity';
        }

        if ($on_what_table == 'opportunities') {
            $attached_to_link = "<a href='$http_site_root/opportunities/one.php?opportunity_id=$on_what_id'>";
            $sql2 = "select opportunity_title as attached_to_name
                    from opportunities
                    where opportunity_id = $on_what_id";
        } elseif ($on_what_table == 'cases') {
            $attached_to_link = "<a href='$http_site_root/cases/one.php?case_id=$on_what_id'>";
            $sql2 = "select case_title as attached_to_name from cases where case_id = $on_what_id";
        } else {
            $attached_to_link = _("N/A");
            $sql2 = "select * from companies where 1 = 2";
        }

        $rst2 = $con->execute($sql2);

       if ($rst2) {
            $attached_to_name = $rst2->fields['attached_to_name'];
            $attached_to_link .= $attached_to_name . "</a>";
            $rst2->close();
        }

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'>
                                <a href='$http_site_root/activities/one.php?return_url=/contacts/one.php?contact_id=$contact_id&activity_id="
                                . $rst->fields['activity_id'] . "'>"
                                . $rst->fields['activity_title']
                                . '</a></td>';

        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . ">$attached_to_link</td>";
        $activity_rows .= '<td colspan=2 class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

// division
$division_row = '';
if ($division_id != '') {
    $division_sql = "select division_id, division_name from company_division where division_id = $division_id";
    $div_rst = $con->execute($division_sql);

    if ($div_rst) {
        $division_row .= '<input type=hidden name=division_id value='.$div_rst->fields['division_id'].'>'
                       .  $div_rst->fields['division_name'];
    }
} //end division select

// associated with
$categories_sql = "select category_display_html
        from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
        where ecm.on_what_table = 'contacts'
        and ecm.on_what_id = $contact_id
        and ecm.category_id = c.category_id
        and cs.category_scope_id = ccsm.category_scope_id
        and c.category_id = ccsm.category_id
        and cs.on_what_table = 'contacts'
        and category_record_status = 'a'
        order by category_display_html";

$rst = $con->execute($categories_sql);
$categories = array();

if ($rst) {
    while (!$rst->EOF) {
        array_push($categories, $rst->fields['category_display_html']);
        $rst->movenext();
    }
    $rst->close();
}

$categories = implode(', ', $categories);

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the siddebars
$on_what_table = 'contacts';
$on_what_id = $contact_id;
$on_what_string = 'contact';

//include the Cases sidebar
$case_limit_sql = "and cases.".$on_what_string."_id = $on_what_id";
require_once( $include_locations_location . 'cases/sidebar.php');

//include the opportunities sidebar
$opportunity_limit_sql = "and opportunities.".$on_what_string."_id = $on_what_id";
require_once( $include_locations_location . 'opportunities/sidebar.php');

//include the contacts-companies sidebar
$working_direction = "from";
$relationship_name = "company link";
$overall_id = $contact_id;
require_once( $include_locations_location . 'relationships/sidebar.php');

//include the files sidebar
require_once( $include_locations_location . 'files/sidebar.php');

//include the notes sidebar
require_once( $include_locations_location . 'notes/sidebar.php');

/** End of the sidebar includes **/
/*********************************/

// we should allow users to delete this contact if there are others
/*
$sql = "select count(contact_id) as contact_count from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $contact_count = $rst->fields['contact_count'];
    $rst->close();
}
*/

$user_menu = get_user_menu($con, $session_user_id);

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
    $rst->close();
}

$con->close();

if (strlen($categories) == 0) {
    $categories = _("No categories");
}

$page_title = _("Contact Details").': '.$first_names . ' ' . $last_name;
//start_page($page_title, true, $msg);

?>



<script language="JavaScript" type="text/javascript">
<!--

function openMsnSession(strIMAddress) {
    objMessengerApp.LaunchIMUI(strIMAddress);
}

function markComplete() {
    document.forms[0].activity_status.value = "c";
    document.forms[0].submit();
}

//-->
</script>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Contact Details"); ?><br><br></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Name"); ?></td>
                                </tr>
                                <tr>    
                                    <td class=clear><?php  echo $last_name . ', ' . $salutation . ' ' . $first_names; ?></td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Gender"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $gender; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Summary"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $summary; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Title"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $title; ?></td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Description"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $description; ?></td>
                                </tr>-->
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Date of Birth"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $date_of_birth; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("E-Mail"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear>
                                    <a href='mailto:<?php echo $email."' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&on_what_id=$contact_id&contact_id=$contact_id&company_id=$company_id&email=$email&activity_title=email to $first_names $last_name&return_url=$http_site_root/contacts/one.php?contact_id=$contact_id";?>'">
                                    <?php echo htmlspecialchars($email); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Work Phone"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $work_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Home Phone"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $home_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Cell Phone"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $cell_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Fax"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $fax; ?></td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Interests"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $interests; ?></td>
                                </tr>-->
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Yahoo! IM"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear>
                                    <?php if (strlen($yahoo_name) > 0) {echo("<a href='ymsgr:sendim?$yahoo_name'><img border=0 src='http://opi.yahoo.com/online?u=$yahoo_name&m=g&t=3'></a>");}; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("MSN IM"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear>
                                    <?php if (strlen($msn_name) > 0) {echo("<a href=\"javascript: openMsnSession('$msn_name');\">$msn_name</a>");}; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("AOL IM"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear>
                                    <?php if (strlen($aol_name) > 0) {echo("<a href='aim:goim?screenname=$aol_name'>$aol_name</a>");}; ?>
                                    </td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Company"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><a href="companies_one.php?company_id=<?php echo $company_id;; ?>"><?php echo $company_name; ?></a> (<?php echo $company_code;?>)</td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Division"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $division_row; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Account Owner"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $account_owner; ?></td>
                                </tr>
                                <!--<tr>
                                    <td class=sublabel><?php echo _("CRM Status"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $crm_status_display_html; ?></td>
                                </tr>-->
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Account Status"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $account_status_display_html; ?></td>
                                </tr>-->
                                <tr>
                                    <td class=sublabel><?php echo _("Address"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php echo $address_to_display ?></td>
                                </tr>
                                <!--<tr>
                                    <td width=1% class=sublabel><?php  echo $contact_custom1_label; ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom1; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $contact_custom2_label; ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom2; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $contact_custom3_label; ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom3; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php  echo $contact_custom4_label; ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $custom4; ?></td>
                                </tr>-->
                                <!--<tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $entered_at; ?> by <?php echo $entered_by; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                </tr>
                                <tr>
                                    <td class=clear><?php  echo $last_modified_at; ?> by <?php echo $last_modified_by; ?></td>
                                </tr>-->
                    </table>

                    <!--<p><?php  echo $profile; ?>-->

                </td>
            </tr>
            <!--<tr>
                <td class=widget_content_form_element>
                    <input class=button type=button value="<?php echo _("Edit"); ?>" onclick="javascript: location.href='edit.php?contact_id=<?php echo $contact_id; ?>';">
                    <?php do_hook('one_contact_buttons'); ?>
                </td>
            </tr>-->
        </table>
        <?php //jscalendar_includes(); ?>
        <!-- activities //-->
        <!--<form action="<?php  echo $http_site_root; ?>/activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/contacts/one.php?contact_id=<?php  echo $contact_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id; ?>">
        <input type=hidden name=contact_id value="<?php echo $contact_id; ?>">
        <input type=hidden name=activity_status value="o">-->
        <!--<table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6><?php echo _("Activities"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("User"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("About"); ?></td>
                <td colspan=2 class=widget_label><?php echo _("On"); ?></td>
            </tr>
          <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element>&nbsp;</td>
                <td colspan=2 class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=scheduled_at value="<?php  echo date('Y-m-d H:i:s'); ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                    <input class=button type=submit value="<?php echo _("Add"); ?>">
                    <input class=button type=button onclick="javascript: markComplete();" value="<?php echo _("Done"); ?>">
                </td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>-->
        </form>

    </div>

        <!-- right column //-->
    <!--<div id="Sidebar">-->

        <!-- categories //-->
        <!--<table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Categories"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?contact_id=<?php  echo $contact_id; ?>';" value="<?php echo _("Manage"); ?>"></td>
            </tr>
        </table>-->

        <!-- opportunities //-->
        <?php //echo $opportunity_rows; ?>

        <!-- cases //-->
        <?php //echo $case_rows; ?>

        <!-- relationship links //-->
        <?php //echo $relationship_link_rows; ?>

        <!-- notes //-->
        <?php //echo $note_rows; ?>

        <!-- files //-->
        <?php //echo $file_rows; ?>

    <!--</div>-->

</div>

<script language="JavaScript" type="text/javascript">

Calendar.setup({
        inputField     :    "f_date_c",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_c",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });

</script>

<?php

end_page();

/**
 * $Log: contacts_one.php,v $
 * Revision 1.2  2005/03/21 13:40:57  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.1  2004/08/23 01:44:58  d2uhlman
 * very basic screens to access contact, company, search by phone plugin, need feedback, no entry possible yet
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
