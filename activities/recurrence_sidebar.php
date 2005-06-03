<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * Activity Recurrance Information Sidebar
 *
 * Include this file anywhere you want to show or modify the recurrence for an activity
 *
 * @param integer $activity_id The activity_id should be set before including this file
 *
 * @author Justin Cooper
 *
 *
 * $Id: recurrence_sidebar.php,v 1.3 2005/06/03 18:59:31 daturaarutad Exp $
 */


/*
	@todo:  I'd like to change the UI so that there is a radio button to select which type of recurrence you're
			selecting instead of trying to infer it based on the data, which doesn't work for the last yearly thingy...


*/




require_once($include_directory.'utils-activities.php');
// add recurrence information block on sidebar
if (!$activity_id) { $recurrence_block=''; return false; }



// try to locate the AR record and set the values from that
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM activities_recurrence where activity_id=$activity_id";

$rst = $con->execute($sql);

if($rst) {
	if($rst->rowcount()) {
	
		$rec = $rst->fetchrow();

		$activity_recurrence_id	= $rec['activity_recurrence_id'];
		$end_count 				= $rec['end_count'];
		$end_datetime 			= $rec['end_datetime'];
		$period					= $rec['period'];
	
		// this block maps the DB fields to HTML form fields
		switch($period) {
	
			case 'daily1':
	            $daily_frequency = $rec['frequency'];
				break;
	
	        case 'weekly1':
	            $weekly_frequency = $rec['frequency'];
				$weekly_week_days = explode(',', $rec['week_days']);
	            $day_offset = 0;
	            break;
	
	        case 'monthly1':
	        case 'monthly2':
	        case 'monthly3':
	            $monthly_frequency = $rec['frequency'];
	
				if($rec['week_offset']) {
	            	$monthly_week_offset = $rec['week_offset'];
	            	$monthly_week_days = $rec['week_days'];
				} elseif($rec['day_offset']) {
	            	$monthly_day_offset = $rec['day_offset'];
				}
	            break;
	
	        case 'yearly1':
	        case 'yearly2':
	        case 'yearly3':
	        case 'yearly4':
	            $yearly_frequency = $rec['frequency'];
	
				if($rec['month_offset'] && $rec['day_offset']) {
	            	$yearly_month_offset = $rec['month_offset'];
	            	$yearly_day_offset = $rec['day_offset'];
				} elseif($rec['week_offset']) {
	            	$yearly_week_offset = $rec['week_offset'];
	            	$yearly_week_days = $rec['week_days'];
	            	$yearly_month_offset2 = $rec['month_offset'];
				} elseif($rec['day_offset']) {
	            	$yearly_day_offset2 = $rec['day_offset'];
				}
		}
	
	
	} else {
	
		// if not:
		$end_datetime = date('Y-m-d', strtotime(date('Y-m-d') . ' +5 years'));
		$daily_frequency = 1;
		$weekly_frequency = 1;
		$monthly_frequency = 1;
		$yearly_frequency = 1;
	}
} else {
	 db_error_handler($con, $sql);
}


$weekly_checkboxes_weekdays = get_checkboxes_weekdays($weekly_week_days);
$monthly_options_weekdays = get_select_options_weekdays($monthly_week_days);
$yearly_options_weekdays = get_select_options_weekdays($yearly_week_days);
$yearly_options_months = get_select_options_months($yearly_month_offset);
$yearly_options_months2 = get_select_options_months($yearly_month_offset2);


function get_checkboxes_weekdays($week_days = null) {
	$days_of_week = array(_('Sun'), _('Mon'), _('Tue'), _('Wed'), _('Thu'), _('Fri'), _('Sat'));

	if(!$week_days) $week_days = array();

	$days = '<tr>';
	$checkboxes = '<tr>';

	foreach($days_of_week as $k => $day_name) {
		if(in_array($k, $week_days)) {
			$days .= "<td>$day_name</td>"; 
			$checkboxes .= "<td><input type=checkbox name=\"weekly_week_days[]\" value=\"$k\" checked></td>"; 
		} else {
			$days .= "<td>$day_name</td>"; 
			$checkboxes .= "<td><input type=checkbox name=\"weekly_week_days[]\" value=\"$k\"></td>"; 
		}
	}
	$options = "<table class=embedded>$days $checkboxes</table>\n";

	return $options;
}

function get_select_options_weekdays($day_offset = null) {
	$days_of_week_long = array(_('Sunday'), _('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'));
	$options = '';

	foreach($days_of_week_long as $k => $day_name) {
		if($k == $day_offset) {
			$options .= "<option value=$k selected>$day_name</option>\n";
		} else {
			$options .= "<option value=$k>$day_name</option>\n";
		}
	}
	return $options;
}

function get_select_options_months($month_offset = null) {
	$months_of_year = array(_('January'), _('February'), _('March'), _('April'), _('May'), _('June'), _('July'), _('August'), _('September'), _('October'), _('November'), _('December'));
	$options = '';

	foreach($months_of_year as $k => $month_name) {
		if($k == $month_offset) {
			$options .= "<option value=$k selected>$month_name</option>\n";
		} else {
			$options .= "<option value=$k>$month_name</option>\n";
		}
	}
	return $options;
}


$return_url="/activities/one.php?activity_id=$activity_id";

$recurrence_block = "
<!-- Begin Recurrence Widget -->
<form action=edit_activity_recurrence.php name=ActivityRecurrence method=POST>

<input type=hidden name=activity_id value=$activity_id>
<input type=hidden name=activity_recurrence_id value=$activity_recurrence_id>
<input type=hidden name=return_url value=\"$return_url\">

