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
    $campaign_title = '';
    $user_id = '';
    $campaign_type_id = '';
    $campaign_status_id = '';
    $campaign_category_id = '';
} elseif ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $campaign_title = $_POST['campaign_title'];
    $user_id = $_POST['user_id'];
    $campaign_type_id = $_POST['campaign_type_id'];
    $campaign_status_id = $_POST['campaign_status_id'];
    $campaign_category_id = $_POST['campaign_category_id'];
} else {
    $sort_column = $_SESSION['campaigns_sort_column'];
    $current_sort_column = $_SESSION['campaigns_current_sort_column'];
    $sort_order = $_SESSION['campaigns_sort_order'];
    $current_sort_order = $_SESSION['campaigns_current_sort_order'];
    $campaign_title = $_SESSION['campaigns_campaign_title'];
    $user_id = $_SESSION['campaigns_user_id'];
    $campaign_type_id = $_SESSION['campaigns_campaign_type_id'];
    $campaign_status_id = $_SESSION['campaigns_campaign_status_id'];
    $campaign_category_id = $_SESSION['campaigns_campaign_category_id'];
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

$ascending_order_image = " <img border=0 height=10 width=10 src=../img/asc.gif>";
$descending_order_image = " <img border=0 height=10 width=10 src=../img/desc.gif>";
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$_SESSION['campaigns_sort_column'] = $sort_column;
$_SESSION['campaigns_current_sort_column'] = $sort_column;
$_SESSION['campaigns_sort_order'] = $sort_order;
$_SESSION['campaigns_current_sort_order'] = $sort_order;
$_SESSION['campaigns_campaign_title'] = $campaign_title;
$_SESSION['campaigns_campaign_type_id'] = $campaign_type_id;
$_SESSION['campaigns_campaign_status_id'] = $campaign_status_id;
$_SESSION['campaigns_campaign_category_id'] = $campaign_category_id;
$_SESSION['campaigns_user_id'] = $user_id;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$starts_at = $con->SQLDate('Y-M-D', 'starts_at');
$ends_at = $con->SQLDate('Y-M-D', 'ends_at');

$sql = "select concat('<a href=one.php?campaign_id=', cam.campaign_id, '>', cam.campaign_title, '</a>') as 'Campaign', camt.campaign_type_pretty_name as 'Type', cams.campaign_status_pretty_name as 'Status', u.username as 'Owner', $starts_at as 'Starts', $ends_at as 'Ends' ";

if ($campaign_category_id > 0) {
    $from = "from campaigns cam, campaign_types camt, campaign_statuses cams, users u, entity_category_map ecm ";
} else {
    $from = "from campaigns cam, campaign_types camt, campaign_statuses cams, users u ";
}

$where .= "where cam.campaign_type_id = camt.campaign_type_id ";
$where .= "and cam.campaign_status_id = cams.campaign_status_id ";
$where .= "and cam.user_id = u.user_id ";
$where .= "and campaign_record_status = 'a'";

$criteria_count = 0;

if ($campaign_category_id > 0) {
    $criteria_count++;
    $where .= " and ecm.on_what_table = 'campaigns' and ecm.on_what_id = cam.campaign_id and ecm.category_id = $campaign_category_id ";
}

if (strlen($campaign_title) > 0) {
    $criteria_count++;
    $where .= " and cam.campaign_title like " . $con->qstr($campaign_title . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and cam.user_id = $user_id";
}

if (strlen($campaign_status_id) > 0) {
    $criteria_count++;
    $where .= " and cam.campaign_status_id = $campaign_status_id";
}

if (strlen($campaign_type_id) > 0) {
    $criteria_count++;
    $where .= " and cam.campaign_type_id = $campaign_type_id";
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

if ($sort_column == 1) {
    $order_by = "campaign_title";
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";
$sql .= $from . $where . " order by $order_by";

$sql_recently_viewed = "select * from recent_items r, campaigns cam, campaign_types camt, campaign_statuses cams
where r.user_id = $session_user_id
and cam.campaign_type_id = camt.campaign_type_id
and cam.campaign_status_id = cams.campaign_status_id
and r.on_what_table = 'campaigns'
and r.on_what_id = cam.campaign_id
and campaign_record_status = 'a'
order by r.recent_item_timestamp desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href=one.php?campaign_id=' . $rst->fields['campaign_id'] . '>' . $rst->fields['campaign_title'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['campaign_type_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['campaign_status_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>No recently viewed campaigns</td></tr>';
}

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

$sql2 = "select campaign_type_pretty_name, campaign_type_id from campaign_types where campaign_type_record_status = 'a' order by campaign_type_pretty_name";
$rst = $con->execute($sql2);
$campaign_type_menu = $rst->getmenu2('campaign_type_id', $campaign_type_id, true);
$rst->close();

$sql2 = "select campaign_status_pretty_name, campaign_status_id from campaign_statuses where campaign_status_record_status = 'a' order by campaign_status_id";
$rst = $con->execute($sql2);
$campaign_status_menu = $rst->getmenu2('campaign_status_id', $campaign_status_id, true);
$rst->close();

$sql2 = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'campaigns'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql2);
$campaign_category_menu = $rst->getmenu2('campaign_category_id', $campaign_category_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'campaigns', '', '');
}

$page_title = 'Campaigns';
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=campaigns_next_page value="<?php  echo $campaigns_next_page ?>">
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
                <td class=widget_label>Campaign Name</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Category</td>
                <td class=widget_label>Media</td>
                <td class=widget_label>Status</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="campaign_title" size=20 value="<?php  echo $campaign_title ?>"></td>
                <td class=widget_content_form_element><?php  echo $campaign_type_menu ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
                <td class=widget_content_form_element><?php  echo $campaign_category_menu ?></td>
                <td class=widget_content_form_element><input type=text name=media size=12 value="<?php  echo $media ?>"></td>
                <td class=widget_content_form_element><?php  echo $campaign_status_menu ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="Search"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="Clear Search"> <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";} ?> </td>
            </tr>
        </table>
        </form>

<?php

$pager = new ADODB_Pager($con,$sql, 'campaigns', false, $sort_column-1, $pretty_sort_order);
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

        <!-- new campaign //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Options</td>
            </tr>
            <tr>
                <td class=widget_content><a href="new.php">Add New Campaign</a></td>
            </tr>
        </table>

        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Recently Viewed</td>
            </tr>
            <tr>
                <td class=widget_label>Campaign</td>
                <td class=widget_label>Type</td>
                <td class=widget_label>Status</td>
            </tr>
            <?php  echo $recently_viewed_table_rows ?>
        </table>

        </td>
    </tr>
</table>

<script language=javascript>
<!--

function initialize() {
    document.forms[0].campaign_title.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "/email/index.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

function submitForm(adodbNextPage) {
    document.forms[0].campaigns_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].campaigns_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>

<?php end_page(); ?>
