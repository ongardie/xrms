<?php
/**
* Application for adding and editing activity recurrence meta-information
*
* @author Justin Cooper
*
* $Id: edit_activity_recurrence.php,v 1.4 2005/06/06 23:23:27 daturaarutad Exp $
*/


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-recurrence.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

GetGlobalVar($return_url,'return_url');
GetGlobalVar($msg, 'msg');

// from the activity record
GetGlobalVar($activity_id, 'activity_id');
GetGlobalVar($scheduled_at, 'scheduled_at');
// activities_recurrence data
GetGlobalVar($activity_recurrence_id, 'activity_recurrence_id');
GetGlobalVar($period, 'period');
// daily(1)
GetGlobalVar($daily_frequency, 'daily_frequency');
// weekly(1)
GetGlobalVar($weekly_frequency, 'weekly_frequency');
GetGlobalVar($weekly_week_days, 'weekly_week_days');
// monthly (1)
GetGlobalVar($monthly_frequency, 'monthly_frequency');
GetGlobalVar($monthly_day_offset, 'monthly_day_offset');
// monthly (2)
GetGlobalVar($monthly_week_offset, 'monthly_week_offset');
GetGlobalVar($monthly_week_days, 'monthly_week_days');
// yearly (1)
GetGlobalVar($yearly_frequency, 'yearly_frequency');
GetGlobalVar($yearly_day_offset, 'yearly_day_offset');
GetGlobalVar($yearly_month_offset, 'yearly_month_offset');
// yearly (2)
GetGlobalVar($yearly_week_offset, 'yearly_week_offset');
GetGlobalVar($yearly_week_days, 'yearly_week_days');
GetGlobalVar($yearly_month_offset2, 'yearly_month_offset2');
// yearly (3)
GetGlobalVar($yearly_day_offset2, 'yearly_day_offset2');

GetGlobalVar($recurrence_range, 'recurrence_range');
GetGlobalVar($end_count, 'end_count');
GetGlobalVar($end_datetime, 'end_datetime');

global $http_site_root;

