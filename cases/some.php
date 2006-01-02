<?php
/**
 * This file allows the searching of cases
 *
 * $Id: some.php,v 1.40 2006/01/02 22:47:25 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-saved-search.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

$con = get_xrms_dbconnection();
// $con->debug = 1;

$on_what_table='cases';
$session_user_id = session_check();

getGlobalVar($browse,'browse');
getGlobalVar($saved_id, 'saved_id');
getGlobalVar($saved_title, 'saved_title');
getGlobalVar($group_item, 'group_item');
getGlobalVar($delete_saved, 'delete_saved');

/*********** SAVED SEARCH BEGIN **********************/
load_saved_search_vars($con, $on_what_table, $saved_id, $delete_saved);

/*********** SAVED SEARCH END **********************/


// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
         'case_title'          => array ( 'cases_case_title', arr_vars_SESSION ),
         'case_id'             => array ( 'cases_case_id', arr_vars_SESSION ),
         'company_name'        => array ( 'cases_company_name', arr_vars_GET_STRLEN_SESSION ),
         // unused // 'company_type_id'     => array ( 'cases_company_type_id', arr_vars_SESSION ),
         'case_type_id'        => array ( 'case_type_id', arr_vars_SESSION ),
         'user_id'             => array ( 'cases_user_id', arr_vars_SESSION ),
         'case_status_id'      => array ( 'cases_case_status_id', arr_vars_GET_SESSION),
         'case_category_id'    => array ( 'cases_case_category_id', arr_vars_SESSION ),
         );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );


$sql = "SELECT " . $con->Concat("'<a href=\"one.php?case_id='", "ca.case_id", "'\">'", "ca.case_title", "'</a>'") . " AS case_name, c.company_name AS company, u.username AS owner, cat.case_type_pretty_name AS type, cap.case_priority_pretty_name AS priority, cas.case_status_pretty_name AS status, " . $con->SQLDate('Y-m-d', 'ca.due_at') . " AS due ";

if ($case_category_id > 0) {
    $from = "from companies c, cases ca, case_types cat, case_priorities cap, case_statuses cas, users u, entity_category_map ecm ";
} else {
    $from = "from companies c, cases ca, case_types cat, case_priorities cap, case_statuses cas, users u  ";
}

//added by Nic to be able to create mail merge to contacts
$from.=",contacts cont ";

$where  = "where ca.case_status_id = cas.case_status_id ";
$where .= "and ca.case_priority_id = cap.case_priority_id ";
$where .= "and ca.case_type_id = cat.case_type_id ";
$where .= "and ca.company_id = c.company_id ";
$where .= "and ca.user_id = u.user_id ";
$where .= "and case_record_status = 'a'";

//added by Nic to be able to create mail merge to contacts
$where.="and cont.contact_id=ca.contact_id ";

$criteria_count = 0;

if ($case_category_id > 0) {
    $criteria_count++;
    $where .= " and ecm.on_what_table = 'cases' and ecm.on_what_id = ca.case_id and ecm.category_id = $case_category_id ";
}

if (strlen($case_title) > 0) {
    $criteria_count++;
    $where .= " and ca.case_title like " . $con->qstr('%' . $case_title . '%', get_magic_quotes_gpc());
}

if (strlen($company_name) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr(company_search_string($company_name), get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and ca.user_id = $user_id";
}

if (strlen($case_status_id) > 0) {
    $criteria_count++;
    $where .= " and ca.case_status_id = $case_status_id";
}

if (strlen($case_id) > 0 and is_numeric($case_id)) {
    $criteria_count++;
    $where .= " and ca.case_id = $case_id ";
}

if (strlen($case_type_id) > 0) {
    $criteria_count++;
    $where .= " and ca.case_type_id = $case_type_id";
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
} else {
    $list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    //print_r($list);
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $where .= " and ca.case_id IN ($list) ";
        }
    } else { $where .= ' AND 1 = 2 '; }
}

$sql .= $from . $where;


