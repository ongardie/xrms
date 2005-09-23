<?php

/**
 * Wrapper class for calendar widget.
 *
 * @author Justin Cooper <daturaarutad@sourceforge.net>
 *
 * $Id: Calendar_View.php,v 1.9 2005/09/23 20:57:48 daturaarutad Exp $
 */

global $include_directory;


require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-users.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');



/**
* Note: user of this class must provide a form and a hidden form field for the calendar_start_date f
*
*/
class CalendarView {

	var $start_date;
	var $calendar_date_field;
	var $form_name;
	var $calendar_type;

	var $display_mode = 'text'; // or iconic

	var $user_styles_count = 10;  // Must agree with CSS style sheet
	var $user_style_prefix = 'calendar_user';  // Must agree with CSS style sheet
	var $td_user_class;

	var $con;

	/**
	* Constructor
	*
	* @param resource ADOdb connection object
	* @param string Form Name from <form name="XXX"> used by Javascript
	* @param string Date Field Name also used by Javascript when Next Week, etc buttons are pressed
	* @param string calendar type ('week','month')
	*
	*/
	function CalendarView($con, $form_name, $initial_calendar_date, $calendar_date_field, $calendar_type) {

		/* Calendar object:
        	-if a date is passed in as a param, use that
        	-elseif the session/cgi var is set, use that....
        	-else use today's date.                               */


		getGlobalVar($calendar_CGI_date, $calendar_date_field);
		// 'text' or 'iconic'
		getGlobalVar($calendar_display_mode, 'calendar_display_mode');
		if(!$calendar_display_mode) {
			$calendar_display_mode = 'text';
		}



		if($initial_calendar_date) {
			$calendar_start_date = $initial_calendar_date;
		} else {

			if($calendar_CGI_date) {
				$calendar_start_date = $calendar_CGI_date;
			} else {
				$calendar_start_date = date('Y-m-d', time());
			}
		}

		//echo "calendar date is $calendar_start_date";
		$this->con					= $con;
		$this->form_name 				= $form_name;
		$this->calendar_date_field 	= $calendar_date_field;
		$this->start_date 			= $calendar_start_date;
		$this->calendar_type 		= $calendar_type;
		$this->display_mode 		= $calendar_display_mode;

	}

	/**
	* Set the display mode
	* @param string may be 'text' or 'iconic'
	*/
	function SetDisplayMode($display_mode) {
		$this->display_mode = $display_mode;
	}


