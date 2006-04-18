<?php
/**
 * opportunities/some.php - This file provides the opportunities search page
 *
 *
 *
 * $Id: some.php,v 1.67 2006/04/18 14:44:39 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-saved-search.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

require_once('opportunities-pager-functions.php');


$con = get_xrms_dbconnection();
// $con->debug = 1;

$on_what_table='opportunities';
$session_user_id = session_check();

getGlobalVar($browse,'browse');
getGlobalVar($saved_id, 'saved_id');
getGlobalVar($saved_title, 'saved_title');
getGlobalVar($group_item, 'group_item');
getGlobalVar($delete_saved, 'delete_saved');


/*********** SAVED SEARCH BEGIN **********************/
load_saved_search_vars($con, $on_what_table, $saved_id, $delete_saved);

/*********** SAVED SEARCH END **********************/


// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
           'opportunity_title'       => array ( 'opportunities_opportunity_title', arr_vars_SESSION ),
           'company_name'            => array ( 'opportunities_company_name', arr_vars_GET_SESSION ),
           'user_id'                 => array ( 'opportunities_user_id', arr_vars_SESSION ),
           'opportunity_type_id'     => array ( 'opportunity_type_id', arr_vars_SESSION ),
           'opportunity_status_id'   => array ( 'opportunities_opportunity_status_id', arr_vars_GET_SESSION ),
           'opportunity_category_id' => array ( 'opportunities_opportunity_category_id', arr_vars_SESSION ),
           'campaign_id'             => array ( 'opportunities_campaign_id', arr_vars_SESSION ) ,
           'industry_id'             => array ( 'industry_id', arr_vars_GET_SESSION ),
           'before_after'            => array ( 'before_after', arr_vars_GET_SESSION ),
           'search_date'             => array ( 'search_date', arr_vars_GET_SESSION ),
           'hide_closed'             => array ( 'hide_closed', arr_vars_SESSION ),
           );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );

$close_at = $con->SQLDate('Y-M-D', 'close_at');

$is_overdue_field="(CASE WHEN (os.status_open_indicator='o') AND (close_at < " . $con->DBTimeStamp(time()) . ") THEN 1 ELSE 0 END)";

// fin Modif

$sql = "SELECT $is_overdue_field AS is_overdue,"
. $con->Concat("'<a id=\"'", "opp.opportunity_title",  "'\" href=\"one.php?opportunity_id='", "opp.opportunity_id", "'\">'", "opp.opportunity_title","'</a>'")
. " AS opportunity" . ",
  c.company_name AS 'company',crst.crm_status_pretty_name as 'crm_status', u.username AS owner " . ",
  ot.opportunity_type_pretty_name AS type,
  CASE
    WHEN (opp.size > 0) THEN opp.size
    ELSE 0
  END AS opportunity_size" . ",
  CASE
    WHEN (opp.size > 0) THEN ((opp.size * opp.probability) / 100)
    ELSE 0
  END AS weighted_size" . ",
  os.opportunity_status_pretty_name AS status " . ", opp.probability as 'prob', "
  . " $close_at AS close_date, close_at, opp.opportunity_title"  . ' ';


if ($opportunity_category_id > 0) {
    $from = "FROM companies c, opportunities opp, opportunity_statuses os, opportunity_types ot, crm_statuses crst,users u, entity_category_map ecm ";
} else {
    $from = "FROM companies c, opportunities opp, opportunity_statuses os, opportunity_types ot, users u, crm_statuses crst ";
}

//added by Nic to be able to create mail merge to contacts
$from.=",contacts cont ";

$where  = "where opp.opportunity_status_id = os.opportunity_status_id ";
$where .= "and opp.opportunity_type_id = ot.opportunity_type_id ";
$where .= "and opp.company_id = c.company_id ";
$where .= "and opp.user_id = u.user_id ";
$where .= "and c.crm_status_id = crst.crm_status_id ";
$where .= "and opportunity_record_status = 'a' ";

// Begin Add JNH
if ( $hide_closed )
{
    $where .= " AND os.status_open_indicator='o' ";
}
// end Add JNH

if($campaign_id) {
    $where .= " AND opp.campaign_id = '$campaign_id'";
}

//added by Nic to be able to create mail merge to contacts
$where.=" and cont.contact_id=opp.contact_id ";

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
    $where .= " and c.company_name like " . $con->qstr(company_search_string($company_name), get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $where .= " and opp.user_id = $user_id";
}

