<?php
/**
 * /activities/some.php
 *
 * Search for and View a list of activities
 *
 * $Id: some.php,v 1.60 2004/09/21 18:21:28 introspectshun Exp $
 */

// handle includes
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once('pager.php');
require_once($include_directory . 'adodb-params.php');

// create session
$session_user_id = session_check();

// Start connection
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// check for saved search
$arr_vars = array ( // local var name       // session variable name
           'saved_id'           => arr_vars_POST_UNDEF,
           'saved_title'        => arr_vars_POST_UNDEF,
           'group_item'         => arr_vars_POST_UNDEF,
           'delete_saved'       => arr_vars_POST_UNDEF,
           'browse'             => arr_vars_POST_UNDEF,
           );
           
$advanced_search = (isset($_GET['advanced_search'])) ? true : false;

// get all passed in variables
arr_vars_post_with_cmd ( $arr_vars );

// get SESSION variables for saved search
// arr_vars_session_get ( $arr_vars );

if($saved_id) {
    $sql = "SELECT saved_data, saved_status
            FROM saved_actions
            WHERE saved_id=" . $saved_id . "
            AND (user_id=" . $session_user_id;
    if($_SESSION['role_short_name'] === 'Admin') {
        $sql .= " OR group_item=1)";
    }
    else {
        $sql .= " AND group_item=0)";
    }
    $sql .= "AND saved_status='a'";
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif($rst->rowcount()) {
        if($delete_saved) {
            $rec = array();
            $rec['saved_status'] = 'd';

            $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
            $con->execute($upd);
        }
        else {
            $_POST = unserialize($rst->fields['saved_data']);
            $day_diff = $_POST['day_diff'];
        }
    }
    if($browse) {
        header("Location: " . $http_site_root . "/activities/browse-next.php?saved_id=" . $saved_id);
    }
}

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                   'sort_column'         => array ( 'activities_sort_column', arr_vars_SESSION ) ,
                   'current_sort_column' => array ( 'activities_current_sort_column', arr_vars_SESSION ) ,
                   'sort_order'          => array ( 'activities_sort_order', arr_vars_SESSION ) ,
                   'current_sort_order'  => array ( 'activities_current_sort_order', arr_vars_SESSION ) ,
                   'title'               => array ( 'activities_title', arr_vars_SESSION ) ,
                   'template_title'      => array ( 'activities_template_title', arr_vars_SESSION ) ,
                   'contact'             => array ( 'activities_contact', arr_vars_SESSION ) ,
                   'contact_id'          => array ( 'activities_contact_id', arr_vars_SESSION ) ,
                   'company'             => array ( 'activities_company', arr_vars_SESSION ) ,
                   'company_id'          => array ( 'activities_company_id', arr_vars_SESSION ) ,
		   // 'owner'               => array ( 'activities_owner', arr_vars_SESSION ) ,
                   'before_after'        => array ( 'activities_before_after', arr_vars_SESSION ) ,
                   'activity_type_id'    => array ( 'activity_type_id', arr_vars_SESSION ) ,
                   'completed'           => array ( 'activities_completed', arr_vars_SESSION ) ,
                   'user_id'             => array ( 'activities_user_id', arr_vars_SESSION ) ,
                   // 'date'                => array ( 'date', arr_vars_SESSION ) ,
                   'search_date'         => array ( 'activities_date', arr_vars_SESSION ) ,
                   'time_zone_between'   => array ( 'time_zone_between', arr_vars_SESSION ) ,
                   'time_zone_between2'  => array ( 'time_zone_between2', arr_vars_SESSION ) ,
                   'opportunity_status_id' => array ( 'opportunity_status_id', arr_vars_SESSION ) ,
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );


// (introspectshun) Updated to use portable database code; removed MySQL-centric date functions
// This will work for positive and negative intervals automatically, so no need for conditional assignment of offset
// (a search for today will add an interval of '0 days')
// Warning: if a user wants to save a search for a particular date, this won't allow it, as it defaults to recurring search
if(isset($day_diff) and $day_diff) {
    $search_date = date('Y-m-d', time() + ($day_diff * 86400));
}
else {
    if ( !$search_date ) {
        $search_date = date('Y-m-d', time());
    }
    $day_diff = (strtotime($search_date) - strtotime(date('Y-m-d', time()))) / 86400;
}

$offset = $con->OffsetDate($day_diff);

