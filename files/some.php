<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');

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
    $opportunity_title = '';
    $company_code = '';
    $company_type_id = '';
    $user_id = '';
    $opportunity_status_id = '';
    $opportunity_category_id = '';
} elseif ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $opportunity_title = $_POST['opportunity_title'];
    $company_code = $_POST['company_code'];
    $company_type_id = $_POST['company_type_id'];
    $user_id = $_POST['user_id'];
    $opportunity_status_id = $_POST['opportunity_status_id'];
    $opportunity_category_id = $_POST['opportunity_category_id'];
} else {
    $sort_column = $_SESSION['opportunities_sort_column'];
    $current_sort_column = $_SESSION['opportunities_current_sort_column'];
    $sort_order = $_SESSION['opportunities_sort_order'];
    $current_sort_order = $_SESSION['opportunities_current_sort_order'];
    $opportunity_title = $_SESSION['opportunities_opportunity_title'];
    $company_code = (strlen($_GET['company_code']) > 0) ? $_GET['company_code'] : $_SESSION['opportunities_company_code'];
    $company_type_id = $_SESSION['opportunities_company_type_id'];
    $user_id = $_SESSION['opportunities_user_id'];
    $opportunity_status_id = $_SESSION['opportunities_opportunity_status_id'];
    $opportunity_category_id = $_SESSION['opportunities_opportunity_category_id'];
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

$ascending_order_image = " <img border=0 height=10 width=10 src=/img/asc.gif>";
$descending_order_image = " <img border=0 height=10 width=10 src=/img/desc.gif>";
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$_SESSION['opportunities_sort_column'] = $sort_column;
$_SESSION['opportunities_current_sort_column'] = $sort_column;
$_SESSION['opportunities_sort_order'] = $sort_order;
$_SESSION['opportunities_current_sort_order'] = $sort_order;
$_SESSION['opportunities_opportunity_title'] = $opportunity_title;
$_SESSION['opportunities_company_code'] = $company_code;
$_SESSION['opportunities_opportunity_category_id'] = $opportunity_category_id;
$_SESSION['opportunities_user_id'] = $user_id;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;
// $con->execute("update users set last_hit = " . $con->dbtimestamp(mktime()) . " where user_id = $session_user_id");

$close_at = $con->SQLDate('Y-M-D', 'close_at');

$sql = "select concat('<a href=one.php?opportunity_id=', opp.opportunity_id, '>', opp.opportunity_title, '</a>') as 'Opportunity', c.company_code as 'Company', u.username as 'Owner', if (size > 0, size, 0) as 'Opportunity Size', if (size > 0, size*probability/100, 0) as 'Weighted Size', os.opportunity_status_pretty_name as 'Status', $close_at as 'Close Date' ";

$from = "from companies c, opportunities opp, opportunity_statuses os, users u ";

$where .= "where opp.opportunity_status_id = os.opportunity_status_id ";
$where .= "and opp.company_id = c.company_id ";
$where .= "and opp.user_id = u.user_id ";
$where .= "and opportunity_record_status = 'act'";

$criteria_count = 0;

if (strlen($opportunity_title) > 0) {
    $criteria_count++;
    $where .= " and opp.opportunity_title like " . $con->qstr($opportunity_title . '%', get_magic_quotes_gpc());
}

if (strlen($company_code) > 0) {
    $criteria_count++;
    $where .= " and c.company_code = " . $con->qstr($company_code, get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and opp.user_id = $user_id";
}

if (strlen($company_type_id) > 0) {
    $criteria_count++;
    $where .= " and c.company_id in (select company_id from company_company_type_map where company_type_id = $company_type_id)";
}

if (strlen($opportunity_status_id) > 0) {
    $criteria_count++;
    $where .= " and opp.opportunity_status_id = $opportunity_status_id";
}

if (strlen($opportunity_category_id) > 0) {
    $criteria_count++;
    $where .= " and opportunity_id in (select opportunity_id from opportunity_opportunity_category_map where opportunity_category_id = $opportunity_category_id)";
}

if (!$criteria_count > 0) {
    $where .= " and 1 = 2";
}

$sql .= $from . $where . " order by $sort_column $sort_order";

$rst = $con->execute("select c.company_id " . $from . $where);
$company_count = $rst->recordcount();
$array_of_companies = array();
if ($rst) {
    while (!$rst->EOF) {
        array_push($array_of_companies, $rst->fields['company_id']);
        $rst->movenext();
    }
    $rst->close();
}

$_SESSION['array_of_companies'] = serialize($array_of_companies);

$sql_recently_viewed_table_rows = "select * from recent_items r, companies c, opportunities opp, opportunity_statuses os ";
$sql_recently_viewed_table_rows .= "where r.user_id = $session_user_id ";
$sql_recently_viewed_table_rows .= "and r.on_what_table = 'opportunities' ";
$sql_recently_viewed_table_rows .= "and c.company_id = opp.company_id ";
$sql_recently_viewed_table_rows .= "and opp.opportunity_status_id = os.opportunity_status_id ";
$sql_recently_viewed_table_rows .= "and r.on_what_id = opp.opportunity_id ";
$sql_recently_viewed_table_rows .= "and opportunity_record_status = 'act' ";
$sql_recently_viewed_table_rows .= "order by r.recent_item_timestamp desc ";

$rst = $con->selectlimit($sql_recently_viewed_table_rows, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href=/opportunities/one.php?opportunity_id=' . $rst->fields['opportunity_id'] . '>' . $rst->fields['opportunity_title'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['company_code'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['opportunity_status_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['close_at']) . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>No recently viewed opportunities</td></tr>';
}

$sql2 = "select company_type_pretty_name, company_type_id from company_types where company_type_record_status = 'act' order by company_type_pretty_name";
$rst = $con->execute($sql2);
$company_type_menu = $rst->getmenu2('company_type_id', $company_type_id, true);
$rst->close();

$sql2 = "select username, user_id from users where user_record_status = 'act' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

$sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status = 'act' order by opportunity_status_id";
$rst = $con->execute($sql2);
$opportunity_status_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, true);
$rst->close();

