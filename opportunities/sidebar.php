<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * Sidebar box for Opportunities
 *
 * $Id: sidebar.php,v 1.12 2005/01/06 20:46:58 vanmer Exp $
 */
/*
Commented until ACL system is implemented
$opList=get_list($session_user_id, 'Read', false, 'opportunities');
if (!$opList) { $opportunity_rows=''; return false; }
else { $opList=implode(",",$opList); $opportunity_limit_sql.=" AND opportunities.opportunity_id IN ($opList) "; }
*/

$opportunity_rows = "<div id='opportunity_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header colspan=4>" . _("Opportunities") . "</td>
            </tr>
            <tr>
                <td class=widget_label>" . _("Name") . "</td>
                <td class=widget_label>" . _("Owner") . "</td>
                <td class=widget_label>" . _("Status") . "</td>
                <td class=widget_label>" . _("Due") . "</td>
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
$rst = $con->SelectLimit($opportunity_sql, 5, 0);

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
    $opportunity_rows .= "<tr> <td class=widget_content colspan=4>" . _("No open opportunities") . "</td> </tr>";
}

//put in the new and search buttons
if ( (isset($company_id) && (strlen($company_id) > 0))  or (isset($contact_id) && (strlen($contact_id) > 0)) ) {
    $new_button=render_create_button('New','submit');
    $opportunity_rows .= "
            <tr>
                <form action='".$http_site_root."/opportunities/new.php' method='post'>
                <input type='hidden' name='company_id' value='$company_id'>
                <input type='hidden' name='division_id' value='$division_id'>
                <input type='hidden' name='contact_id' value='$contact_id'>
                <td class=widget_content_form_element colspan=4>
                    $new_button
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/opportunities/some.php';\" value='" . _("Search") . "'>
                </td>
                </form>
            </tr>\n";
} else {
    $opportunity_rows .="
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/opportunities/some.php';\" value='" . _("Search") . "'>
                </td>
            </tr>\n";
}

//now close the table, we're done
$opportunity_rows .= "        </table>\n</div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.12  2005/01/06 20:46:58  vanmer
 * - added division_id to new opportunity sidebar button
 * - added commented ACL authentication to top of sidebar
 * - added call to render button to create New Opportunity button
 *
 * Revision 1.11  2004/10/01 14:21:26  introspectshun
 * - Fixed a typo so xrms_sql_limit now works
 *
 * Revision 1.10  2004/09/30 20:23:52  dmazand
 * added xrms_sql_limit to define the number or opportunities displayed in the sidebar. Configure in vars.php
 *
 * Revision 1.9  2004/07/20 19:38:31  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.8  2004/07/14 14:49:27  cpsource
 * - All sidebar.php's now support IN_XRMS security feature.
 *
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
