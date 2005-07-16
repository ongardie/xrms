<?php
/**
* activities-widget.php
*
* Consolidated activities pager and calendar widget creator
*
* @author Justin Cooper <justin@braverock.com>
*
*/

global $include_directory;

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');
require_once($include_directory . 'classes/Pager/Session_Var_Watcher.php');

require_once('../activities/activities-pager-functions.php');
require_once('../calendar/Calendar_View.php');

/**
* Given a $con and search terms, returns an activities pager widget
*
* @param object The ADOdb connection object
* @param array Assoc Array of search terms
* @param string name from <form name=XXX>
* @param integer The User's session
* @param string return_url for links
* @param string End rows to add to the bottom of the pager eg "<tr><td colspan=23><input type=button></td></tr>\n";
* @param array List of default columns (used as default for selectable columns)
* @return string The pager widget.  must be placed inside a form to be active!
*/
function GetActivitiesWidget($con, $search_terms, $form_name, $caption, $session_user_id, $return_url, $extra_where='', $end_rows='', $default_columns = null, $show_mini_search = true) {

global $http_site_root;

// This should probably be a system preference.
$description_substring_length = 80;


// This is the name of the CGI field that will be used for storing the calendar's view date
$calendar_date_field = 'calendar_start_date';

getGlobalVar($activities_widget_type, 'activities_widget_type');
if(!$activities_widget_type) $activities_widget_type = 'list';
getGlobalVar($calendar_range, 'calendar_range');
getGlobalVar($before_after, 'before_after');
getGlobalVar($search_date, 'search_date');
getGlobalVar($start_end, 'start_end');


$mini_search_widget_name = 'activities_widget_mini_search';

if($show_mini_search) {
    getGlobalVar($search_enabled, $mini_search_widget_name.'_status');

    if('enable' == $search_enabled) {
        $search_terms = GetMiniSearchTerms($mini_search_widget_name, $search_terms);
    } else {
        $caption .= ' &nbsp;<input type="button" class="button" onclick="document.getElementById(\'' . $mini_search_widget_name . '\').style.display=\'block\';" value="' . _('Filter Activities') . '">';
    }

    $mini_search_widget = GetMiniSearchWidget($mini_search_widget_name, $search_terms, $search_enabled, $form_name);

}


if('list' != $activities_widget_type) {

/*  Calendar:
    -if this is the first pass, the calendar date should be set based on the search date
    -if the user pressed 'next month' or whatever, calendar date should not be affected
    -if a search field has changed, the date should be reset based on search date

    Pass in a (recommended) search date to the calendar, and a flag on whether or not to use it...

    GetActivitiesWidget:
        -use VarWatcher to determine whether or not to pass in a date to constructor
        (this date should be the actual (monday) date).

    Calendar object:
        -if a date is passed in as a param, use that
        -elseif the session/cgi var is set, use that....(user is actively viewing a calendar with hidden date field)
        -else use today's date.

    GetActivitiesWidget:
        -use Calendar object to create sql_offset for query
*/
    if(!$calendar_range) {
        $calendar_range = 'month';
        // This is so that Session_Var_Watcher will see a calendar range as being set even if it wasn't set the first time.
        $_POST['calendar_range'] = $calendar_range;
    }

    $initial_calendar_date = GetInitialCalendarDate($calendar_range, $before_after, $search_date, $start_end);

    //create the Calendar object here, so that we can use it to generate the SQL offset
    $calendar = new CalendarView($con, $form_name, $initial_calendar_date, 'calendar_start_date', $calendar_range);

    // Add Calendar date filtering (only query for visible date range)
    $calendar_offset_sql = $calendar->GetCalendarSQLOffset($con, $calendar_range, $calendar_start_date);
}


$widget = '';




// build the query based upon $search_terms

$select = "SELECT (CASE WHEN (activity_status = 'o') AND (a.ends_at < " . $con->DBTimeStamp(time()) . ") THEN 1 ELSE 0 END) AS is_overdue, "
  ." at.activity_type_pretty_name AS type, "
  . $con->Concat("'<a id=\"'", "cont.last_name", "'_'" ,"cont.first_names","'\" href=\"../contacts/one.php?contact_id='", "cont.contact_id", "'\">'", "cont.first_names", "' '", "cont.last_name", "'</a>'") . " AS contact, "

  . "'$return_url' as return_url, "
  . $con->substr."(activity_description, 1, $description_substring_length)"."AS description_brief, "
  . $con->SQLDate('Y-m-d','a.scheduled_at') . " AS scheduled, "
  . $con->SQLDate('Y-m-d','a.ends_at') . " AS due, "
  . "u.username AS owner, u.user_id, a.activity_id, activity_status, a.on_what_table, a.on_what_id, "
  // these fields are pulled in to speed up the pager sorting (using sql_sort_column)
  . "cont.last_name, cont.first_names, activity_title, a.scheduled_at, a.ends_at, cp.case_priority_pretty_name, rt.resolution_short_name ";

$from = array('activities a');

$joins = "
INNER JOIN activity_types at ON a.activity_type_id = at.activity_type_id
LEFT OUTER JOIN contacts cont ON a.contact_id = cont.contact_id
LEFT OUTER JOIN users u ON a.user_id = u.user_id
LEFT OUTER JOIN activity_participants ON a.activity_id=activity_participants.activity_id
LEFT OUTER JOIN contacts part_cont ON part_cont.contact_id=activity_participants.contact_id
LEFT OUTER JOIN case_priorities cp ON a.activity_priority_id=cp.case_priority_id
LEFT OUTER JOIN activity_resolution_types rt ON a.activity_resolution_type_id=rt.activity_resolution_type_id";

//$where = " WHERE $extra_where ";

$where = " WHERE a.activity_record_status = 'a'
  $extra_where";


// search criteria filtering
$criteria_count = 0;

if (strlen($search_terms['title']) > 0) {
    $criteria_count++;
    $where .= " and a.activity_title like " . $con->qstr('%' . $search_terms['title'] . '%', get_magic_quotes_gpc());
}

if (strlen($search_terms['contact']) > 0) {
    $criteria_count++;
    $where .= " and ((cont.last_name like " . $con->qstr('%' . $search_terms['contact'] . '%', get_magic_quotes_gpc()) . ") OR (cont.first_names like " . $con->qstr('%' . $search_terms['contact'] . '%', get_magic_quotes_gpc()) . "))";
}

if (strlen($search_terms['contact_id'])) {
    $criteria_count++;
    $where .= " and ((cont.contact_id = {$search_terms['contact_id']}) OR (activity_participants.contact_id={$search_terms['contact_id']}))";
}

// join companies if any company-related search terms are enabled.
if (strlen($search_terms['company']) > 0 || strlen($search_terms['company_id']) ||
    (strlen($search_terms['time_zone_between']) and strlen($search_terms['time_zone_between2']))) {

    $select .= ', ' . $con->Concat("'<a id=\"'", "c.company_name", "'\" href=\"../companies/one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS company, c.company_name ";
    $extra_group_by = ", c.company_name,c.company_id";
    array_unshift($from, 'companies c', 'addresses addr');

    $where .= " AND a.company_id = c.company_id ";
    $where .= "AND c.default_primary_address=addr.address_id ";

}

if (strlen($search_terms['company']) > 0) {
    $criteria_count++;
    $where .= " and c.company_name like " . $con->qstr('%' . $search_terms['company'] . '%', get_magic_quotes_gpc());
}

if (strlen($search_terms['company_id'])) {
    $criteria_count++;
    $where .= " and c.company_id = " . $search_terms['company_id'];
}
if (strlen($search_terms['user_id']) > 0) {
    $criteria_count++;
    if($search_terms['user_id'] == '-2') {
        //Not Set
        $where .= " and a.user_id = 0";
    }
    elseif($search_terms['user_id'] == '-1') {
        //Current User
        $where .= " and a.user_id = $session_user_id ";
    }
    elseif($search_terms['user_id'] == 'no') {
        //Not Set
        $where .= " and a.user_id = 0";
    }
    elseif($search_terms['user_id'] == 'cu') {
        //Current User
        $where .= " and a.user_id = $session_user_id ";
    }
    else {
        $where .= " and a.user_id = {$search_terms['user_id']} ";
    }
}

if (strlen($search_terms['activity_type_id']) > 0) {
    $criteria_count++;
    $where .= " and a.activity_type_id = " . $search_terms['activity_type_id'] . " ";
}

if (strlen($search_terms['activity_status']) > 0) {
    $criteria_count++;
    $where .= " and a.activity_status = " . $search_terms['activity_status'] . " ";

}

if (strlen($search_terms['completed']) > 0 and $search_terms['completed'] != "all") {
    $criteria_count++;
    $where .= " and a.activity_status = " . $con->qstr($search_terms['completed'], get_magic_quotes_gpc());
}




// date filter code.  dates can come in via start_end + search_date or day_diff (from a saved search)

if ($search_terms['day_diff'] ) {

if($search_terms['start_end'] == 'start') {
    $field = 'scheduled_at';
} else {
    $field = 'ends_at';
}

$offset_sql = '';

// (introspectshun) Updated to use portable database code; removed MySQL-centric date functions
// This will work for positive and negative intervals automatically, so no need for conditional assignment of offset
// (a search for today will add an interval of '0 days')
// Warning: if a user wants to save a search for a particular date, this won't allow it, as it defaults to recurring search
if(isset($search_terms['day_diff']) and $search_terms['day_diff']) {
    $search_terms['search_date'] = date('Y-m-d', time() + ($search_terms['day_diff'] * 86400));
} else {
    if ( !$search_terms['search_date'] ) {
        $search_terms['search_date'] = date('Y-m-d', time());
    }
    $search_terms['day_diff'] = round((strtotime($search_terms['search_date']) - strtotime(date('Y-m-d', time()))) / 86400);
}


// first set up $offset_sql for before/after search_terms['search_date']
if (strlen($search_terms['search_date']) > 0 && $search_terms['start_end'] != 'all') {
    $criteria_count++;

    if (!$search_terms['before_after']) {
        // before
        $offset_end = $con->OffsetDate($search_terms['day_diff']);
        $offset_sql .= " and a.$field < $offset_end";
    } elseif ($search_terms['before_after'] === 'after') {
        // after
        $offset_start = $con->OffsetDate($search_terms['day_diff']);
        $offset_sql .= " and a.$field > $offset_start";
    } elseif ($search_terms['before_after'] === 'on') {
        // same query for list and calendar views
        $offset_start = $con->OffsetDate($search_terms['day_diff']);
        $offset_end = $con->OffsetDate($search_terms['day_diff']+1);
        // midnight to midnight
        $offset_sql .= " and a.$field > $offset_start and a.$field < $offset_end";
    }

    $where .= $offset_sql;
}
}


if (strlen($calendar_offset_sql) > 0) {
    $criteria_count++;
    $where .= $calendar_offset_sql;
}


if(strlen($search_terms['time_zone_between']) and strlen($search_terms['time_zone_between2'])) {
    update_daylight_savings($con);
    $now = time();
    $now_array=localtime($now, true);
    $hour=$now_array['tm_hour'];
    array_unshift($from, 'time_daylight_savings tds');
    $where .= " and addr.daylight_savings_id = tds.daylight_savings_id";

    $where .= " and ($hour + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") >= " . $search_terms['time_zone_between'];
    $where .= " and ($hour + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") <= " . $search_terms['time_zone_between2'];
}

