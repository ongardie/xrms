<?php
/**
 * Sidebar box for Cases
 *
 * $Id: sidebar.php,v 1.12 2005/01/11 22:32:05 braverock Exp $
 */
if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/*
Commented until ACL system is fully implemented
$caseList=get_list($session_user_id, 'Read', false, 'cases');
if (!$caseList) { $case_rows=''; return false; }
else { $caseList=implode(",",$caseList); $case_limit_sql.=" AND cases.case_id IN ($caseList) "; }
*/

$case_rows = "<div id='case_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header colspan=5>" . _("Open Cases") . "</td>
            </tr>
            <tr>
                <td class=widget_label>" . _("Name") . "</td>
                <td class=widget_label>" . _("Owner") . "</td>
                <td class=widget_label>" . _("Priority") . "</td>
                <td class=widget_label>" . _("Due") . "</td>
            </tr>\n";

//build the cases sql query
$cases_sql = "select * from cases, case_priorities, users, case_statuses
                where cases.case_priority_id = case_priorities.case_priority_id
                and cases.user_id = users.user_id
                and case_record_status = 'a'
                and cases.case_status_id = case_statuses.case_status_id
                and case_statuses.status_open_indicator = 'o'
                $case_limit_sql
                order by due_at";

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

//execute our query
$rst = $con->SelectLimit($cases_sql, 5, 0);

if (strlen($rst->fields['username'])>0) {
    while (!$rst->EOF) {
        $case_rows .= '<tr>';
        $case_rows .= "<td class=widget_content><a href='$http_site_root/cases/one.php?case_id=" . $rst->fields['case_id'] . "'>" . $rst->fields['case_title'] . '</a></td>';
        $case_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $case_rows .= '<td class=widget_content>' . _($rst->fields['case_priority_pretty_name']) . '</td>';
        $case_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['due_at']) . '</td>';
        $case_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    $case_rows .= "            <tr> <td class=widget_content colspan=5> " . _("No open cases") . " </td> </tr>\n";
}

//put in the new and search buttons
if ( (isset($company_id) && (strlen($company_id) > 0))  or (isset($contact_id) && (strlen($contact_id) > 0))) {
    $new_case_button=render_create_button("New",'submit');
    if ($new_case_button) {
        $case_type_sql = "select case_type_pretty_name, case_type_id FROM case_types";
        $type_rst=$con->execute($case_type_sql);
        $new_case_types=$type_rst->getmenu2('case_type_id', '', false);
        $new_case_button=$new_case_types.$new_case_button; 
    }
    $case_rows .= "
            <tr>
                <form action='".$http_site_root."/cases/new.php' method='post'>
                <input type='hidden' name='company_id' value='$company_id'>
                <input type='hidden' name='division_id' value='$division_id'>
                <input type='hidden' name='contact_id' value='$contact_id'>
                <td class=widget_content_form_element colspan=5>
                    $new_case_button
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/cases/some.php';\" value='" . _("Search") . "'>
                </td>
                </form>
            </tr>\n";
} else {
    $case_rows .="
            <tr>
                <td class=widget_content_form_element colspan=5>
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/cases/some.php';\" value='" . _("Search") . "'>
                </td>
            </tr>\n";
}

//now close the table, we're done
$case_rows .= "        </table>\n</div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.12  2005/01/11 22:32:05  braverock
 * - localize case type pretty name in sidebar
 *
 * Revision 1.11  2005/01/11 22:30:28  braverock
 * - update to use case_type_pretty_name instead of case_type_short_name in new dropdown
 *
 * Revision 1.10  2005/01/10 21:53:50  vanmer
 * - added short name for case type when adding a new case
 *
 * Revision 1.9  2005/01/06 20:55:10  vanmer
 * - added division_id to new case sidebar button
 * - added commented ACL authentication to top of sidebar
 * - added call to render button to create New Case button
 *
 * Revision 1.8  2004/07/16 07:11:17  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.7  2004/07/14 14:49:26  cpsource
 * - All sidebar.php's now support IN_XRMS security feature.
 *
 * Revision 1.6  2004/07/14 12:08:19  cpsource
 * - Fix uninitialized variable usage
 *
 * Revision 1.5  2004/06/12 06:33:16  introspectshun
 * - Now use ADODB SelectLimit function.
 *
 * Revision 1.4  2004/04/10 15:20:52  braverock
 * - display only open Cases on Cases sidebar
 *   - apply SF patch 931251 submitted by Glenn Powers
 *
 * Revision 1.3  2004/04/07 19:38:25  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.2  2004/04/07 13:50:52  maulani
 * - Set CSS2 positioning for the home page
 *
 * Revision 1.1  2004/03/07 14:06:19  braverock
 * Initital Checkin of side-bar centralization
 *
 */
?>