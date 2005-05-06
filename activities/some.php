<?php
/**
 * /activities/some.php
 *
 * Search for and View a list of activities
 *
 * $Id: some.php,v 1.109 2005/05/06 22:18:24 daturaarutad Exp $
 */

// handle includes
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once ($include_directory . 'classes/Pager/Session_Var_Watcher.php');
require_once('../activities/activities-pager-functions.php');


// create session
$on_what_table='activities';
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

$advanced_search = (!empty($_REQUEST['advanced_search'])) ? true : false;

// get all passed in variables
arr_vars_post_with_cmd ( $arr_vars );

// get SESSION variables for saved search
// arr_vars_session_get ( $arr_vars );

if($saved_id) {
    $sql = "SELECT saved_data, saved_status, user_id
            FROM saved_actions
            WHERE saved_id=" . $saved_id . "
            AND (user_id=" . $session_user_id . "
              OR group_item = 1)
            AND saved_status='a'";
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif($rst->rowcount()) {
        if($delete_saved && ($_SESSION['role_short_name'] === 'Admin' || $rst->fields['user_id'] == $session_user_id)) {
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
                   'title'               => array ( 'activities_title', arr_vars_SESSION ) ,
                   'template_title'      => array ( 'activities_template_title', arr_vars_SESSION ) ,
                   'contact'             => array ( 'activities_contact', arr_vars_SESSION ) ,
                   'contact_id'          => array ( 'activities_contact_id', arr_vars_SESSION ) ,
                   'company'             => array ( 'activities_company', arr_vars_SESSION ) ,
                   'company_id'          => array ( 'activities_company_id', arr_vars_SESSION ) ,
                   'campaign_id'          => array ( 'activities_campaign_id', arr_vars_SESSION ) ,
           // 'owner'               => array ( 'activities_owner', arr_vars_SESSION ) ,
                   'before_after'        => array ( 'activities_before_after', arr_vars_SESSION ) ,
                   'start_end'           => array ( 'activities_start_end', arr_vars_SESSION ) ,
                   'activity_type_id'    => array ( 'activity_type_id', arr_vars_SESSION ) ,
                   'completed'           => array ( 'activities_completed', arr_vars_SESSION ) ,
                   'user_id'             => array ( 'activities_user_id', arr_vars_SESSION ) ,
                   // 'date'                => array ( 'date', arr_vars_SESSION ) ,
                   'search_date'         => array ( 'activities_date', arr_vars_SESSION ) ,
                   'time_zone_between'   => array ( 'time_zone_between', arr_vars_SESSION ) ,
                   'time_zone_between2'  => array ( 'time_zone_between2', arr_vars_SESSION ) ,
                   'opportunity_status_id' => array ( 'opportunity_status_id', arr_vars_SESSION ) ,
                   'results_view_type'   => array ( 'results_view_type', arr_vars_SESSION ) ,
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );


// (introspectshun) Updated to use portable database code; removed MySQL-centric date functions
// This will work for positive and negative intervals automatically, so no need for conditional assignment of offset
// (a search for today will add an interval of '0 days')
// Warning: if a user wants to save a search for a particular date, this won't allow it, as it defaults to recurring search
if(isset($day_diff) and $day_diff) {
    $search_date = date('Y-m-d', time() + ($day_diff * 86400));
} else {
    if ( !$search_date ) {
        $search_date = date('Y-m-d', time());
    }
    $day_diff = round((strtotime($search_date) - strtotime(date('Y-m-d', time()))) / 86400);
}



getGlobalVar($calendar_start_date, 'calendar_start_date');

//echo "search date is $search_date<br>";
//echo "calendar start date is $calendar_start_date<br>";

if($start_end == 'start') {
    $field = 'scheduled_at';
} else {
    $field = 'ends_at';
}

$offset_sql = '';

// first set up $offset_sql for before/after search_date
if (strlen($search_date) > 0 && $start_end != 'all') {
    $criteria_count++;

    if (!$before_after) {
        // before
        $offset_end = $con->OffsetDate($day_diff);
        $offset_sql .= " and a.$field < $offset_end";
    } elseif ($before_after === 'after') {
        // after
        $offset_start = $con->OffsetDate($day_diff);
        $offset_sql .= " and a.$field > $offset_start";
    } elseif ($before_after === 'on') {
        // same query for list and calendar views
        $offset_start = $con->OffsetDate($day_diff);
        $offset_end = $con->OffsetDate($day_diff+1);
        // midnight to midnight
        $offset_sql .= " and a.$field > $offset_start and a.$field < $offset_end";
    }
}


if(!$results_view_type) {
    $results_view_type = 'list';
}

// watch to see if activities_date | before_after | start_end has changed
$var_watcher = new SessionVarWatcher('Activities_Search');
$var_watcher->RegisterCGIVars(array('search_date', 'before_after', 'start_end'));
$var_watcher->RegisterLocalVar('results_view_type', $results_view_type);


// set up $calendar_start_date and append to $offset_sql for calendar views
if('list' != $results_view_type) {

    require_once('../calendar/agenda/Calendar_View.php');

    // set calendar_start_date from search_date if it's not set already
    if($var_watcher->VarsChanged() || empty($calendar_start_date)) {

        if(!$before_after) {
            // before
            $date_modifier = '-1 ' . $results_view_type . ' ';
        } else {
            $date_modifier = '';
        }

        switch($results_view_type) {
            case 'day':
                $calendar_start_date = date("Y-m-d", strtotime($date_modifier . $search_date));
                break;
            case 'week':
                // align it to the week's start day (e.g. Monday)
                if(empty($calendar_start_date)) {
                    $calendar_start_date = CalendarView::GetWeekStart($start_date, 'Monday');
                } else {
                    $calendar_start_date = CalendarView::GetWeekStart($calendar_start_date, 'Monday');
                }
                break;
            case 'month':
                $calendar_start_date = date("Y-m-", strtotime($date_modifier . $search_date));
                $calendar_start_date .= '01';
                break;
            case 'year':
                $calendar_start_date = date("Y-", strtotime($date_modifier . $search_date));
                $calendar_start_date .= '01-01';
                break;
        }
        //echo "calendar_start_date not set, setting to $calendar_start_date (search date is $search_date)<br>";

        // this is for the calendar widget
        $_POST['calendar_start_date'] = $calendar_start_date;
    }



    // set up the query offsets for the calendar view
    if (strlen($search_date) > 0 && $start_end != 'all') {
        $criteria_count++;

        $calendar_view_start =  (strtotime($calendar_start_date) - time()) / 86400;

        $offset_start = $con->OffsetDate($calendar_view_start);
        $calendar_view_end = (strtotime("$calendar_start_date +1 $results_view_type") - time()) / 86400;
        $offset_end = $con->OffsetDate($calendar_view_end);
        $offset_sql .= " and a.$field > $offset_start and a.$field < $offset_end";
    }
/*
    echo "day_diff is $day_diff<br>";
    echo "calendar_view_start is $calendar_view_start aka " . date('Y-m-d H:i', time() + $calendar_view_start*24*3600)  . "<br>";
    echo "calendar_view_end is $calendar_view_end aka " . date('Y-m-d H:i', time() + $calendar_view_end*24*3600)  . "<br>";
    echo "query window is from " . date('Y-m-d H:i', time() + $calendar_view_start*24*3600) . " to " . date('Y-m-d H:i', time() + $calendar_view_end*24*3600) . '<br>';
    echo "calendar start date is $calendar_start_date<br>";
    echo "offset_sql is $offset_sql<br>";
    */

    // init the CalendarView object
    $calendar = new CalendarView('ActivitiesData', 'calendar_start_date', $results_view_type);
}


if (!strlen($completed) > 0) {
    $completed ='o';
}

//If advanced search template title is on, it should override normal title
$title = ($template_title) ? $template_title : $title;

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
  (CASE WHEN (activity_status = 'o') AND (ends_at < " . $con->DBTimeStamp(time()) . ") THEN ". $con->qstr(_("Yes"),get_magic_quotes_gpc()) ." ELSE '-' END) AS overdue, "
  ." at.activity_type_pretty_name AS type, "
  . $con->Concat("'<a id=\"'", "cont.last_name", "'_'" ,"cont.first_names","'\" href=\"../contacts/one.php?contact_id='", "cont.contact_id", "'\">'", "cont.first_names", "' '", "cont.last_name", "'</a>'") . " AS contact, "
  . $con->Concat("'<a id=\"'", "activity_title", "'\" href=\"one.php?activity_id='", "a.activity_id", "'&amp;return_url=/activities/some.php\">'", "activity_title", "'</a>'") . " AS title, "
  . $con->SQLDate('Y-m-d','a.scheduled_at') . " AS scheduled, "
  . $con->SQLDate('Y-m-d','a.ends_at') . " AS due, "
  . $con->Concat("'<a id=\"'", "c.company_name", "'\" href=\"../companies/one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS company, "
  . "u.username AS owner, u.user_id, activity_id,"
  // these are to speed up the pager sorting
  . "cont.last_name, cont.first_names, activity_title, a.scheduled_at, a.ends_at, c.company_name ";

/* disabled for now...
if($sort_column == 9) {
    $sql .= " o.probability AS " . $con->qstr("%",get_magic_quotes_gpc());
}
else {
    $sql .= " 'n/a' AS " . $con->qstr("%",get_magic_quotes_gpc()) . " ";
}
*/
$sql .= "FROM companies c, activity_types at, addresses addr, activities a ";
if(strlen($time_zone_between) and strlen($time_zone_between2)) {
    $sql .= ", time_daylight_savings tds";
}
/*
if($opportunity_status_id || $sort_column == 9 || $campaign_id) {
    $sql .= ", opportunities o";
}
*/
$sql .= "
LEFT OUTER JOIN contacts cont ON cont.contact_id = a.contact_id
LEFT OUTER JOIN users u ON a.user_id = u.user_id";

$sql .= " WHERE a.company_id = c.company_id";

/*
if($sort_column == 9 || $campaign_id) {
    $sql .= " AND a.on_what_table='opportunities'
  AND a.on_what_id=o.opportunity_id ";
}
*/
$sql .= " AND a.activity_record_status = 'a'
  AND at.activity_type_id = a.activity_type_id
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
    if($user_id == '-2') {
        //Not Set
        $sql .= " and a.user_id = 0";
    }
    elseif($user_id == '-1') {
        //Current User
        $sql .= " and a.user_id = $session_user_id ";
    }
    elseif($user_id == 'no') {
        //Not Set
        $sql .= " and a.user_id = 0";
    }
    elseif($user_id == 'cu') {
        //Current User
        $sql .= " and a.user_id = $session_user_id ";
    }
    else {
        $sql .= " and a.user_id = $user_id ";
    }
}

if (strlen($activity_type_id) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_type_id = " . $activity_type_id . " ";
}

if (strlen($completed) > 0 and $completed != "all") {
    $criteria_count++;
    $sql .= " and a.activity_status = " . $con->qstr($completed, get_magic_quotes_gpc());
}

if (strlen($offset_sql) > 0) {
    $criteria_count++;
    $sql .= $offset_sql;
}

if(strlen($time_zone_between) and strlen($time_zone_between2)) {
    update_daylight_savings($con);
    $now = time();
    $now_array=localtime($now, true);
    $hour=$now_array['tm_hour'];
    $sql .= " and addr.daylight_savings_id = tds.daylight_savings_id";

    $sql .= " and ($hour + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") >= " . $time_zone_between;
    $sql .= " and ($hour + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") <= " . $time_zone_between2;
}

