<?php
/**
 * This file allows the searching of cases
 *
 * $Id: some.php,v 1.12 2004/06/12 18:28:34 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$offset = $_POST['offset'];
$clear = ($_GET['clear'] == 1) ? 1 : 0;
$use_post_vars = ($_POST['use_post_vars'] == 1) ? 1 : 0;
$resort = $_POST['resort'];

if ($clear) {
    $sort_column = '';
    $current_sort_column = '';
    $sort_order = '';
    $current_sort_order = '';
    $case_title = '';
    $case_id = '';
    $company_code = '';
    $company_type_id = '';
    $user_id = '';
    $case_status_id = '';
    $case_category_id = '';
} elseif ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $case_title = $_POST['case_title'];
    $case_id = $_POST['case_id'];
    $company_code = $_POST['company_code'];
    $company_type_id = $_POST['company_type_id'];
    $case_type_id = $_POST['case_type_id'];
    $user_id = $_POST['user_id'];
    $case_status_id = $_POST['case_status_id'];
    $case_category_id = $_POST['case_category_id'];
} else {
    $sort_column = $_SESSION['cases_sort_column'];
    $current_sort_column = $_SESSION['cases_current_sort_column'];
    $sort_order = $_SESSION['cases_sort_order'];
    $current_sort_order = $_SESSION['cases_current_sort_order'];
    $case_title = $_SESSION['cases_case_title'];
    $case_id = $_SESSION['cases_case_id'];
    $company_code = (strlen($_GET['company_code']) > 0) ? $_GET['company_code'] : $_SESSION['cases_company_code'];
    $company_type_id = $_SESSION['cases_company_type_id'];
    $user_id = $_SESSION['cases_user_id'];
    $case_status_id = $_SESSION['cases_case_status_id'];
    $case_category_id = $_SESSION['cases_case_category_id'];
}

if (!strlen($sort_column) > 0) {
    $sort_column = 1;
    $current_sort_column = $sort_column;
    $sort_order = "asc";
}

if (!($sort_column == $current_sort_column)) {
    $sort_order = "asc";
}

$opposite_sort_order = ($sort_order == "asc") ? "desc" : "asc";
$sort_order = (($resort) && ($current_sort_column == $sort_column)) ? $opposite_sort_order : $sort_order;

$ascending_order_image = ' <img border=0 height=10 width=10 src="../img/asc.gif" alt="">';
$descending_order_image = ' <img border=0 height=10 width=10 src="../img/desc.gif" alt="">';
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$_SESSION['cases_sort_column'] = $sort_column;
$_SESSION['cases_current_sort_column'] = $sort_column;
$_SESSION['cases_sort_order'] = $sort_order;
$_SESSION['cases_current_sort_order'] = $sort_order;
$_SESSION['cases_case_title'] = $case_title;
$_SESSION['cases_case_id'] = $case_id;
$_SESSION['cases_company_code'] = $company_code;
$_SESSION['cases_case_category_id'] = $case_category_id;
$_SESSION['cases_user_id'] = $user_id;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;
// $con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

$sql = "SELECT " . $con->Concat("'<a href=\"one.php?case_id='", "ca.case_id", "'\">'", "ca.case_title", "'</a>'") . " AS 'Case',
c.company_code AS 'Company', u.username AS 'Owner', cat.case_type_pretty_name AS 'Type', cap.case_priority_pretty_name AS 'Priority',
cas.case_status_pretty_name AS 'Status', " . $con->SQLDate('Y-m-d', 'ca.due_at') . " AS 'Due At' ";

if ($case_category_id > 0) {
    $from = "from companies c, cases ca, case_types cat, case_priorities cap, case_statuses cas, users u, entity_category_map ecm ";
} else {
    $from = "from companies c, cases ca, case_types cat, case_priorities cap, case_statuses cas, users u ";
}

$where .= "where ca.case_status_id = cas.case_status_id ";
$where .= "and ca.case_priority_id = cap.case_priority_id ";
$where .= "and ca.case_type_id = cat.case_type_id ";
$where .= "and ca.company_id = c.company_id ";
$where .= "and ca.user_id = u.user_id ";
$where .= "and case_record_status = 'a'";

$criteria_count = 0;

if ($case_category_id > 0) {
    $criteria_count++;
    $where .= " and ecm.on_what_table = 'cases' and ecm.on_what_id = ca.case_id and ecm.category_id = $case_category_id ";
}

if (strlen($case_title) > 0) {
    $criteria_count++;
    $where .= " and ca.case_title like " . $con->qstr('%' . $case_title . '%', get_magic_quotes_gpc());
}

if (strlen($company_code) > 0) {
    $criteria_count++;
    $where .= " and c.company_code = " . $con->qstr($company_code, get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and ca.user_id = $user_id";
}

if (strlen($case_status_id) > 0) {
    $criteria_count++;
    $where .= " and ca.case_status_id = $case_status_id";
}

if (strlen($case_id) > 0) {
    $criteria_count++;
    $where .= " and ca.case_id = $case_id ";
}

if (strlen($case_type_id) > 0) {
    $criteria_count++;
    $where .= " and ca.case_type_id = $case_type_id";
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

if ($sort_column == 1) {
    $order_by = "case_title";
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";
$sql .= $from . $where . " order by $order_by";

$sql_recently_viewed = "select * from recent_items r, companies c, cases ca, case_statuses cas
where r.user_id = $session_user_id
and r.on_what_table = 'cases'
and c.company_id = ca.company_id
and ca.case_status_id = cas.case_status_id
and r.on_what_id = ca.case_id
and case_record_status = 'a'
order by r.recent_item_timestamp desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="'.$http_site_root.'/cases/one.php?case_id=' . $rst->fields['case_id'] . '">' . $rst->fields['case_title'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['company_code'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['case_status_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['due_at']) . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>No recently viewed cases</td></tr>';
}

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

$sql2 = "select case_status_pretty_name, case_status_id from case_statuses where case_status_record_status = 'a' order by case_status_id";
$rst = $con->execute($sql2);
$case_status_menu = $rst->getmenu2('case_status_id', $case_status_id, true);
$rst->close();

$sql2 = "select case_type_pretty_name, case_type_id from case_types where case_type_record_status = 'a' order by case_type_pretty_name";
$rst = $con->execute($sql2);
$case_type_menu = $rst->getmenu2('case_type_id', $case_type_id, true);
$rst->close();

$sql2 = "select case_priority_pretty_name, case_priority_id from case_priorities where case_priority_record_status = 'a' order by case_priority_pretty_name";
$rst = $con->execute($sql2);
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

$page_title = 'Cases';
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=cases_next_page value="<?php  echo $cases_next_page ?>">
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order ?>">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>Search Criteria</td>
            </tr>
            <tr>
                <td class=widget_label colspan=2>Case Name</td>
                <td class=widget_label>Case Number</td>
                <td class=widget_label>Company</td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input type=text name="case_title" size=20 value="<?php  echo $case_title ?>"></td>
                <td class=widget_content_form_element><input type=text name="case_id" size=20 value="<?php  echo $case_id ?>"></td>
                <td class=widget_content_form_element><input type=text name="company_code" size=6 value="<?php  echo $company_code ?>"></td>
            </tr>
            <tr>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Category</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Status</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
                <td class=widget_content_form_element><?php  echo $case_category_menu ?></td>
                <td class=widget_content_form_element><?php  echo $case_type_menu ?></td>
                <td class=widget_content_form_element><?php  echo $case_status_menu ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="Search"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="Clear Search"> <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";} ?> </td>
            </tr>
        </table>
        </form>

<?php

$pager = new ADODB_Pager($con, $sql, 'cases', false, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=5>Recently Viewed</td>
            </tr>
            <tr>
                <td class=widget_label>Case</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Status</td>
                <td class=widget_label>Due</td>
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
    document.forms[0].action = "../email/index.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].cases_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].cases_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>

<?php

end_page();

/**
 * $Log: some.php,v $
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
 *
 *
 */
?>