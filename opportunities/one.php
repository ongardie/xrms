<?php
/**
 * View a single Sales Opportunity
 *
 * $Id: one.php,v 1.32 2005/01/11 23:13:35 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$opportunity_id = isset($_GET['opportunity_id']) ? $_GET['opportunity_id'] : '';
$on_what_id=$opportunity_id;
$session_user_id = session_check();

$msg            = isset($_GET['msg']) ? $_GET['msg'] : '';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

update_recent_items($con, $session_user_id, "opportunities", $opportunity_id);

$sql = "select
o.*,
c.company_id, c.company_name, c.company_code,
d.division_name,
cont.first_names, cont.last_name, cont.work_phone, cont.email, cont.address_id,
u1.username as entered_by_username, u2.username as last_modified_by_username,
u3.username as opportunity_owner_username, u4.username as account_owner_username,
as1.account_status_display_html, r.rating_display_html, crm_status_display_html, os.opportunity_status_display_html, cam.campaign_title
from
companies c, contacts cont, users u1, users u2, users u3, users u4,
account_statuses as1, ratings r, crm_statuses crm, opportunity_statuses os,
opportunities o left join campaigns cam on o.campaign_id = cam.campaign_id
left join company_division d on o.division_id=d.division_id
where o.company_id = c.company_id
and o.contact_id = cont.contact_id
and o.entered_by = u1.user_id
and o.last_modified_by = u2.user_id
and o.user_id = u3.user_id
and c.user_id = u4.user_id
and c.account_status_id = as1.account_status_id
and c.rating_id = r.rating_id
and c.crm_status_id = crm.crm_status_id
and o.opportunity_status_id = os.opportunity_status_id
and opportunity_id = $opportunity_id";

// execute
$rst = $con->execute($sql);

// was there a database error ???
if ($rst) {
  // no
  // was there a row ???
  if ( !$rst->EOF ) {
    // yes - there is a row
    $company_id = $rst->fields['company_id'];
    $division_id = $rst->fields['division_id'];
    $division_name=$rst->fields['division_name'];
    $company_name = $rst->fields['company_name'];
    $company_code = $rst->fields['company_code'];
    $contact_id = $rst->fields['contact_id'];
    $first_names = $rst->fields['first_names'];
    $last_name = $rst->fields['last_name'];
    $work_phone = get_formatted_phone($con, $rst->fields['address_id'], $rst->fields['work_phone']);
    $email = $rst->fields['email'];
    $crm_status_display_html = $rst->fields['crm_status_display_html'];
    $account_status_display_html = $rst->fields['account_status_display_html'];
    $rating_display_html = $rst->fields['rating_display_html'];
    $contact_id = $rst->fields['contact_id'];
    $campaign_id = $rst->fields['campaign_id'];
    $campaign_title = $rst->fields['campaign_title'];
    $opportunity_status_display_html = $rst->fields['opportunity_status_display_html'];
    $opportunity_owner_username = $rst->fields['opportunity_owner_username'];
    $account_owner_username = $rst->fields['account_owner_username'];
    $opportunity_title = htmlspecialchars($rst->fields['opportunity_title']);
    $opportunity_description = $rst->fields['opportunity_description'];
    $size = $rst->fields['size'];
    $probability = $rst->fields['probability'];
    $close_at = $con->userdate($rst->fields['close_at']);
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];
  } else {
    // no - there is no row
    $company_id = '';
    $division_id = '';
    $division_name = '';
    $company_name = '';
    $company_code = '';
    $contact_id = '';
    $first_names = '';
    $last_name = '';
    $work_phone = '';
    $email = '';
    $crm_status_display_html = '';
    $account_status_display_html = '';
    $rating_display_html = '';
    $contact_id = '';
    $campaign_id = '';
    $campaign_title = '';
    $opportunity_status_display_html = '';
    $opportunity_owner_username = '';
    $account_owner_username = '';
    $opportunity_title = '';
    $opportunity_description = '';
    $size = '';
    $probability = '';
    $close_at = '';
    $entered_at = '';
    $last_modified_at = '';
    $entered_by = '';
    $last_modified_by = '';
  }

  $rst->close();

} else {
  // yes
  db_error_handler ($con, $sql);
}

// most recent activities

$sql_activities = "
SELECT
  a.activity_id, a.activity_title, a.scheduled_at, a.on_what_table, a.on_what_id,
  a.entered_at, a.activity_status, at.activity_type_pretty_name,
  cont.contact_id, cont.first_names AS contact_first_names,
  cont.last_name AS contact_last_name, u.username,
CASE
  WHEN ((a.activity_status = 'o') AND (a.scheduled_at < " . $con->SQLDate('Y-m-d') . ")) THEN 1
  ELSE 0
END AS is_overdue
FROM activity_types at, activities a
LEFT JOIN contacts cont ON a.contact_id = cont.contact_id
LEFT JOIN users u ON a.user_id = u.user_id
WHERE a.on_what_table = 'opportunities'
  AND a.on_what_id = $opportunity_id
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
} else {
    db_error_handler ($con, $sql_activities);
}

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the siddebars
$on_what_table = 'opportunities';
$on_what_id = $opportunity_id;

//include the categories sidebar
require_once($include_directory . 'categories-sidebar.php');

// include the contact sidebar code
require_once ('../contacts/sidebar.php');

//include the files sidebar
require_once("../files/sidebar.php");

//include the notes sidebar
require_once("../notes/sidebar.php");

//include the relationships sidebar
$relationships = array('opportunities' => $opportunity_id);
require("../relationships/sidebar.php");

/** End of the sidebar includes **/
/*********************************/