if($opportunity_status_id) {
    $sql .= " and a.on_what_table='opportunities' and a.on_what_id=o.opportunity_id and o.opportunity_status_id=" . $opportunity_status_id;
}

if($campaign_id) {
    $sql .= " AND o.campaign_id = " . $campaign_id;
}

if (!$use_post_vars && (!$criteria_count > 0)) {
    $sql .= " and 1 = 2";
} else {
    $list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    //print_r($list);
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $sql .= " and a.activity_id IN ($list) ";
        }
    } else { $sql .= ' AND 1 = 2 '; }
}

//activities Pager table is rendered below by ADOdb pager
//echo htmlspecialchars($sql);
if($advanced_search) {
    //get activities from templates
    $sql2 = "SELECT activity_title, activity_title as activity_title2
            FROM activity_templates
            WHERE activity_template_record_status='a'
            AND on_what_table = 'opportunity_statuses'
            GROUP BY activity_title
            ORDER BY activity_title";
    $rst = $con->execute($sql2);
    if (!$rst) {
        db_error_handler($con, $sql2);
    } elseif ($rst->rowcount()) {
        $activity_menu = $rst->getmenu2('template_title', $template_title, true);
        $rst->close();
    }
}

if($advanced_search) {
    //get campaign titles
    $sql2 = "SELECT campaign_title, campaign_id
             FROM campaigns
             WHERE campaign_record_status = 'a'";
    $rst = $con->execute($sql2);
    if (!$rst) {
        db_error_handler($con, $sql2);
    } elseif ($rst->rowcount()) {
        $campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
        $rst->close();
    }
}

