<?php
/**
 * opportunities/some.php - This file provides the opportunities search page
 *
 *
 *
 * $Id: some.php,v 1.44 2005/03/15 21:49:48 daturaarutad Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');


$on_what_table='opportunities';
$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
           'opportunity_title'       => array ( 'opportunities_opportunity_title', arr_vars_SESSION ),
           'company_name'            => array ( 'opportunities_company_name', arr_vars_GET_SESSION ),
           'user_id'                 => array ( 'opportunities_user_id', arr_vars_SESSION ),
           'opportunity_status_id'   => array ( 'opportunities_opportunity_status_id', arr_vars_GET_SESSION ),
           'opportunity_category_id' => array ( 'opportunities_opportunity_category_id', arr_vars_SESSION ),
           'industry_id' 			 => array ( 'industry_id', arr_vars_GET_SESSION ),
           );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$close_at = $con->SQLDate('Y-M-D', 'close_at');

$sql = "SELECT "
. $con->Concat("'<a id=\"'", "opp.opportunity_title",  "'\" href=\"one.php?opportunity_id='", "opp.opportunity_id", "'\">'", "opp.opportunity_title","'</a>'")
. " AS opportunity" . ",
  c.company_name AS 'company', u.username AS owner " . ",
  CASE
    WHEN (opp.size > 0) THEN opp.size
    ELSE 0
  END AS opportunity_size" . ",
  CASE
    WHEN (opp.size > 0) THEN ((opp.size * opp.probability) / 100)
    ELSE 0
  END AS weighted_size" . ",
  os.opportunity_status_pretty_name AS status " . ","
  . " $close_at AS close_date"  . ' ';


if ($opportunity_category_id > 0) {
    $from = "FROM companies c, opportunities opp, opportunity_statuses os, users u, entity_category_map ecm ";
} else {
    $from = "FROM companies c, opportunities opp, opportunity_statuses os, users u ";
}

//added by Nic to be able to create mail merge to contacts
$from.=",contacts cont ";

$where  = "where opp.opportunity_status_id = os.opportunity_status_id ";
$where .= "and opp.company_id = c.company_id ";
$where .= "and opp.user_id = u.user_id ";
$where .= "and opportunity_record_status = 'a' ";

//added by Nic to be able to create mail merge to contacts
$where.="and cont.contact_id=opp.contact_id ";

$criteria_count = 0;

if ($opportunity_category_id > 0) {
    $criteria_count++;
    $where .= " and ecm.on_what_table = 'opportunities' and ecm.on_what_id = opp.opportunity_id and ecm.category_id = $opportunity_category_id ";
}

if (strlen($opportunity_title) > 0) {
    $where .= " and opp.opportunity_title like " . $con->qstr('%' . $opportunity_title . '%', get_magic_quotes_gpc());
}

if (strlen($company_name) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr('%' . $company_name . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and opp.user_id = $user_id";
}

if (strlen($opportunity_status_id) > 0) {
    $criteria_count++;
    $where .= " and opp.opportunity_status_id = $opportunity_status_id";
}

if (strlen($industry_id) > 0) {
    $criteria_count++;
    $where .= " and c.industry_id = $industry_id";
}
if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
} else {
    $acl_id_list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    //print_r($acl_id_list);
    if ($acl_id_list) {
        if ($acl_id_list!==true) {
            $acl_id_list=implode(",",$acl_id_list);
            $where .= " and opp.opportunity_id IN ($acl_id_list) ";
        }
    } else { $where .= ' AND 1 = 2 '; }
}




$sql .= $from . $where;

$sql_recently_viewed = "select * from recent_items r, companies c, opportunities opp, opportunity_statuses os
where r.user_id = $session_user_id
and r.on_what_table = 'opportunities'
and r.recent_action = ''
and c.company_id = opp.company_id
and opp.opportunity_status_id = os.opportunity_status_id
and r.on_what_id = opp.opportunity_id
and opportunity_record_status = 'a'
order by r.recent_item_timestamp desc";

$recently_viewed_table_rows = '';
$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= "<td class=widget_content><a href='$http_site_root/opportunities/one.php?opportunity_id=" . $rst->fields['opportunity_id'] . "'>" . $rst->fields['opportunity_title'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['company_code'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['opportunity_status_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['close_at']) . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>' . _("No recently viewed opportunities") . '</td></tr>';
}

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

$sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status = 'a' order by opportunity_status_id";
$rst = $con->execute($sql2);
$opportunity_status_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, true);
$rst->close();

$sql2 = "select category_pretty_name, c.category_id
from categories c, category_scopes cs, category_category_scope_map ccsm
where c.category_id = ccsm.category_id
and cs.on_what_table =  'opportunities'
and ccsm.category_scope_id = cs.category_scope_id
and category_record_status =  'a'
order by category_pretty_name";
$rst = $con->execute($sql2);
$opportunity_category_menu = $rst->getmenu2('opportunity_category_id', $opportunity_category_id, true);
$rst->close();

if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'opportunities', '', 4);
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

$page_title = _("Opportunities");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=some.php method=post name="OpportunityData">
        <input type=hidden name=scope value="opportunities">
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=opportunities_next_page value="<?php  echo $opportunities_next_page; ?>">
        <input type=hidden name=resort value="0">
        <input type=hidden name=current_sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=sort_column value="<?php  echo $sort_column; ?>">
        <input type=hidden name=current_sort_order value="<?php  echo $sort_order; ?>">
        <input type=hidden name=sort_order value="<?php  echo $sort_order; ?>">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=3><?php echo _("Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Opportunity Name"); ?></td>
                <td colspan=2 class=widget_label><?php echo _("Company"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="opportunity_title" size=20 value="<?php  echo $opportunity_title; ?>"></td>
                <td colspan=2 class=widget_content_form_element><input type=text name="company_name" size=20 value="<?php  echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Category"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_category_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="<?php echo _("Search"); ?>"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>"> </td>
            </tr>
        </table>
<?php

$_SESSION['search_sql']=$sql;

$owner_query_list = "select " . $con->Concat("u.username", "' ('", "count(u.user_id)", "')'") . ", u.user_id $from $where group by u.username order by u.username";

$owner_query_select = $sql . 'AND u.user_id = XXX-value-XXX';


$status_query_list = "select " . $con->Concat("os.opportunity_status_pretty_name", "' ('", "count(os.opportunity_status_id)", "')'") . ", os.opportunity_status_id $from $where group by os.opportunity_status_id order by os.opportunity_status_pretty_name";

$status_query_select = $sql . 'AND os.opportunity_status_id = XXX-value-XXX';

$columns = array();
$columns[] = array('name' => _('Opportunity'), 'index_sql' => 'opportunity');
$columns[] = array('name' => _('Company'), 'index_sql' => 'company');
$columns[] = array('name' => _('Owner'), 'index_sql' => 'owner', 'group_query_list' => $owner_query_list, 'group_query_select' => $owner_query_select);
$columns[] = array('name' => _('Opportunity Size'), 'index_sql' => 'opportunity_size', 'subtotal' => true, 'css_classname' => 'right');
$columns[] = array('name' => _('Weighted Size'), 'index_sql' => 'weighted_size', 'subtotal' => true, 'css_classname' => 'right');
$columns[] = array('name' => _('Status'), 'index_sql' => 'status', 'group_query_list' => $status_query_list, 'group_query_select' => $status_query_select);
$columns[] = array('name' => _('Close Date'), 'index_sql' => 'close_date');



// selects the columns this user is interested in
$default_columns =  array('opportunity', 'company', 'owner', 'opportunity_size', 'weighted_size', 'status', 'close_date');

$pager_columns = new Pager_Columns('OpportunityPager', $columns, $default_columns, 'OpportunityData');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');



$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            <input type=button class=button onclick=\"javascript: exportIt();\" value="._("Export").">
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=\""._("Mail Merge")."\"></td></tr>";
 
echo $pager_columns_selects;



$pager = new GUP_Pager($con, $sql, null,  _('Search Results'), 'OpportunityData', 'OpportunityPager', $columns);
$pager->AddEndRows($endrows);
$pager->Render($system_rows_per_page);
$con->close();

?>

        </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- recently viewed companies //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Opportunity"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
                <td class=widget_label><?php echo _("Close Date"); ?></td>
            </tr>
            <?php  echo $recently_viewed_table_rows; ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].opportunity_title.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "../email/email.php?scope=opportunities";
    document.forms[0].submit();
}

function exportIt() {
    document.forms[0].action = "export.php";
    document.forms[0].submit();
    // reset the form so that post-export searches work
    document.forms[0].action = "some.php";
}

function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}

//-->
</script>


<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.44  2005/03/15 21:49:48  daturaarutad
 * fixed Mail Merge
 *
 * Revision 1.43  2005/03/11 21:49:18  daturaarutad
 * added industry_id as a search criteria for linking with reports/graphs
 *
 * Revision 1.42  2005/03/01 21:56:55  daturaarutad
 * set the css_classname for right-align on numerics in pager
 *
 * Revision 1.41  2005/02/16 16:15:27  daturaarutad
 * fixed a bug $list should have been $acl_id_list, removed some commented out lines
 *
 * Revision 1.40  2005/02/14 21:48:17  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.39  2005/02/11 21:31:49  daturaarutad
 * removed (hopefully) the last of the localization in the sql queries
 *
 * Revision 1.38  2005/02/10 03:58:49  daturaarutad
 * Updated to use the new Grand Unified Pager, which allows grouping and has a tidier UI
 *
 * Revision 1.37  2005/02/10 01:44:31  braverock
 * - fix malformed $_SESSION declaration to use single quoted array index
 *   fixes "script possibly relies on a session side-effect" error
 *
 * Revision 1.36  2005/02/09 22:24:37  braverock
 * - localized pager column headers
 * - de-localized AS clauses in SQL
 *
 * Revision 1.35  2005/01/31 01:10:35  daturaarutad
 * add subtotal for opportunity size and weighted size columns
 *
 * Revision 1.34  2005/01/25 04:12:04  daturaarutad
 * updated to use new XRMS_Pager and Pager_Columns to implement selectable columns
 *
 * Revision 1.33  2005/01/13 18:55:43  vanmer
 * - ACL restriction on list when searching
 *
 * Revision 1.32  2004/12/30 19:12:28  braverock
 * - localize strings
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.31  2004/12/02 03:14:05  vanmer
 * - added space to main opportunity query to fix pager page display bug
 *
 * Revision 1.30  2004/11/21 17:29:43  braverock
 * - fix select to use $con->qstr so that translations work
 *
 * Revision 1.29  2004/08/19 13:14:05  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * Revision 1.28  2004/08/18 00:06:17  niclowe
 * Fixed bug 941839 - Mail Merge not working
 *
 * Revision 1.27  2004/07/29 10:04:20  cpsource
 * - Rid some undefines.
 *
 * Revision 1.26  2004/07/28 20:42:27  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.25  2004/07/20 19:43:02  introspectshun
 * - Localized SQL aliias strings for i18n/translation support
 *
 * Revision 1.24  2004/07/20 19:38:31  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.23  2004/07/17 11:56:36  cpsource
 * - Null out call arguments to session_check()
 *
 * Revision 1.22  2004/07/15 17:15:51  introspectshun
 * - Fixed errant CVS Commit. Updated s-t's code to reflect recent HTML tweaks and removed empty column in search table.
 *
 * Revision 1.21  2004/07/15 13:49:54  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.20  2004/07/15 13:15:58  cpsource
 * - Add arr_vars sub-system
 *   Get rid of misc undefined variable usages.
 *
 * Revision 1.19  2004/07/14 20:19:50  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.18  2004/07/14 02:06:30  s-t
 * cvs commit opportunities.php
 *
 * Revision 1.17  2004/07/09 18:46:17  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.16  2004/06/24 20:00:21  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.15  2004/06/16 20:44:07  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.14  2004/06/14 20:56:04  gpowers
 * - removed CAST from SELECT statement
 *   - it is not compatible across databases
 *
 * Revision 1.13  2004/06/14 17:41:36  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, Concat and Date functions.
 *
 * Revision 1.12  2004/05/10 13:08:36  maulani
 * - Add level to audit trail
 * - Correct audit trail entry text
 *
 * Revision 1.11  2004/04/15 22:04:39  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.10  2004/04/08 17:13:06  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 * Revision 1.9  2004/03/15 16:53:02  braverock
 * - cleaned up sql formatting
 *
 * Revision 1.8  2004/03/04 00:16:24  maulani
 *  - correct phpdoc entries
 *
 * Revision 1.7  2004/03/01 16:38:32  maulani
 * added phpdoc
 *
 */
?>
