<?php
/**
 * Search for and Display Campaigns
 *
 * This is the main interface for locating Campaigns in XRMS
 *
 * $Id: some.php,v 1.34 2006/01/02 22:41:51 vanmer Exp $
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

$con = get_xrms_dbconnection();
// $con->debug = 1;

$on_what_table='campaigns';
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
                   'campaign_title'       => array ( 'campaigns_campaign_title', arr_vars_SESSION),
                   'user_id'              => array ( 'campaigns_user_id', arr_vars_SESSION),
                   'campaign_type_id'     => array ( 'campaigns_campaign_type_id', arr_vars_SESSION),
                   'campaign_status_id'   => array ( 'campaigns_campaign_status_id', arr_vars_SESSION),
                   'campaign_category_id' => array ( 'campaigns_campaign_category_id', arr_vars_SESSION),
                   'media'                => array ( 'campaigns_media', arr_vars_SESSION),
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );


$starts_at = $con->SQLDate('Y-m-d', 'cam.starts_at');
$ends_at = $con->SQLDate('Y-m-d', 'cam.ends_at');

$sql = "SELECT " .
$con->Concat($con->qstr('<a id="'), 'cam.campaign_title', $con->qstr('" href="one.php?campaign_id='), "cam.campaign_id",  $con->qstr('">'), "cam.campaign_title", $con->qstr('</a>')) . " AS campaign, " .
"camt.campaign_type_pretty_name AS type, cams.campaign_status_pretty_name AS status, u.username AS owner, $starts_at AS starts, $ends_at AS ends ";

if ($campaign_category_id > 0) {
    $from = "from campaigns cam, campaign_types camt, campaign_statuses cams, users u, entity_category_map ecm ";
} else {
    $from = "from campaigns cam, campaign_types camt, campaign_statuses cams, users u ";
}

$where  = "where cam.campaign_type_id = camt.campaign_type_id ";
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
    $where .= " and cam.campaign_title like " . $con->qstr('%' . $campaign_title . '%', get_magic_quotes_gpc());
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
} else {
    $list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    //print_r($list);
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $where .= " and cam.campaign_id IN ($list) ";
        }
    } else { $where .= ' AND 1 = 2 '; }
}

$sql .= $from . $where;

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


$sql_recently_viewed = "select * from recent_items r, campaigns cam, campaign_types camt, campaign_statuses cams
where r.user_id = $session_user_id
and cam.campaign_type_id = camt.campaign_type_id
and cam.campaign_status_id = cams.campaign_status_id
and r.on_what_table = 'campaigns'
and r.recent_action = ''
and r.on_what_id = cam.campaign_id
and campaign_record_status = 'a'
order by r.recent_item_timestamp desc";

$recently_viewed_table_rows = '';

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?campaign_id=' . $rst->fields['campaign_id'] . '">' . $rst->fields['campaign_title'] . '</a></td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['campaign_type_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '<td class=widget_content>' . $rst->fields['campaign_status_pretty_name'] . '</td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = '<tr><td class=widget_content colspan=3>' . _("No recently viewed campaigns") . '</td></tr>';
}

$user_menu = get_user_menu($con, $user_id, true);

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
    add_audit_item($con, $session_user_id, 'searched', 'campaigns', '', 4);
}

$page_title = _("Campaigns");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

    <form action=some.php method=post class="print" name="CampaignForm">
    <input type=hidden name=use_post_vars value=1>
    <input type=hidden name=campaigns_next_page value="<?php  echo $campaigns_next_page ?>">
    <input type=hidden name=resort value="0">
    <input type=hidden name=current_sort_column value="<?php  echo $sort_column ?>">
    <input type=hidden name=sort_column value="<?php  echo $sort_column ?>">
    <input type=hidden name=current_sort_order value="<?php  echo $sort_order ?>">
    <input type=hidden name=sort_order value="<?php  echo $sort_order ?>">
    <table class=widget cellspacing=1 width="100%">
        <tr>
            <td class=widget_header colspan=3><?php echo _("Search Criteria"); ?></td>
        </tr>
        <tr>
            <td width="36%" class=widget_label><?php echo _("Campaign Name"); ?></td>
            <td width="35%" class=widget_label><?php echo _("Type"); ?></td>
            <td width="29%" class=widget_label><?php echo _("Owner"); ?></td>
        </tr>
        <tr>
            <td class=widget_content_form_element><input type=text name="campaign_title" size=20 value="<?php  echo $campaign_title ?>"></td>
            <td class=widget_content_form_element>
                <?php  echo $campaign_type_menu ?>
            </td>
            <td class=widget_content_form_element>
                <?php  echo $user_menu ?>
            </td>
        </tr>
        <tr>
            <td class=widget_content_form_element><?php echo _("Category"); ?></td>
            <td class=widget_content_form_element><?php echo _("Media"); ?></td>
            <td class=widget_content_form_element><?php echo _("Status"); ?></td>
        </tr>
        <tr>
            <td width="33%" class=widget_content_form_element>
                <?php  echo $campaign_category_menu ?>
            </td>
            <td width="33%" class=widget_content_form_element>
                <input type=text name=media size=12 value="<?php  echo $media ?>">
            </td>
            <td width="33%" class=widget_content_form_element>
                <?php  echo $campaign_status_menu ?>
            </td>
        </tr>
        <tr>
            <td class=widget_label colspan="2"><?php echo _("Saved Searches"); ?></td>
            <td class=widget_label colspan="2"><?php echo _("Search Title"); ?></td>
        </tr>
        <tr>
            <td class=widget_content_form_element colspan="1">
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
            <td class=widget_content_form_element colspan=3><input name="submit_form" type=submit class=button value="<?php echo _("Search"); ?>">
                <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
            </td>
        </tr>
    </table>

<?php

//Campaign  Type    Status  Owner   Starts  Ends

$columns = array();
$columns[] = array('name' => _('Campaign'), 'index_sql' => 'campaign', 'type' => 'url');
$columns[] = array('name' => _('Type'), 'index_sql' => 'type');
$columns[] = array('name' => _('Status'), 'index_sql' => 'status');
$columns[] = array('name' => _('Owner'), 'index_sql' => 'owner');
$columns[] = array('name' => _('Starts'), 'index_sql' => 'starts');
$columns[] = array('name' => _('Ends'), 'index_sql' => 'ends');


// selects the columns this user is interested in
// no reason to set this if you don't want all by default
$default_columns = null;
// $default_columns =  array('campaign','type','status','owner','starts','ends');

$pager_columns = new Pager_Columns('CampaignPager', $columns, $default_columns, 'CampaignForm');
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns('default');

echo $pager_columns_selects;


$pager = new GUP_Pager($con, $sql, null, _('Search Results'), 'CampaignForm', 'CampaignPager', $columns, false);

$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            " . $pager->GetAndUseExportButton() .  "
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=\""._("Mail Merge")."\"></td></tr>";

$pager->AddEndRows($endrows);
$pager->Render($system_rows_per_page);

?>

    </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- new campaign //-->
        <div class="noprint">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Options"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><a href="new.php"><?php echo _("Add New Campaign"); ?></a></td>
            </tr>
        </table>
        </div>

        <!-- recently viewed support items //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=3><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Campaign"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
            </tr>
            <?php  echo $recently_viewed_table_rows; ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].campaign_title.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = <?php echo '"'.$http_site_root.'/email/index.php"'; ?>;
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

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.34  2006/01/02 22:41:51  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.33  2005/08/28 16:15:35  braverock
 * - fix incorrect  colspan entries
 *
 * Revision 1.32  2005/08/05 01:54:06  vanmer
 * - added saved search capabilities to campaigns
 *
 * Revision 1.31  2005/04/29 17:51:43  daturaarutad
 * fixed printing of form/search results
 *
 * Revision 1.30  2005/03/21 13:40:53  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.29  2005/03/15 22:36:46  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.28  2005/03/02 20:48:59  daturaarutad
 * forgot to remove sorting variables from arr_list
 *
 * Revision 1.27  2005/03/02 19:36:52  daturaarutad
 * updated to use the GUP_Pager class
 *
 * Revision 1.26  2005/02/14 21:42:11  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.25  2005/02/10 01:21:14  braverock
 * fix Bulk Email button to use $http_site_root
 *
 * Revision 1.24  2005/01/13 18:11:27  vanmer
 * - Basic ACL changes to allow view functionality to be restricted
 *
 * Revision 1.23  2004/10/22 20:51:38  introspectshun
 * - Updated date format for app consistency
 * - 'Recently Viewed' works again
 *
 * Revision 1.22  2004/08/19 13:14:04  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * Revision 1.21  2004/07/28 20:43:25  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.20  2004/07/25 14:29:34  johnfawcett
 * - corrected gettext call
 *
 * Revision 1.19  2004/07/23 03:59:00  braverock
 * - resolve JS error when button is named 'submit'
 *
 * Revision 1.18  2004/07/19 17:19:33  cpsource
 * - 'media' is used undefined. It's now zeroed out, but
 *    should be either implemented or removed.
 *    Resolved two other undefs.
 *
 * Revision 1.17  2004/07/16 05:28:14  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.16  2004/07/15 13:56:11  cpsource
 * - Add support for arr_vars sub-system.
 *
 * Revision 1.15  2004/07/09 18:35:39  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.14  2004/06/26 15:23:18  braverock
 * - change search layout to two rows to improve CSS positioning
 *   - applied modified version of SF patch #971474 submitted by s-t
 *
 * Revision 1.13  2004/06/21 20:53:16  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.12  2004/06/12 18:29:46  braverock
 * - remove CAST, as it is not standard across databases
 *   - database should explicitly convert number to string for CONCAT
 *
 * Revision 1.11  2004/06/12 03:27:32  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.10  2004/05/10 13:08:36  maulani
 * - Add level to audit trail
 * - Correct audit trail entry text
 *
 * Revision 1.9  2004/04/16 14:46:27  maulani
 * - Clean HTML so page will validate
 *
 * Revision 1.8  2004/04/15 22:04:38  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.7  2004/04/08 16:58:23  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