if (!isset($user_id)) {
   $user_id=$session_user_id;
}

//get menu for users
$sql2 = "(SELECT " . $con->qstr(_("Current User"),get_magic_quotes_gpc()) . ", '-1')"
    . " UNION (select username, user_id from users where user_record_status = 'a')"
       . " UNION (SELECT " . $con->qstr(_("Not Set"),get_magic_quotes_gpc()) . ", '-2')  ORDER BY 1";
$rst = $con->execute($sql2);
if (!$rst) {
    db_error_handler($con, $sql2);
} elseif ($rst->rowcount()) {
    $user_menu = $rst->getmenu2('user_id', $user_id, true);
    $rst->close();
}

if($advanced_search) {
    //get menu for opportunity_statuses
    $sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status='a'";
    $rst = $con->execute($sql2);
    if (!$rst) {
        db_error_handler($con, $sql2);
    } elseif ($rst->rowcount()) {
        $opportunity_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, true);
        $rst->close();
    }
}

//get activity type menu
$sql_type = "SELECT activity_type_pretty_name, activity_type_id
             FROM activity_types
             WHERE activity_type_record_status = 'a'
             ORDER BY sort_order, activity_type_pretty_name";
$rst = $con->execute($sql_type);
$type_menu = translate_menu($rst->getmenu2('activity_type_id', $activity_type_id, true));
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
        db_error_handler($con, $sql_saved);
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
} elseif( $rst->RowCount() ) {
    $saved_menu = $rst->getmenu2('saved_id', 0, true) . ' <input name="delete_saved" type=submit class=button value="' . _("Delete") . '">';
} else {
  $saved_menu = '';
}

