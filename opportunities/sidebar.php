<?php
/**
 * Sidebar box for Opportunities
 *
 * $Id: sidebar.php,v 1.21 2006/04/17 19:03:43 vanmer Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

///*
//Commented until ACL system is fully implemented
$opList=acl_get_list($session_user_id, 'Read', false, 'opportunities');
if (!$opList) { $opportunity_rows=''; return false; }
else { if ($opList!==true) { $opList=implode(",",$opList); $opportunity_limit_sql.=" AND opportunities.opportunity_id IN ($opList) "; } }
//*/

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');


$opp_sidebar_form_id='SidebarOpportunities';
$opp_sidebar_header=_("Opportunities");
$target=$http_site_root.current_page();


$opportunity_rows = "<div id='opportunity_sidebar'>";

$opportunity_rows.="<form action=\"$target\" name=\"$opp_sidebar_form_id\" method=GET><input type=hidden name=contact_id value=$contact_id><input type=hidden name=company_id value=$company_id><input type=hidden name=division_id value=$division_id>";


if (!$opportunity_sidebar_rows_per_page) {
    $opportunity_sidebar_rows_per_page=5;
}

//build the cases sql query
$close_at = $con->SQLDate('Y-m-D', 'close_at');
$opportunity_sql_select = "select "
. $con->Concat("'<a id=\"'", "opportunities.opportunity_title",  "'\" href=\"$http_site_root/opportunities/one.php?opportunity_id='", "opportunities.opportunity_id", "'\">'", "opportunities.opportunity_title","'</a>'")
. " AS opportunity" . ",
  c.company_name AS company, u.username AS owner " . ",
  ot.opportunity_type_pretty_name AS type,
  CASE
    WHEN (opportunities.size > 0) THEN opportunities.size
    ELSE 0
  END AS opportunity_size" . ",
  CASE
    WHEN (opportunities.size > 0) THEN ((opportunities.size * opportunities.probability) / 100)
    ELSE 0
  END AS weighted_size" . ",
  os.opportunity_status_pretty_name AS status " . ","
  . " $close_at AS close_date, close_at, opportunities.opportunity_title";
  $opportunity_sql_from="opportunities, opportunity_statuses os, opportunity_types ot, users u, companies c";
$opportunity_sql_where="
where opportunities.opportunity_status_id = os.opportunity_status_id
and opportunities.user_id = u.user_id
and opportunities.opportunity_type_id = ot.opportunity_type_id
and opportunities.company_id = c.company_id
and opportunities.opportunity_record_status = 'a'
and os.status_open_indicator = 'o'
$opportunity_limit_sql
";

$opportunity_sql="$opportunity_sql_select FROM $opportunity_sql_from $opportunity_sql_where";

$owner_query_list = "select " . $con->Concat("u.username", "' ('", "count(u.user_id)", "')'") . ", u.user_id FROM $opportunity_sql_from $opportunity_sql_where group by u.username order by u.username";

$owner_query_select = $opportunity_sql . 'AND u.user_id = XXX-value-XXX';

$status_query_list = "select " . $con->Concat("os.opportunity_status_pretty_name", "' ('", "count(os.opportunity_status_id)", "')'") . ", os.opportunity_status_id FROM $opportunity_sql_from $opportunity_sql_where group by os.opportunity_status_id order by os.sort_order";

$status_query_select = $opportunity_sql . ' AND os.opportunity_status_id = XXX-value-XXX';

$type_query_list = "select " . $con->Concat("ot.opportunity_type_pretty_name", "' ('", "count(ot.opportunity_type_id)", "')'") . ", ot.opportunity_type_id FROM $opportunity_sql_from $opportunity_sql_where group by ot.opportunity_type_id order by ot.opportunity_type_pretty_name";

$type_query_select = $opportunity_sql . ' AND ot.opportunity_type_id = XXX-value-XXX';

$company_query_list = "select " . $con->Concat("c.company_name", "' ('", "count(c.company_id)", "')'") . ", c.company_id FROM $opportunity_sql_from $opportunity_sql_where group by c.company_id order by c.company_name";

$company_query_select = $opportunity_sql . ' AND c.company_id = XXX-value-XXX';