/******* SAVED SEARCH BEGINS *****/
    $saved_data = $_POST;
    $saved_data["sql"] = $sql;
    $saved_data["day_diff"] = $day_diff;

    if(!$saved_title) {
        $saved_title = "Current";
        $group_item = 0;
    }
    if ($saved_title OR $browse) {
//        echo "adding saved search";
        $saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);
//        echo "$saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);";
    }

//get saved searches
$rst=get_saved_search_item($con, $on_what_table, $session_user_id, false,  false, true,'search', true);
if( $rst AND $rst->RowCount() ) {
    $saved_menu = $rst->getmenu2('saved_id', 0, true) . ' <input name="delete_saved" type=submit class=button value="' . _("Delete") . '">';
} else {
  $saved_menu = '';
}

/********** SAVED SEARCH ENDS ****/



$sql_recently_viewed = "select * from recent_items r, companies c, cases ca, case_statuses cas
where r.user_id = $session_user_id
and r.on_what_table = 'cases'
and r.recent_action = ''
and c.company_id = ca.company_id
and ca.case_status_id = cas.case_status_id
and r.on_what_id = ca.case_id
and case_record_status = 'a'
order by r.recent_item_timestamp desc";

$recently_viewed_table_rows = '';

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="'.$http_site_root.'/cases/one.php?case_id=' . $rst->fields['case_id'] . '">' . $rst->fields['case_title'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['company_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['case_status_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['due_at']) . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>' . _("No recently viewed cases") . '</td></tr>';
}

$user_menu = get_user_menu($con, $user_id, true);

$sql2 = "select " . $con->concat('case_type_pretty_name',$con->qstr(' - '), 'case_status_pretty_name') .", case_status_id from case_statuses JOIN case_types ON case_statuses.case_type_id=case_types.case_type_id where case_status_record_status = 'a' order by case_statuses.case_type_id, sort_order";
$rst = $con->execute($sql2);
if (!$rst) { db_error_handler($con, $sql2); }
$case_status_menu = $rst->getmenu2('case_status_id', $case_status_id, true);
$rst->close();

$sql2 = "select case_type_pretty_name, case_type_id from case_types where case_type_record_status = 'a' order by case_type_pretty_name";
$rst = $con->execute($sql2);
$case_type_menu = $rst->getmenu2('case_type_id', $case_type_id, true);
$rst->close();

$sql2 = "select case_priority_pretty_name, case_priority_id from case_priorities where case_priority_record_status = 'a' order by case_priority_pretty_name";
$rst = $con->execute($sql2);
$case_priority_id = '';
if ( !$rst ) {
  db_error_handler($con, $sql2);
} elseif ( !$rst->EOF ) {
  $case_priority_id = $rst->fields['case_priority_id'];
}
$case_priority_menu = $rst->getmenu2('case_priority_id', $case_priority_id, true);
$rst->close();

$sql2 = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'cases'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql2);
$case_category_menu = $rst->getmenu2('case_category_id', $case_category_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'cases', '', 4);
}

// get company_count
$rst = $con->execute($sql);
$company_count = 0;
if ( $rst ) {
  while (!$rst->EOF) {
    $company_count += 1;
    break;                // we only care if we have more than 0, so stop here
    $rst->movenext();
  }
  $rst->close();
}