if (strlen($opportunity_status_id) > 0) {
    $criteria_count++;
    $where .= " and opp.opportunity_status_id = $opportunity_status_id ";
}

if (strlen($opportunity_type_id) > 0) {
    $criteria_count++;
    $where .= " and ot.opportunity_type_id = $opportunity_type_id ";
}
if (strlen($industry_id) > 0) {
    $criteria_count++;
    $where .= " and c.industry_id = $industry_id";
}

    if ($search_date) {
        $field='close_at';
        $day_diff = round((strtotime($search_date) - strtotime(date('Y-m-d', time()))) / 86400);

        if (!$before_after) {
            // before
            $offset_end = $con->OffsetDate($day_diff);
            $offset_sql .= " and opp.$field < $offset_end";
        } elseif ($before_after === 'after') {
            // after
            $offset_start = $con->OffsetDate($day_diff);
            $offset_sql .= " and opp.$field > $offset_start";
        } elseif ($before_after === 'on') {
            // same query for list and calendar views
            $offset_start = $con->OffsetDate($day_diff);
            $offset_end = $con->OffsetDate($day_diff+1);
            // midnight to midnight
            $offset_sql .= " and opp.$field > $offset_start and opp.$field < $offset_end";
        }

        $where .= $offset_sql;
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

//echo $sql;

/******* SAVED SEARCH BEGINS *****/
    $saved_data = $_POST;
    $saved_data["sql"] = $sql;
    $saved_data["day_diff"] = $day_diff;

    if(!$saved_title) {
        $saved_title = "Current";
        $group_item = 0;
    }
    if ($saved_title OR $browse) {
//        echo "adding saved search";
        $saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);
//        echo "$saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);";
    }

//get saved searches
$rst=get_saved_search_item($con, $on_what_table, $session_user_id, false,  false, true,'search', true);
if( $rst AND $rst->RowCount() ) {
    $saved_menu = $rst->getmenu2('saved_id', 0, true) . ' <input name="delete_saved" type=submit class=button value="' . _("Delete") . '">';
} else {
  $saved_menu = '';
}

/********** SAVED SEARCH ENDS ****/


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

$sql2 = "select opportunity_type_pretty_name, opportunity_type_id from opportunity_types where opportunity_type_record_status = 'a' order by opportunity_type_pretty_name";
$rst = $con->execute($sql2);
if(!$rst) {
	db_error_handler($con, $sql2);
}
$opportunity_type_menu = $rst->getmenu2('opportunity_type_id', $opportunity_type_id, true);
$rst->close();

//get campaign titles
$sql2 = "SELECT campaign_title, campaign_id
         FROM campaigns c, campaign_statuses cs
         WHERE c.campaign_status_id = cs.campaign_status_id
           AND c.campaign_record_status = 'a'
           AND campaign_status_record_status = 'a'
           AND status_open_indicator = 'o'
           ";

$rst = $con->execute($sql2);
if (!$rst) {
    db_error_handler($con, $sql2);
} elseif ($rst->rowcount()) {
    $campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=4>' . _("No recently viewed opportunities") . '</td></tr>';
}

$user_menu = get_user_menu($con, $user_id, true);