	/**
	* Static Function, if we had them. ($this need not be available)
	*
	* @param string the starting date (any day of the week)
	* @param string the day of the week ('Mon','Tuesday',etc)
	* @return string the first Monday of the week before
	*/
	function GetWeekStart($date, $day = 'Monday') {
		if (!isset($set_weekstart_default))
			$set_weekstart_default = 'Sunday';

  		$timestamp = strtotime($date);
  		$num = date('w', strtotime($day));
  		$start_day_time = strtotime((date('w',$timestamp)==$num ? "$day" : "last $day"), $timestamp);
  		$ret_unixtime = strtotime($day,$start_day_time);
  		$ret_unixtime = strtotime('+12 hours', $ret_unixtime);
  		$ret = date('Y-m-d',$ret_unixtime);

		return $ret;
	}


/**
* Render the calendar widgets
* @param array array of assoc arrays of activity values.
* expected fields are: activity_id, scheduled_at, ends_at, contact_id, activity_title, activity_description, user_id
*
* @return array assoc. array containing 'content' and 'sidebar' values which contain the calendar widgets
*/
function Render($activity_data) {

	global $http_site_root;

	$events = $this->BuildDailyEvents($activity_data);

	$return = '';

    // add the hook to include the JS for the tooltips
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['end_page']['calendar'] = 'javascript_tooltips_include';

	$view_mode_buttons = "
            <tr>
                <td class=widget_label colspan=\"8\">
					<input type=\"button\" class=\"button\" onclick=\"javascript:document.{$this->form_name}.activities_widget_type.value='list'; document.{$this->form_name}.submit();\" value=\""._('List View ')."\">
					<input type=\"button\" class=\"button\" onclick=\"javascript:document.{$this->form_name}.calendar_range.value='week'; document.{$this->form_name}.submit();\" value=\""._('Week View ')."\">
					<input type=\"button\" class=\"button\" onclick=\"javascript:document.{$this->form_name}.calendar_range.value='month'; document.{$this->form_name}.submit();\" value=\""._('Month View ')."\">" . 

					('text' == $this->display_mode ? 
						"<input type=\"button\" class=\"button\" onclick=\"javascript:document.{$this->form_name}.calendar_display_mode.value='iconic'; document.{$this->form_name}.submit();\" value=\""._('Iconic View ')."\">"
					:
						"<input type=\"button\" class=\"button\" onclick=\"javascript:document.{$this->form_name}.calendar_display_mode.value='text'; document.{$this->form_name}.submit();\" value=\""._('Normal View ')."\">"
					) .  "
                </td>
            </tr>
			";


	switch($this->calendar_type) {

		case 'month':

			// display starts on monday, not necessarily the 1st
			$visible_start_date = $this->GetWeekStart($this->start_date, 'Monday');

			$current_month = date('m', strtotime($this->start_date));

			$widget = '';
			$week_count = 5;

			if($visible_start_date != $this->start_date) {
				$days_offset = (strtotime($this->start_date) - strtotime($visible_start_date)) / 86400;
			} else {
				$days_offset = 0;
				$week_count--;
			}


	/*		each day looks like this
			 ____________
			|          2 |
			|------------|
			|  ________  |
			| | event1 | |
			| |--------| |
			| | event2 | |
			|  --------  |
			|____________|
			*/
			for($week=0; $week < $week_count; $week++) {
				$widget .= "<tr>";

				for($day=0; $day<7; $day++) {

					$day_of_month = $week * 7 + $day;

					$day_start_time = strtotime($visible_start_date . " + $day_of_month days");

					$td_class = '';
					$events_rows = '';

					if(is_array($events[$day_of_month-$days_offset])) {

						$events_rows = '<table>';

						foreach(($events[$day_of_month-$days_offset]) as $event) {

							if('text' == $this->display_mode) {
								//$event_link = "<a href=\"$http_site_root/activities/one.php?activity_id={$event['activity_id']}&return_url=" . current_page() . "\">" .
								$event_link = "<a href=\"$http_site_root/activities/one.php?activity_id={$event['activity_id']}&return_url=" . current_page() . "\"onmouseover=\"return escape('" . addslashes("{$event['description_brief']}") . "')\">" .
										 	' ' . $event['activity_title'] .
											"</a><br>" .  date('h:iA', strtotime($event['scheduled_at'])) . ' - ' . date('h:iA', strtotime($event['ends_at'])) ;
							} else {
								$event_link = "<a href=\"$http_site_root/activities/one.php?activity_id={$event['activity_id']}&return_url=" . current_page() . "\" onmouseover=\"return escape('" . addslashes("{$event['activity_title']}<br/>(" . date('H:i', strtotime($event['scheduled_at'])) . '-' . date('H:i', strtotime($event['ends_at'])) . ")") . "')\">" .
										 	" <img src=\"$http_site_root/img/calendar_time_icon.gif\"></a>";




							}

							$i = $this->td_user_class[$event['user_id']];

							$events_rows .= "<tr><td class=\"{$this->user_style_prefix}$i small\">$event_link</td></tr>";

						}
						$events_rows .= '</table>';
					}

					// CSS classes for prev, next month, current day highlighting
					if($current_month != date('m', $day_start_time)) {
						$td_class .= 'widget_content_alt2';
					} elseif(date('Y-m-d') == date('Y-m-d', $day_start_time)) {
						$td_class .= 'widget_content_alt';
					} else {
						$td_class .= 'widget_content';
					}

					$widget .= "<td class=\"$td_class\" width=105 height=105>
								<table width=\"100%\" border=0 cellspacing=0 cellpadding=1>
									<tr><td class=\"$td_class right\">" . date('j', $day_start_time) . "</td></tr>
									<tr><td class=\"$td_class\" valign=top width=105>$events_rows</td></tr>
								</table></td>";

				}
				$widget .= "</tr>\n";
			}

			$display_date = date('F Y', strtotime($this->start_date));

	       	$days_header = "<tr>";
	    	for ($i=0; $i<7; $i++) {
	        	$day = date('D',  strtotime("+$i days  $visible_start_date "));

	        	$days_header .= "<td width=\"105\" class=\"center widget_content\">$day</td>";
	    	}
	       	$days_header .= "</tr>";

		    $next_month_display = date("M Y", strtotime($this->start_date . ' +1 month'));
		    $prev_month_display = date( "M Y", strtotime($this->start_date . ' -1 month'));

			$calendar_nav = "
		    <tr>
		     <td colspan=30 class=widget_content>
		      <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		       <tr>
		        <td class=\"widget_label center\">
		            <input class=button type=button value=\"$prev_month_display\" onclick=\"javascript:calendar_previous_month();\">
		            <!--
		            <input class=button type=button value=\"Previous Day\" onclick=\"javascript:calendar_previous_day();\">
		            -->
				</td>
		        <td class=\"widget_label center\">
		         $display_date
		        </td>
		        <td class=\"widget_label center\">
		            <!--
		            <input class=button type=button value=\"Next Day\" onclick=\"javascript:calendar_next_day();\">
		            -->
		            <input class=button type=button value=\"$next_month_display\" onclick=\"javascript:calendar_next_month();\">
		        </td>
		       </tr>
		      </table>
			</td>
		 	</tr>";
			$calendar_nav .= $days_header;

			break;



		// week
		case 'week':

			$widget = '';

			$start_hour = '8:00:00';
			$end_hour = '22:00:00';
			$slice_length_minutes = '30';

			$start_time = strtotime(date('Y-m-d', strtotime($this->start_date)) . ' ' . $start_hour);
			$end_time = strtotime(date('Y-m-d', strtotime($this->start_date)) . ' ' . $end_hour);

			//echo "start time is $start_time aka " . date('Y-m-d H:i', $start_time) . "<br>";
			//echo "end time is $end_time aka " . date('Y-m-d H:i', $end_time) . "<br>";

			$days_header .= '<tr>';
			$days_header .= '<td class=widget_content_alt>&nbsp;</td>';


			// Some days will have simultaneous events happening, so we need to calculate
			// the value that will be used for colspan=N in the | Mon 2 | Tue 3 | Weds ... header
			// and also to know how many dummy columns to output to keep the table lined up
			$colspan_by_day = array();

			for($day_of_week=0; $day_of_week<7; $day_of_week++) {

				$days_slice_event_count = 0;

				if(is_array($events[$day_of_week])) {
					// foreach day
					for($i=0; $i<(($end_time - $start_time)/($slice_length_minutes * 60)); $i++) {

						$current_time = $start_time + $i*$slice_length_minutes*60;
						$current_time_this_day = $current_time + $day_of_week*86400;

						$slot_start = $current_time_this_day;
						$slot_end = $current_time_this_day + $slice_length_minutes*60;

						$slice_events_count = 0;

						// count how many events occur during this time slot
						foreach($events[$day_of_week] as $event_key => $event) {
							// if it ends before the slot starts or starts after the slot ends we don't want it
							if(strtotime($event['ends_at']) <= $slot_start || strtotime($event['scheduled_at']) >= $slot_end) {
								//echo "event {$event['title']} at {$event['scheduled_at']} not happening during " .
								//date('Y-m-d H:i', $slot_start) . ' and ' . date('Y-m-d H:i', $slot_end) . '<br>';
							} else {

								$slice_events_count++;

								//echo "event {$event['title']} at {$event['scheduled_at']} happening during " .
								//date('Y-m-d H:i', $slot_start) . ' and ' . date('Y-m-d H:i', $slot_end) . '<br>';
							}
						}
						// store the max # of simultaneous events today
						$days_slice_event_count = max($days_slice_event_count, $slice_events_count);
					}
				}
				//echo "max is $days_slice_event_count for day $day_of_week<br>";
				$colspan_by_day[$day_of_week] = $days_slice_event_count;

				$colspan = ($colspan_by_day[$day_of_week] > 1) ? "colspan=\"" . $colspan_by_day[$day_of_week] . '"' : '';

				$days_header .= "<td width=13% class=\"widget_content_alt center\" $colspan>" . date('D d', $start_time + $day_of_week*86400) . "</td>\n";
			}
			$days_header .= "</tr>\n";

			$widget .= $days_header;

			// render the week
			for($i=0; $i<(($end_time - $start_time)/($slice_length_minutes * 60)); $i++) {

				$current_time = $start_time + $i*$slice_length_minutes*60;
				//echo date('Y-m-d H:i', $current_time) . '<br>';
				$widget .= '<tr>';

				$widget .= '<td class=widget_content_alt>' . date('H:i', $current_time) . '</td>';
				for($day_of_week=0; $day_of_week<7; $day_of_week++) {

					// if there are events happening today
					if(is_array($events[$day_of_week])) {

						$current_time_this_day = $current_time + $day_of_week*86400;
						$start_time_this_day = $start_time + $day_of_week*86400;
						$end_time_this_day = $end_time + $day_of_week*86400;


						$virtual_columns_outputted_count = 0;

						// loop through the events, outputting maximum of $colspan_by_day[$day_of_week] <td>'s
						// output the events and count how many we output or are virtual (covered by rowspan)
						foreach($events[$day_of_week] as $event_key => $event) {

							$event_start = strtotime($event['scheduled_at']);

							$event_start = max($event_start, $start_time_this_day);

							// avoid a division by zero error later by making sure the duration is at least 1 second.
							$event_end = max(strtotime($event['ends_at']), $event_start+1);
							// if this event goes on to tomorrow, we cut it off a the end of the day.
							$event_end = min($event_end, $end_time_this_day);

							$slot_start = $current_time_this_day;
							$slot_end = $current_time_this_day + $slice_length_minutes*60;

							// if event is starting in this slot
							if($event_start >= $slot_start && $event_start < $slot_end) {

								$rowspan = intval(($event_end - $slot_start + $slice_length_minutes*60-1) / ($slice_length_minutes*60));
								if($rowspan) {
									$rowspan_html = "rowspan=$rowspan";
									// save the rowspan
									$events[$day_of_week][$event_key]['rowspan'] = $rowspan-1;
								}


								// show the event

							if('text' == $this->display_mode) {

								$widget .= "<td class=\"small {$this->user_style_prefix}{$this->td_user_class[$event['user_id']]}\" $rowspan_html>
											<a href=\"$http_site_root/activities/one.php?activity_id={$event['activity_id']}&return_url=" . current_page() . "\">{$event['activity_title']}</a><br>" .
											"(" . date('H:i', strtotime($event['scheduled_at'])) . '-' . date('H:i', strtotime($event['ends_at'])) . ")</td>";
							} else {
								$widget .= "<td class=\"small {$this->user_style_prefix}{$this->td_user_class[$event['user_id']]}\" $rowspan_html>
											<a href=\"$http_site_root/activities/one.php?activity_id={$event['activity_id']}&return_url=" . current_page() . "\" onmouseover=\"return escape('" .
											addslashes("{$event['activity_title']} (" . date('H:i', strtotime($event['scheduled_at'])) . '-' . date('H:i', strtotime($event['ends_at'])) . ")") .

											"')\"><img src=\"$http_site_root/img/calendar_time_icon.gif\"></a></td>";

							}
								$virtual_columns_outputted_count++;
							} else {
								if($events[$day_of_week][$event_key]['rowspan']) {
									$events[$day_of_week][$event_key]['rowspan']--;
									$virtual_columns_outputted_count++;
								}
							}
						}

						// pad the remainder of <td>s

						// store the column number when you store the rowspan
						// because there might be no events, but your rowspan is actually there in the middle so you need to output<td>fin</td><td colspan=2>fin</td>

						$finishing_colspan = $colspan_by_day[$day_of_week] - $virtual_columns_outputted_count;

						if($finishing_colspan > 0) {
							$finishing_colspan_html = "colspan=$finishing_colspan";

							$widget .= "<td class=widget_content $finishing_colspan_html>&nbsp;</td>";
						}
						/*
						while($virtual_columns_outputted_count < $colspan_by_day[$day_of_week]) {
							$widget .= '<td class=widget_content>&nbsp;</td>';
							$virtual_columns_outputted_count++;
						}
						*/
					} else {
						// no events today.
						$widget .= '<td class="widget_content">&nbsp;</td>';
					}
				}
				$widget .= "</tr>\n";
			}

			$display_date = date('M d', strtotime($this->start_date)) . ' - ' . date('M d', strtotime($this->start_date) + 6 * 86400);

			$calendar_nav = "
		    <tr>
		     <td colspan=30 class=widget_content>
		      <table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
		       <tr>
		        <td class=\"widget_label center\">
		            <input class=button type=button value=\"Previous Week\" onclick=\"javascript:calendar_previous_week();\">
		            <!--
		            <input class=button type=button value=\"Previous Day\" onclick=\"javascript:calendar_previous_day();\">
		            -->
				</td>
		        <td class=\"widget_label center\">
		         $display_date
		        </td>
		        <td class=\"widget_label center\">
		            <!--
		            <input class=button type=button value=\"Next Day\" onclick=\"javascript:calendar_next_day();\">
		            -->
		            <input class=button type=button value=\"Next Week\" onclick=\"javascript:calendar_next_week();\">
		        </td>
		       </tr>
		      </table>
			</td>
		 	</tr>";
	

		break;


	}

	$return['content'] = "
		<!-- Calendar Begins -->\n
		<div id=\"xrms_calendar\">
			<input type=hidden name=\"{$this->calendar_date_field}\" value=\"{$this->start_date}\">
			<input type=hidden name=\"calendar_display_mode\" value=\"{$this->display_mode}\">
	   		<table class=\"widget\" cellspacing=\"1\">
	    		<tr>
	        		<td colspan=30 class='widget_header'>Calendar</td>
	    		</tr>
				$calendar_nav
				$widget
				$view_mode_buttons
			</table>
		</div>
		<!-- Calendar Ends -->
	";

	$return['sidebar'] = '';



	// Build the user_legend sidebar
	$legend = '';
	if(count($this->td_user_class)) {
		$legend .= '<div id="xrms_calendar_legend">';
		$legend .= '<table class="widget"><tr><td class="widget_header" colspan=23>' . _('Legend') . '</td></tr>';

		$i=1;
		foreach($this->td_user_class as $user_id => $user_style_id) {

			$user_info = get_xrms_user($this->con, null, $user_id);

			$legend .= "<tr><td class=\"{$this->user_style_prefix}$i small\"><img src=\"$http_site_root/img/calendar_time_icon.gif\"></td><td class=\"widget_content\">{$user_info['last_name']}, {$user_info['first_names']}</td></tr>\n";
			$i++;
		}
		$legend .= '</table>';
		$legend .= '</div>';
	}

	$return['sidebar'] = $legend;
	$return['js'] = $this->GetCalendarJS();

	return $return;
}

/**
* Given an array of activities, builds an array of "day's events".
* This is used to simplify the rendering of events that span multiple days.
* @param array activities
* @return array events
*/
function BuildDailyEvents($activity_data) {

	$events = array();

	$user_style_index = 1;

	if ($activity_data)
	foreach($activity_data as $activity) {

		if($activity['scheduled_at']) {

			$activity_start_unixtime = strtotime($activity['scheduled_at']);
			$activity_end_unixtime = strtotime($activity['ends_at']);
			$start_date_unixtime = strtotime($this->start_date);

			
			$start_day_of_week = ($activity_start_unixtime - $start_date_unixtime) / 86400;
			$end_day_of_week = ($activity_end_unixtime - $start_date_unixtime) / 86400;

			if($start_day_of_week < 0) {
				$start_day_of_week = intval($start_day_of_week) - 1;
			} else {
				$start_day_of_week = intval($start_day_of_week);
			}
			if($end_day_of_week < 0) {
				$end_day_of_week = intval($end_day_of_week) - 1;
			} else {
				$end_day_of_week = intval($end_day_of_week);
			}
			

			for($i=$start_day_of_week; $i<($end_day_of_week+1); $i++) {
				$events[$i][] = $activity;
			}

			if(!$this->td_user_class[$activity['user_id']]) {
				$this->td_user_class[$activity['user_id']] = $user_style_index;
				$user_style_index++;
			}
	 	}
	}
	return $events;
}

/**
* Returns the javascript functions like calendar_next_day that are used by the buttons in the calendar.
*/
function GetCalendarJS() {

	$calendar_start_date = $this->start_date;
	$date_field_name = $this->calendar_date_field;

        $year = date('Y', strtotime($calendar_start_date));
        $month = date('m', strtotime($calendar_start_date));
        $day = date('d', strtotime($calendar_start_date));

        $month_n = $month+1;
        $year_n = $year;
        if($month_n > 12) {
            $month_n = 1;
            $year_n = $year +1;
        }
       	$next_month =  "$year_n-$month_n-$day";

        $month_p = $month-1;
        $year_p = $year;
        if(!$month_p) {
            $month_p=12;
            $year_p = $year -1;
        }
        $prev_month = "$year_p-$month_p-$day";

	return "
<script language=\"JavaScript\" type=\"text/javascript\">

function calendar_next_day() {
    document.{$this->form_name}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' +1 days')) . "';
    document.{$this->form_name}.submit();
}
function calendar_next_week() {
    document.{$this->form_name}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' +7 days')) . "';
    document.{$this->form_name}.submit();
}
function calendar_next_month() {
    document.{$this->form_name}.$date_field_name.value = '$next_month';
    document.{$this->form_name}.submit();
}

