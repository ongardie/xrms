<?php
/**
 * Sidebar box for Cases
 *
 * $Id: sidebar.php,v 1.6 2004/07/14 12:08:19 cpsource Exp $
 */

$case_rows = "<div id='case_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header colspan=5>Open Cases</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Priority</td>
                <td class=widget_label>Due</td>
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
        $case_rows .= '<td class=widget_content>' . $rst->fields['case_priority_pretty_name'] . '</td>';
        $case_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['due_at']) . '</td>';
        $case_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    $case_rows .= "            <tr> <td class=widget_content colspan=5> No open cases </td> </tr>\n";
}

//put in the new and search buttons
if ( (isset($company_id) && (strlen($company_id) > 0))  or (isset($contact_id) && (strlen($contact_id) > 0))) {
    $case_rows .= "
            <tr>
                <form action='".$http_site_root."/cases/new.php' method='post'>
                <input type='hidden' name='company_id' value='$company_id'>
                <input type='hidden' name='contact_id' value='$contact_id'>
                <td class=widget_content_form_element colspan=5>
                    <input type=submit class=button value='New'>
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/cases/some.php';\" value='Search'>
                </td>
                </form>
            </tr>\n";
} else {
    $case_rows .="
            <tr>
                <td class=widget_content_form_element colspan=5>
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/cases/some.php';\" value='Search'>
                </td>
            </tr>\n";
}

//now close the table, we're done
$case_rows .= "        </table>\n</div>";

/**
 * $Log: sidebar.php,v $
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