add_audit_item($con, $session_user_id, 'searched', 'activities', '', 4);

//debug
//echo $sql.'<br>';

$page_title = _("Open Activities");
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="ContentFullWidth">

    <form action=some.php class="print" method=post name="ActivitiesData">
        <input type=hidden name=advanced_search value="<?php echo $advanced_search; ?>">
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
                <td colspan="2" class=widget_label><?php echo _("Summary"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("Company"); ?></td>
            </tr>
            <tr>
                <td colspan="2" class=widget_content_form_element> <input type=text name="title" size=48 value="<?php  echo $title; ?>">
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
            <tr>
                <td colspan="4" class=widget_label><?php echo _("Campaigns"); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="widget_content_form_element"><?php echo $campaign_menu; ?></td>
            </tr>
<?php } //end if advanced search ?>
            <tr>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Search By Date"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Completed"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <?php  echo $user_menu; ?>
                </td>
                <td class=widget_content_form_element>
                    <select name="start_end">
                        <option value="end"<?php if($start_end == 'end') { print " selected"; }?>><?php echo _("Scheduled End"); ?></option>
                        <option value="start"<?php if($start_end == 'start') { print " selected"; }?>><?php echo _("Scheduled Start"); ?></option>
                        <option value="all"<?php if($start_end == 'all') { print " selected"; }?>><?php echo _("All Dates"); ?></option>
                    </select>
                    <select name="before_after">
                        <option value=""<?php if (!$before_after) { print " selected"; } ?>><?php echo _("Before"); ?></option>
                        <option value="after"<?php if ($before_after == "after") { print " selected"; } ?>><?php echo _("After"); ?></option>
                        <option value="on"<?php if ($before_after == "on") { print " selected"; } ?>><?php echo _("On"); ?></option>
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
                <!-- Time Zone Handling Selects -->
                <td class=widget_content_form_element colspan="2">
                     <select name="time_zone_between" >
                        <option></option>
                        <option value='0'>00:00:00</option>
                        <option value='1'>01:00:00</option>
                        <option value='2'>02:00:00</option>
                        <option value='3'>03:00:00</option>
                        <option value='4'>04:00:00</option>
                        <option value='5'>05:00:00</option>
                        <option value='6'>06:00:00</option>
                        <option value='7'>07:00:00</option>
                        <option value='8'>08:00:00</option>
                        <option value='9'>09:00:00</option>
                        <option value='10'>10:00:00</option>
                        <option value='11'>11:00:00</option>
                        <option value='12'>12:00:00</option>
                        <option value='13'>13:00:00</option>
                        <option value='14'>14:00:00</option>
                        <option value='15'>15:00:00</option>
                        <option value='16'>16:00:00</option>
                        <option value='17'>17:00:00</option>
                        <option value='18'>18:00:00</option>
                        <option value='19'>19:00:00</option>
                        <option value='20'>20:00:00</option>
                        <option value='21'>21:00:00</option>
                        <option value='22'>22:00:00</option>
                        <option value='23'>23:00:00</option>
                        <option value='24'>00:00:00</option>
                     </select>
                     <?php echo "\n"._("and")."\n"; ?>
                     <select name="time_zone_between2" >
                        <option></option>
                        <option value='0'>00:00:00</option>
                        <option value='1'>01:00:00</option>
                        <option value='2'>02:00:00</option>
                        <option value='3'>03:00:00</option>
                        <option value='4'>04:00:00</option>
                        <option value='5'>05:00:00</option>
                        <option value='6'>06:00:00</option>
                        <option value='7'>07:00:00</option>
                        <option value='8'>08:00:00</option>
                        <option value='9'>09:00:00</option>
                        <option value='10'>10:00:00</option>
                        <option value='11'>11:00:00</option>
                        <option value='12'>12:00:00</option>
                        <option value='13'>13:00:00</option>
                        <option value='14'>14:00:00</option>
                        <option value='15'>15:00:00</option>
                        <option value='16'>16:00:00</option>
                        <option value='17'>17:00:00</option>
                        <option value='18'>18:00:00</option>
                        <option value='19'>19:00:00</option>
                        <option value='20'>20:00:00</option>
                        <option value='21'>21:00:00</option>
                        <option value='22'>22:00:00</option>
                        <option value='23'>23:00:00</option>
                        <option value='24'>00:00:00</option>
                  </select>
                </td>
                <td class=widget_content_form_element colspan="2">
                    <?php echo $opportunity_menu; ?>
                </td>
            </tr>
