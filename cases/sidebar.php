<?php
/**
 * Sidebar box for Cases
 *
 * $Id: sidebar.php,v 1.22 2006/04/17 19:03:41 vanmer Exp $
 */
if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

///*  // Commented until ACL system is fully implemented
    $caseList=acl_get_list($session_user_id, 'Read', false, 'cases');
    if (!$caseList) {
        $case_rows=''; return false;
    } 
    else {
    	if ($caseList!==true) {
            $caseList=implode(",",$caseList);
            $case_limit_sql.=" AND cases.case_id IN ($caseList) ";
	}
    }
//*/

$case_sidebar_header=_("Open Cases");
$case_sidebar_form_id='SidebarCases';
$target=$http_site_root.current_page();

$case_rows = "<div id='case_sidebar'>";

$case_rows.="<form action=\"$target\" name=\"$case_sidebar_form_id\" method=GET><input type=hidden name=contact_id value=$contact_id><input type=hidden name=company_id value=$company_id><input type=hidden name=division_id value=$division_id>";


if (!$cases_sidebar_rows_per_page) {
    $cases_sidebar_rows_per_page=5;
}

//build the cases sql query
$cases_sql_select = "SELECT " . $con->Concat("'<a href=\"$http_site_root/cases/one.php?case_id='", "cases.case_id", "'\">'", "cases.case_title", "'</a>'") . " AS case_name, cases.*, " .$con->SQLDate('Y-m-d', 'cases.due_at') . " AS due, case_types.case_type_pretty_name as type,
case_statuses.case_status_pretty_name as status,
c.company_name as company, users.username as username, case_priorities.case_priority_pretty_name as priority";
$cases_sql_from= "FROM cases, case_priorities, users, case_statuses, companies c, case_types";
$cases_sql_where="WHERE cases.case_priority_id = case_priorities.case_priority_id
                and cases.user_id = users.user_id
                and case_record_status = 'a'
                and cases.company_id = c.company_id
                and cases.case_status_id = case_statuses.case_status_id
                and cases.case_type_id = case_types.case_type_id
                and case_statuses.status_open_indicator = 'o'
                $case_limit_sql";

$cases_sql="$cases_sql_select $cases_sql_from $cases_sql_where";

$owner_query_list = "select " . $con->Concat("users.username", "' ('", "count(users.user_id)", "')'") . ", users.user_id $cases_sql_from $cases_sql_where group by users.username order by users.username";

$owner_query_select = $cases_sql . ' AND users.user_id = XXX-value-XXX';


$status_query_list = "select " . $con->Concat("case_statuses.case_status_pretty_name", "' ('", "count(case_statuses.case_status_id)", "')'") . ", case_statuses.case_status_id $cases_sql_from $cases_sql_where group by case_statuses.case_status_id order by case_statuses.sort_order";

$status_query_select = $cases_sql . ' AND case_statuses.case_status_id = XXX-value-XXX';

$type_query_list = "select " . $con->Concat("case_types.case_type_pretty_name", "' ('", "count(case_types.case_type_id)", "')'") . ", case_types.case_type_id $cases_sql_from $cases_sql_where group by case_types.case_type_id order by case_types.case_type_pretty_name";

$type_query_select = $cases_sql . ' AND case_types.case_type_id = XXX-value-XXX';

$company_query_list = "select " . $con->Concat("c.company_name", "' ('", "count(c.company_id)", "')'") . ", c.company_id $cases_sql_from $cases_sql_where group by c.company_id order by c.company_name";

$company_query_select = $cases_sql . ' AND c.company_id = XXX-value-XXX';

$priority_query_list = "select " . $con->Concat("case_priorities.case_priority_pretty_name", "' ('", "count(case_priorities.case_priority_id)", "')'") . ", case_priorities.case_priority_id $cases_sql_from $cases_sql_where group by case_priorities.case_priority_id order by case_priorities.case_priority_pretty_name";

$priority_query_select = $cases_sql . ' AND case_priorities.case_priority_id = XXX-value-XXX';

$columns = array();
$columns[] = array('name' => _('Case'), 'index_sql' => 'case_name', 'sql_sort_column' => 'cases.case_title', 'type' => 'url');
$columns[] = array('name' => _('Priority'), 'index_sql' => 'priority', 'group_query_list' => $priority_query_list, 'group_query_select' => $priority_query_select);
$columns[] = array('name' => _('Type'), 'index_sql' => 'type', 'group_query_list' => $type_query_list, 'group_query_select' => $type_query_select);
$columns[] = array('name' => _('Status'), 'index_sql' => 'status', 'group_query_list' => $status_query_list, 'group_query_select' => $status_query_select);
$columns[] = array('name' => _('Due'), 'index_sql' => 'due');
$columns[] = array('name' => _('Company'), 'index_sql' => 'company', 'group_query_list' => $company_query_list, 'group_query_select' => $company_query_select);
$columns[] = array('name' => _('Owner'), 'index_sql' => 'username', 'group_query_list' => $owner_query_list, 'group_query_select' => $owner_query_select);