if($search_terms['opportunity_status_id']) {
    array_unshift($from, 'opportunities o');
    $where .= " and a.on_what_table='opportunities' and a.on_what_id=o.opportunity_id and o.opportunity_status_id=" . $search_terms['opportunity_status_id'];
}

if($search_terms['division_id']) {
    array_unshift($from, 'opportunities o', 'cases cas');
    // extra_where is passed in for the division where clause.
}



if($search_terms['on_what_table']) {
    $where .= " AND a.on_what_table='{$search_terms['on_what_table']}' and a.on_what_id={$search_terms['on_what_id']}";
}

if($search_terms['campaign_id']) {
    $where .= " AND a.on_what_table='campaigns' AND a.on_what_id=" . $search_terms['campaign_id'];
}

// acl filtering
$list=acl_get_list($session_user_id, 'Read', false, 'activities');

if ($list) {
    if ($list!==true) {
        $list=implode(",",$list);
        $where .= " and a.activity_id IN ($list) ";
    }
} else {
    $where .= ' AND 1 = 2 ';
}

// MS-SQL server requires that when using GROUP BY, all fields in select clause must be mentioned
$group_by .=" GROUP BY a.activity_id, cont.first_names, cont.last_name, cont.contact_id, a.ends_at, a.scheduled_at, a.activity_status, a.activity_title, u.username, u.user_id, at.activity_type_pretty_name, a.on_what_table, a.on_what_id, a.activity_description, rt.resolution_short_name, cp.case_priority_pretty_name $extra_group_by";


