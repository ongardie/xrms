<?php
/**
 * Search and view summary information on multiple companies
 *
 * This is the main way of locating companies in XRMS
 *
 * $Id: some.php,v 1.47 2004/12/31 21:14:30 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once('pager.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                   'sort_column'         => array('companies_sort_column',arr_vars_SESSION),
                   'current_sort_column' => array('companies_current_sort_column',arr_vars_SESSION),
                   'sort_order'          => array('companies_sort_order',arr_vars_SESSION),
                   'current_sort_order'  => array('companies_current_sort_order',arr_vars_SESSION),
                   'company_name'        => array('companies_company_name',arr_vars_SESSION),
                   'company_type_id'     => array('companies_company_type_id',arr_vars_SESSION),
                   'company_category_id' => array('companies_company_category_id',arr_vars_SESSION),
                   'company_code'        => array('companies_company_code',arr_vars_SESSION),
                   'user_id'             => array('companies_user_id',arr_vars_SESSION),
                   'crm_status_id'       => array('companies_crm_status_id',arr_vars_SESSION),
                   'industry_id'         => array('industry_id',arr_vars_SESSION),
                   'city'                => array('city',arr_vars_SESSION),
                   'state'               => array('state',arr_vars_SESSION)
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

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

// set all session variables
arr_vars_session_set ( $arr_vars );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment this line if you suspect a problem with the SQL query
//$con->debug = 1;

$sql = "
SELECT " . $con->Concat("'<a href=\"one.php?company_id='","c.company_id","'\">'","c.company_name","'</a>'") . " AS '"._("Company Name")."',
c.company_code AS '"._("Company Code")."',
u.username AS '"._("User")."',
industry_pretty_name as '"._("Industry")."',
crm_status_pretty_name AS '"._("CRM Status")."',
as1.account_status_display_html AS '"._("Account Status")."',
r.rating_display_html AS '"._("Rating")."' ";

$criteria_count = 0;

if ($company_category_id > 0) {
    $criteria_count++;
    $from = "from industries i, crm_statuses crm, ratings r, account_statuses as1, users u, entity_category_map ecm, companies c ";
} else {
    $from = "from industries i, crm_statuses crm, ratings r, account_statuses as1, users u, companies c ";
}

$from  .= "LEFT JOIN addresses addr on addr.address_id = c.default_primary_address ";
$where = "where c.industry_id = i.industry_id ";
$where .= "and c.crm_status_id = crm.crm_status_id ";
//remove next line because it makes companies without default addr not display
//$where .= "and c.default_primary_address = addr.address_id ";
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
    $sql   .= ", addr.city as '"._("City")."' \n";
    if (!strlen($state) > 0) {
        $sql   .= ", addr.province as '"._("State")."' \n";
    }
    $where .= " and addr.city LIKE " . $con->qstr($city . '%' , get_magic_quotes_gpc()) ;
}

if (strlen($state) > 0) {
    $criteria_count++;
    if (!strlen($city) > 0) {
        $sql   .= ", addr.city as '"._("City")."' \n";
    }
    $sql   .= ", addr.province as '"._("State")."' \n";
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

// note: $sql is the list of companies we will be selecting
// we need to determine the $company_count from it.
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

$sql_recently_viewed = "select
c.company_id,
c.company_name,
max(r.recent_item_timestamp) as lasttime
from recent_items r, companies c
where r.user_id = $session_user_id
and r.on_what_table = 'companies'
and r.recent_action = ''
and r.on_what_id = c.company_id
and c.company_record_status = 'a'
group by company_id,
c.company_name
order by lasttime desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

$recently_viewed_table_rows = '';

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?company_id=' . $rst->fields['company_id'] . '">' . $rst->fields['company_name'] . '</a></td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = "<tr><td class=widget_content colspan=3>"._("No recently viewed companies")."</td></tr>";
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

$sql2 = "select company_type_pretty_name, company_type_id from company_types where company_type_record_status = 'a' order by company_type_pretty_name";
$rst = $con->execute($sql2);
$company_type_menu = translate_menu($rst->getmenu2('company_type_id', $company_type_id, true));
$rst->close();

$sql2 = "select crm_status_pretty_name, crm_status_id from crm_statuses where crm_status_record_status = 'a' order by crm_status_pretty_name";
$rst = $con->execute($sql2);
$crm_status_menu = translate_menu($rst->getmenu2('crm_status_id', $crm_status_id, true));
$rst->close();

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_pretty_name";
$rst = $con->execute($sql2);
$industry_menu = translate_menu($rst->getmenu2('industry_id', $industry_id, true));
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'companies', '', 4);
}

$page_title = _("Search Companies");
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
        <input type=hidden name=company_type_id value="<?php  echo $company_type_id; ?>">
        <input type=hidden name=company_code value="<?php  echo $company_code; ?>">
        <input type=hidden name=crm_status_id value="<?php  echo $crm_status_id; ?>">

        <input type=hidden name=companies_next_page>
        <table class=widget cellspacing=1 width="100%">
            <tr>
          <td class=widget_header colspan=6>
            <?php  echo _("Search Criteria"); ?>
          </td>
        </tr>
        <tr>
          <td class=widget_label>
            <?php  echo _("Company Name"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("Owner"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("Category"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("Industry"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("City"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("State"); ?>
          </td>
        </tr>
        <tr>
            <td class=widget_content_form_element>
                <input type=text name="company_name" size=15 value="<?php  echo $company_name; ?>">
            </td>
            <td class=widget_content_form_element>
                <?php  echo $user_menu; ?>
            </td>
            <td class=widget_content_form_element>
                <?php  echo $company_category_menu; ?>
            </td>
            <td class=widget_content_form_element>
                <?php  echo $industry_menu; ?>
            </td>
            <td class=widget_content_form_element>
                <input type=text name="city" size=10 value="<?php  echo $city; ?>">
            </td>
            <td class=widget_content_form_element>
                <input type=text name="state" size=5 value="<?php echo $state; ?>">
            </td>
        </tr>
        <tr>
            <td class=widget_content_form_element colspan=6>
                <input name="submit_form" type=submit class=button value="<?php echo _("Search"); ?>">
                <input name="clear_search" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                <?php
                    if ($company_count > 0) {
                        print "<input class=button type=button onclick='javascript: bulkEmail()' value='". _("Bulk E-Mail")."'>";
                    };
                ?>
                <input name="advanced_search" type=button class=button onclick="javascript: location.href='advanced-search.php';" value="<?php echo _("Advanced Search"); ?>">
            </td>
        </tr>
      </table>
  </form>

<?php
//Nic - I did this different than the other some.phps because it is a more complex sql you have to write to retrieve company email records
$_SESSION["search_sql"]["from"]=$from;
$_SESSION["search_sql"]["where"]=$where;
$_SESSION["search_sql"]["order"]=" order by $order_by";

$pager = new Companies_Pager($con, $sql, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

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

function initialize() {
    document.forms[0].company_name.focus();
}

initialize();

function submitForm(companiesNextPage) {
    document.forms[0].companies_next_page.value = companiesNextPage;
    document.forms[0].submit();
}

function exportIt() {
    //document.forms[0].action = "export.php";
    //document.forms[0].submit();
    // reset the form so that post-export searches work
    //document.forms[0].action = "some.php";
                alert('Export functionality hasnt been implemented yet for multiple companies')
}

function bulkEmail() {
    document.forms[0].action = "../email/email.php?scope=companies";
    document.forms[0].submit();
    //alert('Mail Merge functionality hasnt been implemented yet for multiple companies')
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
 * Revision 1.47  2004/12/31 21:14:30  braverock
 * - sort select lists in search
 *
 * Revision 1.46  2004/10/28 22:17:02  gpowers
 * - removed company_code from Recently Viewed Sidebar
 *   - If anyone wants to keep it, let me know and I'll revert this patch and create a new patch with a $show_company_code_in_sidebars var.
 *
 * Revision 1.45  2004/08/26 23:16:28  niclowe
 * Enabled mail merge functionality for companies/some.php
 * Sorted pre-sending email checkbox page by company then contact lastname
 * Enabled mail merge for advanced-search companies
 *
 * Revision 1.44  2004/08/26 22:35:28  niclowe
 * Enabled mail merge functionality for companies
 *
 * Revision 1.43  2004/08/20 17:14:40  braverock
 * - add translate_menu for additional menus in search
 *
 * Revision 1.42  2004/08/19 13:14:05  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * Revision 1.41  2004/08/19 12:01:53  braverock
 * - added space after Rating so that the $from clause wouldn't collide
 *   - fixes SF bug 996549 using suggestion from Roberto Durrer (durrer)
 *
 * Revision 1.40  2004/08/18 00:06:16  niclowe
 * Fixed bug 941839 - Mail Merge not working
 *
 * Revision 1.39  2004/08/17 10:56:44  johnfawcett
 * - added translate_menu call to Industries select menu
 *
 * Revision 1.38  2004/08/13 12:29:57  maulani
 * - Fix errant copy and paste
 *
 * Revision 1.37  2004/08/03 14:36:54  maulani
 * - Fix recent items sql to only list each company once and to optimize the sql
 * - Fix advanced search button to remove erroneous comment
 *
 * Revision 1.36  2004/07/31 12:23:19  cpsource
 * - Reactivate advanced search feature
 *
 * Revision 1.35  2004/07/31 12:14:59  cpsource
 * - Stub advanced search as it doesn't seem to work.
 *
 * Revision 1.34  2004/07/28 20:41:30  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.33  2004/07/25 12:43:25  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.32  2004/07/19 02:21:56  braverock
 * - localize all strings for i18n
 *
 * Revision 1.31  2004/07/16 11:31:42  cpsource
 * - Removed hard-coded english language construct
 *   Removed advanced-search.php button as advanced-search had problems
 *
 * Revision 1.30  2004/07/15 13:49:53  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.29  2004/07/15 13:05:08  cpsource
 * - Add arr_vars sub-system for passing variables between code streams.
 *
 * Revision 1.28  2004/07/14 20:19:50  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.27  2004/07/14 16:06:55  cpsource
 * - Fix numerous undefined variable usages, including a database
 *   loop to determine $company_count.
 *
 * Revision 1.26  2004/07/10 12:56:06  braverock
 * - fix $SESSION sort order variables
 *   - applies patch suggest by cpsource in SF bug 976223
 *
 * Revision 1.25  2004/07/09 18:42:13  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 * - Removed 'as' in JOIN as it fails on Oracle and isn't needed with other db engines
 *
 * Revision 1.24  2004/07/05 21:30:54  introspectshun
 * - Moved 'companies c' to the end of 'from' clause.
 * - Query fails on MSSQL otherwise (column 'c' not recognized during 'LEFT JOIN' operation)
 *
 * Revision 1.23  2004/07/02 17:38:18  maulani
 * - Fix addresses reference in sql in patch submitted by Nic Lowe (niclowe)
 * - Patch # 981927
 *
 * Revision 1.22  2004/07/01 15:50:25  maulani
 * - Fix bug 976220 reported by cpsource ($where used before defined)
 *
 * Revision 1.21  2004/07/01 13:37:25  braverock
 * - compress quick search back onto one line now that advanced search exists
 *   - @todo add Category search to Advanced search
 *
 * Revision 1.20  2004/07/01 13:21:06  braverock
 * - change name of submit to submit_form to not conflict with js
 *   - patch supplied by David Uhlman
 *
 * Revision 1.19  2004/06/29 14:43:21  maulani
 * - Full implementation of advanced companies search
 *
 * Revision 1.18  2004/06/26 15:36:03  braverock
 * - change search layout to two rows to improve CSS positioning
 *   - applied modified version of SF patch #971474 submitted by s-t
 *
 * Revision 1.17  2004/06/23 21:50:53  braverock
 * - use join to find address so that even companies without addr will display
 *   - patch submitted by David Uhlman
 *
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