// no reason to set this if you don't want all by default
if (!$case_sidebar_default_columns) $case_sidebar_default_columns = array('case_name', 'priority','type', 'due');

$case_pager_columns = new Pager_Columns('CasesSidebarPager', $columns, $case_sidebar_default_columns, $case_sidebar_form_id);
$case_pager_columns_button = $case_pager_columns->GetSelectableColumnsButton();
$case_pager_columns_selects = $case_pager_columns->GetSelectableColumnsWidget();

$columns = $case_pager_columns->GetUserColumns('default');
$colspan = count($columns);

// output the selectable columns widget
$case_rows.= $case_pager_columns_selects;

// caching is disabled for this pager (since it's all sql)
$pager = new GUP_Pager($con, $cases_sql, null,$case_sidebar_header, $case_sidebar_form_id, 'CasesSidebarPager', $columns, false, true);

//put in the new and search buttons
if ( (isset($company_id) && (strlen($company_id) > 0))  or (isset($contact_id) && (strlen($contact_id) > 0))) {
    $new_case_button=render_create_button("New",'button',"javascript:location.href='$http_site_root/cases/new.php?company_id=$company_id&division_id=$division_id&contact_id=$contact_id&case_type_id=' + document.$case_sidebar_form_id.case_type_id.value;", false, false, 'cases');
    if ($new_case_button) {
        $case_type_sql = "SELECT case_type_pretty_name,case_type_id
                          FROM case_types
                          WHERE case_type_record_status = 'a'
                          ORDER BY case_type_pretty_name";
        $type_rst=$con->execute($case_type_sql);
        $new_case_types=$type_rst->getmenu2('case_type_id', '', false);
        $new_case_button=$new_case_types.$new_case_button;
    }

    $endrows = "
            <tr>
                <td class=widget_content_form_element colspan=$colspan>
                    $case_pager_columns_button<br>
                    $new_case_button
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/cases/some.php';\" value='" . _("Search") . "'>
                </td>
            </tr>\n";
} else {
    $endrows ="
            <tr>
                <td class=widget_content_form_element colspan=$colspan>
                    $case_pager_columns_button
                    <input type=button class=button onclick=\"javascript:location.href='".$http_site_root."/cases/some.php';\" value='" . _("Search") . "'>
                </td>
            </tr>\n";
}

$pager->AddEndRows($endrows);

$case_rows.=$pager->Render($cases_sidebar_rows_per_page);

//now close the table, we're done
$case_rows .= "</form></div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.22  2006/04/17 19:03:41  vanmer
 * - added proper ACL restriction to sidebar output
 *
 * Revision 1.21  2005/08/28 16:18:52  braverock
 * - remove commented export code, non-relevant for this page
 *
 * Revision 1.20  2005/08/28 15:42:45  braverock
 * - remove spurious second closing of form tag, confused some browsers
 * - remove commented obsolete non-pager sidebar code
 * - @todo make the case list ACL compliant
 *
 * Revision 1.19  2005/08/15 23:55:05  vanmer
 * - changed column variables to be unique within the sidebar
 * - fixes problem when included before output on page where other pager column selects use same variables
 *
 * Revision 1.18  2005/08/01 22:09:13  vanmer
 * - added javascript to ensure that the proper case type id is set when creating a new case from the sidebar
 *
 * Revision 1.17  2005/07/28 17:11:49  vanmer
 * - added grouping on company and priority columns in cases sidebar
 *
 * Revision 1.16  2005/07/28 16:44:03  vanmer
 * - split cases sidebar sql into seperate select, from and where pieces
 * - added grouping by owner, type and status in the pager
 *
 * Revision 1.15  2005/07/28 15:44:40  vanmer
 * - changed sidebar to use GUP_Pager instead of direct HTML
 * - changed from form submit for new case from sidebar to use javascript button with onclick
 *
 * Revision 1.14  2005/07/11 22:48:19  vanmer
 * - added table name to explicitly check permissions on cases instead of on parent (company)
 *
 * Revision 1.13  2005/02/24 12:46:42  braverock
 * - improve SQL formatting
 * - only show case types that have an 'a'ctive status
 *   - modified from patch submitted by Keith Edmunds
 *
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