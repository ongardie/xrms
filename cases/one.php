<?php
/**
 * View a single Service Case
 *
 * $Id: one.php,v 1.14 2004/06/12 04:08:06 introspectshun Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$case_id = $_GET['case_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "cases", $case_id);

$sql = "select ca.*, c.company_id, c.company_name, c.company_code,
cont.first_names, cont.last_name, cont.work_phone, cont.email,
cas.case_status_display_html, cap.case_priority_display_html, cat.case_type_display_html,
u1.username as entered_by_username, u2.username as last_modified_by_username,
u3.username as case_owner_username, u4.username as account_owner_username,
as1.account_status_display_html, r.rating_display_html, crm_status_display_html
from cases ca, case_statuses cas, case_priorities cap, case_types cat, companies c, contacts cont,
users u1, users u2, users u3, users u4, account_statuses as1, ratings r, crm_statuses crm
where ca.company_id = c.company_id
and ca.case_status_id = cas.case_status_id
and ca.case_priority_id = cap.case_priority_id
and ca.case_type_id = cat.case_type_id
and ca.contact_id = cont.contact_id
and ca.entered_by = u1.user_id
and ca.last_modified_by = u2.user_id
and ca.user_id = u3.user_id
and c.user_id = u4.user_id
and c.account_status_id = as1.account_status_id
and c.rating_id = r.rating_id
and c.crm_status_id = crm.crm_status_id
and case_id = $case_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $company_name = $rst->fields['company_name'];
    $company_code = $rst->fields['company_code'];
    $contact_id = $rst->fields['contact_id'];
    $first_names = $rst->fields['first_names'];
    $last_name = $rst->fields['last_name'];
    $work_phone = $rst->fields['work_phone'];
    $email = $rst->fields['email'];
    $crm_status_display_html = $rst->fields['crm_status_display_html'];
    $account_status_display_html = $rst->fields['account_status_display_html'];
    $rating_display_html = $rst->fields['rating_display_html'];
    $contact_id = $rst->fields['contact_id'];
    $case_status_display_html = $rst->fields['case_status_display_html'];
    $case_priority_display_html = $rst->fields['case_priority_display_html'];
    $case_type_display_html = $rst->fields['case_type_display_html'];
    $account_owner_username = $rst->fields['account_owner_username'];
    $case_title = $rst->fields['case_title'];
    $case_description = nl2br($rst->fields['case_description']);
    $case_owner_username = $rst->fields['case_owner_username'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];
    $rst->close();
}

// most recent activities

$sql_activities = "select activity_id,
activity_title,
scheduled_at,
a.entered_at,
a.on_what_table,
a.on_what_id,
activity_status,
at.activity_type_pretty_name,
cont.contact_id,
cont.first_names as contact_first_names,
cont.last_name as contact_last_name,
u.username,
(CASE WHEN (activity_status = 'o') AND (ends_at < " . $con->SQLDate('Y-m-d') . ") THEN 1 ELSE 0 END) AS is_overdue
from activity_types at, users u, activities a left join contacts cont on a.contact_id = cont.contact_id
where a.on_what_table = 'cases'
and a.on_what_id = $case_id
and a.user_id = u.user_id
and a.activity_type_id = at.activity_type_id
and a.activity_record_status = 'a'
order by is_overdue desc, a.scheduled_at desc, a.entered_at desc";

$rst = $con->selectlimit($sql_activities, $display_how_many_activities_on_contact_page);

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

        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'><a href='$http_site_root/activities/one.php?return_url=/contacts/one.php?contact_id=$contact_id&activity_id=" . $rst->fields['activity_id'] . "'>" . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['username'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['contact_first_names'] . ' ' . $rst->fields['contact_last_name'] . "</td>";
        $activity_rows .= '<td colspan=2 class=' . $classname . '>' . $con->userdate($rst->fields['scheduled_at']) . '</td>';
        $activity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$categories_sql = "select category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'cases'
and ecm.on_what_id = $case_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'cases'
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($categories_sql);
$categories = array();

if ($rst) {
    while (!$rst->EOF) {
        array_push($categories, $rst->fields['category_pretty_name']);
        $rst->movenext();
    }
    $rst->close();
}

$categories = implode(', ', $categories);

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the siddebars
$on_what_table = 'cases';
$on_what_id = $case_id;
$on_what_string = 'case';

//include the Cases sidebar
//$case_limit_sql = "and cases.".$on_what_string."_id = $on_what_id";
//require_once("../cases/sidebar.php");

//include the opportunities sidebar
//$opportunity_limit_sql = "and opportunities.".$on_what_string."_id = $on_what_id";
//require_once("../opportunities/sidebar.php");

//include the files sidebar
require_once("../files/sidebar.php");

//include the notes sidebar
require_once("../notes/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . ", contact_id FROM contacts WHERE company_id = $company_id AND contact_record_status = 'a' ORDER BY last_name";
$rst = $con->execute($sql);
if ($rst) {
    $contact_menu = $rst->getmenu2('contact_id', $contact_id, true);
    $rst->close();
}

$con->close();

if (strlen($categories) == 0) {
    $categories = "No categories";
}

$page_title = "Case #$case_id: $case_title";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Case Details</td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Title</td>
                                    <td class=clear><?php  echo $case_title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Type</td>
                                    <td class=clear><?php  echo $case_type_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Owner</td>
                                    <td class=clear><?php  echo $case_owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Status</td>
                                    <td class=clear><?php  echo $case_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Priority</td>
                                    <td class=clear><?php  echo $case_priority_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Created</td>
                                    <td class=clear><?php  echo $entered_at; ?> (<?php  echo $entered_by; ?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Last Modified</td>
                                    <td class=clear><?php  echo $last_modified_at; ?> (<?php  echo $last_modified_by; ?>)</td>
                                </tr>
                                </table>
                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel>Contact</td>
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/contacts/one.php?contact_id=<?php  echo $contact_id; ?>"><?php  echo $first_names; ?> <?php  echo $last_name; ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Work Phone</td>
                                    <td class=clear><?php  echo $work_phone; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>E-Mail</td>
                                    <td class=clear><a href='mailto:<?php echo $email . "' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&on_what_id=$case_id&contact_id=$contact_id&on_what_table=cases&activity_title=email RE: $case_title&company_id=$company_id&email=$email&return_url=/cases/one.php?case_id=$case_id'\" >" . htmlspecialchars($email); ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Company</td>
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>"><?php  echo $company_name; ?></a> (<?php  echo $company_code; ?>)</td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Acct. Owner</td>
                                    <td class=clear><?php  echo $account_owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>CRM Status</td>
                                    <td class=clear><?php  echo $crm_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>Account Status</td>
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
                <td class=widget_content_form_element><input class=button type=button value="Edit" onclick="javascript: location.href='edit.php?case_id=<?php  echo $case_id; ?>';"></td>
            </tr>
        </table>

        <!-- activities //-->
        <form action="../activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/cases/one.php?case_id=<?php  echo $case_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id ?>">
        <input type=hidden name=on_what_table value="cases">
        <input type=hidden name=on_what_id value="<?php  echo $case_id; ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6>Activities</td>
            </tr>
            <tr>
                <td class=widget_label>Title</td>
                <td class=widget_label>User</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Contact</td>
                <td colspan=2 class=widget_label>On</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title size=50></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
                <td colspan=2 class=widget_content_form_element><input type=text size=12 name=scheduled_at value="<?php  echo date('Y-m-d'); ?>"> <input class=button type=submit value="Add"> <input class=button type=button onclick="javascript: markComplete();" value="Done"></td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Categories</td>
            </tr>
            <tr>
                <td class=widget_content><?php  echo $categories; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=button class=button onclick="javascript: location.href='categories.php?case_id=<?php  echo $case_id; ?>';" value="Manage"></td>
            </tr>
        </table>

        <!-- notes //-->
        <?php echo $note_rows; ?>

        <!-- files //-->
        <?php echo $file_rows; ?>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
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