function calendar_previous_week() {
    document.{$this->form_name}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' -7 days')) . "';
    document.{$this->form_name}.submit();
}
function calendar_previous_day() {
    document.{$this->form_name}.$date_field_name.value = '" .  date('Y-m-d', strtotime($calendar_start_date . ' -1 days')) . "';
    document.{$this->form_name}.submit();
}
function calendar_previous_month() {
    document.{$this->form_name}.$date_field_name.value = '$prev_month';
    document.{$this->form_name}.submit();
}
</script>
	";
}

/**
*
* Create an ADOdb query WHERE string to limit activities returned to only those visible.
*
* @return string Query WHERE clause
*/
function GetCalendarSQLOffset() {

    // set up the query offsets for the calendar view

    // special case for month view...
    if('month' == $this->calendar_type) {
        $adjusted_start_date = CalendarView::GetWeekStart($this->start_date, 'Monday');

        $calendar_view_start =  (strtotime($adjusted_start_date) - time()) / 86400;
        $calendar_view_end = (strtotime("$adjusted_start_date +6 weeks") - time()) / 86400;

    } else {
        $calendar_view_start =  (strtotime($this->start_date) - time()) / 86400;
        $calendar_view_end = (strtotime("$this->start_date +1 $this->calendar_type") - time()) / 86400;
    }

    $offset_start = $this->con->OffsetDate($calendar_view_start);
    $offset_start = ereg_replace(",",".",$offset_start);
    $offset_end = $this->con->OffsetDate($calendar_view_end);
    $offset_end = ereg_replace(",",".",$offset_end);
    $offset_sql = "\nAND a.ends_at > $offset_start AND a.scheduled_at < $offset_end";

 	//echo "GetCalendarSQLOffset $this->calendar_type, $this->start_date range is $calendar_view_start-$calendar_view_end<br>";

    return $offset_sql;
}


}
/**
* $Log: Calendar_View.php,v $
* Revision 1.9  2005/09/23 20:57:48  daturaarutad
* add tooltip for calendar events
*
* Revision 1.8  2005/07/23 00:16:11  vanmer
* - ensure activity data is available before adding anything to the event list
*
* Revision 1.7  2005/06/29 15:39:00  daturaarutad
* moved view mode buttons to bottom of widget
*
* Revision 1.6  2005/06/27 16:32:53  daturaarutad
* updated to work with GetActivitiesWidget and improved Initial date set
*
* Revision 1.5  2005/06/08 15:53:38  daturaarutad
* fixed a bug with non-current-month activities being misplaced on the monthly calendar.  Also added return_url to the links to activities/one.php
*
* Revision 1.4  2005/05/20 17:39:15  daturaarutad
* added missing </td>s
*
* Revision 1.3  2005/05/20 17:20:24  daturaarutad
* added cellspacing=1 to tables
*
* Revision 1.2  2005/05/18 21:51:23  daturaarutad
* removed trailing spaces...added check that there are events before creating legend
*
* Revision 1.1  2005/05/18 16:16:57  daturaarutad
* new location for this file. (old was calendar/agenda/Calendar_View.php)
*
* Revision 1.11  2005/05/14 18:18:57  daturaarutad
* fixed missing strtotime() calls
*
* Revision 1.10  2005/05/14 18:08:08  daturaarutad
* rewrite.  see @todo for what is left
*
* Revision 1.9  2005/05/06 22:12:56  daturaarutad
* fixed a bug in year transition when calculating previous month
*
* Revision 1.8  2005/05/05 17:50:30  daturaarutad
* removed debug msg
*
* Revision 1.7  2005/05/05 17:21:33  daturaarutad
* added better comments...changed Render() and added GetWeekStart
*
* Revision 1.6  2005/04/18 17:44:26  daturaarutad
* removed debug msg
*
* Revision 1.5  2005/04/18 16:34:40  daturaarutad
* fixed the last fix
*
* Revision 1.4  2005/04/18 15:26:13  daturaarutad
* removed bad include path
*
* Revision 1.3  2005/04/15 01:39:26  daturaarutad
* added phpdoc comments
*
*/

?>
