<?php
/**
 * Sidebar list box for Companies
 *
 * This produces a list of companies attached to a given campaign
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

if (!$campaign_companies_rows_per_page) {
    $campaign_companies_rows_per_page=10;
}

$coList=acl_get_list($session_user_id, 'Read', false, 'companies');
if (!$coList) { $company_rows=''; return false; }
else { if ($coList!==true) { $coList=implode(",",$coList); $companies_limit_sql.=" AND companies.company_id IN ($coList) "; } }

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

require_once('campaign-companies-pager-functions.php');

$co_sidebar_form_id='SidebarCampaignCompanies';
$co_sidebar_header=_("Companies");
$target=$http_site_root.current_page();

$campaign_company_rows = '<div id="campaign_companies_sidebar">';
$campaign_company_rows .= "<form action=\"$target\" name=\"$co_sidebar_form_id\" method=GET>";

$company_sql  = 'SELECT ' . $con->Concat("'<a id=\"'", "co.company_name",  "'\" href=\"$http_site_root/companies/one.php?company_id='", "co.company_id", "'\">'", "co.company_name","'</a>'") . " AS company_name,";
$company_sql .= 'co.entered_at AS date_added,';
$company_sql .= 'u.username AS owner ';
$company_sql .= "FROM company_campaign_map ccm,companies co, users u ";
$company_sql .= "WHERE ccm.campaign_id = $campaign_id AND ccm.company_id = co.company_id AND co.company_record_status = 'a' AND u.user_id = co.user_id";

// Set up the column_info array describing the data
$columns = array();
$columns[] = array('name' => 'Company Name', 'index_sql' => 'company_name', 'default_sort' => 'asc');
$columns[] = array('name' => 'Date Added', 'index_sql' => 'date_added');
$columns[] = array('name' => 'Owner', 'index_sql' => 'owner');

$coPager = new GUP_Pager($con, $company_sql, 'GetCampaignCompaniesPagerData', $co_sidebar_header, $co_sidebar_form_id, 'campaign_companies_Pager', $columns, false, true);

$campaign_company_rows .= $coPager->Render($campaign_companies_rows_per_page);
$campaign_company_rows .= "</form></div>\n";

?>
