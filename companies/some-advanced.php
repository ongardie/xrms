<?php
/**
 * Show search results for advanced company search
 *
 * $Id: some-advanced.php,v 1.23 2006/01/02 22:56:27 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');


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
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// generated from thin air as far as I can tell
// probably a BUG - TBD - Please Fix Me!
$company_category_id = '';

// set all session variables
arr_vars_session_set ( $arr_vars );

//if ( 0 ) {
// seems to be unused
//  $_SESSION['companies_company_name'] = $company_name;
//  $_SESSION['companies_company_category_id'] = $company_category_id;
//  $_SESSION['companies_company_code'] = $company_code;
//  $_SESSION['companies_user_id'] = $user_id;
//  $_SESSION['companies_crm_status_id'] = $crm_status_id;
//}

$con = get_xrms_dbconnection();

//uncomment this line if you suspect a problem with the SQL query
//$con->debug = 1;


  

$sql = "
SELECT distinct " . $con->Concat("'<a href=\"one.php?company_id='","c.company_id","'\">'","c.company_name","'</a>'") . " AS name,
c.company_code AS code,
u.username AS user,
industry_pretty_name as industry,
crm_status_pretty_name AS crm_status,
as1.account_status_display_html as account_status,
r.rating_display_html AS rating, 
count(con.contact_id) AS contacts
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
    $where .= " and c.company_name like " . $con->qstr(company_search_string($company_name), get_magic_quotes_gpc());
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
} else {
    $list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $where .= " and c.company_id IN ($list) ";
        }
    } else { $where .= ' AND 1 = 2 '; }
}

$sql .= $from . $where . " group by c.company_id ";

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

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'companies', '', 4);
}

$page_title = _("Search Companies");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">
		<form action=some-advanced.php method=post name="CompaniesSomeAdvanced">
	      <input type=hidden name=use_post_vars value=1>
		  <input type=hidden name=companies_next_page value="<?php  echo $companies_next_page; ?>">


<?php
//Nic - I did this different than the other some.phps because it is a more complex sql you have to write to retrieve company email records
$_SESSION["search_sql"]["from"]=$from;
$_SESSION["search_sql"]["where"]=$where;
$_SESSION["search_sql"]["order"]=" order by $order_by";

$columns = array();
$columns[] = array('name' => _("Company Name"), 'index_sql' => 'name', 'sql_sort_column' => 'company_name', 'type' => 'url');
$columns[] = array('name' => _("Company Code"), 'index_sql' => 'code');
$columns[] = array('name' => _("User"), 'index_sql' => 'user');
$columns[] = array('name' => _("Industry"), 'index_sql' => 'industry');
$columns[] = array('name' => _("CRM Status"), 'index_sql' => 'crm_status');
$columns[] = array('name' => _("Account Status"), 'index_sql' => 'account_status', 'type' => 'html');
$columns[] = array('name' => _("Rating"), 'index_sql' => 'rating', 'type' => 'html');
$columns[] = array('name' => _("Contacts"), 'index_sql' => 'contacts');

// selects the columns this user is interested in
// no reason to set this if you don't want all by default
$default_columns = null;
// $default_columns =  array("name","code","user","industry","crm_status","account_status","rating");

$pager_columns = new Pager_Columns('CompanyAdvancedPager', $columns, $default_columns, 'CompaniesSomeAdvanced');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

echo $pager_columns_selects;

//echo htmlentities($sql);


$pager = new GUP_Pager($con, $sql, null, _('Search Results'), 'CompaniesSomeAdvanced', 'CompanyAdvancedPager', $columns);

$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            " . $pager->GetAndUseExportButton() .  "
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=\""._("Mail Merge")."\"></td></tr>";

$pager->AddEndRows($endrows);
$pager->Render($system_rows_per_page);

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
 * Revision 1.23  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.22  2005/08/05 21:39:09  vanmer
 * - changed to use centralized company search name function
 *
 * Revision 1.21  2005/05/03 16:44:28  daturaarutad
 * updated to use GUP_Pager
 *
 * Revision 1.20  2005/03/21 13:40:55  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.19  2005/03/20 01:49:45  maulani
 * - Remove obsolete code
 *
 * Revision 1.18  2005/02/14 21:43:45  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.17  2005/01/13 18:23:47  vanmer
 * - ACL restriction on search
 *
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