<table class=widget cellspacing=1 width=\"100%\">
    <tr>
        <td class=widget_header colspan=3>".('Recurring Activity')."</td>
    </tr>
	<!-- Daily -->
    <tr>
        <td class=widget_label>".('Period')."</td>
        <td class=widget_label colspan=2>".('Frequency')."</td>
    </tr>
	<tr>
		<td rowspan=2> "._('Daily')."</td>
		<td colspan=2>" . _('Every ') . "<input type=\"text\" size=\"2\" name=\"daily_frequency\" value=\"$daily_frequency\">"._(' day(s)')."</td>
	</tr>
	<tr>
		<td><input type=radio name=period value=daily1 " . ('daily1' == $period ? 'checked' : '') . "></td> 
		<td>"._('all days of week')."</td>
	</tr>
	<!-- Weekly -->
	<tr>
		<td rowspan=2> "._('Weekly')."</td>
		<td colspan=2>"._('Every ')."<input type=\"text\" size=\"2\" name=\"weekly_frequency\" value=\"$weekly_frequency\">"._(' week(s)')."</td>
	</tr>
	<tr>
		<td><input type=radio name=period value=weekly1 " . ('weekly1' == $period ? 'checked' : '') . ">
		<td> $weekly_checkboxes_weekdays </td>
	</tr>
	<!-- Monthly -->
	<tr>
		<td rowspan=3> "._('Monthly')."</td> 
		<td colspan=2>"._('Every ')."<input type=\"text\" size=\"2\" name=\"monthly_frequency\" value=\"$monthly_frequency\">"._(' month(s)')."</td>
	</tr>
	<tr>
		<td> <input type=radio name=period value=monthly1 " . ('monthly1' == $period ? 'checked' : '') . "> </td> 

		<td>"._('Recur on the ')."<input type=\"text\" size=\"2\" name=\"monthly_day_offset\" value=\"$monthly_day_offset\">"._(' day of the  month')."</td>
	</tr>
	<tr>
		<td> <input type=radio name=period value=monthly2 " . ('monthly2' == $period ? 'checked' : '') . "> </td>

		<td>"._('Recur on the ')."<input type=\"text\" size=\"2\" name=\"monthly_week_offset\" value=\"$monthly_week_offset\">
		<select name=monthly_week_days>$monthly_options_weekdays</select>" .  _(' of the month')."</td>
	</tr>
	<!-- Yearly -->
	<tr>
		<td rowspan=4> "._('Yearly')."</td>
		<td colspan=2> "._('Every ')."<input type=\"text\" size=\"2\" name=\"yearly_frequency\" value=\"$yearly_frequency\">"._(' year(s)')."</td>
	</tr>
	<tr>
		<td><input type=radio name=period value=yearly1 " . ('yearly1' == $period ? 'checked' : '') . "></td>

		<td>"._('Recur on day ')."<input type=\"text\" size=\"2\" name=\"yearly_day_offset\" value=\"$yearly_day_offset\">"._(' of ')."
		<select name=yearly_month_offset>$yearly_options_months</select></td>
	</tr>
	<tr>
		<td><input type=radio name=period value=yearly2 " . ('yearly2' == $period ? 'checked' : '') . "></td>
		<td> " .
			_('Recur on the ')."<input type=\"text\" size=\"2\" name=\"yearly_week_offset\" value=\"$yearly_week_offset\"><select name=yearly_week_days>$yearly_options_weekdays</select>"._(' of ')."
		<select name=yearly_month_offset2>$yearly_options_months2</select>
		</td>
	</tr>
	<tr>
		<td><input type=radio name=period value=yearly3 " . ('yearly3' == $period ? 'checked' : '') . "></td>
		<td>"._('Recur on the ')."<input type=\"text\" size=\"2\" name=\"yearly_day_offset2\" value=\"$yearly_day_offset2\">"._(' day of the  year')."</td>
	</tr>

    <tr>
        <td class=widget_label colspan=3>"._('Recurrence Range')."</td>
    </tr>

	<tr>
		<td rowspan=2 class=widget>"._('Ends')."</td>
		<td class=widget colspan=2>
			<input type=radio name=recurrence_range value=end_after " . ($end_count ? 'checked' : '') . ">" . _('End After ') ."
			<input type=text size=\"2\" name=\"end_count\" value=$end_count>" . _(' occurence(s)'). "
		</td>
	</tr>

	<tr>
		<td class=widget colspan=2>
			<input type=radio name=recurrence_range value=end_on " . ($end_datetime ? 'checked' : '') . ">" . _('End on '). "
			<input type=text size=\"12\" name=\"end_datetime\" value=$end_datetime>
		</td>
	</tr>

	<tr>
		<td colspan=3 class=widget_content_form_element>" . 
			($activity_recurrence_id ? 
			"<input type=submit value=\""._("Update Recurring Activity")."\" class=button name=btEditRecurring>" :
			"<input type=submit value=\""._("Add New Recurring Activity")."\" class=button name=btAddRecurring>") . "
		</td>
	</tr>

</table>
</form>
<!-- Begin Recurrence Widget -->
";

/**
 * $Log: recurrence_sidebar.php,v $
 * Revision 1.3  2005/06/03 18:59:31  daturaarutad
 * updated layout, using period to determine recurrence type
 *
 * Revision 1.2  2005/05/25 05:18:34  daturaarutad
 * added update capability...still not working 100%
 *
 * Revision 1.1  2005/05/24 16:41:39  daturaarutad
 * initial not-necessarily-working version
 */
?>
