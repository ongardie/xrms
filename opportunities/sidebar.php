<?php
/**
 * Sidebar box for Opportunities
 *
 * $Id: sidebar.php,v 1.2 2004/03/15 16:51:28 braverock Exp $
 */

$opportunity_rows = "
        <table class=widget cellspacing=1 width=100%>
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
$opportunity_limit_sql
order by close_at, sort_order
limit 5";

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

//execute our query
$rst = $con->execute($opportunity_sql);

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
if ((strlen($company_id) > 0)  or (strlen($contact_id) > 0)) {
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
$opportunity_rows .= "        </table>\n";

/**
 * $Log: sidebar.php,v $
 * Revision 1.2  2004/03/15 16:51:28  braverock
 * - add sort_order to opportunity sidebar
 *
 * Revision 1.1  2004/03/07 14:02:28  braverock
 * Initital Checkin of side-bar centralization
 *
 */
?>