$from_list = join(', ', $from);



$activity_sql = "$select FROM $from_list $joins $where $group_by";

$count_sql = "SELECT count(distinct a.activity_id) FROM $from_list $joins $where";

// end build query

// save query for mail merge
$_SESSION["search_sql"] = "$select FROM $from_list $joins $where";




//echo '<pre>' . htmlentities($activity_sql) . '</pre>';



if('list' != $activities_widget_type) {

    // begin calendar stuff
    $activity_calendar_rst = $con->execute($activity_sql);

    if($activity_calendar_rst) {

        $i=0;

        while (!$activity_calendar_rst->EOF) {

            $activity_calendar_data[$i]['activity_id'] = $activity_calendar_rst->fields['activity_id'];
            $activity_calendar_data[$i]['scheduled_at'] = $activity_calendar_rst->fields['scheduled_at'];
            $activity_calendar_data[$i]['ends_at'] = $activity_calendar_rst->fields['ends_at'];
            $activity_calendar_data[$i]['contact_id'] = $activity_calendar_rst->fields['contact_id'];
            $activity_calendar_data[$i]['activity_title'] = $activity_calendar_rst->fields['activity_title'];
            $activity_calendar_data[$i]['description_brief'] = $activity_calendar_rst->fields['description_brief'];
            $activity_calendar_data[$i]['user_id'] = $activity_calendar_rst->fields['user_id'];

            $activity_calendar_rst->movenext();
            $i++;
        }

    } else {
        db_error_handler($con, $activity_sql);
    }

    $search_date = date('Y-m-d');

    switch($activities_view_type) {
        case 'week':
            // align it to the week's start day (e.g. Monday)
            if(empty($calendar_start_date)) {
                $calendar_start_date = CalendarView::GetWeekStart($start_date, 'Monday');
            } else {
                $calendar_start_date = CalendarView::GetWeekStart($calendar_start_date, 'Monday');
            }
            break;
        case 'month':
            if(empty($calendar_start_date)) {
                $calendar_start_date = date("Y-m-", strtotime($date_modifier . $search_date));
                $calendar_start_date .= '01';
            }
            break;
    }

    $widget = $calendar->Render($activity_calendar_data);
    // end calendar code

} else {
    global $system_rows_per_page;

    $thread_query_list = "select activity_title, activity_id from activities where thread_id is not null group by thread_id order by activity_id";

    $thread_query_select = $activity_sql . 'AND thread_id = XXX-value-XXX';


    $columns = array();
    $columns[] = array('name' => _("Overdue"), 'index_sql' => 'is_overdue');
    $columns[] = array('name' => _("Type"), 'index_sql' => 'type');
    $columns[] = array('name' => _("Contact"), 'index_sql' => 'contact', 'sql_sort_column' => 'cont.last_name,cont.first_names', 'type' => 'url');
    $columns[] = array('name' => _("Summary"), 'index_sql' => 'title', 'sql_sort_column' => 'activity_title', 'type' => 'url');
    $columns[] = array('name' => _("Description"), 'index_calc' => 'description_brief', 'sql_sort_column' => 'activity_description', 'type' => 'url');
    $columns[] = array('name' => _("Priority"), 'index_sql' => 'case_priority_pretty_name', 'sql_sort_column'=>'a.activity_priority_id');
    $columns[] = array('name' => _("Scheduled Start"), 'index_sql' => 'scheduled', 'sql_sort_column' => 'a.scheduled_at');
    $columns[] = array('name' => _("Scheduled End"), 'index_sql' => 'due', 'default_sort' => 'desc', 'sql_sort_column' => 'a.ends_at');
    $columns[] = array('name' => _("Company"), 'index_sql' => 'company', 'sql_sort_column' => 'c.company_name', 'type' => 'url');
    $columns[] = array('name' => _("Owner"), 'index_sql' => 'owner');
    //$columns[] = array('name' => _("Thread"), 'index_sql' => 'thread', 'group_query_list' => $thread_query_list, 'group_query_select' => $thread_query_select);
    $columns[] = array('name' => _("About"), 'index_calc' => 'activity_about');
    $columns[] = array('name' => _("Resolution"), 'index_sql' => 'resolution_short_name', 'sql_sort_column'=>'a.activity_resolution_type_id');

    // selects the columns this user is interested in
    $pager_columns = new Pager_Columns('ActivitiesPager'.$form_name, $columns, $default_columns, $form_name);
    $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
    $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

    $columns = $pager_columns->GetUserColumns('default');

    global $activity_column_names;
    $activity_column_names = $pager_columns->GetUserColumnNames();

    // save sql for browse button
    $sql_session_var = $form_name . '_activities_sql';
    $_SESSION[$sql_session_var] = $activity_sql;


    $endrows = $end_rows .
                "<tr><td class=widget_content_form_element colspan=20>
                $pager_columns_button
                <input type=button class=button onclick=\"javascript: document.$form_name.activities_widget_type.value='calendar'; document.$form_name.submit();\" name=\"calendar_view\" value=\"" . _("Calendar View") ."\">
                <input type=button class=button onclick=\"javascript: exportIt();\" value=\"" . _("Export") ."\">
                <input type=button class=button onclick=\"javascript: bulkEmailActivity();\" value=\"" . _("Mail Merge") . "\">
                <input type=button class=button onclick=\"javascript: location.href='$http_site_root/activities/browse-next.php?browse=true&sql_session_var=$sql_session_var';\" value=\"" . _("Browse") . "\"></td>
                </tr>\n";

    $pager = new GUP_Pager($con, $activity_sql, 'GetActivitiesPagerData', $caption, $form_name, 'ActivitiesPager', $columns, false, true);
    $pager->AddEndRows($endrows);
    $pager->SetCountSQL($count_sql);
    $widget['content'] =  $pager_columns_selects .  $pager->Render($system_rows_per_page);
}