$sql2 = "select opportunity_category_pretty_name, opportunity_category_id from opportunity_categories where opportunity_category_record_status = 'act' order by opportunity_category_pretty_name";
$rst = $con->execute($sql2);
$opportunity_category_menu = $rst->getmenu2('opportunity_category_id', $opportunity_category_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'opportunities', '', '');
}

$page_title = 'Opportunities';
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=opportunities_next_page value="<?php  echo $opportunities_next_page ?>">
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order ?>">
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=6>Search Criteria</td>
            </tr>
            <tr>
                <td class=widget_label>Opportunity Name</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Company Type</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Category</td>
                <td class=widget_label>Status</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="opportunity_title" size=20 value="<?php  echo $opportunity_title ?>"></td>
                <td class=widget_content_form_element><input type=text name="company_code" size=6 value="<?php  echo $company_code ?>"></td>
                <td class=widget_content_form_element><?php  echo $company_type_menu ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_category_menu ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_status_menu ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="Search"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="Clear Search"> <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";} ?> </td>
            </tr>
        </table>
        </form>

<?php

$pager = new ADODB_Pager($con,$sql, 'opportunities', false, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

        </td>
        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=34% valign=top>

        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Recently Viewed</td>
            </tr>
            <tr>
                <td class=widget_label>Opportunity</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Status</td>
                <td class=widget_label>Close Date</td>
            </tr>
            <?php  echo $recently_viewed_table_rows ?>
        </table>

        </td>
    </tr>
</table>

<script language=javascript>
<!--

function initialize() {
    document.forms[0].opportunity_title.focus();
}

initialize();

function submitForm(adodbNextPage) {
    document.forms[0].adodb_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function bulkEmail() {
    document.forms[0].action = "/email/index.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].opportunities_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].opportunities_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>

<?php end_page(); ?>