if (!strlen($sort_column) > 0) {
    $sort_column = 1;
    $current_sort_column = $sort_column;
    $sort_order = "asc";
}
if (!strlen($completed) > 0) {
    $completed ='o';
}

if (!($sort_column == $current_sort_column)) {
    $sort_order = "asc";
}

//If advanced search template title is on, it should override normal title
$title = ($template_title) ? $template_title : $title;

$opposite_sort_order = ($sort_order == "asc") ? "desc" : "asc";
$sort_order = (($resort) && ($current_sort_column == $sort_column)) ? $opposite_sort_order : $sort_order;
$ascending_order_image = ' <img border=0 height=10 width=10 src="../img/asc.gif" alt="">';
$descending_order_image = ' <img border=0 height=10 width=10 src="../img/desc.gif" alt="">';
$pretty_sort_order = ($sort_order == "asc") ? $ascending_order_image : $descending_order_image;

// set all session variables
arr_vars_session_set ( $arr_vars );

//uncomment this to see what's going on with the database
//$con->debug=1;

/*********************************
//*** Include the sidebar boxes ***/

/** End of the sidebar includes **
//*********************************/

if(strlen($time_zone_between) and strlen($time_zone_between2)) {
    update_daylight_savings($con);
}

$sql = "SELECT
  (CASE WHEN (activity_status = 'o') AND (ends_at < " . $con->DBTimeStamp(time()) . ") THEN 'Yes' ELSE '-' END) AS '" . _("Overdue") . "',"
  ." at.activity_type_pretty_name AS '" . _("Type") . "',"
  . $con->Concat("'<a href=\"../contacts/one.php?contact_id='", "cont.contact_id", "'\">'", "cont.first_names", "' '", "cont.last_name", "'</a>'") . "AS '" . _("Contact") . "',"
  . $con->Concat("'<a href=\"one.php?activity_id='", "a.activity_id", "'&amp;return_url=/activities/some.php\">'", "activity_title", "'</a>'")
  . " AS '" . _("Title") . "', "
  . $con->SQLDate('Y-m-d','a.scheduled_at') . " AS '" . _("Scheduled") . "', "
  . $con->SQLDate('Y-m-d','a.ends_at') . " AS '" . _("Due") . "', "
  . $con->Concat("'<a href=\"../companies/one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS '" . _("Company") . "',
  u.username AS '" . _("Owner") . "', ";
if($sort_column == 9) {
    $sql .= " o.probability AS '" . _("%") . "' ";
}
else {
    $sql .= " 'n/a' AS '" . _("%") . "' ";
}
$sql .= "FROM companies c, users u, activity_types at, addresses addr, activities a";
if(strlen($time_zone_between) and strlen($time_zone_between2)) {
    $sql .= ", time_daylight_savings tds";
}
if($opportunity_status_id or $sort_column == 9) {
    $sql .= ", opportunities o";
}
$sql .= " LEFT OUTER JOIN contacts cont ON cont.contact_id = a.contact_id
  WHERE a.company_id = c.company_id";
if($sort_column == 9) {
    $sql .= " AND a.on_what_table='opportunities'
  AND a.on_what_id=o.opportunity_id ";
}
$sql .= " AND a.activity_record_status = 'a'
  AND at.activity_type_id = a.activity_type_id
  AND a.user_id = u.user_id
  AND c.default_primary_address=addr.address_id";

$criteria_count = 0;

if (strlen($title) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_title like " . $con->qstr('%' . $title . '%', get_magic_quotes_gpc());
}

if (strlen($contact) > 0) {
    $criteria_count++;
    $sql .= " and cont.last_name like " . $con->qstr('%' . $contact . '%', get_magic_quotes_gpc());
}

if (strlen($contact_id)) {
    $criteria_count++;
    $sql .= " and cont.contact_id = " . $contact_id;
}

if (strlen($company) > 0) {
    $criteria_count++;
    $sql .= " and c.company_name like " . $con->qstr('%' . $company . '%', get_magic_quotes_gpc());
}

if (strlen($company_id)) {
    $criteria_count++;
    $sql .= " and c.company_id = " . $company_id;
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $sql .= " and a.user_id = $user_id ";
}

if (strlen($activity_type_id) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_type_id = " . $activity_type_id . " ";
}

if (strlen($completed) > 0 and $completed != "all") {
    $criteria_count++;
    $sql .= " and a.activity_status = " . $con->qstr($completed, get_magic_quotes_gpc());
}

if (strlen($search_date) > 0) {
    $criteria_count++;
    if (!$before_after) {
        $sql .= " and a.ends_at < " . $offset;
    } else {
        $sql .= " and a.ends_at > " . $offset;
    }
}

if(strlen($time_zone_between) and strlen($time_zone_between2)) {
    update_daylight_savings($con);
    $sql .= " and addr.daylight_savings_id = tds.daylight_savings_id";
    
    $sql .= " and (hour(" . $con->DBTimeStamp(time()) . ") + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") >= " . $time_zone_between;
    $sql .= " and (hour(" . $con->DBTimeStamp(time()) . ") + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") <= " . $time_zone_between2;
}