$widget['content'] .= "<input type=hidden name=activities_widget_type value=\"$activities_widget_type\">\n";
$widget['content'] .= "<input type=hidden name=calendar_range value=\"$calendar_range\">\n";

if($show_mini_search)
    $widget['content'] = $mini_search_widget . $widget['content'];

$widget['content'] .= '
<script language="JavaScript" type="text/javascript">
<!--
    function bulkEmailActivity() {
        document.'.$form_name.'.action = "../email/email.php";
        document.'.$form_name.'.submit();
    }
//-->
</script>'."\n";

return $widget;

} // end function




/**
* GetInitialCalendarDate
* Based on search
* -use GetInitialCalendarDate to determine whether or not to pass in a date to constructor
*       (this date should be the actual date).
*
*/
function GetInitialCalendarDate($calendar_range, $before_after, $search_date) {

//echo" GetInitialCalendarDate($calendar_range, $before_after, $search_date) ";

    // watch CGI vars to see if we should reset the calendar date
    $var_watcher = new SessionVarWatcher('Activities');
    $var_watcher->RegisterCGIVars(array('activities_widget_type', 'calendar_range', 'search_date', 'before_after'));

    // set calendar_start_date from search_date if it's not set already
    if($var_watcher->VarsChanged()) {

        //echo "vars changed or new...setting up initial calendar start date<br>";

        // before_after is only relevant if there is a search date.
        if($search_date && !$before_after) {
            // before
            $date_modifier = '-1 ' . $calendar_range . ' ';
        } else {
            $date_modifier = '';
        }

        if(!$search_date) $search_date=date('Y-m-d', time());

        switch($calendar_range) {
            case 'day':
                $initial_calendar_date = date("Y-m-d", strtotime($date_modifier . $search_date));
                break;
            case 'week':
                // align it to the week's start day (e.g. Monday)
                if(empty($initial_calendar_date)) {
                    $initial_calendar_date = CalendarView::GetWeekStart($start_date, 'Monday');
                } else {
                    $initial_calendar_date = CalendarView::GetWeekStart($initial_calendar_date, 'Monday');
                }
                break;
            case 'month':
                //$initial_calendar_date = date("Y-m-", strtotime($date_modifier . $search_date));
                $initial_calendar_date = date("Y-m-", strtotime($search_date));
                $initial_calendar_date .= '01';
                break;
            case 'year':
                $initial_calendar_date = date("Y-", strtotime($date_modifier . $search_date));
                $initial_calendar_date .= '01-01';
                break;
        }
        //echo "initial_calendar_date not set, setting to $initial_calendar_date (search date is $search_date)<br>";

        // this is for the calendar widget
        $_POST['initial_calendar_date'] = $initial_calendar_date;
    }
    return $initial_calendar_date;
}