// Ajout JNH classement par le sort order
$sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status = 'a' order by sort_order";
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

        <form action=some.php class="print" method=post name="OpportunityData">
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
                <td class=widget_header colspan=4><?php echo _("Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Opportunity Name"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Campaigns"); ?></td>
                <td class=widget_label colspan=2><?php echo _("Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name="opportunity_title" size=20 value="<?php  echo $opportunity_title; ?>"></td>
                <td class=widget_content_form_element><input type=text name="company_name" size=20 value="<?php  echo $company_name; ?>"></td>
                <td class=widget_content_form_element><?php echo $campaign_menu; ?></td>
                <td class=widget_content_form_element colspan=2><?php  echo $opportunity_type_menu; ?></td>
            </tr>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
                <td class=widget_label><?php echo _("Category"); ?></td>
                <td class=widget_label><?php echo _("Close Date"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
                <td class=widget_content_form_element>
                    <?php  echo $opportunity_status_menu; ?>
                    <input name="hide_closed" type=checkbox
                    <?php
                        if ($hide_closed) {
                            echo "checked=\"true\"";
                        }
                        echo ">" . _("Hide Closed");
                    ?>
                </td>
                <td class=widget_content_form_element><?php  echo $opportunity_category_menu; ?></td>
                <td class=widget_content_form_element>
                    <select name="before_after">
                        <option value=""<?php if (!$before_after) { print " selected"; } ?>><?php echo _("Before"); ?></option>
                        <option value="after"<?php if ($before_after == "after") { print " selected"; } ?>><?php echo _("After"); ?></option>
                        <option value="on"<?php if ($before_after == "on") { print " selected"; } ?>><?php echo _("On"); ?></option>
                    </select>
                    <input type=text ID="f_date_d" name="search_date" size=12 value="<?php  echo $search_date; ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif" alt="">
                </td>
            </tr>
            <tr>
                <td class=widget_label colspan="2"><?php echo _("Saved Searches"); ?></td>
                <td class=widget_label colspan="2"><?php echo _("Search Title"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan="2">
                    <?php echo ($saved_menu) ? $saved_menu : _("No Saved Searches"); ?>
                </td>
                <td class=widget_content_form_element colspan="2">
                    <input type=text name="saved_title" size=24>
                    <?php
                        if(check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
                            echo _("Add to Everyone").' <input type=checkbox name="group_item" value=1>';
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=6><input class=button type=submit value="<?php echo _("Search"); ?>"> <input class=button type=button onclick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>"> </td>
            </tr>
        </table>
<?php

$_SESSION['search_sql']=$sql;

$owner_query_list = "select " . $con->Concat("u.username", "' ('", "count(u.user_id)", "')'") . ", u.user_id $from $where group by u.username order by u.username";
$owner_query_select = $sql . 'AND u.user_id = XXX-value-XXX';

// add jNH

//   $is_overdue_field="(CASE WHEN (activity_status = 'o') AND (a.ends_at < " . $con->DBTimeStamp(time()) . ") THEN 1 ELSE 0 END)";
//   $is_overdue_text_field="(CASE WHEN (activity_status = 'o') AND (a.ends_at < " . $con->DBTimeStamp(time()) . ") THEN ".$con->qstr(_("Yes"))." ELSE " . $con->qstr("")." END)";

//   $overdue_query_list = "select DISTINCT $is_overdue_text_field AS is_overdue, $is_overdue_field FROM $from_list $joins $where $group_by";
 //  $overdue_query_select = "$select FROM $from_list $joins $where $group_by HAVING $is_overdue_field = XXX-value-XXX";


// end Add JNH

$status_query_list = "select " . $con->Concat("os.opportunity_status_pretty_name", "' ('", "count(os.opportunity_status_id)", "')'") . ", os.opportunity_status_id $from $where group by os.opportunity_status_id order by os.sort_order";
$status_query_select = $sql . ' AND os.opportunity_status_id = XXX-value-XXX';

$type_query_list = "select " . $con->Concat("ot.opportunity_type_pretty_name", "' ('", "count(ot.opportunity_type_id)", "')'") . ", ot.opportunity_type_id $from $where group by ot.opportunity_type_id order by ot.opportunity_type_pretty_name";
$type_query_select = $sql . ' AND ot.opportunity_type_id = XXX-value-XXX';

$company_query_list = "select " . $con->Concat("c.company_name", "' ('", "count(c.company_id)", "')'") . ", c.company_id $from $where group by c.company_id order by c.company_name";
$company_query_select = $sql . 'AND c.company_id = XXX-value-XXX';

$columns = array();
// Add JNH
$columns[] = array('name' => _("Overdue"), 'index_sql' => 'is_overdue');
// End Add Jnh
$columns[] = array('name' => _('Opportunity'), 'index_sql' => 'opportunity', 'sql_sort_column' => 'opportunity_title', 'type' => 'url');
$columns[] = array('name' => _('Company'), 'index_sql' => 'company', 'group_query_list' => $company_query_list, 'group_query_select' => $company_query_select);
$columns[] = array('name' => _('CRM Status'), 'index_sql' => 'crm_status');
$columns[] = array('name' => _('Owner'), 'index_sql' => 'owner', 'group_query_list' => $owner_query_list, 'group_query_select' => $owner_query_select);
$columns[] = array('name' => _('Opportunity Size'), 'index_sql' => 'opportunity_size', 'subtotal' => true, 'css_classname' => 'right');
$columns[] = array('name' => _('Probability'), 'index_sql' => 'prob', 'css_classname' => 'right');
$columns[] = array('name' => _('Weighted Size'), 'index_sql' => 'weighted_size', 'subtotal' => true, 'css_classname' => 'right');
//$columns[] = array('name' => _('Type'), 'index_sql' => 'type', 'group_query_list' => $type_query_list, 'group_query_select' => $type_query_select);
$columns[] = array('name' => _('Status'), 'index_sql' => 'status', 'group_query_list' => $status_query_list, 'group_query_select' => $status_query_select);
//$columns[] = array('name' => _('Close Date'), 'index_sql' => 'close_date', 'sql_sort_column' => 'close_at');
//JNH
$columns[] = array('name' => _('Close Date'), 'index_sql' => 'close_date', 'sql_sort_column' => 'close_at', 'default_sort' => 'asc');
//JNH



// selects the columns this user is interested in
// no reason to set this if you don't want all by default
//$default_columns = null;
// Add JNH
$default_columns =  array('opportunity', 'company', 'owner','opportunity_size', 'prob', 'weighted_size', 'status', 'close_date');
// End Add JNH
$pager_columns = new Pager_Columns('OpportunityPager', $columns, $default_columns, 'OpportunityData');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

echo $pager_columns_selects;



$pager = new GUP_Pager($con, $sql, 'GetOpportunityPagerData',  _('Search Results'), 'OpportunityData', 'OpportunityPager', $columns);

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

        <!-- recently viewed companies //-->
    <div id="Recent" class="noprint">
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

Calendar.setup({
        inputField     :    "f_date_d",      // id of the input field
        ifFormat       :    "%Y-%m-%d",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "f_trigger_d",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });

//-->
</script>


<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.67  2006/04/18 14:44:39  braverock
 * - remove unnecessary admin check from 'hide closed' option
 *
 * Revision 1.66  2006/04/11 01:57:46  vanmer
 * - added marking of overdue opportunites in red, like activities
 * - added extra columns and groupability on the opportunites pager
 * - added ability to hide closed opportunities
 * - Thanks to Jean-Noël HAYART for providing this patch
 *
 * Revision 1.65  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.64  2005/12/17 21:27:59  vanmer
 * - changed SQL statement to not quote fieldname
 * - patch provided by kennyholden
 *
 * Revision 1.63  2005/12/06 23:25:10  vanmer
 * - added opportunity type name to list of statuses
 * - changed sort order of statuses to reflect table sort_order column
 * - may not work in SQL server
 *
 * Revision 1.62  2005/09/23 21:07:27  daturaarutad
 * add db_error_handler()
 *
 * Revision 1.61  2005/08/28 18:09:10  braverock
 * - remove trailing whitespace
 *
 * Revision 1.60  2005/08/28 18:08:18  braverock
 * - fix colspan for recently viewed items
 *
 * Revision 1.59  2005/08/05 21:53:54  vanmer
 * - changed to use centralized company search
 *
 * Revision 1.58  2005/08/05 01:41:26  vanmer
 * - added saved search functionality to list of opportunities
 *
 * Revision 1.57  2005/08/04 18:05:46  vanmer
 * - added search on close date to opportunities search
 * - moved status and category search around to allow better formatting of page
 *
 * Revision 1.56  2005/07/28 17:15:04  vanmer
 * - added grouping on company column in opportunity results pager
 *
 * Revision 1.55  2005/07/06 23:04:16  braverock
 * - make opportunity type a groupable column
 * - order statuses list by sort_order
 *
 * Revision 1.54  2005/07/06 22:50:31  braverock
 * - add opportunity types
 *
 * Revision 1.53  2005/05/13 13:47:09  braverock
 * - make Recently Viewed Items not print
 *
 * Revision 1.52  2005/04/29 17:57:21  daturaarutad
 * fixed printing of form/search results
 *
 * Revision 1.51  2005/04/29 16:30:38  daturaarutad
 * updated to use GUP_Pager for export
 *
 * Revision 1.50  2005/04/29 14:39:41  braverock
 * - fixed SQL spacing that was causing opportunity status query to fail with run-on...
 *
 * Revision 1.49  2005/04/19 14:10:59  daturaarutad
 * added sql_sort_column => close_at for opportunities pager
 *
 * Revision 1.48  2005/04/01 22:06:18  ycreddy
 * Modified the INNER JOIN to a portable syntax
 *
 * Revision 1.47  2005/03/30 19:47:51  gpowers
 * - added Campaigns to search/display (patch by glasshut@sf)
 *
 * Revision 1.46  2005/03/21 13:40:57  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.45  2005/03/15 22:29:34  daturaarutad
 * pager tuning sql_sort_column
 *
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