if ($btCancel==_("Cancel")) {
    //Cancelling, go back to return url immediately
    Header("Location: {$http_site_root}{$return_url}");
    exit;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

if ($activity_id) {
    $activity_info=get_activity($con, array('activity_id'=>$activity_id));
    $activity=current($activity_info);
} else if ($activity_participant_action!='deleteActivityParticipant') {
    $msg=urlencode(_("Failed to find activity"));
    Header("Location:some.php?msg=$msg");
    exit;
}

/*
* Guide to usage of activities_recurrence fields:
*
*	 Period   		SQL field		FORM field				Meaning
*	 daily			frequency		daily_frequency			Every N day
*	
*	 Recur on W days every N week
*	 weekly			frequency		weekly_frequency		Every N week
*	 weekly			week_days		weekly_week_days		Every W of the week (starts Sunday so 1,2,3,4,5,6 is M-F)
*	
*	 Recur on the D day of the month (23rd)
*	 monthly1		frequency		monthly_frequency		Every N month
*	 monthly1		day_offset		monthly_day_offset		Every D month
*	
*	 Recur on the O W of the month (4th monday)
*	 monthly2		frequency		monthly_frequency		Every N month
*	 monthly2		week_offset		monthly_week_offset		Every O week
*	 monthly2		week_days		monthly_week_days		Every W of the week (starts Sunday so 1,2,3,4,5,6 is M-F)
*	
*	 Recur on day D of M (31st of October)
*	 yearly1		frequency		yearly_frequency		Every N year
*	 yearly1		day_offset		yearly_day_offset		Every D Day
*	 yearly1		month_offset	yearly_month_offset 	Every M month
*	
*	 Recur on the O W of M (2nd Tuesday of February)
*	 yearly2		frequency		yearly_frequency		Every N year
*	 yearly2		week_offset		yearly_week_offset		Every D Day
*	 yearly2		week_days		yearly_week_days		Every W of the week (starts Sunday so 1,2,3,4,5,6 is M-F)
*	 yearly2		month_offset	yearly_month_offset2	Every M month
*	
*	 Recur on the N day of the year
*	 yearly3		frequency		yearly_frequency		Every N year
*	 yearly3		day_offset		yearly_day_offset2		Every D Day
*	 
*/
	// this array is used for strtotime, as in +1 days and is defined in utils-recurrence.php
	global $period_to_span;
    global $days_of_week_long;
    global $months_of_year;

	switch($period) {
		case 'daily1':
			$frequency 		= $daily_frequency;
			$day_offset 	= 0;
			break;
		case 'weekly1':
			$frequency 		= $weekly_frequency;
			$week_days 		= $weekly_week_days;
			$day_offset 	= 0;
			break;
		case 'monthly1':
			$frequency 		= $monthly_frequency;
			$day_offset 	= $monthly_day_offset;
			break;
		case 'monthly2':
			$frequency  	= $monthly_frequency;
			$week_offset 	= $monthly_week_offset;
			$week_days   	= $monthly_week_days;
			break;
		case 'yearly1':
			$frequency   	= $yearly_frequency;
			$day_offset  	= $yearly_day_offset;
			$month_offset 	= $yearly_month_offset;
			break;
		case 'yearly2':
			$frequency 		= $yearly_frequency;
			$week_offset  	= $yearly_week_offset;
			$week_days 		= $yearly_week_days;
			$month_offset 	= $yearly_month_offset2;
			break;
		case 'yearly3':
			$frequency 		= $yearly_frequency;
			$day_offset 	= $yearly_day_offset2;
			break;
	}



	// Insert/Update the activities_recurrence record
	$rec = array();
	$rec['activity_id']		= $activity_id;
	$rec['start_datetime']  = $activity['scheduled_at'];
	if('end_on' == $recurrence_range) {
		$rec['end_datetime']    = $end_datetime;
	} else {
		$rec['end_count']    	= $end_count;
	}
	$rec['frequency']       = $frequency;
	$rec['period']          = $period;
	$rec['day_offset']      = $day_offset;
	$rec['month_offset']    = $month_offset;
	$rec['week_offset']     = $week_offset;
	if(is_array($week_days)) {
		$rec['week_days']       = join(',', $week_days);
	} else {
		$rec['week_days']       = $week_days;
	}

   	$tbl = 'activities_recurrence';

	if($activity_recurrence_id) {

    	$sql = "SELECT * FROM $tbl WHERE activity_recurrence_id = $activity_recurrence_id";
    	$rst = $con->execute($sql);
		if(!$rst) {
			db_error_handler ($con,$sql);
		}

    	$upd = $con->GetUpdateSQL($rst, $rec, true, get_magic_quotes_gpc());
    	if (strlen($upd)>0) {
        	$upd_rst = $con->execute($upd);
        	if (!$upd_rst) {
            	db_error_handler ($con, $upd);
        	}

			// delete the previously created activities that have not yet happened
			$where_clause = "activity_recurrence_id=$activity_recurrence_id and scheduled_at > " . $con->OffsetDate(0);
			delete_activities($con, $where_clause, false, false, true);

    	}
		$action_msg = _("Recurring Activities successfully updated");
	} else {
    	$sql = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());

    	if(!$con->execute($sql)) {
			db_error_handler ($con,$sql);
		}
		$action_msg = _("Recurring Activities successfully added");
    	$activity_recurrence_id = $con->insert_id();
	}

	
    // create activities from recurrence
    $start_time = null;



	// set up loop start and end time
	$start = strtotime($rec['start_datetime']);

	if('end_on' == $recurrence_range) {
		$end = strtotime($rec['end_datetime']);
	} else {
		$end = strtotime($rec['start_datetime'] . " +{$rec['end_count']} {$period_to_span[$rec['period']]}");
	}

	//echo "working from " . date('Y-m-d', $start) . " to " . date('Y-m-d', $end) . " +1 $offset<br>";

	$activities_to_add = build_recurring_activities_list($start, $end, $rec['period'], $frequency, $day_offset, $week_offset, $week_days, $month_offset);
	



	// now, insert all these activities...
	
	$activity_length = strtotime($activity['ends_at']) - strtotime($activity['scheduled_at']);

	foreach($activities_to_add as $add_datetime) {

		$activity['activity_id'] = null;
		$activity['scheduled_at'] = date('Y-m-d H:i:s', $add_datetime);
		$activity['activity_recurrence_id'] = $activity_recurrence_id;

		//echo "adding at {$activity['scheduled_at']}<br>";

		if($activity_length) {
			$activity['ends_at'] = date('Y-m-d H:i:s', strtotime("+$activity_length seconds {$activity['scheduled_at']}"));
		} else {
			$activity['ends_at'] = $activity['scheduled_at'];
		}

		$tbl = 'activities';

    	$ins = $con->GetInsertSQL($tbl, $activity, get_magic_quotes_gpc());

    	$rst = $con->execute($ins);
		if(!$rst) {
			db_error_handler($con, $ins);
		}


	}


$msg=urlencode(_($action_msg));
Header("Location:{$http_site_root}$return_url&msg=$msg");


/*
 * $Log: edit_activity_recurrence.php,v $
 * Revision 1.4  2005/06/06 23:23:27  daturaarutad
 * moved globals and build_activities_list() to utils-recurrence.php in include/
 *
 * Revision 1.3  2005/06/03 18:48:42  daturaarutad
 * too many changes to mention.  should be considered initially functional at this point.
 *
 * Revision 1.2  2005/05/25 05:17:58  daturaarutad
 * added sql update ability
 *
 * Revision 1.1  2005/05/24 16:41:39  daturaarutad
 * initial not-necessarily-working version
 *
*/
?>