function GetNewActivityWidget($con, $session_user_id, $return_url, $on_what_table, $on_what_id, $company_id, $contact_id) {

global $http_site_root;

$form_name = 'NewActivity';

if(!$company_id) $company_id = 0;



// create menu of users
$user_menu = get_user_menu($con, $session_user_id);

// create menu of activity types
$sql = "SELECT activity_type_pretty_name, activity_type_id
        FROM activity_types
        WHERE activity_type_record_status = 'a'
        ORDER BY sort_order, activity_type_pretty_name";
$rst = $con->execute($sql);
if ($rst) {
    $activity_type_menu = $rst->getmenu2('activity_type_id', '', false,false,0, 'style="font-size: x-small; border: outset; width: 80px;"');
    $rst->close();
}


// create menu of contacts
if($company_id) {
    $sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, contact_id
            FROM contacts
            WHERE company_id = $company_id
            AND contact_record_status = 'a'
            ORDER BY last_name";

    $rst = $con->execute($sql);
    if ($rst) {
        $contact_menu = $rst->getmenu2('contact_id', $contact_id, true, false, 0, 'style="font-size: x-small; border: outset; width: 80px;"');
        $rst->close();
    } else {
        db_error_handler ($con, $sql);
    }
}

$hidden = '';

if($on_what_table && $on_what_id) {
    $hidden .= "<input type=hidden name=on_what_table value=\"$on_what_table\">";
    $hidden .= "<input type=hidden name=on_what_id value=\"$on_what_id\">";
}

$hidden .= "<input type=hidden name=company_id value=\"$company_id\">";

if($contact_id) {
    $hidden .= "<input type=hidden name=contact_id value=\"$contact_id\">";
}

$ret = "
<script language=\"JavaScript\" type=\"text/javascript\">
<!--
function markComplete() {
    document.$form_name.activity_status.value = \"c\";
    document.$form_name.submit();
}
//-->
</script>


        <!-- activities //-->
        <form name=\"$form_name\" action=\"$http_site_root/activities/new-2.php\" method=post>
        <input type=hidden name=return_url value=\"$return_url\">
        $hidden
        <input type=hidden name=activity_status value=\"o\">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5>". _("New Activity") . "</td>
            </tr>
            <tr>
                <td class=widget_label>" . _("Summary") . "</td>
                <td class=widget_label>" . _("User") . "</td>
                <td class=widget_label>" . _("Type") . "</td> ".
                ($contact_menu ? "<td class=widget_label>" . _("Contact") . "</td>" : "") ."
                <td colspan=2 class=widget_label>" . _("Scheduled End") . "</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text name=activity_title></td>
                <td class=widget_content_form_element size=10>$user_menu</td>
                <td class=widget_content_form_element size=10 >$activity_type_menu</td>" .
                ($contact_menu ? "<td class=widget_content_form_element size=10>$contact_menu</td>" : "") ."
                <td colspan=2 class=widget_content_form_element size=20>
                    <input type=text ID=\"f_date_new_activity\" name=ends_at value=\"" . date('Y-m-d H:i:s') . "\">
                    <img ID=\"f_trigger_new_activity\" style=\"CURSOR: hand\" border=0 src=\"../img/cal.gif\">" .
                    render_create_button(_("Add")) .
                    render_create_button(_("Done"),'button',"javascript: markComplete();") . "
                </td>
            </tr>
        </table>
        </form>
        <script language=\"JavaScript\" type=\"text/javascript\">
            Calendar.setup({
                    inputField     :    \"f_date_new_activity\",      // id of the input field
                    ifFormat       :    \"%Y-%m-%d %H:%M:%S\",       // format of the input field
                    showsTime      :    true,            // will display a time selector
                    button         :    \"f_trigger_new_activity\",   // trigger for the calendar (button ID)
                    singleClick    :    false,           // double-click mode
                    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                    align          :    \"Bl\"           // alignment (defaults to \"Bl\")
            });
        </script>
";

return $ret;
}

