<?php
/**
 * Show search results for advanced company search
 *
 * $Id: some-advanced.php,v 1.16 2004/08/30 14:08:46 neildogg Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once('pager.php');
require_once($include_directory . 'adodb-params.php');

// a helper routine to retrieve one field from a table
//
// Call:
//
// $con - db connection
// $sql - the sql statement to execute
// $nam - the option to highlight - if it's '', then first option is
//        the default and it is blank.
//
// Return:
//
// a string of the html menu
//
function check_and_get ( $con, $sql, $nam )
{
  $rst = $con->execute($sql);

  if ( !$rst ) {
    db_error_handler($con, $sql);
  }
  if ( !$rst->EOF && $nam ) {
    $GLOBALS[$nam] = $rst->fields[$nam];
    $tmp = $rst->getmenu2($nam, $GLOBALS[$nam], true);
  } else {
    $tmp = $rst->getmenu2($nam, '', true);
  }

  $rst->close();

  return $tmp;
}

$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                    'company_name'        => array('companies_company_name',arr_vars_SESSION),
                    'legal_name'          => array('companies_legal_name',arr_vars_SESSION),
                    'company_code'        => array('companies_company_code',arr_vars_SESSION),
                    'crm_status_id'       => array('companies_crm_status_id',arr_vars_SESSION),
                    'company_source_id'   => array('companies_company_source_id',arr_vars_SESSION),
                    'industry_id'         => array('companies_industry_id',arr_vars_SESSION),
                    'user_id'             => array('companies_user_id',arr_vars_SESSION),
                    'phone' => array ( 'companies_phone' , arr_vars_SESSION),
                    'phone2' => array ( 'companies_phone2' , arr_vars_SESSION),
                    'fax' => array ( 'companies_fax' , arr_vars_SESSION),
                    'url' => array ( 'companies_url' , arr_vars_SESSION),
                    'employees' => array ( 'companies_employees' , arr_vars_SESSION),
                    'revenue' => array ( 'companies_revenue' , arr_vars_SESSION),
                    'custom1' => array ( 'companies_custom1' , arr_vars_SESSION),
                    'custom2' => array ( 'companies_custom2' , arr_vars_SESSION),
                    'custom3' => array ( 'companies_custom3' , arr_vars_SESSION),
             		   'custom4' => array ( 'companies_custom4' , arr_vars_SESSION),
             		   'profile' => array ( 'companies_profile' , arr_vars_SESSION),
             		   'address_name' => array ( 'companies_address_name' , arr_vars_SESSION),
             		   'line1' => array ( 'companies_line1' , arr_vars_SESSION),
	             	   'line2' => array ( 'companies_line2' , arr_vars_SESSION),
	             	   'city' => array ( 'companies_city' , arr_vars_SESSION),
	             	   'province' => array ( 'companies_province' , arr_vars_SESSION),
	             	   'postal_code' => array ( 'companies_postal_code' , arr_vars_SESSION),
	             	   'country_id' => array ( 'companies_country_id' , arr_vars_SESSION),
	             	   'address_body' => array ( 'companies_address_body' , arr_vars_SESSION),
        
	             	   'sort_column'         => array ( 'sort_column'         , arr_vars_REQUEST),
	             	   'current_sort_column' => array ( 'current_sort_column' , arr_vars_REQUEST),
	             	   'sort_order'          => array ( 'sort_order'          , arr_vars_REQUEST),
	             	   'current_sort_order'  => array ( 'current_sort_order'  , arr_vars_REQUEST),
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// generated from thin air as far as I can tell
// probably a BUG - TBD - Please Fix Me!
$company_category_id = '';

if ( !$sort_column ) {
    $sort_column = 1;
    $current_sort_column = $sort_column;
    $sort_order = "asc";
}

if (!($sort_column == $current_sort_column)) {
    $sort_order = "asc";
}

$opposite_sort_order = ($sort_order == "asc") ? "desc" : "asc";
$sort_order = ( $resort && ($current_sort_column == $sort_column)) ? $opposite_sort_order : $sort_order;

$ascending_order_image  = '<img border=0 height=10 width=10 alt="" src=../img/asc.gif>' ;
$descending_order_image = '<img border=0 height=10 width=10 alt="" src=../img/desc.gif>';

$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

// set all session variables
arr_vars_session_set ( $arr_vars );

//if ( 0 ) {
// seems to be unused
//  $_SESSION['companies_sort_column'] = $sort_column;
//  $_SESSION['companies_current_sort_column'] = $sort_column;
//  $_SESSION['companies_sort_order'] = $sort_order;
//  $_SESSION['companies_current_sort_order'] = $sort_order;
//  $_SESSION['companies_company_name'] = $company_name;
//  $_SESSION['companies_company_category_id'] = $company_category_id;
//  $_SESSION['companies_company_code'] = $company_code;
//  $_SESSION['companies_user_id'] = $user_id;
//  $_SESSION['companies_crm_status_id'] = $crm_status_id;
//}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment this line if you suspect a problem with the SQL query
//$con->debug = 1;

$sql = "
SELECT distinct " . $con->Concat("'<a href=\"one.php?company_id='","c.company_id","'\">'","c.company_name","'</a>'") . " AS '"._("Company Name")."',
c.company_code AS '"._("Company Code")."',
u.username AS '"._("User")."',
industry_pretty_name as '"._("Industry")."',
crm_status_pretty_name AS '"._("CRM Status")."',
as1.account_status_display_html AS '"._("Account Status")."',
r.rating_display_html AS '"._("Rating")."',
count(con.contact_id) AS '"._("Contacts")."'
";

$criteria_count = 0;

if ($company_category_id > 0) {
    $criteria_count++;
    $from = "from contacts con, companies c, addresses addr, industries i, crm_statuses crm, ratings r, account_statuses as1, users u, entity_category_map ecm ";
} else {
    $from = "from contacts con, companies c, addresses addr, industries i, crm_statuses crm, ratings r, account_statuses as1, users u ";
}

$where = "where c.industry_id = i.industry_id ";
$where .= "and c.company_id = con.company_id  ";
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

if ( $company_name ) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr($company_name, get_magic_quotes_gpc());
}

if ( $legal_name ) {
    $criteria_count++;
    $where .= " and c.legal_name like " . $con->qstr($legal_name, get_magic_quotes_gpc());
}

if ( $company_code ) {
    $criteria_count++;
    $where .= " and c.company_code = " . $con->qstr($company_code, get_magic_quotes_gpc());
}

if ( $crm_status_id ) {
    $criteria_count++;
    $where .= " and c.crm_status_id = $crm_status_id";
}

if ( $company_source_id ) {
    $criteria_count++;
    $where .= " and c.company_source_id = $company_source_id";
}

if ( $industry_id ) {
    $criteria_count++;
    $where .= " and c.industry_id = $industry_id";
}

if ( $user_id ) {
    $criteria_count++;
    $where .= " and c.user_id = $user_id";
}

if ( $phone ) {
    $criteria_count++;
    $where .= " and c.phone like " . $con->qstr($phone, get_magic_quotes_gpc());
}

if ( $phone2 ) {
    $criteria_count++;
    $where .= " and c.phone2 like " . $con->qstr($phone2, get_magic_quotes_gpc());
}

if ( $fax ) {
    $criteria_count++;
    $where .= " and c.fax like " . $con->qstr($fax, get_magic_quotes_gpc());
}

if (strlen($url) > 0) {
    $criteria_count++;
    $where .= " and c.url like " . $con->qstr($url, get_magic_quotes_gpc());
}

if ( $employees ) {
    $criteria_count++;
    $where .= " and c.employees like " . $con->qstr($employees, get_magic_quotes_gpc());
}

if ( $revenue ) {
    $criteria_count++;
    $where .= " and c.revenue like " . $con->qstr($revenue, get_magic_quotes_gpc());
}

if ( $custom1 ) {
    $criteria_count++;
    $where .= " and c.custom1 like " . $con->qstr($custom1, get_magic_quotes_gpc());
}

if ( $custom2 ) {
    $criteria_count++;
    $where .= " and c.custom2 like " . $con->qstr($custom2, get_magic_quotes_gpc());
}

if ( $custom3 ) {
    $criteria_count++;
    $where .= " and c.custom3 like " . $con->qstr($custom3, get_magic_quotes_gpc());
}

if ( $custom4 ) {
    $criteria_count++;
    $where .= " and c.custom4 like " . $con->qstr($custom4, get_magic_quotes_gpc());
}

if ( $profile ) {
    $criteria_count++;
    $where .= " and c.profile like " . $con->qstr($profile, get_magic_quotes_gpc());
}

if ( $address_name ) {
    $criteria_count++;
    $where .= " and addr.address_name like " . $con->qstr($address_name, get_magic_quotes_gpc());
}

if ( $line1 ) {
    $criteria_count++;
    $where .= " and addr.line1 like " . $con->qstr($line1, get_magic_quotes_gpc());
}

if ( $line2 ) {
    $criteria_count++;
    $where .= " and addr.line2 like " . $con->qstr($line2, get_magic_quotes_gpc());
}

if ( $city ) {
    $criteria_count++;
    $where .= " and addr.city like " . $con->qstr($city, get_magic_quotes_gpc());
}

if ( $province ) {
    $criteria_count++;
    $where .= " and addr.province like " . $con->qstr($province, get_magic_quotes_gpc());
}

if ( $postal_code ) {
    $criteria_count++;
    $where .= " and addr.postal_code like " . $con->qstr($postal_code, get_magic_quotes_gpc());
}

if ( $country_id ) {
    $criteria_count++;
    $where .= " and addr.country_id = $country_id";
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

if ($sort_column == 1) {
    $order_by = "company_name";
}
elseif($sort_column == 8) {
    $order_by = _("Contacts");
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";

$sql .= $from . $where . " group by c.company_id order by $order_by";

//echo "sql = $sql<br>";

$sql_recently_viewed = "select * from recent_items r, companies c, crm_statuses crm
where r.user_id = $session_user_id
and r.on_what_table = 'companies'
and r.recent_action = ''
and c.crm_status_id = crm.crm_status_id
and r.on_what_id = c.company_id
and company_record_status = 'a'
order by r.recent_item_timestamp desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

$recently_viewed_table_rows = '';

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
    $recently_viewed_table_rows = "<tr><td class=widget_content colspan=3>" . _("No recently viewed companies") . "</td></tr>";
}

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$user_menu = check_and_get($con,$sql2,'user_id');

$sql2 = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'companies'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$company_category_menu = check_and_get($con,$sql2,'category_id');

$sql2 = "select company_type_pretty_name, company_type_id from company_types where company_type_record_status = 'a' order by company_type_id";
$company_type_menu = check_and_get($con,$sql2,'company_type_id');

$sql2 = "select crm_status_pretty_name, crm_status_id from crm_statuses where crm_status_record_status = 'a' order by crm_status_id";
$crm_status_menu = check_and_get($con,$sql2,'crm_status_id');

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_id";
$industry_menu = check_and_get($con,$sql2,'industry_id');

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'companies', '', 4);
}

$page_title = _("Search Companies");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">
		<form action=some-advanced.php method=post>
		  <input type=hidden name=companies_next_page value="<?php  echo $companies_next_page; ?>">
      <input type=hidden name=resort value="0">
      <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
      <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
      <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
      <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">


<?php
//Nic - I did this different than the other some.phps because it is a more complex sql you have to write to retrieve company email records
$_SESSION["search_sql"]["from"]=$from;
$_SESSION["search_sql"]["where"]=$where;
$_SESSION["search_sql"]["order"]=" order by $order_by";

$pager = new Companies_Pager($con, $sql, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>
		</form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- new company //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Company Options"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="new.php"><?php echo _("New Company"); ?></a></td>
            </tr>
        </table>

        <!-- recently viewed companies //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php  echo _("Company Name"); ?></td>
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
    document.forms[0].action = "../email/email.php?scope=companies";
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
 * Revision 1.16  2004/08/30 14:08:46  neildogg
 * - Grab sorting values
 *
 * Revision 1.15  2004/08/30 13:20:17  neildogg
 * - Robustified search
 *
 * Revision 1.14  2004/08/30 12:52:38  neildogg
 * - Got rid of use_post_vars which overrode
 *  - saved session vars
 *  - Added sort by # Contacts
 *
 * Revision 1.13  2004/08/26 22:55:26  niclowe
 * Enabled mail merge functionality for companies/some.php
 * Sorted pre-sending email checkbox page by company then contact lastname
 * Enabled mail merge for advanced-search companies
 *
 * Revision 1.12  2004/08/19 13:14:05  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * Revision 1.11  2004/08/14 21:41:51  niclowe
 * added adodb pager to advanced search - fixed minor incorrect, non bug inducing form parameters
 *
 * Revision 1.10  2004/08/14 21:26:13  niclowe
 * added adodb pager to advanced search
 *
 * Revision 1.9  2004/07/31 16:23:09  cpsource
 * - Make default menu items blank
 *
 * Revision 1.8  2004/07/31 12:11:04  cpsource
 * - Fixed multiple undefines and subsequent hidden bugs
 *   Used arr_vars for retrieving POST'ed variables
 *   Code cleanup and simplification.
 *   Removed setting session variables as they were unused
 *   Set use_post_vars as needed.
 *
 * Revision 1.7  2004/07/28 20:41:31  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.6  2004/07/21 19:17:57  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.5  2004/07/09 18:41:10  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.4  2004/07/01 15:50:25  maulani
 * - Fix bug 976220 reported by cpsource ($where used before defined)
 *
 * Revision 1.3  2004/06/29 14:43:21  maulani
 * - Full implementation of advanced companies search
 *
 * Revision 1.2  2004/06/29 13:19:59  maulani
 * - Additional fields for advanced search
 *
 * Revision 1.1  2004/06/28 23:08:40  maulani
 * - Advanced search allows searching with a lot more fields
 *
 */
?>
