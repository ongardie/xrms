<?php
/**
 * Search and view summary information on multiple companies
 *
 * This is the main way of locating companies in XRMS
 *
 * $Id: some.php,v 1.16 2004/06/21 20:56:29 introspectshun Exp $
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
    $company_name = '';
    $company_type_id = '';
    $company_category_id = '';
    $company_code = '';
    $user_id = '';
    $crm_status_id = '';
    $industry_id = '';
} elseif ($use_post_vars) {
    $sort_column = $_POST['sort_column'];
    $current_sort_column = $_POST['current_sort_column'];
    $sort_order = $_POST['sort_order'];
    $current_sort_order = $_POST['current_sort_order'];
    $company_name = $_POST['company_name'];
    $company_type_id = $_POST['company_type_id'];
    $company_category_id = $_POST['company_category_id'];
    $company_code = $_POST['company_code'];
    $city = $_POST ['city'];
    $state = $_POST ['state'];
    $user_id = $_POST['user_id'];
    $crm_status_id = $_POST['crm_status_id'];
    $industry_id = $_POST['industry_id'];
} else {
    $sort_column = $_SESSION['campaigns_sort_column'];
    $current_sort_column = $_SESSION['campaigns_current_sort_column'];
    $sort_order = $_SESSION['campaigns_sort_order'];
    $current_sort_order = $_SESSION['campaigns_current_sort_order'];
    $company_name = $_SESSION['companies_company_name'];
    $company_type_id = $_SESSION['companies_company_type_id'];
    $company_category_id = $_SESSION['companies_company_category_id'];
    $company_code = $_SESSION['companies_company_code'];
    $user_id = $_SESSION['companies_user_id'];
    $crm_status_id = $_SESSION['companies_crm_status_id'];
    $industry_id = $_SESSION['industry_id'];
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
$where .= "and c.default_primary_address = addr.address_id ";
$where .= "and r.rating_id = c.rating_id ";
$where .= "and as1.account_status_id = c.account_status_id ";
$where .= "and c.user_id = u.user_id ";
$where .= "and company_record_status = 'a'";

if ($company_category_id > 0) {
    $where .= " and ecm.on_what_table = 'companies' and ecm.on_what_id = c.company_id and ecm.category_id = $company_category_id ";
}

if (strlen($company_name) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr('%'. $company_name . '%', get_magic_quotes_gpc());
}

if (strlen($company_type_id) > 0) {
    $criteria_count++;
    $where .= " and c.company_id in (select company_id from company_company_type_map where company_type_id = $company_type_id)";
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and c.user_id = $user_id";
}

if (strlen($company_code) > 0) {
    $criteria_count++;
    $where .= " and c.company_code = " . $con->qstr($company_code, get_magic_quotes_gpc());
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

if (strlen($crm_status_id) > 0) {
    $criteria_count++;
    $where .= " and c.crm_status_id = $crm_status_id";
}

if (strlen($industry_id) > 0) {
    $criteria_count++;
    $where .= " and c.industry_id = $industry_id";
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

        <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=companies_next_page>
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=8><?php  echo $strCompaniesSomeSearchCriteriaTitle; ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyNameLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyCodeLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyUserLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyCategoryLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyIndustrylabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyCRMStatusLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyCityLabel; ?></td>
                <td class=widget_label><?php  echo $strCompaniesSomeCompanyStateLabel; ?></td>

            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="company_name" size=15 value="<?php  echo $company_name; ?>"></td>
                <td class=widget_content_form_element><input type=text name="company_code" size=4 value="<?php  echo $company_code; ?>"></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $company_category_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $industry_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $crm_status_menu; ?></td>
                <td class=widget_content_form_element><input type=text name="city" size=10 value="<?php  echo $city; ?>"></td>
                <td class=widget_content_form_element><input type=text name="state" size=5 value="<?php echo $state; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=8><input class=button type=submit value="Search"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="Clear Search"> <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";}; ?> </td>
            </tr>
        </table>
        </form>

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

function initialize() {
    document.forms[0].company_name.focus();
}

initialize();

function submitForm(companiesNextPage) {
    document.forms[0].companies_next_page.value = companiesNextPage;
    document.forms[0].submit();
}

function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
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
 * $Log: some.php,v $
 * Revision 1.16  2004/06/21 20:56:29  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.15  2004/06/16 20:42:02  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.14  2004/06/12 16:15:06  braverock
 * - remove CAST on CONCAT - databases should implicitly convert numeric to string and VARCHAR is not universally supported
 *
 * Revision 1.13  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.12  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.11  2004/05/06 13:55:49  braverock
 * -add industry search to Companies
 *  - modified form of SF patch 949147 submitted by frenchman
 *
 * Revision 1.10  2004/04/15 22:04:39  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.9  2004/04/07 19:38:25  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.8  2004/03/09 13:47:42  braverock
 * - fixed duplicate city,state display when both are in search terms
 *
 * Revision 1.7  2004/03/09 13:39:39  braverock
 * - fixed broken city and state search
 * - add phpdoc
 *
 */
?>