/**
* This function processes the form data from the Mini Search Widget and packs it into search_terms,
* similar to what is done in activities/some.php
*/
function GetMiniSearchTerms($widget_name, $search_terms) {

    getGlobalVar($search_terms['title'], $widget_name.'_activity_title');
    getGlobalVar($search_terms['contact'], $widget_name.'_activity_contact');
    getGlobalVar($search_terms['start_end'], $widget_name.'_start_end');
    getGlobalVar($search_terms['before_after'], $widget_name.'_before_after');
    getGlobalVar($search_terms['search_date'], $widget_name.'_search_date');

    return $search_terms;
}

function GetMiniSearchWidget($widget_name, $search_terms, $search_enabled, $form_name) {

    if('enable' == $search_enabled) {
        $title          = $search_terms['title'];
        $contact        = $search_terms['contact'];
        $start_end      = $search_terms['start_end'];
        $before_after   = $search_terms['before_after'];
        $search_date    = $search_terms['search_date'];
    }

    $ret =
    "<div id=$widget_name>

        <input type=hidden name={$widget_name}_status value='$search_enabled'>

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5>". _("Filter Activities") . "</td>
            </tr>
            <tr>
                <td class=widget_label>" . _("Summary") . "</td>
                <td class=widget_label>" . _("Contact") . "</td>
                <td class=widget_label>" . _("Search By Date") . "</td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text size=12 name={$widget_name}_activity_title value=\"$title\"></td>
                <td class=widget_content_form_element><input type=text size=12 name={$widget_name}_activity_contact value=\"$contact\"></td>
                <td class=widget_content_form_element>
                <select name=\"{$widget_name}_start_end\">
                    <option value=\"end\"" . ($start_end == 'end' ?  ' selected' : '' ). '>' . _("Scheduled End") . "</option>
                    <option value=\"start\"" . ($start_end == 'start' ?  ' selected' : '' ). '>' . _("Scheduled Start") . "</option>
                    <option value=\"all\"" . ($start_end == 'all' ?  ' selected' : '' ). '>' . _("All Dates") . "</option>
                </select>

                <select name=\"{$widget_name}_before_after\">
                    <option value=\"\"" . (!$before_after ?  ' selected' : '' ). '>' . _("Before") . "</option>
                    <option value=\"after\"" . ($before_after == 'after' ?  ' selected' : '' ). '>' . _("After") . "</option>
                    <option value=\"on\"" . ($before_after == 'on' ?  ' selected' : '' ). '>' . _("On") . "</option>
                </select>

                    <input type=text ID=\"f_date_{$widget_name}_search_date\" name={$widget_name}_search_date value=\"$search_date\">
                    <img ID=\"f_trigger_{$widget_name}_search_date\" style=\"CURSOR: hand\" border=0 src=\"../img/cal.gif\">
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=5>
                    <input type=button class=button onclick=\"document.$form_name.{$widget_name}_status.value='enable'; document.$form_name.submit();\" value=\"" . _('Filter Activities') . "\">
                    <input type=button class=button onclick=\"ClearActivitiesFilter()\" value=\"" . _('Clear Filter') . "\">
                </td>
            </tr>
        </table>

    </div>

    <script language=\"JavaScript\" type=\"text/javascript\">

        // hide the widget to start with
        document.getElementById('{$widget_name}').style.display = \"" . ('enable' == $search_enabled ? 'block' : 'none') . "\";

        function ClearActivitiesFilter() {
            document.$form_name.{$widget_name}_status.value='disable';
            document.$form_name.submit();
        }

        Calendar.setup({
                inputField     :    \"f_date_{$widget_name}_search_date\",      // id of the input field
                ifFormat       :    \"%Y-%m-%d\",       // format of the input field
                showsTime      :    true,            // will display a time selector
                button         :    \"f_trigger_{$widget_name}_search_date\",   // trigger for the calendar (button ID)
                singleClick    :    false,           // double-click mode
                step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                align          :    \"Bl\"           // alignment (defaults to \"Bl\")
            });

    </script>
    ";

    return $ret;
}

