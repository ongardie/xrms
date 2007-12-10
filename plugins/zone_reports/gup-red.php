<?php
/**
 * Sidebar box for Opportunities
 *
 * $Id: gup-red.php,v 1.1 2007/12/10 23:30:16 gpowers Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/*
Commented until ACL system is fully implemented
$opList=acl_get_list($session_user_id, 'Read', false, 'opportunities');
if (!$opList) { $opportunity_rows=''; return false; }
else { $opList=implode(",",$opList); $opportunity_limit_sql.=" AND opportunities.opportunity_id IN ($opList) "; }
*/

// require_once($include_directory . 'classes/Pager/GUP_Pager.php');
// require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

$opp_sidebar_form_id='RedOpportunities';
$opp_sidebar_header=_("<font color=red  size=\"+1\">Red Zone</font>");
$target=$http_site_root.current_page();

// $con->debug=1;

$opportunity_rows = "<div id='OpportunitiesRedPager'>";

$opportunity_rows.="<form action=\"$target\" name=\"$opp_sidebar_form_id\" method=GET>";

$twoweeks = date("Y-m-d", strtotime("+2 weeks", strtotime(date("Y-m-d"))));
$today = strtotime(date("Y-m-d 00:00:00"));

//build the cases sql query
$close_at = $con->SQLDate('Y-m-D', 'close_at');
$opportunity_sql_select = "select "
. $con->Concat("'<a id=\"'", "opportunities.opportunity_title",  "'\" href=\"$http_site_root/contacts/one.php?contact_id='", "opportunities.contact_id", "'\">'", "opportunities.opportunity_title","'</a>'")
. " AS opportunity" . ", "
. $con->Concat("'<a id=\"'", "opportunities.company_id",  "'\" href=\"$http_site_root/companies/one.php?company_id='", "opportunities.company_id", "'\" \">'", "c.company_name", "'</a>'")
. " AS company" . ",
company_sources.company_source_pretty_name as source,
u.username AS owner " . ",
  ot.opportunity_type_pretty_name AS type,
  os.opportunity_status_pretty_name AS status,
opportunities.opportunity_title";
  $opportunity_sql_from="opportunities, opportunity_statuses os, opportunity_types ot, users u, companies c
  LEFT JOIN activities ON (activities.on_what_table = 'opportunities')
LEFT JOIN company_sources ON (c.company_source_id = company_sources.company_source_id)
";
$opportunity_sql_where="
where opportunities.opportunity_status_id = os.opportunity_status_id
and opportunities.opportunity_type_id = ot.opportunity_type_id
and opportunities.user_id =u.user_id
AND activities.on_what_id = opportunities.opportunity_id                                            
and opportunities.company_id = c.company_id
and opportunities.opportunity_record_status = 'a'
AND opportunities.opportunity_status_id IN (4,5,6)
AND CAST(activities.scheduled_at AS DATE) < '" . $twoweeks . "'";

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
$columns[] = array('name' => _('Contact'), 'index_sql' => 'opportunity', 'sql_sort_column' => 'opportunity_title', 'type' => 'url');
$columns[] = array('name' => _('Company'), 'index_sql' => 'company', 'group_query_list' => $company_query_list, 'group_query_select' => $company_query_select);
$columns[] = array('name' => _('Source'), 'index_sql' => 'source');
$columns[] = array('name' => _('Owner'), 'index_sql' => 'owner', 'group_query_list' => $owner_query_list, 'group_query_select' => $owner_query_select);
$columns[] = array('name' => _('Type'), 'index_sql' => 'type', 'group_query_list' => $type_query_list, 'group_query_select' => $type_query_select);
$columns[] = array('name' => _('Status'), 'index_sql' => 'status', 'group_query_list' => $status_query_list, 'group_query_select' => $status_query_select);

$opportunity_sidebar_default_columns = array('opportunity', 'company', 'source', 'status');

$opp_pager_columns = new Pager_Columns('OpportunitiesRedPager', $columns, $opportunity_sidebar_default_columns, $opp_sidebar_form_id);

$columns = $opp_pager_columns->GetUserColumns('default');
$colspan = count($columns);

// output the selectable columns widget
// $opportunity_rows.= $opp_pager_columns_selects;

// caching is disabled for this pager (since it's all sql)
$pager = new GUP_Pager($con, $opportunity_sql, null,$opp_sidebar_header, $opp_sidebar_form_id, 'OpportunitiesRedPager', $columns, false, true);

$pager->AddEndRows($endrows);

$opportunity_rows.=$pager->Render($opportunity_sidebar_rows_per_page);

//now close the table, we're done
$opportunity_rows .= "</form></div>";
?>