$page_title = _("Cases");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=some.php class="print" name="CasesData" method=post>
        <input type=hidden name=use_post_vars value=1>
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4><?php echo _("Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Case Name"); ?></td>
                <td class=widget_label><?php echo _("Case Number"); ?></td>
                <td class=widget_label colspan=2><?php echo _("Company"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="case_title" value="<?php  echo $case_title ?>"></td>
                <td class=widget_content_form_element><input type=text name="case_id" size=5 value="<?php  echo $case_id ?>"></td>
                <td class=widget_content_form_element colspan=2><input type=text name="company_name" value="<?php  echo $company_name ?>"></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Category"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
                <td class=widget_content_form_element><?php  echo $case_category_menu ?></td>
                <td class=widget_content_form_element><?php  echo $case_type_menu ?></td>
                <td class=widget_content_form_element><?php  echo $case_status_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label colspan="2"><?php echo _("Saved Searches"); ?></td>
                <td class=widget_label colspan="2"><?php echo _("Search Title"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan="2">
                    <?php echo ($saved_menu) ? $saved_menu : _("No Saved Searches"); ?>
                </td>
                <td class=widget_content_form_element colspan="2">
                    <input type=text name="saved_title" size=24>
                    <?php
                        if(check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
                            echo _("Add to Everyone").' <input type=checkbox name="group_item" value=1>';
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="<?php echo _("Search"); ?>"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>"> <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='" . _("Bulk E-Mail") . "'>";} ?> </td>
            </tr>
        </table>

<?php
$_SESSION["search_sql"]=$sql;

$owner_query_list = "select " . $con->Concat("u.username", "' ('", "count(u.user_id)", "')'") . ", u.user_id $from $where group by u.username, u.user_id order by u.username";

$owner_query_select = $sql . 'AND u.user_id = XXX-value-XXX';

$status_query_list = "select " . $con->Concat("cas.case_status_pretty_name", "' ('", "count(cas.case_status_id)", "')'") . ", cas.case_status_id $from $where group by cas.case_status_id, case_status_pretty_name order by cas.case_status_pretty_name";

$status_query_select = $sql . ' AND cas.case_status_id = XXX-value-XXX';

$type_query_list = "select " . $con->Concat("cat.case_type_pretty_name", "' ('", "count(cat.case_type_id)", "')'") . ", cat.case_type_id $from $where group by cat.case_type_id, case_type_pretty_name order by cat.case_type_pretty_name";

$type_query_select = $sql . ' AND cat.case_type_id = XXX-value-XXX';

$company_query_list = "select " . $con->Concat("c.company_name", "' ('", "count(c.company_id)", "')'") . ", c.company_id $from $where group by c.company_id, c.company_name order by c.company_name";

$company_query_select = $sql . 'AND c.company_id = XXX-value-XXX';

$priority_query_list = "select " . $con->Concat("cap.case_priority_pretty_name", "' ('", "count(cap.case_priority_id)", "')'") . ", cap.case_priority_id $from $where group by cap.case_priority_id, case_priority_pretty_name order by cap.case_priority_pretty_name";

$priority_query_select = $sql . ' AND cap.case_priority_id = XXX-value-XXX';

$columns = array();
$columns[] = array('name' => _('Case'), 'index_sql' => 'case_name', 'sql_sort_column' => 'ca.case_title', 'type' => 'url');
$columns[] = array('name' => _('Company'), 'index_sql' => 'company', 'group_query_list' => $company_query_list, 'group_query_select' => $company_query_select);
$columns[] = array('name' => _('Owner'), 'index_sql' => 'owner', 'group_query_list' => $owner_query_list, 'group_query_select' => $owner_query_select);
$columns[] = array('name' => _('Type'), 'index_sql' => 'type', 'group_query_list' => $type_query_list, 'group_query_select' => $type_query_select);
$columns[] = array('name' => _('Status'), 'index_sql' => 'status', 'group_query_list' => $status_query_list, 'group_query_select' => $status_query_select);
$columns[] = array('name' => _('Priority'), 'index_sql' => 'priority', 'group_query_list' => $priority_query_list, 'group_query_select' => $priority_query_select);
$columns[] = array('name' => _('Due'), 'index_sql' => 'due');


// selects the columns this user is interested in
// no reason to set this if you don't want all by default
$default_columns = null;

$pager_columns = new Pager_Columns('SomeCasesPager', $columns, $default_columns, 'CasesData');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

// output the selectable columns widget
echo $pager_columns_selects;

// caching is disabled for this pager (since it's all sql)
$pager = new GUP_Pager($con, $sql, null, _('Search Results'), 'CasesData', 'SomeCasesPager', $columns, false);

// set up the bottom row of buttons
$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            " . $pager->GetAndUseExportButton() .  "
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=\"" . _('Mail Merge') . "\"></td></tr>";

$pager->AddEndRows($endrows);
$pager->Render($system_rows_per_page);

$con->close();

?>

        </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Case"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
                <td class=widget_label><?php echo _("Due"); ?></td>
            </tr>
            <?php  echo $recently_viewed_table_rows ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].case_title.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].cases_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

