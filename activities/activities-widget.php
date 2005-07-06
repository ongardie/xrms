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
function GetActivitiesWidget($con, $search_terms, $form_name, $caption, $session_user_id, $return_url, $extra_where='', $end_rows='', $default_columns = null) {

// This should probably be a system preference.
$description_substring_length = 80;

$calendar_date_field = 'calendar_start_date';

getGlobalVar($activities_widget_type, 'activities_widget_type');
getGlobalVar($calendar_range, 'calendar_range');
getGlobalVar($before_after, 'before_after');
getGlobalVar($search_date, 'search_date');
getGlobalVar($start_end, 'start_end');

if(!$activities_widget_type) $activities_widget_type = 'list';


if('list' != $activities_widget_type) {

/*  -if this is the first pass, the calendar date should be set based on the search date
    -if the user pressed 'next month' or whatever, calendar date should not be affected
    -if a search field has changed, the date should be reset based on search date

    so it sounds like we need to pass in a (recommended) search date, and a flag on whether or not
    to use it...

    GetActivitiesWidget:
        -use VarWatcher to determine whether or not to pass in a date to constructor
        (this date should be the actual date).

    Calendar object:
        -if a date is passed in as a param, use that
        -elseif the session/cgi var is set, use that....
        -else use today's date.

    GetActivitiesWidget:
        -use Calendar object to create sql_offset
*/
    if(!$calendar_range) {
        $calendar_range = 'month';
        // This is so that Session_Var_Watcher will see a calendar range as being set even if it wasn't set the first time.
        $_POST['calendar_range'] = $calendar_range;
    }

    $initial_calendar_date = GetInitialCalendarDate($calendar_range, $before_after, $search_date, $start_end);

    //echo "init date is $initial_calendar_date<br>";

    //create the Calendar object here, so that we can use it to generate the SQL offset
    $calendar = new CalendarView($con, $form_name, $initial_calendar_date, 'calendar_start_date', $calendar_range);

    // Add Calendar date filtering (only query for visible date range)
    $calendar_offset_sql = $calendar->GetCalendarSQLOffset($con, $calendar_range, $calendar_start_date);
}

if(!$activities_widget_type) {
    $activities_widget_type = 'list';
}

$widget = '';


// build the query based upon $search_terms

$sql = "SELECT (CASE WHEN (activity_status = 'o') AND (ends_at < " . $con->DBTimeStamp(time()) . ") THEN 1 ELSE 0 END) AS is_overdue, "
  ." at.activity_type_pretty_name AS type, "
  . $con->Concat("'<a id=\"'", "cont.last_name", "'_'" ,"cont.first_names","'\" href=\"../contacts/one.php?contact_id='", "cont.contact_id", "'\">'", "cont.first_names", "' '", "cont.last_name", "'</a>'") . " AS contact, "

  . "'$return_url' as return_url, "

  //.	$con->substr."(activity_description, 1, $description_substring_length) AS description_brief, " 

  . $con->SQLDate('Y-m-d','a.scheduled_at') . " AS scheduled, "
  . $con->SQLDate('Y-m-d','a.ends_at') . " AS due, "
  . $con->Concat("'<a id=\"'", "c.company_name", "'\" href=\"../companies/one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS company, "
  . "u.username AS owner, u.user_id, a.activity_id, activity_status, a.on_what_table, a.on_what_id, "
  // these fields are pulled in to speed up the pager sorting (using sql_sort_column)
  . "cont.last_name, cont.first_names, activity_title, a.scheduled_at, a.ends_at, c.company_name, cp.case_priority_pretty_name, rt.resolution_short_name ";

$sql .= "FROM companies c, activity_types at, addresses addr, activities a ";

$sql .= "
LEFT OUTER JOIN contacts cont ON cont.contact_id = a.contact_id
LEFT OUTER JOIN users u ON a.user_id = u.user_id
LEFT OUTER JOIN activity_participants ON a.activity_id=activity_participants.activity_id
LEFT OUTER JOIN contacts part_cont ON part_cont.contact_id=activity_participants.contact_id 
LEFT OUTER JOIN case_priorities cp ON a.activity_priority_id=cp.case_priority_id
LEFT OUTER JOIN activity_resolution_types rt ON a.activity_resolution_type_id=rt.activity_resolution_type_id";
$sql .= " WHERE a.company_id = c.company_id $extra_where ";

$sql .= " AND a.activity_record_status = 'a'
  AND at.activity_type_id = a.activity_type_id
  AND c.default_primary_address=addr.address_id";


// search criteria filtering
$criteria_count = 0;

if (strlen($search_terms['title']) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_title like " . $con->qstr('%' . $search_terms['title'] . '%', get_magic_quotes_gpc());
}

if (strlen($search_terms['contact']) > 0) {
    $criteria_count++;
    $sql .= " and ((cont.last_name like " . $con->qstr('%' . $search_terms['contact'] . '%', get_magic_quotes_gpc()) . ") OR (part_cont.last_name like " . $con->qstr('%' . $search_terms['contact'] . '%', get_magic_quotes_gpc()) . "))";
}

if (strlen($search_terms['contact_id'])) {
    $criteria_count++;
    $sql .= " and ((cont.contact_id = {$search_terms['contact_id']}) OR (activity_participants.contact_id={$search_terms['contact_id']}))";
}

if (strlen($search_terms['company']) > 0) {
    $criteria_count++;
    $sql .= " and c.company_name like " . $con->qstr('%' . $search_terms['company'] . '%', get_magic_quotes_gpc());
}

if (strlen($search_terms['company_id'])) {
    $criteria_count++;
    $sql .= " and c.company_id = " . $search_terms['company_id'];
}
if (strlen($search_terms['user_id']) > 0) {
    $criteria_count++;
    if($search_terms['user_id'] == '-2') {
        //Not Set
        $sql .= " and a.user_id = 0";
    }
    elseif($search_terms['user_id'] == '-1') {
        //Current User
        $sql .= " and a.user_id = $session_user_id ";
    }
    elseif($search_terms['user_id'] == 'no') {
        //Not Set
        $sql .= " and a.user_id = 0";
    }
    elseif($search_terms['user_id'] == 'cu') {
        //Current User
        $sql .= " and a.user_id = $session_user_id ";
    }
    else {
        $sql .= " and a.user_id = {$search_terms['user_id']} ";
    }
}

if (strlen($search_terms['activity_type_id']) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_type_id = " . $search_terms['activity_type_id'] . " ";
}

if (strlen($search_terms['activity_status']) > 0) {
    $criteria_count++;
    $sql .= " and a.activity_status = " . $search_terms['activity_status'] . " ";

}

if (strlen($search_terms['completed']) > 0 and $search_terms['completed'] != "all") {
    $criteria_count++;
    $sql .= " and a.activity_status = " . $con->qstr($search_terms['completed'], get_magic_quotes_gpc());
}

if (strlen($search_terms['offset_sql']) > 0) {
    $criteria_count++;
    $sql .= $search_terms['offset_sql'];
}
if (strlen($calendar_offset_sql) > 0) {
    $criteria_count++;
    $sql .= $calendar_offset_sql;
}



if(strlen($search_terms['time_zone_between']) and strlen($search_terms['time_zone_between2'])) {
    update_daylight_savings($con);
    $now = time();
    $now_array=localtime($now, true);
    $hour=$now_array['tm_hour'];
    $sql .= " and addr.daylight_savings_id = tds.daylight_savings_id";

    $sql .= " and ($hour + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") >= " . $search_terms['time_zone_between'];
    $sql .= " and ($hour + tds.current_hour_shift + addr.offset - " . date('Z')/3600 . ") <= " . $search_terms['time_zone_between2'];
}

if($search_terms['opportunity_status_id']) {
    $sql .= " and a.on_what_table='opportunities' and a.on_what_id=o.opportunity_id and o.opportunity_status_id=" . $search_terms['opportunity_status_id'];
}


if($search_terms['on_what_table']) {
    $sql .= " AND a.on_what_table='{$search_terms['on_what_table']}' and a.on_what_id={$search_terms['on_what_id']}";
}

if($search_terms['campaign_id']) {
    $sql .= " AND o.campaign_id = " . $search_terms['campaign_id'];
}

// acl filtering
$list=acl_get_list($session_user_id, 'Read', false, 'activities');

if ($list) {
    if ($list!==true) {
        $list=implode(",",$list);
        $sql .= " and a.activity_id IN ($list) ";
    }
} else {
    $sql .= ' AND 1 = 2 ';
}

// MS-SQL server requires that when using GROUP BY, all fields in select clause must be mentioned
$sql .=" GROUP BY a.activity_id, a.on_what_id, a.on_what_table, c.company_name,c.company_id, cont.first_names, cont.last_name, cont.contact_id, a.ends_at, a.scheduled_at, a.activity_status, a.activity_title, u.username, u.user_id, at.activity_type_pretty_name, rt.resolution_short_name, cp.case_priority_pretty_name";

// end build query

// save query for mail merge
$_SESSION["search_sql"] = $sql;




//echo '<pre>' . htmlentities($sql) . '</pre>';



if('list' != $activities_widget_type) {

    // begin calendar stuff
    $activity_calendar_rst = $con->execute($sql);

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

    } else {
        db_error_handler($con, $sql);
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

    $columns = array();
    $columns[] = array('name' => _('Overdue'), 'index_sql' => 'is_overdue');
    $columns[] = array('name' => _('Type'), 'index_sql' => 'type');
    $columns[] = array('name' => _('Contact'), 'index_sql' => 'contact', 'sql_sort_column' => 'cont.last_name,cont.first_names', 'type' => 'url');
    $columns[] = array('name' => _('Summary'), 'index_sql' => 'title', 'sql_sort_column' => 'activity_title', 'type' => 'url');
    $columns[] = array('name' => _('Description'), 'index_calc' => 'description_brief', 'sql_sort_column' => 'activity_description', 'type' => 'url');
    $columns[] = array('name' => _('Priority'), 'index_sql' => 'case_priority_pretty_name', 'sql_sort_column'=>'a.activity_priority_id'); 
    $columns[] = array('name' => _('Scheduled Start'), 'index_sql' => 'scheduled', 'sql_sort_column' => 'a.scheduled_at');
    $columns[] = array('name' => _('Scheduled End'), 'index_sql' => 'due', 'default_sort' => 'desc', 'sql_sort_column' => 'a.ends_at');
    $columns[] = array('name' => _('Company'), 'index_sql' => 'company', 'sql_sort_column' => 'c.company_name', 'type' => 'url');
    $columns[] = array('name' => _('Owner'), 'index_sql' => 'owner');
    $columns[] = array('name' => _('About'), 'index_calc' => 'activity_about'); 
    $columns[] = array('name' => _('Resolution'), 'index_sql' => 'resolution_short_name', 'sql_sort_column'=>'a.activity_resolution_type_id'); 
	
	// selects the columns this user is interested in
	$pager_columns = new Pager_Columns('ActivitiesPager'.$form_name, $columns, $default_columns, $form_name);
	$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
	$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();
	
	$columns = $pager_columns->GetUserColumns('default');
	
	
	
	$endrows = $end_rows . 
				"<tr><td class=widget_content_form_element colspan=10>
            	$pager_columns_button
            	<input type=button class=button onclick=\"javascript: document.$form_name.activities_widget_type.value='calendar'; document.$form_name.submit();\" name=\"calendar_view\" value=\"" . _('Calendar View') ."\">
            	<input type=button class=button onclick=\"javascript: exportIt();\" value=" . _('Export') .">
            	<input type=button class=button onclick=\"javascript: bulkEmail();\" value=" . _('Mail Merge') . "></td></tr>";
	
	$pager = new GUP_Pager($con, $sql, 'GetActivitiesPagerData', $caption, $form_name, 'ActivitiesPager', $columns, false, true);
	$pager->AddEndRows($endrows);
	$widget['content'] =  $pager_columns_selects .  $pager->Render($system_rows_per_page);
}

$widget['content'] .= "<input type=hidden name=activities_widget_type value=\"$activities_widget_type\">\n";
$widget['content'] .= "<input type=hidden name=calendar_range value=\"$calendar_range\">\n";

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


/**
* $Log: activities-widget.php,v $
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
