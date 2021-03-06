<?php
/**
* activities-widget.php
*
* Consolidated activities pager and calendar widget creator
*
* @author Justin Cooper <justin@braverock.com>
*
* $Id: activities-widget.php,v 1.74 2011/03/02 14:28:28 gopherit Exp $
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
* @param object  $con             The ADOdb connection object
* @param array   $search_terms    Assoc Array of search terms
* @param string  $form_name       name from <form name=XXX>
* @param string  $caption
* @param integer $session_user_id  The User's session
* @param string  $return_url       return_url for links
* @param string  $end_rows         End rows to add to the bottom of the pager eg "<tr><td colspan=23><input type=button></td></tr>\n";
* @param array   $default_columns  List of default columns (used as default for selectable columns)
* @param boolean $show_mini_search Whether or not to show the mini-search mini-widget
* @param array   $default_sort     field_name => asc/desc description of the default sort column
* @param string  $instance
*
* @return string The pager widget.  must be placed inside a form to be active!
*/
function GetActivitiesWidget($con, $search_terms, $form_name, $caption, $session_user_id, $return_url,
                             $extra_where='', $end_rows='', $default_columns = null, $show_mini_search = true,
                             $default_sort = null, $instance='') {

    global $http_site_root;
    $datetime_format = set_datetime_format($con, $session_user_id);

    // acl filtering
    $list=acl_get_list($session_user_id, 'Read', false, 'activities');

    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $list_where = " and a.activity_id IN ($list) ";
        } else $list_where=false;
    } else {
        return '';
    }

        // added by Randy for iCal plugin
        // call the ical_button hook
        // make sure $ical_button is defined
        if ( !isset($ical_button) ) {
          $ical_button = '';
        }
        $ical_button = do_hook_function('ical_button', $ical_button);
        //$ical_button added to button line below

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
    getGlobalVar($completed, 'completed');

    // selects the columns this user is interested in
    $pager_id='ActivitiesPager'.$form_name.$instance;

    //set columns to false, will set them later
    $columns=false;

    //set default columns if not provided
    if (!$default_columns) {
        $default_columns=array('is_overdue','title','type','contact','scheduled','due','company','owner','case_priority_pretty_name','activity_about');
    }

    $pager_columns = new Pager_Columns($pager_id, $columns, $default_columns, $form_name, 6, $con, $search_terms);

    $view_criteria=$pager_columns->GetViewCriteria();
    if ($view_criteria) { $show_mini_search=true; $show_search_terms=true; }

    $initial_search_terms=$search_terms;

    /**** MINI SEARCH ****/
    $mini_search_widget_name = 'activities_mini_search'.$form_name.$instance;

    if($show_mini_search) {
        if (!$show_search_terms) {
            getGlobalVar($search_enabled, $mini_search_widget_name.'_status');
        } else $search_enabled="enable";

        if('enable' == $search_enabled) {
            $search_terms = GetMiniSearchTerms($mini_search_widget_name, $search_terms);
        } else {
            $caption .= '&nbsp;<input type="button" class="button" onclick="document.getElementById(\'' . $mini_search_widget_name . '\').style.display=\'block\';" value="' . _('Filter Activities') . '">';
        }
        if ($search_enabled!='enable') {
            $search_terms=$initial_search_terms;
            //search is disabled, so set criteria to be disabled as well, and save itself if view is being saved
            $pager_columns->SetCurrentViewCriteria(false);
        } else {
            //set current view criteria to include newly found search terms
            $pager_columns->SetCurrentViewCriteria($search_terms);
        }

        //if view criteria was loaded, use it
        if ($view_criteria) $search_terms=$view_criteria;

    //    echo "SEARCH PRE-PAGER COLUMNS:<pre>\n"; print_r($search_terms); echo "</pre>\n";
    //    echo "SEARCH POST-PAGER COLUMNS:<pre>\n"; print_r($search_terms); echo "</pre>\n";

        $mini_search_widget = GetMiniSearchWidget($mini_search_widget_name, $search_terms, $search_enabled, $form_name, $con);

    }

    /**** END MINI SEARCH ***/

    if($activities_widget_type != 'list') {

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

            $calendar_range = 'week';
            //originally set as 'month'

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
    $is_overdue_field="(CASE WHEN (activity_status = 'o') AND (a.ends_at < " . $con->DBTimeStamp(time()) . ") THEN 1 ELSE 0 END)";
    $is_overdue_text_field="(CASE WHEN (activity_status = 'o') AND (a.ends_at < " . $con->DBTimeStamp(time()) . ") THEN ".$con->qstr(_("Yes"))." ELSE " . $con->qstr("")." END)";
    $select = "SELECT $is_overdue_field AS is_overdue, "
        ." at.activity_type_pretty_name AS type, "
        . $con->Concat("'<a id=\"'", "cont.last_name", "'_'" ,"cont.first_names","'\" href=\"$http_site_root/contacts/one.php?contact_id='", "cont.contact_id", "'\">'", "cont.first_names", "' '", "cont.last_name", "'</a>'") . " AS contact, cont.first_names, cont.last_name, "
        . "'$return_url' as return_url, "
        . $con->substr."(activity_description, 1, $description_substring_length)"."AS description_brief, "
        . $con->SQLDate($datetime_format,'a.scheduled_at') . " AS scheduled, "
        . $con->SQLDate($datetime_format,'a.ends_at') . " AS due, "
        . "u.username AS owner, u.user_id, a.activity_id, activity_status, a.on_what_table, a.on_what_id, "
        // these fields are pulled in to speed up the pager sorting (using sql_sort_column)
        . "cont.last_name, cont.first_names, activity_title, a.scheduled_at, a.ends_at, cp.case_priority_pretty_name, rt.resolution_short_name ";

    $select .= ', ' . $con->Concat("'<a id=\"'", "c.company_name", "'\" href=\"$http_site_root/companies/one.php?company_id='", "c.company_id", "'\">'", "c.company_name", "'</a>'") . " AS company, c.company_name ";

    $from = array('activities a');

    $joins = "
    INNER JOIN activity_types at ON a.activity_type_id = at.activity_type_id
    LEFT OUTER JOIN contacts cont ON a.contact_id = cont.contact_id
    LEFT OUTER JOIN companies c ON a.company_id = c.company_id
    LEFT OUTER JOIN users u ON a.user_id = u.user_id
    LEFT OUTER JOIN activity_participants ON a.activity_id=activity_participants.activity_id
    LEFT OUTER JOIN contacts part_cont ON part_cont.contact_id=activity_participants.contact_id
    LEFT OUTER JOIN case_priorities cp ON a.activity_priority_id=cp.case_priority_id
    LEFT OUTER JOIN activity_resolution_types rt ON a.activity_resolution_type_id=rt.activity_resolution_type_id";

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

        //$extra_group_by = ", c.company_name,c.company_id";
        array_unshift($from, 'addresses addr');

        $where .= " AND c.default_primary_address=addr.address_id ";
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

    if (strlen($search_terms['owner']) > 0) {
        $criteria_count++;
        $where .= " and a.user_id = {$search_terms['owner']} ";

        $x = count($default_columns);
        while ($x > 0){
            $y = strcmp($default_columns[$x],"owner");
            if ($y == 0) break;
            $x--;
        }
        if ($y <> 0) array_push($default_columns,"owner");
    }

    if (strlen($search_terms['activity_type_id']) > 0) {
        $criteria_count++;
        $where .= " and a.activity_type_id = " . $search_terms['activity_type_id'] . " ";
    }

    if (strlen($search_terms['type']) > 0) {
        $criteria_count++;
        $where .= " and a.activity_type_id = " . $search_terms['type'] . " ";
    }

    if (strlen($search_terms['activity_status']) > 0) {
        $criteria_count++;
        $where .= " and a.activity_status = " . $search_terms['activity_status'] . " ";
    }

    if (strlen($search_terms['completed']) > 0 and $search_terms['completed'] != "all") {
        $criteria_count++;
        $where .= " and a.activity_status = " . $con->qstr($search_terms['completed'], get_magic_quotes_gpc());
    }

    if (strlen($search_terms['description']) > 0) {
        $criteria_count++;
        $where .= " and a.activity_description like " . $con->qstr('%' . $search_terms['description'] . '%', get_magic_quotes_gpc());
    }

    if (strlen($search_terms['company_type_id']) > 0) {
        $criteria_count++;
        $where .= " and c.company_type_id = " . $search_terms['company_type_id'] . " ";
    }

    if (strlen($search_terms['industry_id']) > 0) {
        $criteria_count++;
        $where .= " and c.industry_id = " . $search_terms['industry_id'] . " ";
    }

    if (strlen($search_terms['campaign_id1']) > 0) {
        $criteria_count++;
        $joins .= " LEFT OUTER JOIN company_campaign_map ccm ON ccm.campaign_id = " . $search_terms['campaign_id1'] . " ";
        $where .= " and a.company_id = ccm.company_id ";
    }

    if (strlen($search_terms['company_category_id']) > 0) {
        $criteria_count++;
        $joins .= " LEFT OUTER JOIN entity_category_map ecm ON a.company_id = ecm.on_what_id
                AND ecm.on_what_table = 'companies'
                AND ecm.category_id = " . $search_terms['company_category_id'] . " ";
        $where .= " and ecm.on_what_table = 'companies' and ecm.on_what_id = c.company_id and ecm.category_id = " . $search_terms['company_category_id'] . " ";
    }

    // date filter code.  dates can come in via start_end + search_date or day_diff (from a saved search)

    if($search_terms['start_end'] == 'start') {
        $field = 'scheduled_at';
    } else {
        $field = 'ends_at';
    }

    $offset_sql = '';

    // This will work for positive and negative intervals automatically, so no need for conditional assignment of offset
    // (a search for today will add an interval of '0 days')
    // Warning: if a user wants to save a search for a particular date, this won't allow it, as it defaults to recurring search
    if(isset($search_terms['day_diff']) and $search_terms['day_diff']) {
        $search_terms['search_date'] = date('Y-m-d', time() + ($search_terms['day_diff'] * 86400));
    } else {
        if($search_terms['search_date'] ) {
            $search_terms['day_diff'] = round((strtotime($search_terms['search_date']) - strtotime(date('Y-m-d', time()))) / 86400);
        }
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

    if ($list_where) $where.=$list_where;

    // MS SQL Server requires that when using GROUP BY, all fields in select clause must be mentioned
    $group_by .=" GROUP BY a.activity_id, cont.first_names, cont.last_name, cont.contact_id, a.ends_at, a.scheduled_at, a.activity_status, a.activity_title, u.username, u.user_id, at.activity_type_pretty_name, a.on_what_table, a.on_what_id, a.activity_description, rt.resolution_short_name, cp.case_priority_pretty_name, c.company_name,c.company_id $extra_group_by";

    $from_list = join(', ', $from);

    $activity_sql = "$select FROM $from_list $joins $where $group_by";

    $count_sql = "SELECT count(distinct a.activity_id) FROM $from_list $joins $where";

    // end build query

    // save query for mail merge
    $_SESSION["search_sql"] = "$select FROM $from_list $joins $where";

    //echo '<pre>' . htmlentities($activity_sql) . '</pre>';

    if($activities_widget_type != 'list') {

        // begin calendar stuff

        // Activities in the calendar views are always sorted by scheduled_at
        $activity_calendar_rst = $con->execute($activity_sql . ' ORDER BY a.scheduled_at');

        if($activity_calendar_rst) {

            $i=0;

            while (!$activity_calendar_rst->EOF) {

                $activity_calendar_data[$i]['activity_id'] = $activity_calendar_rst->fields['activity_id'];
                $activity_calendar_data[$i]['scheduled_at'] = $activity_calendar_rst->fields['scheduled_at'];
                $activity_calendar_data[$i]['ends_at'] = $activity_calendar_rst->fields['ends_at'];
                $activity_calendar_data[$i]['contact_id'] = $activity_calendar_rst->fields['contact_id'];
                $activity_calendar_data[$i]['contact_link'] = $activity_calendar_rst->fields['contact'];
                $activity_calendar_data[$i]['contact_name'] = $activity_calendar_rst->fields['first_names'].' '.$activity_calendar_rst->fields['last_name'];
                $activity_calendar_data[$i]['company_name'] = $activity_calendar_rst->fields['company_name'];
                $activity_calendar_data[$i]['company_link'] = $activity_calendar_rst->fields['company'];
                $activity_calendar_data[$i]['activity_title'] = $activity_calendar_rst->fields['activity_title'];
                $activity_calendar_data[$i]['description_brief'] = $activity_calendar_rst->fields['description_brief'];
                $activity_calendar_data[$i]['user_id'] = $activity_calendar_rst->fields['user_id'];

                $activity_calendar_rst->movenext();
                $i++;
            }
        } else {
            db_error_handler($con, $activity_sql);
        }

        $widget = $calendar->Render($activity_calendar_data);
        // end calendar code
    } else {
        global $system_rows_per_page;

        /** DEFINE COLUMN INFORMATION **/
        $thread_query_list = "select activity_title, activity_id from activities where thread_id is not null group by thread_id order by activity_id";

        $thread_query_select = "$select FROM $from_list $joins $where " . ' AND thread_id = XXX-value-XXX ' . $group_by;

        $overdue_query_list = "select DISTINCT $is_overdue_text_field AS is_overdue, $is_overdue_field FROM $from_list $joins $where $group_by";

        $overdue_query_select = "$select FROM $from_list $joins $where $group_by HAVING $is_overdue_field = XXX-value-XXX";

        $type_query_list = "SELECT DISTINCT at.activity_type_pretty_name, a.activity_type_id FROM $from_list $joins $where $group_by ORDER BY at.sort_order, at.activity_type_pretty_name";

        $type_query_select = "$select FROM $from_list $joins $where " . ' AND a.activity_type_id = XXX-value-XXX ' . $group_by;

        $resolution_query_list = "SELECT DISTINCT rt.resolution_short_name, a.activity_resolution_type_id FROM $from_list $joins $where $group_by ORDER BY rt.sort_order, rt.resolution_short_name";

        $resolution_query_select = "$select FROM $from_list $joins $where " . ' AND a.activity_resolution_type_id = XXX-value-XXX ' . $group_by;

        $owner_query_list = "SELECT DISTINCT ". $con->Concat('u.last_name',"', '",'u.first_names') . ", a.user_id FROM $from_list $joins $where $group_by ORDER BY u.last_name, u.first_names";

        $owner_query_select = "$select FROM $from_list $joins $where " . ' AND a.user_id = XXX-value-XXX ' . $group_by;

        $contact_query_list = "SELECT DISTINCT ". $con->Concat('part_cont.first_names',"' '",'part_cont.last_name') . ", part_cont.contact_id FROM $from_list $joins $where ORDER BY part_cont.last_name, part_cont.first_names";

        $contact_query_select = "$select FROM $from_list $joins $where " . ' AND ( cont.contact_id = XXX-value-XXX OR part_cont.contact_id = XXX-value-XXX) ' . $group_by;

        $priority_query_list = "SELECT DISTINCT cp.case_priority_pretty_name, a.activity_priority_id FROM $from_list $joins $where $group_by";

        $priority_query_select = "$select FROM $from_list $joins $where " . ' AND ( a.activity_priority_id = XXX-value-XXX ) ' . $group_by;

        $company_query_list = "SELECT DISTINCT c.company_name, a.company_id FROM $from_list $joins $where $group_by ORDER BY c.company_name";

        $company_query_select = "$select FROM $from_list $joins $where " . ' AND ( a.company_id = XXX-value-XXX ) ' . $group_by;

        $columns = array();
        $columns[] = array('name' => _("Overdue"), 'index_sql' => 'is_overdue', 'group_query_list'=>$overdue_query_list, 'group_query_select'=>$overdue_query_select);
        $columns[] = array('name' => _("Type"), 'index_sql' => 'type', 'group_query_list'=>$type_query_list, 'group_query_select'=>$type_query_select);
        $columns[] = array('name' => _("Contact"), 'index_sql' => 'contact', 'sql_sort_column' => 'cont.last_name,cont.first_names', 'type' => 'url', 'group_query_list'=>$contact_query_list, 'group_query_select'=>$contact_query_select);
        $columns[] = array('name' => _("Summary"), 'index_sql' => 'title', 'sql_sort_column' => 'activity_title', 'type' => 'url');
        $columns[] = array('name' => _("Description"), 'index_calc' => 'description_brief', 'sql_sort_column' => 'activity_description', 'type' => 'url');
        $columns[] = array('name' => _("Priority"), 'index_sql' => 'case_priority_pretty_name', 'sql_sort_column'=>'a.activity_priority_id','group_query_list'=>$priority_query_list, 'group_query_select'=>$priority_query_select);
        $columns[] = array('name' => _("Scheduled Start"), 'index_sql' => 'scheduled', 'sql_sort_column' => 'a.scheduled_at');
        $columns[] = array('name' => _("Scheduled End"), 'index_sql' => 'due', 'default_sort' => 'desc', 'sql_sort_column' => 'a.ends_at');
        $columns[] = array('name' => _("Company"), 'index_sql' => 'company', 'sql_sort_column' => 'c.company_name', 'type' => 'url', 'group_query_list'=>$company_query_list, 'group_query_select'=>$company_query_select);
        $columns[] = array('name' => _("Owner"), 'index_sql' => 'owner', 'group_query_list'=>$owner_query_list, 'group_query_select'=>$owner_query_select);
        //$columns[] = array('name' => _("Thread"), 'index_sql' => 'thread', 'group_query_list' => $thread_query_list, 'group_query_select' => $thread_query_select);
        $columns[] = array('name' => _("About"), 'index_calc' => 'activity_about');
        $columns[] = array('name' => _("Resolution"), 'index_sql' => 'resolution_short_name', 'sql_sort_column'=>'a.activity_resolution_type_id', 'group_query_list'=>$resolution_query_list, 'group_query_select'=>$resolution_query_select);

        $pager_columns->SetPagerColumns($columns);
        $columns = $pager_columns->GetUserColumns();

    /*** END DEFINITION OF PAGER COLUMNS **/

        $pager_columns_button = $pager_columns->GetSelectableColumnsButton();
        $pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

        global $activity_column_names;
        $activity_column_names = $pager_columns->GetUserColumnNames();

        // save sql for browse button
        $sql_session_var = $form_name . '_activities_sql';
        $_SESSION[$sql_session_var] = $activity_sql;

        //added by randym56 to enable hiding non-functional buttons
        $rst = $con->execute($activity_sql);
        if ((!$rst->EOF) && ($rst->rowcount() > 0)) {
                $show_pager_footer_buttons = true;
                } else $show_pager_footer_buttons = false;
        //added by randym56 to enable hiding non-functional buttons

        $pager = new GUP_Pager($con, $activity_sql, 'GetActivitiesPagerData', $caption, $form_name, $pager_id, $columns, false, true, true);

        $endrows = $end_rows .
                "<tr><td class=widget_content_form_element colspan=20>
                $pager_columns_button
                <input type=button class=button onclick=\"javascript: document.$form_name.activities_widget_type.value='calendar'; document.$form_name.submit();\" name=\"calendar_view\" value=\"" . _("Calendar View") ."\">";

        if ($show_pager_footer_buttons) $endrows = $endrows.$pager->GetAndUseExportButton(). $ical_button ."
                <input type=button class=button onclick=\"javascript: location.href='../email/email.php?return_url=$return_url'\" value=\"" . _("eMail Merge") . "\">" . /* commented out by Randy - neither of these have a place in this widget
                <input type=button class=button onclick=\"javascript: location.href='../bulkactivity/bulkassignment.php?return_url=$return_url'\" value=\"" . _("Bulk Assignment") . "\">
                <input type=button class=button onclick=\"javascript: location.href='../bulkactivity/bulkactivity-0.php?return_url=$return_url'\" value=\"" . _("Bulk Activity") . "\"> */
                "<input type=button class=button onclick=\"javascript: location.href='$http_site_root/activities/browse-next.php?browse=true&sql_session_var=$sql_session_var';\" value=\"" . _("Browse") . "\">";
        $endrows = $endrows."</td></tr>\n";

        if(is_array($default_sort)) {
            $pager->SetDefaultSortColumn($default_sort);
        }
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

} // end function GetActivitiesWidget

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
                    $initial_calendar_date = CalendarView::GetWeekStart($search_date, 'Monday');
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
} // end function GetInitialCalendarDate


/**
* This function renders the new activities widget that is displayed all over XRMS
*/
function GetNewActivityWidget($con, $session_user_id, $return_url, $on_what_table, $on_what_id, $company_id, $contact_id) {

    global $http_site_root;

    //ensure that user has create activity permission
    $table='activities';
    $action='Create';
    $object=false;
    $ret=check_object_permission($session_user_id, $object, $action, $table);
    if (!$ret) return '';

    // Setup all the time defaults
    $datetime_format = set_datetime_format($con, $session_user_id);
    // @TODO: Move the default_activity_duration and default_followup_time
    // to the system/user preferences
    // Default activity duration 15 mins (900 seconds)
    // Default followup time 1 week (604800 seconds)
    $default_activity_duration = 900;
    $default_followup_time = 604800;

    $base_time              = time();
    $scheduled_at           = date($datetime_format, $base_time);
    $ends_at                = date($datetime_format, $base_time + $default_activity_duration);
    $followup_scheduled_at  = date($datetime_format, $base_time + $default_followup_time);
    $followup_ends_at       = date($datetime_format, $base_time + $default_followup_time + $default_activity_duration);

    $form_name = 'NewActivity';

    if(!$company_id) $company_id = 0;



    // create menu of users
    $user_menu = get_user_menu($con, $session_user_id, $blank_user=false, $fieldname='user_id', $truncate=true);
    // Create menu of users for the follow-up activity
    $followup_user_menu = get_user_menu($con, $session_user_id, $blank_user=false, $fieldname='followup_user_id', $truncate=true);

    // Create activity type menu
    // Call the activity type menu plugin hook
    $plugin_parameters = array ('activity_id'      => $activity_id,
                                'activity_type_id' => $activity_type_id,
                                'fieldname'        => 'activity_type_id');
    $activity_type_menu = do_hook_function ('activity_type_menu', $plugin_parameters);

    // In the absence of activity type menu plugin data, create the standard activity type menu
    if (!$activity_type_menu) {
        $activity_type_menu = get_activity_type_menu($con);
    }

    // Create activity type menu for the follow-up activity
    // Call the activity type menu plugin hook
    $plugin_parameters = array ('activity_id'      => $activity_id,
                                'activity_type_id' => $activity_type_id,
                                'fieldname'        => 'followup_activity_type_id');
    $followup_activity_type_menu = do_hook_function ('activity_type_menu', $plugin_parameters);

    // In the absence of activity type menu plugin data, create the standard activity type menu
    if (!$followup_activity_type_menu) {
        $followup_activity_type_menu=get_activity_type_menu($con, '', 'followup_activity_type_id');
    }

    // create menu of contacts
    if($company_id) {
        $sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, contact_id
                FROM contacts
                WHERE company_id = $company_id
                AND contact_record_status = 'a'
                ORDER BY last_name";
        if($company_id == 1 && strlen($contact_id) > 0) { //special handling for unknown company
            $sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, contact_id
                    FROM contacts
                    WHERE company_id = $company_id
                    AND contact_id = $contact_id
                    AND contact_record_status = 'a'
                    ORDER BY last_name";
        }
        $rst = $con->execute($sql);
        if ($rst) {

            $contact_menu = $rst->getmenu2('contact_id', $contact_id, true, false, 0, 'id="contact_id" style="font-size: x-small; width: 80px; height: 20px;"');
            $rst->MoveFirst();
            $followup_contact_menu = $rst->getmenu2('followup_contact_id', $contact_id, true, false, 0, 'style="font-size: x-small; width: 80px; height: 20px;"');

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

    $ret = "<!-- activities //-->
            <form name=\"$form_name\" action=\"$http_site_root/activities/new&followup.php\" method=post onsubmit=\"return validate();\">
            <input type=hidden name=return_url value=\"$return_url\">
	    <input type=hidden name=on_what_table value=\"$on_what_table\">
	    <input type=hidden name=on_what_id value=\"$on_what_id\">
            $hidden
            <input type=hidden id=toggle_activity_status name=activity_status value=\"o\">
            <table class=widget cellspacing=1>
                <tr>
                    <td class=widget_header colspan=5>". _("New Activity") . "</td>
                </tr>

                <tr>
                    <td class=widget_content_form_element></td>
                    <td class=widget_content_form_element>" . _("Type") .":&nbsp;". $activity_type_menu ."</td>\n
                    <td class=widget_content_form_element>" . _("Contact") .":&nbsp;". $contact_menu ."</td>\n
                    <td class=widget_content_form_element>".
                        _("Start")
                        ."&nbsp;<input type=text size=16 ID=\"activity_start_date\" name=scheduled_at value=\"" . $scheduled_at . "\">
                        <img ID=\"activity_start_date_trigger\" style=\"CURSOR: hand\" border=0 src=\"../img/cal.gif\">
                        </td>\n
                        <td class=widget_content_form_element>".
                        _("End")
                        ."&nbsp;<input type=text size=16 ID=\"activity_end_date\" name=ends_at value=\"" . $ends_at . "\" onFocus=\"CheckDate()\">
                        <img ID=\"activity_end_date_trigger\" style=\"CURSOR: hand\" border=0 src=\"../img/cal.gif\">
                    </td>\n
                </tr>

                <tr>
                    <td class=widget_content_form_element>" . _("Summary") . "</td>\n
                    <td class=widget_content_form_element colspan=3>
                        <input type=text name=activity_title size=80>
                    </td>
                    <td class=widget_content_form_element>" . _("User") .":&nbsp;\n".
                        $user_menu ."\n
                    </td>\n
                </tr>

                <tr>
                    <td class= widget_content_form_element>"._("Notes")."</td>
                    <td class= widget_content_form_element colspan=3>
                          <textarea name=activity_description cols='80' rows='5' style='width: 98%;'></textarea>
                    </td>
                    <td class='widget_content_form_element' style='padding-left: 2em; vertical-align: middle;'>
                        <input type='checkbox' id='completed_chkbx' name='activity_status' value='c' onclick=\"
                            toggle_disabled('sch_fup_chkbx');
                            \" />" . _("Completed") ."<br /><br />
                            <input type='checkbox' name='new_and_followup' id='sch_fup_chkbx' value='true' onclick=\"
                                toggle_disabled('completed_chkbx');
                                js_toggle_activity_status();
                                toggle_disabled('add_activity');
                                toggle_visibility('sch_fup_tr_1');
                                toggle_visibility('sch_fup_tr_2');
                                \" disabled='disabled'/>" . _("Schedule Followup") ."<br /><br />".
                        render_create_button(_("Add Activity"),'submit', false, false, 'add_activity', 'activities') . "
                    </td>\n
                </tr>\n";

   $ret .= "<tr id='sch_fup_tr_1' style='display:none;'>\n
                <td class=widget_content_form_element rowspan=2>". _('Followup') ."</td>\n
                <td class=widget_content_form_element>" . _("Type") .":&nbsp;". $followup_activity_type_menu ."</td>\n
                <td class=widget_content_form_element>" . _("Contact") .":&nbsp;". $followup_contact_menu ."</td>\n
                <td class=widget_content_form_element>".
                    _("Start")
                    ."&nbsp;<input type=text size=16 ID=\"followup_activity_start_date\" name=followup_scheduled_at value=\"" . $followup_scheduled_at . "\">
                    <img ID=\"followup_activity_start_date_trigger\" style=\"CURSOR: hand\" border=0 src=\"../img/cal.gif\">
                    </td>\n
                    <td class=widget_content_form_element>".
                    _("End")
                    ."&nbsp;<input type=text size=16 ID=\"followup_activity_end_date\" name=followup_ends_at value=\"" . $followup_ends_at . "\"onFocus=\"CheckFollowupDate()\">
                    <img ID=\"followup_activity_end_date_trigger\" style=\"CURSOR: hand\" border=0 src=\"../img/cal.gif\">
                </td>\n
            </tr>\n
            <tr id='sch_fup_tr_2' style='display:none;'>\n
                <td class=widget_content_form_element>
                    <input type='checkbox' name='followup_transfer_notes' value='true' checked/>" . _("Transfer Activity Notes") ."
                </td>
                <td class=widget_content_form_element>" . _("User") .":&nbsp;". $followup_user_menu ."</td>\n
                <td class=widget_content_form_element colspan=3 style='text-align: center;'>".
                    render_create_button(_("Add Activity and Schedule Followup"),'submit', false, false, 'add_activity_and_fup', 'activities') ."
                </td>
            </tr>\n";

	if ($datetime_format == 'Y-m-d H:i:s') {
		$java_timeformat = "%Y-%m-%d %H:%M";
		$java_timevalue = '24';
		}
		else {
		$java_timeformat = "%Y-%m-%d %I:%M %p";
		$java_timevalue = '12';
		}
	    $ret .= "
            </table>
            </form>
            <script language=\"JavaScript\" type=\"text/javascript\">
                    Calendar.setup({
                    inputField     :    \"activity_start_date\",      // id of the input field
                    ifFormat       :    \"".$java_timeformat."\",       // format of the input field
                    showsTime      :    true,            // will display a time selector
		    timeFormat	   :    value=\"".$java_timevalue."\",  //12 or 24
                    button         :    \"activity_start_date_trigger\",   // trigger for the calendar (button ID)
                    singleClick    :    false,           // double-click mode
                    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                    align          :    \"TL\"           // alignment (defaults to \"Bl\")
                });
                    Calendar.setup({
                    inputField     :    \"activity_end_date\",      // id of the input field
                    ifFormat       :    \"".$java_timeformat."\",       // format of the input field
                    showsTime      :    true,            // will display a time selector
		    timeFormat	   :    value=\"".$java_timevalue."\",  //12 or 24
                    button         :    \"activity_end_date_trigger\",   // trigger for the calendar (button ID)
                    singleClick    :    false,           // double-click mode
                    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                    align          :    \"TL\"           // alignment (defaults to \"Bl\")
                });
                    Calendar.setup({
                    inputField     :    \"followup_activity_start_date\",      // id of the input field
                    ifFormat       :    \"".$java_timeformat."\",       // format of the input field
                    showsTime      :    true,            // will display a time selector
		    timeFormat	   :    value=\"".$java_timevalue."\",  //12 or 24
                    button         :    \"followup_activity_start_date_trigger\",   // trigger for the calendar (button ID)
                    singleClick    :    false,           // double-click mode
                    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                    align          :    \"TL\"           // alignment (defaults to \"Bl\")
                });
                    Calendar.setup({
                    inputField     :    \"followup_activity_end_date\",      // id of the input field
                    ifFormat       :    \"".$java_timeformat."\",       // format of the input field
                    showsTime      :    true,            // will display a time selector
		    timeFormat	   :    value=\"".$java_timevalue."\",  //12 or 24
                    button         :    \"followup_activity_end_date_trigger\",   // trigger for the calendar (button ID)
                    singleClick    :    false,           // double-click mode
                    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                    align          :    \"TL\"           // alignment (defaults to \"Bl\")
                });

    		function CheckDate()
				{
				var ends_at = Date.parse(document.$form_name.ends_at.value);
				var starts_at = Date.parse(document.$form_name.scheduled_at.value);
				if (ends_at < starts_at) or (isNaN(ends_at))
					{
      				document.$form_name.ends_at.value = document.$form_name.scheduled_at.value;
					}
    			}

    		function CheckFollowupDate()
				{
				var ends_at = Date.parse(document.$form_name.followup_ends_at.value);
				var starts_at = Date.parse(document.$form_name.followup_scheduled_at.value);
				if (ends_at < starts_at) or (isNaN(ends_at))
					{
      				document.$form_name.followup_ends_at.value = document.$form_name.followup_scheduled_at.value;
					}
    			}

                function toggle_visibility(id) {
                    var e = document.getElementById(id);
                    if(e.style.display != 'none')
                        e.style.display = 'none';
                    else
                        e.style.display = '';
                }

                function toggle_disabled(id) {
                    var e = document.getElementById(id);
                    if(e.disabled)
                        e.disabled = false;
                    else
                        e.disabled = 'disabled';
                }

                function js_toggle_activity_status() {
                    var e = document.getElementById('toggle_activity_status');
                    if(e.value != 'c')
                        e.value = 'c';
                    else
                        e.value = 'o';
                }

                function validate() {

                    if ( document.$form_name.contact_id.selectedIndex == 0 ) {
                        alert('". addslashes(_('You must assign this activity to a contact.')) ."');
                        document.$form_name.contact_id.focus();
                        return false;
                    } else {
                        return true;
                    }
                }

            </script>
    ";
    return $ret;
}  // end function GetNewActivityWidget

/**
* This function processes the form data from the Mini Search Widget and packs it into search_terms,
* similar to what is done in activities/some.php
*/
function GetMiniSearchTerms($widget_name, $search_terms) {

    getGlobalVar($search_terms['title'], $widget_name.'_activity_title');
//    getGlobalVar($search_terms['about'], $widget_name.'_activity_about');
    getGlobalVar($search_terms['owner'], $widget_name.'_activity_owner');
    getGlobalVar($search_terms['type'], $widget_name.'_activity_type');
    getGlobalVar($search_terms['contact'], $widget_name.'_activity_contact');
    getGlobalVar($search_terms['company'], $widget_name.'_activity_company');
    getGlobalVar($search_terms['start_end'], $widget_name.'_start_end');
    getGlobalVar($search_terms['before_after'], $widget_name.'_before_after');
    getGlobalVar($search_terms['search_date'], $widget_name.'_search_date');
    getGlobalVar($search_terms['completed'], $widget_name.'_completed');

    return $search_terms;
}

/**
* This function creates the Mini Search Widget and packs it into search_terms,
* similar to what is done in activities/some.php
*/
function GetMiniSearchWidget($widget_name, $search_terms, $search_enabled, $form_name, $con=false) {

    if('enable' == $search_enabled) {
        $title          = $search_terms['title'];
        $type          = $search_terms['type'];
//        $about          = $search_terms['about'];
        $owner          = $search_terms['owner'];
        $contact        = $search_terms['contact'];
        $company        = $search_terms['company'];
        $start_end      = $search_terms['start_end'];
        $before_after   = $search_terms['before_after'];
        $search_date    = $search_terms['search_date'];
        $completed      = $search_terms['completed'];

    }
    if (!$con) $con=get_xrms_dbconnection();

    $hideCaption=_("Hide");

    $activity_type_menu=get_activity_type_menu($con, $type, $widget_name.'_activity_type',true);
    $activity_owner_menu = get_user_menu($con, $owner, true, $widget_name.'_activity_owner', $truncate=true);
    $showhide_link="<a href=\"#\" id=\"{$widget_name}_showhideLink\" onclick=\"javascript:{$widget_name}_Hide();\">{$hideCaption}</a>";

    $ret =
    "<div id=$widget_name>

        <input type=hidden name={$widget_name}_status value='$search_enabled'>

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>
                    <table width=\"100%\" cellspacing=0 cellpadding=0 border=0>
                        <tr>
                            <td class=widget_header align=left>
                                ". _("Filter Activities") . "
                            </td>
                            <td class=widget_header align=right>
                                $showhide_link
                            </td>
                       </tr>
                   </table>
            </tr>
            <tr>
                <td class=widget_label>" . _("Summary") . "</td>
                <td class=widget_label>" . _("Contact") . "</td>
                <td class=widget_label>" . _("Search By Date") . "</td>
                                <td>&nbsp;</td>
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
                                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class=widget_label>" . _("Type") . "</td>
                <td class=widget_label>" . _("Owner") . "</td>
                <td class=widget_label>" . _("Company") . "</td>
                                <td class=widget_label>" . _("Activity Status") . "</td>
            </tr>
            <tr>
                <td class=widget_content_form_element>$activity_type_menu</td>
                <td class=widget_content_form_element>$activity_owner_menu</td>
                <td class=widget_content_form_element><input type=text size=12 name={$widget_name}_activity_company value=\"$company\"></td>
                                <td class=widget_content_form_element>
                 <select name=\"{$widget_name}_completed\">
                    <option value=\"o\"" . ($completed == 'o' ?  ' selected' : '' ). '>' . _("Non-Completed") . "</option>
                    <option value=\"c\"" . ($completed == 'c' ?  ' selected' : '' ). '>' . _("Completed") . "</option>
                    <option value=\"all\"" . ($completed == 'all' ?  ' selected' : '' ). '>' . _("All") . "</option>
                </select>
                                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input type=button class=button onclick=\"document.$form_name.{$widget_name}_status.value='enable'; document.$form_name.submit();\" value=\"" . _('Filter Activities') . "\">
                    <input type=button class=button onclick=\"{$widget_name}_ClearActivitiesFilter()\" value=\"" . _('Clear Filter') . "\">
                </td>
            </tr>
        </table>

    </div>

    <script language=\"JavaScript\" type=\"text/javascript\">

        // hide the widget to start with
        document.getElementById('{$widget_name}').style.display = \"" . ('enable' == $search_enabled ? 'block' : 'none') . "\";

        function {$widget_name}_ClearActivitiesFilter() {
            document.$form_name.{$widget_name}_status.value='disable';
            document.$form_name.submit();
        }
        function {$widget_name}_Hide() {
            document.getElementById('{$widget_name}').style.display = \"none\";
        }

        Calendar.setup({
                inputField     :    \"f_date_{$widget_name}_search_date\",      // id of the input field
                ifFormat       :    \"%Y-%m-%d %H:%M:%S\",       // format of the input field
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
* Revision 1.74  2011/03/02 14:28:28  gopherit
* Removed unnecessary $tmp variable & very minor code cleanup.
*
* Revision 1.73  2011/03/01 20:14:23  gopherit
* FIXED Bug Artifact #2998959:  Activities are now properly sorted by their scheduled_at date in any of the Calendar Views.
*
* Revision 1.72  2010/10/06 13:16:21  gopherit
* A couple of tweaks:
* * Implemented default_activity_duration and default_followup_time
* * Adjusted UI to fit on a 1024/768 screen
*
* Revision 1.71  2010/05/06 20:11:05  gopherit
* Added Javascript validation to prevent the creation or saving of activities without them being assigned to a contact.
*
* Revision 1.70  2009/12/15 18:57:52  gopherit
* Implemented the activity_type_menu plugin hooks for both the activity and the followup activity type menus.
*
* Revision 1.69  2009/12/15 14:36:27  gopherit
* Added functionality to the 'New Activity' widget to enable creating a followup activity at the time a new completed activity is entered.
*
* Revision 1.68  2009/11/12 23:13:39  gopherit
* A tweak of the <input> and <textarea> properties.
*
* Revision 1.67  2009/11/11 21:03:25  gopherit
* Refactored the Activities Widget to make it more user friendly.
*
* Revision 1.66  2009/03/23 07:59:02  polyformal_sp
* removed / (slash) because of translation issues
*
* Revision 1.65  2009/02/17 01:38:09  randym56
* - Patch to allow for individual user to change datetime_format views
*
* Revision 1.64  2009/02/14 18:02:32  randym56
* - Update $datetime_format - removed from vars.php - installed with updateto2.1.php into system/user prefs
*
* Revision 1.63  2009/02/05 23:04:44  randym56
* - Bug fixes and updates in several scripts. Prep for new release.
* - Added ability to set $datetime_format in vars.php
* - TODO: put $datetime_format in setup table rather than vars.php
* - TODO: fix javascript bugs in /activities/templates/v1.99.php
*
* Revision 1.62  2008/10/07 16:43:22  metamedia
* BUG FIX: The default week view, in Calendar View, was showing as the first week 1970, rather than the current week.
*
* Revision 1.61  2008/01/30 21:08:41  gpowers
* - added $rst check
* - formatted HTML
*
* Revision 1.60  2007/10/30 02:31:38  randym56
* - Changed "Mail Merge" to be "eMail Merge" to separate from "Snail Mail Merge"
*
* Revision 1.59  2007/10/18 02:01:06  randym56
* Stop Export, iCal, Mail Merge and Browse buttons from appearing if no data shows in the widget.
*
* Revision 1.58  2007/10/17 00:26:17  randym56
* - added scheduled start date field to "GetNewActivityWidget" Lines 723 & 731-732 & 743-750
* - added H:i:s to "GetActivitiesWidget" line 177-178
*
* Revision 1.57  2007/06/02 11:32:52  fcrossen
* - fixed a HTML typo
*
* Revision 1.56  2007/06/02 11:29:42  fcrossen
* - fixed missing " char introduced in last commit. (Thanks randym56!)
*
* Revision 1.55  2007/06/01 14:27:09  fcrossen
* - fixed missing table name in call to render_create_button() - perms were not being checked. See https://sourceforge.net/forum/forum.php?thread_id=1742584&forum_id=305410
*
* Revision 1.54  2006/12/29 22:00:14  ongardie
* - Avoid SQL error when contact_id is empty.
*
* Revision 1.53  2006/11/14 19:55:21  braverock
* - special handling for unknown company
*   based on patches by fcrossen
*
* Revision 1.52  2006/10/01 12:51:42  braverock
* - fix . on line 430
*
* Revision 1.51  2006/10/01 10:33:11  braverock
* - add contact and company fields to the activity calendar data array used by the calendar view
*
* Revision 1.50  2006/10/01 00:51:12  braverock
* - normalize use of truncate flag in get_user_menu
*
* Revision 1.49  2006/09/30 18:08:03  braverock
* - clean up button formatting
*
* Revision 1.48  2006/09/30 18:02:55  braverock
* - apply some patches form 2006/07/31 dbaudone
*   -- default calendar view set to "week"
*   -- force to set "owner" in default columns array
*   -- add sql queries for serach fields added (company type, industry, campaign, company category)
*   -- modified "Mail Merge" button to pass $return_url
*   -- added "Bulk Assigment" and "Bulk Activity" buttons
*   -- eliminated border outset style in contact menu
*
* - clean indentation, comments, and phpdoc
* - remove obsolete code
*
*
* Revision 1.47  2006/07/25 19:48:44  vanmer
* - added default column definition if not provided from the calling page
*
* Revision 1.46  2006/07/19 01:39:25  vanmer
* - added ability to clear view criteria when saving a view that has no criteria
* - added code to ensure that if a view is saved with no criteria that the mini search widget does not appear when loading this view
*
* Revision 1.45  2006/07/17 05:39:00  vanmer
* - changed link to companies and contacts to work regardless of path to page being displayed
*
* Revision 1.44  2006/07/14 04:12:04  vanmer
* - added variables to minisearch widget to ensure unique behavior
* - added Hide link to activities filters display
*
* Revision 1.43  2006/07/13 00:14:37  vanmer
* - moved definition of pager columns object to top of function, to allow load of views
* - added calls to grab view criteria if view has just been loaded
*
* Revision 1.42  2006/07/12 03:50:45  vanmer
* - added instance parameter to activities widget, allows multiple activities widgets on a page
* - changed pager_id to be consistent between Pager_Columns and GUP_Pager
*
* Revision 1.41  2006/04/17 19:19:15  vanmer
* - added ACL checks to the beginning of the activities widget, so widget does not render if no ACL permission is allowed
* - added ACL checks to new activities widget, checks Create permission on activities before rendering new activity line
*
* Revision 1.40  2005/10/21 18:45:19  vanmer
* - patch to fix export of activities pager, thanks to tomver at SF
*
* Revision 1.39  2005/09/23 16:07:36  daturaarutad
* add default sort parameter to GetActivitiesWidget()
*
* Revision 1.38  2005/08/28 16:31:24  braverock
* - fix incorrect colspan entries
*
* Revision 1.37  2005/08/28 15:56:44  braverock
* - remove unneccessary colspan
*
* Revision 1.36  2005/08/28 15:33:15  braverock
* - remove size attribute from td tags, as this is not a valid attribute for td
*
* Revision 1.35  2005/08/19 18:59:58  daturaarutad
* no longer set search_date if it is not set (old code that should have been deleted) to fix date filtering bug
*
* Revision 1.34  2005/08/15 21:35:12  daturaarutad
* removed if ($search_terms["day_diff"]) conditional around date filter handling
*
* Revision 1.33  2005/08/15 00:48:30  daturaarutad
* enable group_mode_paging
*
* Revision 1.32  2005/08/11 16:58:59  ycreddy
* Added company_name and company_id as default columns to GROUP BY column list
*
* Revision 1.31  2005/08/05 16:08:43  vanmer
* - added missing space to where clause in activities widget
*
* Revision 1.30  2005/07/27 00:07:11  vanmer
* - added grouping on resolutions on activities
*
* Revision 1.29  2005/07/26 23:22:09  vanmer
* - added grouping on many fields
* - added company to list of fields that are always included in the sql join
* - added mini search on type, owner and company
*
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
*/
?>
