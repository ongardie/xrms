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
 * $Id: recurrence_sidebar.php,v 1.1 2005/05/24 16:41:39 daturaarutad Exp $
 */
require_once($include_directory.'utils-activities.php');
// add recurrence information block on sidebar
if (!$activity_id) { $recurrence_block=''; return false; }



// try to locate the AR record and set the values from that

// if not:
$end_datetime = date('Y-m-d', strtotime(date('Y-m-d') . ' +5 years'));
$daily_frequency = 1;
$weekly_frequency = 1;
$monthly_frequency = 1;
$yearly_frequency = 1;


$weekly_checkboxes_weekdays = get_checkboxes_weekdays();
$monthly_options_weekdays = get_select_options_weekdays();
$yearly_options_weekdays = get_select_options_weekdays();
$yearly_options_months = get_select_options_months();
$yearly_options_months2 = get_select_options_months();


function get_checkboxes_weekdays($day_offset = null) {
	$days_of_week = array(_('Sun'), _('Mon'), _('Tue'), _('Wed'), _('Thu'), _('Fri'), _('Sat'));
	$options = '';

	foreach($days_of_week as $k => $day_name) {
		if($k == $day_offset) {
			$options .= "<input type=checkbox name=\"weekly_week_days[]\" value=\"$k\" selected>$day_name\n"; 
		} else {
			$options .= "<input type=checkbox name=\"weekly_week_days[]\" value=\"$k\">$day_name\n"; 
		}
	}
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

$options_months = get_select_options_months();

function get_select_options_months($month_offset = null) {
	$months_of_year = array(_('January'), _('February'), _('March'), _('April'), _('May'), _('June'), _('July'), _('August'), _('September'), _('October'), _('November'), _('December'));
	$options = '';

	foreach($months_of_year as $k => $month_name) {
		if($k == $month_offset) {
			$options .= "<option value=$k>$month_name</option>\n";
		} else {
			$options .= "<option value=$k selected>$month_name</option>\n";
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
<input type=hidden name=scheduled_at value=\"$scheduled_at\">

<table class=widget cellspacing=1 width=\"100%\">
    <tr>
        <td class=widget_header colspan=2>".('Recurring Activity')."</td>
    </tr>
	<!-- Daily -->
    <tr>
        <td class=widget_label colspan=2>".('Frequency')."</td>
    </tr>
	<tr>
		<td>
		<input type=radio name=frequency_type value=daily>"._('Daily')."</td>
		<td>"._('Every ')."<input type=\"text\" size=\"2\" name=\"daily_frequency\" value=\"$daily_frequency\">"._(' day(s)')."</td>
	</tr>
	<!-- Weekly -->
	<tr>
		<td rowspan=2>
		<input type=radio name=frequency_type value=weekly>"._('Weekly')."</td>
		<td>"._('Every ')."<input type=\"text\" size=\"2\" name=\"weekly_frequency\" value=\"$weekly_frequency\">"._(' week(s)')."</td>
	</tr>
	<tr>
		<td> $weekly_checkboxes_weekdays </td>
	</tr>
	<!-- Monthly -->
	<tr>
		<td rowspan=3>
		<input type=radio name=frequency_type value=monthly>"._('Monthly')."</td>
		<td>"._('Every ')."<input type=\"text\" size=\"2\" name=\"monthly_frequency\" value=\"$monthly_frequency\">"._(' month(s)')."</td>
	</tr>
	<tr>
		<td>"._('Recur on the ')."<input type=\"text\" size=\"2\" name=\"monthly_day_offset\" value=\"$monthly_day_offset\">"._(' day of the  month')."</td>
	</tr>
	<tr>
		<td>"._('Recur on the ')."<input type=\"text\" size=\"2\" name=\"monthly_week_offset\" value=\"$monthly_week_offset\">
		<select name=monthly_week_days>$monthly_options_weekdays</select>" .  _(' of the month')."</td>
	</tr>
	<!-- Yearly -->
	<tr>
		<td rowspan=4>
		<input type=radio name=frequency_type value=yearly>"._('Yearly')."</td>
		<td>"._('Every ')."<input type=\"text\" size=\"2\" name=\"yearly_frequency\" value=\"$yearly_frequency\">"._(' year(s)')."</td>
	</tr>
	<tr>
		<td>"._('Recur on day ')."<input type=\"text\" size=\"2\" name=\"yearly_day_offset\" value=\"$yearly_day_offset\">"._(' of ')."
		<select name=yearly_month_offset>$yearly_options_months</select></td>
	</tr>
	<tr>
		<td> " .
			_('Recur on the ')."<input type=\"text\" size=\"2\" name=\"yearly_week_offset\" value=\"$yearly_week_offset\"><select name=yearly_week_days>$yearly_options_weekdays</select>"._(' of ')."
		<select name=yearly_month_offset2>$yearly_options_months2</select>
		</td>
	</tr>
	<tr>
		<td>"._('Recur on the ')."<input type=\"text\" size=\"2\" name=\"yearly_day_offset2\" value=\"$yearly_day_offset2\">"._(' day of the  year')."</td>
	</tr>

    <tr>
        <td class=widget_label colspan=2>"._('Recurrence Range')."</td>
    </tr>

	<tr>
		<td rowspan=2 class=widget>"._('Ends')."</td>
		<td class=widget>
			<input type=radio name=recurrence_range value=end_after>" . _('End After ') ."
			<input type=text size=\"2\" name=\"end_count\" value=$end_count>" . _(' occurence(s)'). "
		</td>
	</tr>

	<tr>
		<td class=widget>
			<input type=radio name=recurrence_range value=end_on checked>" . _('End on '). "
			<input type=text size=\"12\" name=\"end_datetime\" value=$end_datetime>
		</td>
	</tr>

	<tr>
		<td colspan=2 class=widget_content_form_element>
			<input type=submit value=\""._("Add New Recurring Activity")."\" class=button name=btAddRecurring>
			<input type=submit value=\""._("Update Recurring Activity")."\" class=button name=btEditRecurring>
		</td>
	</tr>

</table>
</form>
<!-- Begin Recurrence Widget -->
";

/**
 * $Log: recurrence_sidebar.php,v $
 * Revision 1.1  2005/05/24 16:41:39  daturaarutad
 * initial not-necessarily-working version
 *
 * Revision 1.4  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.3  2005/04/22 22:05:53  ycreddy
 * Added the missing .php extension for the Remove Participant Link
 *
 * Revision 1.2  2005/04/18 23:34:12  maulani
 * - recurrence sidebar include was stomping on $return_url variable.  Changed
 *   variable name to resolve conflict in activities/one.php
 *
 * Revision 1.1  2005/04/15 16:55:07  vanmer
 * -Initial revision of the sidebar for recurrence lists on an activity
 *
**/
?>