// get user name menu
$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
if ($rst) {
    $user_menu = $rst->getmenu2('user_id', $session_user_id, false);
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    $activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

// get contact names
$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . ", contact_id FROM contacts WHERE company_id = $company_id AND contact_record_status = 'a' ORDER BY last_name";
$rst = $con->execute($sql);
if ($rst) {
    $contact_menu = $rst->getmenu2('contact_id', $contact_id, true);
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

$con->close();

if (strlen($activity_rows) == 0) {
    $activity_rows = "<tr><td class=widget_content colspan=6>" . _("No activities") . "</td></tr>";
}

$page_title = _("Opportunity Details") . " : " . $opportunity_title;
start_page($page_title, true, $msg);

?>

<script language="JavaScript" type="text/javascript">
<!--
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
                <td class=widget_header><?php echo _("Opportunity Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Title"); ?></td>
                                    <td class=clear><?php  echo $opportunity_title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Owner"); ?></td>
                                    <td class=clear><?php  echo $opportunity_owner_username; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Campaign"); ?></td>
                                    <td class=clear><a href="../campaigns/one.php?campaign_id=<?php  echo $campaign_id; ?>"><?php  echo $campaign_title; ?></a></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Size"); ?></td>
                                    <td class=clear>$<?php  echo number_format($size, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Probability"); ?></td>
                                    <td class=clear><?php  echo $probability; ?>%</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Weighted Size"); ?></td>
                                    <td class=clear>$<?php  echo number_format($size * $probability/100, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Status"); ?></td>
                                    <td class=clear>
                                        <?php  echo $opportunity_status_display_html; ?>
                                        <a href="#" onclick="javascript:window.open('opportunity-view.php');"><?php echo _("Status Definitions"); ?></a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Closes"); ?></td>
                                    <td class=clear><?php  echo $close_at; ?></td>
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
                                    <td class=clear>
                                        <a href='mailto:<?php echo $email . "' onclick=\"location.href='../activities/new-2.php?user_id=$session_user_id&activity_type_id=3&on_what_id=$opportunity_id&contact_id=$contact_id&on_what_table=opportunities&activity_title=email RE: $opportunity_title&company_id=$company_id&email=true&return_url=/opportunities/one.php?opportunity_id=$opportunity_id'\" >" . htmlspecialchars($email); ?></a>
                                    </td>
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
                                    <td class=clear><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>&division_id=<?php  echo $division_id; ?>"><?php  echo $division_name; ?></a></td>
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

                    <p>
                    <?php
                        // clean this up for display
                        $opportunity_description = htmlspecialchars ($opportunity_description);
                        $opportunity_description = str_replace("\n", '<br>', $opportunity_description);
                        echo $opportunity_description;
                    ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=button value="<?php echo _("Edit"); ?>" onclick="javascript: location.href='edit.php?opportunity_id=<?php  echo $opportunity_id; ?>';"></td>
            </tr>
        </table>

<?php
    //place the plug-in hook before the Activities
    do_hook ('opportunity_detail');
?>

        <!-- activities //-->
        <form action="../activities/new-2.php" method=post>
        <input type=hidden name=return_url value="/opportunities/one.php?opportunity_id=<?php  echo $opportunity_id; ?>">
        <input type=hidden name=company_id value="<?php echo $company_id ?>">
        <input type=hidden name=on_what_table value="opportunities">
        <input type=hidden name=on_what_id value="<?php  echo $opportunity_id; ?>">
        <input type=hidden name=activity_status value="o">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6><?php echo _("Activities"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("User"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td colspan=2 class=widget_label><?php echo _("On"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
                <td colspan=2 class=widget_content_form_element><input type=text size=12 name=scheduled_at value="<?php  echo date('Y-m-d'); ?>"> <input class=button type=submit value="<?php echo _("Add"); ?>"> <input class=button type=button onclick="javascript: markComplete();" value="<?php echo _("Done"); ?>"></td>
            </tr>
            <?php  echo $activity_rows; ?>
        </table>
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

        <!-- relationships //-->
        <?php echo $relationship_link_rows; ?>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.32  2005/01/11 23:13:35  braverock
 * - removed bad javascript window.open hack, now set empty anchor on current page
 *
 * Revision 1.31  2005/01/11 13:57:24  braverock
 * - removed bad javascript window.open hack - now set empty anchor on current page
 *
 * Revision 1.30  2005/01/11 13:39:59  braverock
 * - removed on_what_string hack, changed to use standard make_singular function
 *
 * Revision 1.29  2005/01/10 20:48:03  neildogg
 * - Changed to support new relationship sidebar variable requirement
 *
 * Revision 1.28  2005/01/07 01:55:07  braverock
 * - add Status definitions link
 *
 * Revision 1.27  2005/01/06 20:51:17  vanmer
 * - moved setup of initial values to above session_check (for ACL)
 * - added division to display of one opportunity, if available
 *
 * Revision 1.26  2004/12/20 21:21:18  neildogg
 * - User 0 support in opportunities
 *
 * Revision 1.25  2004/10/26 16:39:00  introspectshun
 * - Centralized category handling as sidebar
 *
 * Revision 1.24  2004/07/30 10:05:36  cpsource
 * - Remove undefines
 *     activity_rows
 *
 * Revision 1.23  2004/07/29 10:04:20  cpsource
 * - Rid some undefines.
 *
 * Revision 1.22  2004/07/25 14:03:48  johnfawcett
 * - modified string Acct. to Account to unify across application
 * - standardized page title
 *
 * Revision 1.21  2004/07/21 21:10:28  neildogg
 * - Added get_formatted_phone
 *
 * Revision 1.20  2004/07/20 19:38:31  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.19  2004/07/14 22:24:25  braverock
 * - cleaned up some of the SQL syntax
 * - added db_error_handler and rst checks around all queries
 *
 * Revision 1.18  2004/06/14 17:41:36  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, Concat and Date functions.
 *
 * Revision 1.17  2004/06/04 13:49:33  braverock
 * - update email link to improve activity tracking
 *
 * Revision 1.16  2004/06/03 16:16:18  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.15  2004/04/25 22:45:19  braverock
 * clean up formatting of email link
 *
 * Revision 1.14  2004/04/17 15:59:59  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.13  2004/04/16 22:22:41  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.12  2004/04/08 17:13:06  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.11  2004/03/29 21:12:58  maulani
 * - Add plugin hook for quotes attached to an opportunity
 *   (Or other functionality to be displayed on the opportunity detail screen)
 *
 * Revision 1.10  2004/03/09 14:59:05  braverock
 * - removed obsolete code after sidebar conversion
 *
 * Revision 1.9  2004/03/07 14:08:22  braverock
 * - use centralized side-bar code in advance of i18n conversion
 *
 */
?>