if($opportunity_status_id) {
    $sql .= " and a.on_what_table='opportunities' and a.on_what_id=o.opportunity_id and o.opportunity_status_id=" . $opportunity_status_id;
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $sql .= " and 1 = 2";
}


if ($sort_column == 1) {
    $order_by = _("Overdue");
} elseif ($sort_column == 2) {
    $order_by = "activity_type_pretty_name";
} elseif ($sort_column == 3) {
    $order_by = "cont.last_name";
} elseif ($sort_column == 4) {
    $order_by = "activity_title";
} elseif ($sort_column == 5) {
    $order_by = _("Scheduled");
} elseif ($sort_column == 6) {
    $order_by = "a.ends_at";
} elseif ($sort_column == 7) {
    $order_by = "c.company_name";
} elseif ($sort_column == 8) {
    $order_by = "owner";
} elseif ($sort_column == 9) {
    $order_by = "o.probability";
} else {
    $order_by = $sort_column;
}


$order_by .= " $sort_order";

$sql .= " order by $order_by"; // is_overdue desc, a.scheduled_at, a.entered_at desc";
//activities Pager table is rendered below by ADOdb pager

if($advanced_search) {
    //get activities from templates
    $sql2 = "SELECT activity_title, activity_title as activity_title2 
            FROM activity_templates 
            WHERE activity_template_record_status='a' 
            AND on_what_table = 'opportunity_statuses'
            GROUP BY activity_title 
            ORDER BY activity_title";
    $rst = $con->execute($sql2);
    $activity_menu = $rst->getmenu2('template_title', $template_title, true);
    $rst->close();
}

//get menu for users
$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();

if($advanced_search) {
    //get menu for opportunity_statuses
    $sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status='a'";
    $rst = $con->execute($sql2);
    $opportunity_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, true);
}

//get activity type menu
$sql_type = "select activity_type_pretty_name, activity_type_id
from activity_types at
order by activity_type_pretty_name";
$rst = $con->execute($sql_type);
$type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, true);
$rst->close();

// save search
$saved_data = $_POST;
$saved_data["sql"] = $sql;
$saved_data["day_diff"] = $day_diff;

if(!$saved_title) {
    $saved_title = "Current";
    $group_item = 0;
}

$rec = array();
$rec['saved_title'] = $saved_title;
$rec['group_item'] = round($group_item);
$rec['on_what_table'] = "activities";
$rec['saved_action'] = "search";
$rec['user_id'] = $session_user_id;
$rec['saved_data'] = str_replace("'", "\\'", serialize($saved_data));

if($saved_title or $browse) {
    $sql_saved = "SELECT *
            FROM saved_actions
            WHERE (user_id=" . $session_user_id . "
            OR group_item=1)
            AND saved_title='" . $saved_title . "'
            AND saved_status='a'";
    $rst = $con->execute($sql_saved);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif($rst->rowcount()) {
        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);
        $saved_id = $rst->fields['saved_id'];
    }
    else {
        $tbl = "saved_actions";
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        $con->execute($ins);
        $saved_id = $con->Insert_ID();
    }
    if($browse) {
        header("Location: " . $http_site_root . "/activities/browse-next.php?saved_id=" . $saved_id);
    }
}

//get saved searches
$sql_saved = "SELECT saved_title, saved_id
        FROM saved_actions
        WHERE (user_id=$session_user_id
        OR group_item=1)
        AND on_what_table='activities'
        AND saved_action='search'
        AND saved_status='a'
        AND saved_title!='Current'";
$rst = $con->execute($sql_saved);
if ( !$rst ) {
  db_error_handler($con, $sql_saved);
}
if( $rst->RowCount() ) {
    $saved_menu = $rst->getmenu2('saved_id', 0, true) . ' <input name="delete_saved" type=submit class=button value="' . _("Delete") . '">';
} else {
  $saved_menu = '';
}