<?php } //end if advanced search ?>
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
                <td class=widget_label colspan="4"><?php echo _("Results View"); ?></td>
            </tr>
            <tr>
                <td class=widget_label colspan="4">
                    <?php echo _("View as List:"); ?> <input type="radio" name="results_view_type" value="list"<?php if(!$results_view_type || 'list' == $results_view_type) echo ' checked="true" ' ?>> &nbsp; &nbsp; &nbsp;
                    <?php echo ' ' . _("View as Calendar: "); ?>
<!--
                    <?php echo ' ' . _("Calendar: Day"); ?> <input type="radio" name="results_view_type" value="day"<?php if('day' == $results_view_type) echo ' checked="true" ' ?> > &nbsp;
-->
                    <?php echo _("Week"); ?> <input type="radio" name="results_view_type" value="week"<?php if('week' == $results_view_type) echo ' checked="true" ' ?> > &nbsp;
                    <?php echo _("Month"); ?> <input type="radio" name="results_view_type" value="month"<?php if('month' == $results_view_type) echo ' checked="true" ' ?> > &nbsp;
<!--
                    <?php echo _("Year"); ?> <input type="radio" name="results_view_type" value="year"<?php if('year' == $results_view_type) echo ' checked="true" ' ?> > &nbsp;
-->

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input name="submitted" type=submit class=button value="<?php echo _("Search"); ?>">
                    <input name="browse" type=submit class=button value="<?php echo _("Browse"); ?>">
                    <input name="button" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                    <?php
                        if(!$advanced_search) {
                            echo ' <input name="advanced_search" type=button class=button onclick="javascript: location.href=\'some.php?advanced_search=true\';" value="'._("Advanced Search").'">';
                        }
                    ?>
                </td>
            </tr>
    </table>

