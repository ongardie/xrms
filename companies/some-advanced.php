<?php
/**
 * Show search results for advanced company search
 *
 * $Id: some-advanced.php,v 1.2 2004/06/29 13:19:59 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

//set the language
$_SESSION['language'] = 'english';

$session_user_id = session_check();

require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$use_post_vars = ($_POST['use_post_vars'] == 1) ? 1 : 0;
$resort = $_POST['resort'];


$sort_column = $_POST['sort_column'];
$current_sort_column = $_POST['current_sort_column'];
$sort_order = $_POST['sort_order'];
$current_sort_order = $_POST['current_sort_order'];

$company_name = $_POST['company_name'];
$legal_name = $_POST['legal_name'];
$company_code = $_POST['company_code'];
$crm_status_id = $_POST['crm_status_id'];
$company_source_id = $_POST['company_source_id'];
$industry_id = $_POST['industry_id'];
$user_id = $_POST['user_id'];
$phone = $_POST ['phone'];
$phone2 = $_POST ['phone2'];
$fax = $_POST ['fax'];
$url = $_POST ['url'];
$employees = $_POST ['employees'];
$revenue = $_POST ['revenue'];
$custom1 = $_POST ['custom1'];
$custom2 = $_POST ['custom2'];
$custom3 = $_POST ['custom3'];
$custom4 = $_POST ['custom4'];
$profile = $_POST ['profile'];
$address_name = $_POST ['address_name'];
$line1 = $_POST ['line1'];
$line2 = $_POST ['line2'];
$city = $_POST ['city'];
$province = $_POST ['province'];
$postal_code = $_POST ['postal_code'];
$country_id = $_POST['country_id'];
$address_body = $_POST['address_body'];

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

$ascending_order_image = ' <img border=0 height=10 width=10 alt="" src=../img/asc.gif>';
$descending_order_image = ' <img border=0 height=10 width=10 alt="" src=../img/desc.gif>';

$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

$_SESSION['companies_sort_column'] = $sort_column;
$_SESSION['companies_current_sort_column'] = $sort_column;
$_SESSION['companies_sort_order'] = $sort_order;
$_SESSION['companies_current_sort_order'] = $sort_order;
$_SESSION['companies_company_name'] = $company_name;
$_SESSION['companies_company_category_id'] = $company_category_id;
$_SESSION['companies_company_code'] = $company_code;
$_SESSION['companies_user_id'] = $user_id;
$_SESSION['companies_crm_status_id'] = $crm_status_id;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment this line if you suspect a problem with the SQL query
// $con->debug = 1;

$sql = "
SELECT " . $con->Concat("'<a href=\"one.php?company_id='","CAST(c.company_id AS CHAR)","'\">'","c.company_name","'</a>'") . " AS '$strCompaniesSomeCompanyNameLabel',
c.company_code AS '$strCompaniesSomeCompanyCodeLabel',
u.username AS '$strCompaniesSomeCompanyUserLabel',
industry_pretty_name as '$strCompaniesSomeCompanyIndustrylabel',
crm_status_pretty_name AS '$strCompaniesSomeCompanyCRMStatusLabel',
as1.account_status_display_html AS '$strCompaniesSomeCompanyAccountStatusLabel',
r.rating_display_html AS '$strCompaniesSomeCompanyRatingLabel'
";

$criteria_count = 0;

if ($company_category_id > 0) {
    $criteria_count++;
    $from = "from companies c, addresses addr, industries i, crm_statuses crm, ratings r, account_statuses as1, users u, entity_category_map ecm ";
} else {
    $from = "from companies c, addresses addr, industries i, crm_statuses crm, ratings r, account_statuses as1, users u ";
}

$where .= "where c.industry_id = i.industry_id ";
$where .= "and c.crm_status_id = crm.crm_status_id ";
$where .= "and c.company_id = addr.company_id ";
$where .= "and r.rating_id = c.rating_id ";
$where .= "and as1.account_status_id = c.account_status_id ";
$where .= "and c.user_id = u.user_id ";
$where .= "and company_record_status = 'a' ";
$where .= "and address_record_status = 'a'";

if ($company_category_id > 0) {
    $where .= " and ecm.on_what_table = 'companies' and ecm.on_what_id = c.company_id and ecm.category_id = $company_category_id ";
}

if (strlen($company_name) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr($company_name, get_magic_quotes_gpc());
}

if (strlen($legal_name) > 0) {
    $criteria_count++;
    $where .= " and c.legal_name like " . $con->qstr($legal_name, get_magic_quotes_gpc());
}

if (strlen($company_code) > 0) {
    $criteria_count++;
    $where .= " and c.company_code = " . $con->qstr($company_code, get_magic_quotes_gpc());
}

if (strlen($crm_status_id) > 0) {
    $criteria_count++;
    $where .= " and c.crm_status_id = $crm_status_id";
}

if (strlen($company_source_id) > 0) {
    $criteria_count++;
    $where .= " and c.company_source_id = $company_source_id";
}

if (strlen($industry_id) > 0) {
    $criteria_count++;
    $where .= " and c.industry_id = $industry_id";
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and c.user_id = $user_id";
}

if (strlen($phone) > 0) {
    $criteria_count++;
    $where .= " and c.phone like " . $con->qstr($phone, get_magic_quotes_gpc());
}

if (strlen($company_type_id) > 0) {
    $criteria_count++;
    $where .= " and c.company_id in (select company_id from company_company_type_map where company_type_id = $company_type_id)";
}

if (strlen($city) > 0) {
    $criteria_count++;
    $sql   .= ", addr.city as '$strCompaniesSomeCompanyCityLabel' \n";
    if (!strlen($state) > 0) {
        $sql   .= ", addr.province as '$strCompaniesSomeCompanyStateLabel' \n";
    }
    $where .= " and addr.city LIKE " . $con->qstr($city . '%' , get_magic_quotes_gpc()) ;
}

if (strlen($state) > 0) {
    $criteria_count++;
    if (!strlen($city) > 0) {
        $sql   .= ", addr.city as '$strCompaniesSomeCompanyCityLabel' \n";
    }
    $sql   .= ", addr.province as '$strCompaniesSomeCompanyStateLabel' \n";
    $where .= " and addr.province LIKE " . $con->qstr($state, get_magic_quotes_gpc());
}


if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

if ($sort_column == 1) {
    $order_by = "company_name";
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";

$sql .= $from . $where . " order by $order_by";

$sql_recently_viewed = "select * from recent_items r, companies c, crm_statuses crm
where r.user_id = $session_user_id
and r.on_what_table = 'companies'
and c.crm_status_id = crm.crm_status_id
and r.on_what_id = c.company_id
and company_record_status = 'a'
order by r.recent_item_timestamp desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?company_id=' . $rst->fields['company_id'] . '">' . $rst->fields['company_name'] . ' (' . $rst->fields['company_code'] . ')</a></td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = "<tr><td class=widget_content colspan=3>$strCompaniesSomeNoRecentlyViewedMessage</td></tr>";
}

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

$sql2 = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'companies'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql2);
$company_category_menu = $rst->getmenu2('company_category_id', $company_category_id, true);
$rst->close();

$sql2 = "select company_type_pretty_name, company_type_id from company_types where company_type_record_status = 'a' order by company_type_id";
$rst = $con->execute($sql2);
$company_type_menu = $rst->getmenu2('company_type_id', $company_type_id, true);
$rst->close();

$sql2 = "select crm_status_pretty_name, crm_status_id from crm_statuses where crm_status_record_status = 'a' order by crm_status_id";
$rst = $con->execute($sql2);
$crm_status_menu = $rst->getmenu2('crm_status_id', $crm_status_id, true);
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_id";
$rst = $con->execute($sql2);
$industry_menu = $rst->getmenu2('industry_id', $industry_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'companies', '', 4);
}

$page_title = $strCompaniesSomePageTitle;
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

<?php

$pager = new ADODB_Pager($con, $sql, 'companies', false, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- new company //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2>Company Options</td>
            </tr>
            <tr>
                <td class=widget_content><a href="new.php">New Company</a></td>
            </tr>
        </table>

        <!-- recently viewed companies //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header>Recently Viewed</td>
            </tr>
            <tr>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyNameLabel; ?></td>
            </tr>
            <?php  echo $recently_viewed_table_rows; ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function submitForm(companiesNextPage) {
    document.forms[0].companies_next_page.value = companiesNextPage;
    document.forms[0].submit();
}

function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].companies_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
}

//-->
</script>

<?php

end_page();

/**
 * $Log: some-advanced.php,v $
 * Revision 1.2  2004/06/29 13:19:59  maulani
 * - Additional fields for advanced search
 *
 * Revision 1.1  2004/06/28 23:08:40  maulani
 * - Advanced search allows searching with a lot more fields
 *
 */
?>