//-->
</script>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.40  2006/01/02 22:47:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.39  2005/12/06 23:16:07  vanmer
 * - added case type to status dropdown
 * - added order by for status dropdown (may not work with sql server)
 *
 * Revision 1.38  2005/08/28 16:22:20  braverock
 * - fix incorrect colspan
 *
 * Revision 1.37  2005/08/17 18:47:20  ycreddy
 * Fix for company search - used like instead of =
 *
 * Revision 1.36  2005/08/11 21:46:07  ycreddy
 * Fixed the GROUP BY query issues in the Cases Pager for SQL Server
 *
 * Revision 1.35  2005/08/05 21:54:52  vanmer
 * - changed to use centralized function for company search
 *
 * Revision 1.34  2005/08/05 01:45:31  vanmer
 * - added saved search functionality to cases
 *
 * Revision 1.33  2005/07/28 17:12:50  vanmer
 * - added grouping on company, owner, type, status and priority fields for results pager
 *
 * Revision 1.32  2005/05/31 17:34:24  daturaarutad
 * removed translation from query since it is happening in the pager columns
 *
 * Revision 1.31  2005/04/29 17:52:37  daturaarutad
 * fixed printing of form/search results
 *
 * Revision 1.30  2005/04/29 16:10:20  daturaarutad
 * updated to use GUP_Pager for display, export
 *
 * Revision 1.29  2005/03/21 13:40:54  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.28  2005/03/11 17:28:19  daturaarutad
 * added GET to cases_case_status_id for linking to reports/graphs
 *
 * Revision 1.27  2005/02/14 21:43:14  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.26  2005/02/10 02:28:16  braverock
 * - add is_numeric check for case_id search
 *   - this should be an advanced search field
 *
 * Revision 1.25  2005/01/13 18:13:36  vanmer
 * - Basic ACL changes to allow display functionality to be restricted
 *
 * Revision 1.24  2005/01/09 03:22:33  braverock
 * - modified to use company name rather than company code
 * - modified to turn on export
 *
 * Revision 1.23  2004/08/19 13:12:16  maulani
 * - Add specific pager to override formatting
 *
 * Revision 1.22  2004/08/18 00:06:15  niclowe
 * Fixed bug 941839 - Mail Merge not working
 *
 * Revision 1.21  2004/07/30 11:43:10  cpsource
 * - Check for db errors and record found before setting
 *     case_prority_id
 *
 * Revision 1.20  2004/07/28 20:41:04  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.19  2004/07/20 22:08:24  cpsource
 * - get rid of company_type_id as it's unused
 *
 * Revision 1.18  2004/07/20 21:32:50  cpsource
 * - Set case_priority_id
 *
 * Revision 1.17  2004/07/16 07:11:17  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.16  2004/07/15 13:49:53  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.15  2004/07/14 20:19:49  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.14  2004/07/09 18:36:45  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.13  2004/06/21 20:54:57  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.12  2004/06/12 18:28:34  braverock
 * - remove CAST, as it is not standard across databases
 *   - database should explicitly convert number to string for CONCAT
 *
 * Revision 1.11  2004/06/12 04:08:06  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.10  2004/05/10 13:08:36  maulani
 * - Add level to audit trail
 * - Correct audit trail entry text
 *
 * Revision 1.9  2004/04/20 14:42:14  braverock
 * - add search for case type
 *   - fixes SF bug 930935
 *
 * Revision 1.8  2004/04/16 14:46:27  maulani
 * - Clean HTML so page will validate
 *
 * Revision 1.7  2004/04/15 22:04:38  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.6  2004/04/10 14:53:57  braverock
 * - add search by Case ID
 * - applied SF patch 925621 submitted by Glenn Powers
 *
 * Revision 1.5  2004/04/08 16:59:15  maulani
 * - Update javascript declaration
 * - Add phpdoc
 */
?>