<?php
$_SESSION["search_sql"]=$sql;

if('list' == $results_view_type) {

    $columns = array();
    $columns[] = array('name' => _('Overdue'), 'index_sql' => 'overdue');
    $columns[] = array('name' => _('Type'), 'index_sql' => 'type');
    $columns[] = array('name' => _('Contact'), 'index_sql' => 'contact', 'sql_sort_column' => 'cont.last_name,cont.first_names', 'type' => 'url');
    $columns[] = array('name' => _('Summary'), 'index_sql' => 'title', 'sql_sort_column' => 'activity_title', 'type' => 'url');
    $columns[] = array('name' => _('Scheduled Start'), 'index_sql' => 'scheduled', 'sql_sort_column' => 'a.scheduled_at');
    $columns[] = array('name' => _('Scheduled End'), 'index_sql' => 'due', 'default_sort' => 'desc', 'sql_sort_column' => 'a.ends_at');
    $columns[] = array('name' => _('Company'), 'index_sql' => 'company', 'sql_sort_column' => 'c.company_name', 'type' => 'url');
    $columns[] = array('name' => _('Owner'), 'index_sql' => 'owner');


    // selects the columns this user is interested in
    // no reason to set this if you don't want all by default
    $default_columns = null;
    //$default_columns =  array('overdue','type','contact','title','scheduled','due','company','owner');

    $pager_columns = new Pager_Columns('SomeActivitiesPager', $columns, $default_columns, 'ActivitiesData');
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');


    // output the selectable columns widget
    echo $pager_columns_selects;

    // caching is disabled for this pager (since it's all sql)
    $pager = new GUP_Pager($con, $sql, 'GetActivitiesPagerData', _('Search Results'), 'ActivitiesData', 'SomeActivitiesPager', $columns, false);

    // set up the bottom row of buttons
    $endrows = "<tr><td class=widget_content_form_element colspan=10>
                $pager_columns_button
                " . $pager->GetAndUseExportButton() .  "
                <input type=button class=button onclick=\"javascript: bulkEmail();\" value=\"" . _('Mail Merge') . "\"></td></tr>";

    $pager->AddEndRows($endrows);
    $pager->Render($system_rows_per_page);


    //echo htmlentities($sql);

} else {
    $activity_calendar_rst=$con->execute($sql);

    $activity_calendar_data = array();

    if($activity_calendar_rst) {

        $i=0;
        while (!$activity_calendar_rst->EOF) {
            $activity_calendar_data[$i]['activity_id'] = $activity_calendar_rst->fields['activity_id'];
            $activity_calendar_data[$i]['scheduled_at'] = $activity_calendar_rst->fields['scheduled_at'];
            $activity_calendar_data[$i]['ends_at'] = $activity_calendar_rst->fields['ends_at'];
            $activity_calendar_data[$i]['contact_id'] = $activity_calendar_rst->fields['contact_id'];
            $activity_calendar_data[$i]['activity_title'] = $activity_calendar_rst->fields['activity_title'];
            $activity_calendar_data[$i]['activity_description'] = $activity_calendar_rst->fields['activity_description'];
            $activity_calendar_data[$i]['user_id'] = $activity_calendar_rst->fields['user_id'];

            $activity_calendar_rst->movenext();
            $i++;
        }
    }

    $calendar_js_functions = $calendar->GetCalendarJS();

    $widget = $calendar->Render($activity_calendar_data);

    echo $widget['result'];
}

//echo $widget['features'];