$columns = array();
$columns[] = array('name' => _('Opportunity'), 'index_sql' => 'opportunity', 'sql_sort_column' => 'opportunity_title', 'type' => 'url');
$columns[] = array('name' => _('Company'), 'index_sql' => 'company', 'group_query_list' => $company_query_list, 'group_query_select' => $company_query_select);
$columns[] = array('name' => _('Owner'), 'index_sql' => 'owner', 'group_query_list' => $owner_query_list, 'group_query_select' => $owner_query_select);
$columns[] = array('name' => _('Opportunity Size'), 'index_sql' => 'opportunity_size', 'css_classname' => 'right');
$columns[] = array('name' => _('Weighted Size'), 'index_sql' => 'weighted_size',  'css_classname' => 'right');
$columns[] = array('name' => _('Type'), 'index_sql' => 'type', 'group_query_list' => $type_query_list, 'group_query_select' => $type_query_select);
$columns[] = array('name' => _('Status'), 'index_sql' => 'status', 'group_query_list' => $status_query_list, 'group_query_select' => $status_query_select);
$columns[] = array('name' => _('Close Date'), 'index_sql' => 'close_date', 'sql_sort_column' => 'close_at');

if (!$opportunity_sidebar_default_columns) $opportunity_sidebar_default_columns = array('opportunity', 'type','status', 'close_date');

$opp_pager_columns = new Pager_Columns('OpportunitiesSidebarPager', $columns, $opportunity_sidebar_default_columns, $opp_sidebar_form_id);
$opp_pager_columns_button = $opp_pager_columns->GetSelectableColumnsButton();
$opp_pager_columns_selects = $opp_pager_columns->GetSelectableColumnsWidget();

$columns = $opp_pager_columns->GetUserColumns('default');
$colspan = count($columns);

// output the selectable columns widget
$opportunity_rows.= $opp_pager_columns_selects;

// caching is disabled for this pager (since it's all sql)
$pager = new GUP_Pager($con, $opportunity_sql, null,$opp_sidebar_header, $opp_sidebar_form_id, 'OpportunitiesSidebarPager', $columns, false, true);

//put in the new and search buttons
if ( (isset($company_id) && (strlen($company_id) > 0))  or (isset($contact_id) && (strlen($contact_id) > 0)) ) {
    $new_opp_button=render_create_button('New','button', "javascript:location.href='$http_site_root/opportunities/new.php?company_id=$company_id&division_id=$division_id&contact_id=$contact_id&opportunity_type_id='+document.$opp_sidebar_form_id.opportunity_type_id.value;", false, false, 'opportunities');
    if ($new_opp_button) {
        $opp_type_sql = "SELECT opportunity_type_pretty_name,opportunity_type_id
                          FROM opportunity_types
                          WHERE opportunity_type_record_status = 'a'
                          ORDER BY opportunity_type_pretty_name";
        $type_rst=$con->execute($opp_type_sql);
        $new_opp_types=$type_rst->getmenu2('opportunity_type_id', '', false);
        $new_opp_button=$new_opp_types.$new_opp_button;
    }


    $endrows = "
            <tr>
                <td class=widget_content_form_element colspan=$colspan>
                    $opp_pager_columns_button
                    $new_opp_button
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/opportunities/some.php';\" value='" . _("Search") . "'>
                </td>
            </tr>\n";
} else {
    $endrows ="
            <tr>
                <td class=widget_content_form_element colspan=$colspan>
                    $opp_pager_columns_button
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/opportunities/some.php';\" value='" . _("Search") . "'>
                </td>
            </tr>\n";
}

$pager->AddEndRows($endrows);

$opportunity_rows.=$pager->Render($opportunity_sidebar_rows_per_page);


//now close the table, we're done
$opportunity_rows .= "</form></div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.21  2006/04/17 19:03:43  vanmer
 * - added proper ACL restriction to sidebar output
 *
 * Revision 1.20  2005/12/17 21:28:43  vanmer
 * - removed quotes from company fieldname
 * - patch provided by kennyholden
 *
 * Revision 1.19  2005/08/28 15:45:09  braverock
 * - remove unnecessary second form close tag, it confuses some browsers
 *
 * Revision 1.18  2005/08/15 23:55:05  vanmer
 * - changed column variables to be unique within the sidebar
 * - fixes problem when included before output on page where other pager column selects use same variables
 *
 * Revision 1.17  2005/08/02 17:33:04  vanmer
 * - added full link to opportunity from the sidebar
 *
 * Revision 1.16  2005/08/01 22:09:33  vanmer
 * - added ability to set the opportunity type for a new activity from the sidebar
 *
 * Revision 1.15  2005/07/28 17:14:30  vanmer
 * - added grouping on company column in opportunity sidebar pager
 * - removed subtotals from opportunity sidebar pager
 *
 * Revision 1.14  2005/07/28 16:45:30  vanmer
 * - changed to use GUP_Pager instead of rendering table directly
 * - added grouping on type, status and owner
 * - changed output of new opportunity button on sidebar from form submit to javascript button with onclick
 *
 * Revision 1.13  2005/07/11 22:43:56  vanmer
 * - changed to reflect correct table for permissions for create button
 *
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