add_audit_item($con, $session_user_id, 'searched', 'activities', '', 4);

//debug
//echo $sql.'<br>';

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

//debug
//echo 'company_count = ' . $company_count;

$page_title = _("Open Activities");
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

    <form action=some.php method=post>
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=activities_next_page value="<?php  echo $activities_next_page; ?>">
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
                <td colspan="2" class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
            </tr>
            <tr>
                <td colspan="2" class=widget_content_form_element> <input type=text name="title" size=24 value="<?php  echo $title; ?>">
                </td>
                <td class=widget_content_form_element><input type=text name="contact" size=12 value="<?php  echo $contact; ?>">
                </td>
                <td class=widget_content_form_element><input type=text name="company" size=15 value="<?php  echo $company; ?>">
                </td>
            </tr>
<?php if($advanced_search) { ?>
            <tr>
                <td colspan="2" class=widget_label><?php echo _("Template Titles"); ?></td>
                <td class=widget_label><?php echo _("Contact ID"); ?></td>
                <td class=widget_label><?php echo _("Company ID"); ?></td>
            </tr>
            <tr>
                <td colspan="2" class=widget_content_form_element><?php echo $activity_menu; ?>
                </td>
                <td class=widget_content_form_element><input type=text name="contact_id" size=12 value="<?php  echo $contact_id; ?>">
                </td>
                <td class=widget_content_form_element><input type=text name="company_id" size=15 value="<?php  echo $company_id; ?>">
                </td>
            </tr>
<?php } ?>
            <tr>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("End/Due Date"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Completed"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <?php  echo $user_menu; ?>
                </td>
                <td class=widget_content_form_element>
                    <select name="before_after">
                        <option value=""<?php if (!$before_after) { print " selected"; } ?>><?php echo _("Before"); ?></option>
                        <option value="after"<?php if ($before_after == "after") { print " selected"; } ?>><?php echo _("After"); ?></option>
                    </select>
                    <input type=text ID="f_date_d" name="search_date" size=12 value="<?php  echo $search_date; ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif" alt="">
                </td>
                <td class=widget_content_form_element>
                    <?php  echo $type_menu; ?>
                </td>
                <td class=widget_content_form_element>
                    <select name="completed">
                        <option value="all"<?php if ($completed == "all") { print " selected"; } ?>><?php echo _("All"); ?></option>
                        <option value="o"<?php if ($completed == "o" or !$completed) { print " selected"; } ?>><?php echo _("Non-Completed"); ?></option>
                        <option value="c"<?php if ($completed == "c") { print " selected"; } ?>><?php echo _("Completed"); ?></option>
                    </select>
                </td>
            </tr>
<?php if($advanced_search) { ?>
            <tr>
                <td class=widget_label colspan="2"><?php echo _("Local Time Between"); ?></td>
                <td class=widget_label colspan="2"><?php echo _("Opportunity Status"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan="2">
<?php
                    $sql2 = "SELECT " . $con->concat("(CASE WHEN country_id%25 < 24 THEN lpad(country_id%25, 2, '0') ELSE '00' END)", "':00:00'") . " as counter, country_id%25 as counter2 from countries group by counter2 order by counter2";
                    $rst = $con->execute($sql2);
                    if(!$rst) {
                        db_error_handler($con, $sql2);
                    }
                    print $rst->getmenu2('time_zone_between', $time_zone_between, true);
                ?>
 and 
<?php
                    $rst->movefirst();
                    print $rst->getmenu2('time_zone_between2', $time_zone_between2, true);
                ?>
                </td>
                <td class=widget_content_form_element colspan="2">
                    <?php echo $opportunity_menu; ?>
                </td>
            </tr>
<? } ?>
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
                        if($_SESSION['role_short_name'] === 'Admin') {
                            echo _("Add to Everyone").' <input type=checkbox name="group_item" value=1>';
                        }
                    ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input name="submitted" type=submit class=button value="<?php echo _("Search"); ?>">
                    <input name="browse" type=submit class=button value="<?php echo _("Browse"); ?>">
                    <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                    <?php
                        if ($company_count > 0) {
                            echo "<input class=button type=button onclick='javascript: bulkEmail()' value='" . _("Bulk E-Mail") . "'>";
                        }
                        if(!$advanced_search) {
                    ?>
                    <input name="advanced_search" type=button class=button onclick="javascript: location.href='some.php?advanced_search=true';" value="<?php echo _("Advanced Search"); ?>">
                    <?php } ?>
                </td>
            </tr>
    </table>
    </form>

<?php
$_SESSION["search_sql"]=$sql;

$pager = new Activities_Pager($con, $sql, $sort_column-1, $pretty_sort_order);
$pager->render($rows_per_page=$system_rows_per_page);
$con->close();

?>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

    </div>
</div>

<script language="JavaScript" type="text/javascript">

function initialize() {
    document.forms[0].title.focus();
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
    document.forms[0].activities_next_page.value = adodbNextPage;
    document.forms[0].submit();
}

function resort(sortColumn) {
    document.forms[0].sort_column.value = sortColumn + 1;
    document.forms[0].activities_next_page.value = '';
    document.forms[0].resort.value = 1;
    document.forms[0].submit();
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

</script>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.60  2004/09/21 18:21:28  introspectshun
 * - Changed table order in main query FROM clause
 *   - Join fails on MSSQL otherwise
 *
 * Revision 1.59  2004/08/25 15:49:39  introspectshun
 * - Fixed errant variable name
 *
 * Revision 1.58  2004/08/25 15:01:17  neildogg
 * - Searches local time with proper constraints
 *  - Saves temporary searches properly
 *
 * Revision 1.57  2004/08/19 20:37:20  neildogg
 * - Added many advanced search features
 *  - Search by template title, contact ID, company ID
 *  - Search by opportunity status
 *  - Moved local time to advanced search
 *
 * Revision 1.56  2004/08/18 06:08:14  niclowe
 * Merged 1.54 neildogg chnages into 1.55 niclowe changes due to incorrect commit. by niclowe
 *
 * Revision 1.54  2004/08/16 21:02:09  neildogg
 * - Allows to find activities ending within a time range
 *  - (for all time zones)
 *
 * Revision 1.53  2004/08/16 14:39:23  maulani
 * - Override ADODB_Pager class for activities to allow customization
 * - Customization still todo
 *
 * Revision 1.52  2004/08/05 18:42:56  neildogg
 * - Date offset now compatible thanks to
 *  - advice from David Rogers
 *
 * Revision 1.51  2004/07/30 13:01:28  neildogg
 * - Restores $search_date using stored $day_diff
 *
 * Revision 1.50  2004/07/27 19:50:41  neildogg
 * - Major changes to browse functionality
 *  - Removal of sidebar for "browse" button
 *  - Removal of activity_type browse
 *  - Aesthetic modifications
 *  - Date in some.php is now mySQL curdate()
 *
 * Revision 1.49  2004/07/27 10:21:50  cpsource
 * - Fix some undefs
 *
 * Revision 1.48  2004/07/26 16:13:00  neildogg
 * - Now it actually defines an undefined variable
 *  - instead of assigning it an empty string
 *
 * Revision 1.47  2004/07/23 21:46:54  cpsource
 * - Get rid of some undefined variable usages.
 *
 * Revision 1.46  2004/07/23 11:29:59  braverock
 * - remove hard-coded error_reporting
 * - add time() param to DBTimeStamp call
 *
 * Revision 1.45  2004/07/22 23:58:35  braverock
 * - further cleanup in formatting to minimize possibility of parse errors
 *
 * Revision 1.44  2004/07/22 23:48:25  braverock
 * - fixed a short php tag that was causing a parse error
 * - removed a bunch of stubbed out useless code
 *
 * Revision 1.43  2004/07/22 22:00:24  introspectshun
 * - Updated date offset logic to use portable database code
 *   - Removed MySQL-centric date functions and conditional block
 *
 * Revision 1.42  2004/07/22 19:59:03  neildogg
 * - Missed concat .
 *
 * Revision 1.41  2004/07/22 19:57:49  neildogg
 * - activity_type_id is an ID
 *
 * Revision 1.40  2004/07/22 18:06:40  introspectshun
 * - Localized "Add to Everyone"
 *
 * Revision 1.39  2004/07/22 13:58:27  neildogg
 * - Limit group saved-search functionality to admin
 *
 * Revision 1.38  2004/07/21 22:39:04  neildogg
 * - Allow saved search deletion
 *
 * Revision 1.37  2004/07/21 20:32:19  neildogg
 * - Added ability to save searches
 *  - Any improvements welcome
 *
 * Revision 1.36  2004/07/16 11:11:48  cpsource
 * - Remove unused $open_activities variable.
 *   Add a programming note on how to efficiently tell if string length is 0
 *
 * Revision 1.35  2004/07/16 04:53:51  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.34  2004/07/15 14:07:38  cpsource
 * - Ported arr_vars sub-system.
 *
 * Revision 1.33  2004/07/14 20:19:49  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.32  2004/07/13 14:18:19  neildogg
 * - Changed submit button name to another name
 *   - resolves SF bug 9888931 reported by braverock
 *
 * Revision 1.31  2004/07/11 12:32:48  braverock
 * - eliminate manual table generation in favor of pager object
 * - rearrange column order based on input from Walt Pennington
 *
 * Revision 1.30  2004/07/10 12:24:59  braverock
 * - fixed undefined activity_id
 * - fixed misdefined activity_type_pretty_name
 *   - fixes SF bugs reported by cpsource
 *
 * Revision 1.29  2004/07/10 12:14:53  braverock
 * - applied patch for undefined variables
 *   - modified from SF patch 979124 supplied by cpsource
 *
 * Revision 1.28  2004/07/09 18:35:07  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 *
 * Revision 1.27  2004/07/05 20:29:09  introspectshun
 * - Updated Concat to use CAST AS CHAR for activity_id.
 *
 * Revision 1.26  2004/07/02 15:22:44  maulani
 * - Fix formatting and HTML so page will validate
 *
 * Revision 1.25  2004/06/26 15:17:14  braverock
 * - change search layout to two pages to improve CSS positioning
 *   - applied modified version of SF patch # 971474submitted by s-t
 *
 * Revision 1.24  2004/06/25 03:12:09  braverock
 * - make default search for open activities only
 *
 * Revision 1.23  2004/06/24 19:58:47  braverock
 * - committing enhancements to Save&Next functionality
 *   - patches submitted by Neil Roberts
 *
 * Revision 1.22  2004/06/22 11:04:16  braverock
 * - fixed timestamp to be in proper database compliant mode
 *
 * Revision 1.21  2004/06/21 20:51:01  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.20  2004/06/15 14:13:36  gpowers
 * - corrected time formats: changed DBTimeStamp(time()) to time()
 *   -  DBTimeStamp(time()) does not work with MySQL
 *
 * Revision 1.19  2004/06/13 09:15:07  braverock
 * - add Save & Next functionality
 *   - code contributed by Neil Roberts
 *
 * Revision 1.18  2004/06/12 18:15:59  braverock
 * - fix DBTimestamp errors after upgrade
 * - remove CAST, as it is not standard across databases
 *   - database should explicitly convert number to string for CONCAT
 *
 * Revision 1.17  2004/06/11 21:20:11  introspectshun
 * - Now use ADODB Concat and Date functions.
 *
 * Revision 1.16  2004/06/04 16:43:17  braverock
 * - adjusted size of input boxes on search
 * - removed unecessary sidebar whitespace on this page, since it is not used
 *
 * Revision 1.15  2004/06/04 16:28:44  gpowers
 * Removed time selection from calendar applet settings
 *
 * Revision 1.14  2004/06/04 16:03:59  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.13  2004/06/03 16:11:01  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.12  2004/05/10 13:07:20  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.11  2004/05/04 20:51:26  braverock
 * -set return_url on title link.
 *   - fixes SF bug 947755 reported by Beth Macknik (maulani)
 *
 * Revision 1.10  2004/04/27 13:43:24  gpowers
 * removed audit_items entry for searching. it is a duplicate of information
 * available in the httpd access log.
 *
 * Revision 1.9  2004/04/22 18:29:36  gpowers
 * removed echo order_by , ^M's, //user_id=1
 *
 * Revision 1.8  2004/04/20 12:53:48  braverock
 * - add direct link to activity
 * - add owner in the list
 * - fix bug with sorting options
 *   - apply SF patch 938385 submitted by frenchman
 *
 * Revision 1.7  2004/04/14 22:48:28  maulani
 * - Add CSS2 positioning
 * - Fix minor HTML problems
 * - Update HTML so it will validate
 *
 * Revision 1.6  2004/04/09 21:11:42  braverock
 * - add check for activity_record_status = 'a'
 *   - fixes SF bug 932545 reported by Beth (maulani)
 *
 * Revision 1.5  2004/04/09 20:01:20  braverock
 * - display search results using adodb pager for consistency
 * - allow export of search results as CSV file
 *   - modified files submitted by Olivier Collonna of Fontaine Consulting
 */
?>