/*

$calendar=require_once('../calendar/agenda/agenda_index.php');
echo $calendar['result'];

*/
$con->close();
?>

    </form>

    </div>
        <!-- right column //-->
    <?php
    /*
    <div id="Sidebar">

    </div>
    */
    //no sidebar here yet, so make the table expand
    ?>
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

    echo $calendar_js_functions;

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.109  2005/05/06 22:18:24  daturaarutad
 * fixed wrong var names for var_watcher; now resetting calendar_start_date whenever search dates are changed
 *
 * Revision 1.108  2005/05/06 15:43:23  braverock
 * - changed labels for scheduled_on, ends_on, and title/summay for consistency
 *
 * Revision 1.107  2005/05/05 17:58:03  daturaarutad
 * updated to reflect changes in CalendarView interface
 * mostly fixed 'week start' problem...still an issue with week view sometimes
 * monthly view should be working fine
 * no longer show list view if in calendar view
 *
 * Revision 1.106  2005/05/04 19:26:55  braverock
 * - changed labels for scheduled_on, ends_on, and title/summay for consistency
 *
 * Revision 1.105  2005/05/02 17:11:55  daturaarutad
 * fixed a bug in the date restriction for list view
 *
 * Revision 1.104  2005/04/29 17:44:11  daturaarutad
 * made form printable
 *
 * Revision 1.103  2005/04/29 16:12:22  daturaarutad
 * updated to use GUP_Pager for export
 *
 * Revision 1.102  2005/04/14 20:46:34  daturaarutad
 * fixed query syntax error
 *
 * Revision 1.101  2005/04/14 20:30:45  daturaarutad
 * added calendar to some.php
 *
 * Revision 1.100  2005/03/15 22:34:45  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.99  2005/03/07 16:26:59  daturaarutad
 * removed some vestigal sort column stuff (that moved to pager)
 * added sql_sort_columns to speed up column sorting for concat'ed columns
 * disabled the opportunity.probability for now
 *
 * Revision 1.98  2005/03/04 17:57:13  daturaarutad
 * set Due to the default_sort
 *
 * Revision 1.97  2005/03/02 15:15:44  daturaarutad
 * removed redundant mail merge button and corresponding query
 *
 * Revision 1.96  2005/03/01 21:56:45  daturaarutad
 * set the css_classname for right-align on numerics in pager
 *
 * Revision 1.95  2005/02/28 22:18:38  daturaarutad
 * changed columns to be index_sql so that the pager knows it doesnt have to get the whole data set
 *
 * Revision 1.94  2005/02/25 03:41:02  daturaarutad
 * updated to use GUP_Pager class
 *
 * Revision 1.93  2005/02/15 20:52:00  vanmer
 * - removed broken cast statement from activities user list sql
 * - added order by statement to activities user list sql
 *
 * Revision 1.92  2005/02/15 15:17:30  ycreddy
 * Changed the SQL query that fetches users to make it compatible with SQL Server
 *
 * Revision 1.91  2005/02/14 22:04:05  vanmer
 * - updated to reflect ACL speed updates
 *
 * Revision 1.90  2005/02/10 01:08:51  braverock
 * - change order of LEFT OUTER JOIN query for MS SQL server portability
 *
 * Revision 1.89  2005/02/09 22:24:03  braverock
 * - localized pager column headers
 * - de-localized AS clauses in SQL
 *
 * Revision 1.88  2005/01/26 22:37:15  vanmer
 * - altered query to allow activities table to always directly preceed LEFT OUTER JOINS, to fix sql server error
 *
 * Revision 1.87  2005/01/25 22:11:59  daturaarutad
 * updated to use new XRMS_Pager and Pager_Columns to implement selectable columns
 *
 * Revision 1.86  2005/01/22 15:07:24  braverock
 * - add sort order to activity_types menu
 *
 * Revision 1.85  2005/01/13 22:35:34  vanmer
 * - removed HOUR sql code to allow compliance with other database servers
 *
 * Revision 1.84  2005/01/13 17:54:45  vanmer
 * - ACL restriction on activity list when searching
 *
 * Revision 1.83  2005/01/11 01:06:51  braverock
 * - remove lpad database query and replace with hard-coded select statements
 * - lpad isn't portable SQL, and we didn't need two extra queries there to make a list
 *
 * Revision 1.82  2005/01/07 15:55:56  braverock
 * - convert search params for UNION to string, int types
 *   enhances SQL portability across databases that require compatible types
 * - added check for old strings to not break saved searches
 *
 * Revision 1.81  2005/01/07 14:20:54  neildogg
 * - Saved search was being incorrectly called, moved restrictions to deletion only
 *
 * Revision 1.80  2004/12/27 13:15:54  braverock
 * - add additional database error handling to several of the search parameters
 * - fix problem with CURRENT_USER on some browsers
 * - localize 'Not Set' and 'Current User' strings
 *
 * Revision 1.79  2004/12/26 21:58:18  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.78  2004/12/26 19:41:06  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.77  2004/12/26 15:58:19  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.76  2004/12/26 13:31:43  braverock
 * - fix string quoting to resolve problems with French translation
 *
 * Revision 1.75  2004/12/24 15:57:49  braverock
 * - expand to use ContentFullWidth with new CSS
 *
 * Revision 1.74  2004/12/23 16:16:24  neildogg
 * - Adjusted since advanced search is always submitted as a variable
 *
 * Revision 1.73  2004/12/20 22:16:06  neildogg
 * - Saves advanced search status
 *
 * Revision 1.72  2004/12/20 15:51:37  neildogg
 * - Because you can't order by a string
 *
 * Revision 1.71  2004/12/20 14:45:55  neildogg
 * - Changed user table to a left join to allow an empty user search
 *
 * Revision 1.70  2004/12/18 21:34:27  neildogg
 * Added empty user and current user search (great for saved searches)
 *
 * Revision 1.69  2004/12/18 21:21:31  neildogg
 * Added advanced search by Campaign
 *
 * Revision 1.68  2004/12/18 20:25:47  neildogg
 * Added Search by All Dates
 *
 * Revision 1.67  2004/12/18 20:06:50  neildogg
 * Added Search by Scheduled/Due and made ON date search accurate
 *
 * Revision 1.66  2004/11/26 17:26:19  braverock
 * - quote order by clause for i18n
 *
 * Revision 1.65  2004/11/26 17:22:00  braverock
 * - quote order by clause for i18n
 *
 * Revision 1.64  2004/11/26 15:10:35  braverock
 * - add translate_menu call to activity types menu for i18n
 *
 * Revision 1.63  2004/11/12 15:30:12  braverock
 * - added closing } to resolve parse error
 *
 * Revision 1.62  2004/11/12 15:25:09  braverock
 * - fixed short php tags
 * - cleaned up some other code formatting
 *
 * Revision 1.61  2004/10/29 15:34:11  introspectshun
 * - Added option to search activities 'on' a specific date
 *
 * Revision 1.60  2004/09/21 18:21:28  introspectshun
 * - Changed table order in main query FROM clause
 * - Join fails on MSSQL otherwise
 *
 * Revision 1.59  2004/08/25 15:49:39  introspectshun
 * - Fixed errant variable name
 *
 * Revision 1.58  2004/08/25 15:01:17  neildogg
 * - Searches local time with proper constraints
 * - Saves temporary searches properly
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
 *   (for all time zones)
 *
 * Revision 1.53  2004/08/16 14:39:23  maulani
 * - Override ADODB_Pager class for activities to allow customization
 * - Customization still todo
 *
 * Revision 1.52  2004/08/05 18:42:56  neildogg
 * - Date offset now compatible thanks to
 *   advice from David Rogers
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
 *   instead of assigning it an empty string
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
 * - Removed MySQL-centric date functions and conditional block
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
 *   Any improvements welcome
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
 *   resolves SF bug 9888931 reported by braverock
 *
 * Revision 1.31  2004/07/11 12:32:48  braverock
 * - eliminate manual table generation in favor of pager object
 * - rearrange column order based on input from Walt Pennington
 *
 * Revision 1.30  2004/07/10 12:24:59  braverock
 * - fixed undefined activity_id
 * - fixed misdefined activity_type_pretty_name
 * - fixes SF bugs reported by cpsource
 *
 * Revision 1.29  2004/07/10 12:14:53  braverock
 * - applied patch for undefined variables
 * - modified from SF patch 979124 supplied by cpsource
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
