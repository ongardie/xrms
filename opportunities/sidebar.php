<?php
/**
 * Sidebar box for Opportunities
 *
 * $Id: sidebar.php,v 1.7 2004/07/14 12:21:41 cpsource Exp $
 */

$opportunity_rows = "<div id='opportunity_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header colspan=4>Opportunities</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Status</td>
                <td class=widget_label>Due</td>
            </tr>\n";

//build the cases sql query
$opportunity_sql = "select * from opportunities, opportunity_statuses, users
where opportunities.opportunity_status_id = opportunity_statuses.opportunity_status_id
and opportunities.user_id = users.user_id
and opportunity_record_status = 'a'
and opportunity_statuses.status_open_indicator = 'o'
$opportunity_limit_sql
order by close_at, sort_order";

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

//execute our query
$rst = $con->SelectLimit($opportunity_sql, 1, 0);

if (strlen($rst->fields['username'])>0) {
    while (!$rst->EOF) {
        $opportunity_rows .= '<tr>';
        $opportunity_rows .= "<td class=widget_content><a href='$http_site_root/opportunities/one.php?opportunity_id=" . $rst->fields['opportunity_id'] . "'>" . $rst->fields['opportunity_title'] . '</a></td>';
        $opportunity_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
        $opportunity_rows .= '<td class=widget_content>' . $rst->fields['opportunity_status_pretty_name'] . '</td>';
        $opportunity_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['close_at']) . '</td>';
        $opportunity_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    $opportunity_rows .= '<tr> <td class=widget_content colspan=4> No open opportunities </td> </tr>';
}

//put in the new and search buttons
if ( (isset($company_id) && (strlen($company_id) > 0))  or (isset($contact_id) && (strlen($contact_id) > 0)) ) {
    $opportunity_rows .= "
            <tr>
                <form action='".$http_site_root."/opportunities/new.php' method='post'>
                <input type='hidden' name='company_id' value='$company_id'>
                <input type='hidden' name='contact_id' value='$contact_id'>
                <td class=widget_content_form_element colspan=4>
                    <input type=submit class=button value='New'>
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/opportunities/some.php';\" value='Search'>
                </td>
                </form>
            </tr>\n";
} else {
    $opportunity_rows .="
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/opportunities/some.php';\" value='Search'>
                </td>
            </tr>\n";
}

//now close the table, we're done
$opportunity_rows .= "        </table>\n</div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.7  2004/07/14 12:21:41  cpsource
 * - Resolve uninitialized variable usage
 *
 * Revision 1.6  2004/06/14 17:41:36  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, Concat and Date functions.
 *
 * Revision 1.5  2004/04/19 12:12:10  braverock
 * - show only open opportunities in sidebar
 *
 * Revision 1.4  2004/04/07 19:38:26  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.3  2004/04/07 13:50:54  maulani
 * - Set CSS2 positioning for the home page
 *
 * Revision 1.2  2004/03/15 16:51:28  braverock
 * - add sort_order to opportunity sidebar
 *
 * Revision 1.1  2004/03/07 14:02:28  braverock
 * Initital Checkin of side-bar centralization
 *
 */
?>