/**
* $Log: activities-widget.php,v $
* Revision 1.28  2005/07/16 00:19:25  daturaarutad
* add http_site_root to browse link
*
* Revision 1.27  2005/07/16 00:13:51  daturaarutad
* save activities_sql for browse button
*
* Revision 1.26  2005/07/11 15:50:23  ycreddy
* Fixes to date in the where clause and the Count Query
*
* Revision 1.24  2005/07/11 13:47:07  braverock
* - change 'Contact' search to use either first name or last name
* @todo fix so a search for 'First Last' will work as well, probably using split() php fn
*
* Revision 1.23  2005/07/10 20:24:45  daturaarutad
* changed $sql => $activity_sql in calendar code
*
* Revision 1.22  2005/07/10 20:22:13  daturaarutad
* implemented Mini Search for pager
*
* Revision 1.21  2005/07/08 19:40:34  braverock
* - remove unecessary Concat fn, as it didn't help MS SQL Server anyway
*
* Revision 1.20  2005/07/08 19:08:42  braverock
* - add explicit join on activity_types rather than implicit equijoin from the where clause
*
* Revision 1.19  2005/07/08 17:20:57  daturaarutad
* hopefully fixed query for MSSQL server; added not-yet-functional mini-search code
*
* Revision 1.18  2005/07/08 01:14:11  braverock
* - fix 'Mail Merge' quoting
* - fix BulkEmailActivity javascript
* - fix search_sql for mail merge
*
* Revision 1.16  2005/07/07 20:15:54  braverock
* - trim widths of drop-down menus in new activities widget for better screen formatting
*
* Revision 1.15  2005/07/07 19:47:59  daturaarutad
* re-add activity_description field
*
* Revision 1.14  2005/07/07 18:54:18  daturaarutad
* add handler for division_id (join opportunities and cases)
*
* Revision 1.13  2005/07/07 18:14:26  daturaarutad
* fix FROM list order to keep MSSQL happy; tidy up comments
*
* Revision 1.12  2005/07/07 17:33:32  braverock
* - move jscalendar_includes to start_page fn
*
* Revision 1.11  2005/07/07 16:31:06  daturaarutad
* add Calendar.setup code to new activity widget
*
* Revision 1.10  2005/07/07 03:37:38  daturaarutad
* temporarily disable thread_id column
*
* Revision 1.9  2005/07/07 03:33:09  daturaarutad
* added GetNewActivityWidget(); broke up query into pieces; now using $count_sql to speed up pagination
*
* Revision 1.8  2005/07/06 21:39:00  ycreddy
* Changes to SQL Query for SQL Server portability
*
* Revision 1.7  2005/06/30 18:00:23  daturaarutad
* moved creation of title html link to GetActivitiesPagerData and added popup/tooltip containing activity description; add on_what_table criteria to query; add description as available column in pager
*
* Revision 1.6  2005/06/30 04:40:23  vanmer
* - added extra joins and fields to display resolution type and activity priority on activities widget
*
* Revision 1.5  2005/06/29 17:14:38  daturaarutad
* remove User field (duplicate of owner)
*
* Revision 1.4  2005/06/28 20:10:35  daturaarutad
* removed results_view_type from param list; set $_SESSION[search_sql]
*
* Revision 1.3  2005/06/28 14:03:25  braverock
* - fix activity summary link
*
* Revision 1.2  2005/06/27 16:30:10  daturaarutad
* removed debug msgs
*
* Revision 1.1  2005/06/27 16:24:36  daturaarutad
* new file
*
*
*/
?>
