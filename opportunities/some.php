<?php
/**
 * opportunities/some.php - This file provides the opportunities search page
 *
 *
 *
 * $Id: some.php,v 1.23 2004/07/17 11:56:36 cpsource Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

//set target and see if we are logged in
//$tmp = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( '' );

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
		   'sort_column'             => array ( 'opportunities_sort_column', arr_vars_SESSION ),
		   'current_sort_column'     => array ( 'opportunities_current_sort_column', arr_vars_SESSION ),
		   'sort_order'              => array ( 'opportunities_sort_order', arr_vars_SESSION ),
		   'current_sort_order'      => array ( 'opportunities_current_sort_order', arr_vars_SESSION ),
		   'opportunity_title'       => array ( 'opportunities_opportunity_title', arr_vars_SESSION ),
		   'company_name'            => array ( 'opportunities_company_name', arr_vars_GET_SESSION ),
		   'user_id'                 => array ( 'opportunities_user_id', arr_vars_SESSION ),
		   'opportunity_status_id'   => array ( 'opportunities_opportunity_status_id', arr_vars_SESSION ),
		   'opportunity_category_id' => array ( 'opportunities_opportunity_category_id', arr_vars_SESSION ),
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

$ascending_order_image = ' <img border=0 height=10 width=10 src="../img/asc.gif" alt="">';
$descending_order_image = ' <img border=0 height=10 width=10 src="../img/desc.gif" alt="">';
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

// set all session variables
arr_vars_session_set ( $arr_vars );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$close_at = $con->SQLDate('Y-M-D', 'close_at');

$sql = "SELECT " .
  $con->Concat("'<a href=\"one.php?opportunity_id='", "opp.opportunity_id", "'\">'", "opp.opportunity_title","'</a>'") . " AS 'Opportunity',
  c.company_name AS 'Company', u.username AS 'Owner',
  CASE
    WHEN (opp.size > 0) THEN opp.size
    ELSE 0
  END AS 'Opportunity Size',
  CASE
    WHEN (opp.size > 0) THEN ((opp.size * opp.probability) / 100)
    ELSE 0
  END AS 'Weighted Size',
  os.opportunity_status_pretty_name AS 'Status', $close_at AS 'Close Date'
";

if ($opportunity_category_id > 0) {
    $from = "from companies c, opportunities opp, opportunity_statuses os, users u, entity_category_map ecm ";
} else {
    $from = "from companies c, opportunities opp, opportunity_statuses os, users u ";
}

$where  = "where opp.opportunity_status_id = os.opportunity_status_id ";
$where .= "and opp.company_id = c.company_id ";
$where .= "and opp.user_id = u.user_id ";
$where .= "and opportunity_record_status = 'a'";

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

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
}

if ($sort_column == 1) {
    $order_by = "opportunity_title";
} else {
    $order_by = $sort_column;
}

$order_by .= " $sort_order";
$sql .= $from . $where . " order by $order_by";


$sql_recently_viewed = "select * from recent_items r, companies c, opportunities opp, opportunity_statuses os
where r.user_id = $session_user_id
and r.on_what_table = 'opportunities'
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
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=5>No recently viewed opportunities</td></tr>';
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

$page_title = 'Opportunities';
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=some.php method=post>
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
                <td class=widget_header colspan=3>Search Criteria</td>
            </tr>
            <tr>
                <td class=widget_label>Opportunity Name</td>
                <td colspan=2 class=widget_label>Company</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="opportunity_title" size=20 value="<?php  echo $opportunity_title; ?>"></td>
                <td colspan=2 class=widget_content_form_element><input type=text name="company_name" size=20 value="<?php  echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Category</td>
                <td class=widget_label>Status</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_category_menu; ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="Search"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="Clear Search"> <?php if ($company_count > 0) {print "<input class=button type=button onclick='javascript: bulkEmail()' value='Bulk E-Mail'>";}; ?> </td>
            </tr>
        </table>
        </form>
<?php

$pager = new ADODB_Pager($con, $sql, 'opportunities', false, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- recently viewed companies //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>Recently Viewed</td>
            </tr>
            <tr>
                <td class=widget_label>Opportunity</td>
                <td class=widget_label>Company</td>
                <td class=widget_label>Status</td>
                <td class=widget_label>Close Date</td>
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
    document.forms[0].action = "../email/email.php";
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


<?php

end_page();

/**
 * $Log: some.php,v $
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
