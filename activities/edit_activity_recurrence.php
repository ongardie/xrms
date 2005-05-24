<?php
/**
* Application for adding and editing activity recurrence meta-information
*
* @author Justin Cooper
*
*/


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

GetGlobalVar($return_url,'return_url');
GetGlobalVar($msg, 'msg');

// from the activity record
GetGlobalVar($activity_id, 'activity_id');
GetGlobalVar($scheduled_at, 'scheduled_at');
// activities_recurrence data
GetGlobalVar($activities_recurrence_id, 'activities_recurrence_id');
GetGlobalVar($frequency_type, 'frequency_type');
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


if($activity_recurrence_id) {
	//probably do the same as below, but possibly want to modify the start_date...

} else {

	switch($frequency_type) {
		case 'daily':
			$frequency = $daily_frequency;
			$day_offset = 0;

			break;

		case 'weekly':
			$frequency = $weekly_frequency;
			$day_offset = 0;
			break;
		case 'monthly':
			$frequency = $monthly_frequency;
			$day_offset = $monthly_day_offset;
			$week_offset = $monthly_week_offset;
			$week_days = $monthly_week_days;

			break;
		case 'yearly':
			$frequency = $yearly_frequency;
			if(isset($yearly_day_offset)) {
				$day_offset = $yearly_day_offset;
			} else {
				$day_offset = $yearly_day_offset2;
			}
			if(isset($yearly_month_offset)) {
				$month_offset = $yearly_month_offset;
			} else {
				$month_offset = $yearly_month_offset2;
			}
			$week_days = $yearly_week_days;
			break;
	}

/*
* Guide to usage of activities_recurrence fields:
*
*	 REC. TYPE		SQL field		FORM field				Meaning
*	 daily			frequency		daily_frequency			Every N day
*	
*	 Recur on W days every N week
*	 weekly			frequency		weekly_frequency		Every N week
*	 weekly			week_days		weekly_week_days		Every W of the week (starts Sunday so 1,2,3,4,5,6 is M-F)
*	
*	 Recur on the D day of the month (23rd)
*	 monthly		frequency		monthly_frequency		Every N month
*	 monthly		day_offset		monthly_day_offset		Every D month
*	
*	 Recur on the O W of the month (4th monday)
*	 monthly		frequency		monthly_frequency		Every N month
*	 monthly		week_offset		monthly_week_offset		Every O week
*	 monthly		week_days		monthly_week_days		Every W of the week (starts Sunday so 1,2,3,4,5,6 is M-F)
*	
*	 Recur on day D of M (31st of October)
*	 yearly			frequency		yearly_frequency		Every N year
*	 yearly			day_offset		yearly_day_offset		Every D Day
*	 yearly			month_offset	yearly_month_offset 	Every M month
*	
*	 Recur on the O W of M (2nd Tuesday of February)
*	 yearly			frequency		yearly_frequency		Every N year
*	 yearly			week_offset		yearly_week_offset		Every D Day
*	 yearly			week_days		yearly_week_days		Every W of the week (starts Sunday so 1,2,3,4,5,6 is M-F)
*	 yearly			month_offset	yearly_month_offset2	Every M month
*	
*	 Recur on the N day of the year
*	 yearly			frequency		yearly_frequency		Every N year
*	 yearly			day_offset		yearly_day_offset2		Every D Day
*	 
*/

    $days_of_week_long = array(_('Sunday'), _('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'));
    $months_of_year = array(_('January'), _('February'), _('March'), _('April'), _('May'), _('June'), _('July'), _('August'), _('September'), _('October'), _('November'), _('December'));


	$rec = array();
	$rec['activity_id']		= $activity_id;
	$rec['start_datetime']  = $scheduled_at;
	$rec['end_count']    	= $end_count;
	$rec['end_datetime']    = $end_datetime;
	$rec['frequency']       = $frequency;
	$rec['period']          = $frequency_type;
	$rec['day_offset']      = $day_offset;
	$rec['month_offset']    = $month_offset;
	$rec['week_offset']     = $week_offset;
	$rec['week_days']       = $week_days;

    $tbl = 'activities_recurrence';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());

	echo $ins;

    //$con->execute($ins);

    $activity_id = $con->insert_id();

	
    // create activities from recurrence
    $start_time = null;
    $activities_count = 0;

	// useful for strtotime
	switch($rec['period']) {
		case 'daily': $offset = 'days'; break;
		case 'weekly': $offset = 'weeks'; break;
		case 'monthly': $offset = 'months'; break;
		case 'yearly': $offset = 'years'; break;
		default: echo "error no period selected!!!!!!!!!!!!!!!!!!!!!!!!!!!\n<br>"; exit(); break;
	}

	$start = strtotime($rec['start_datetime']);
	if('end_on' == $recurrence_range) {
		$end = strtotime($rec['end_datetime']);
	} else {
		$end = strtotime($rec['start_datetime'] . " +{$rec['end_count']} $offset");
	}

	echo "working from " . date('Y-m-d', $start) . " to " . date('Y-m-d', $end) . " +1 $offset<br>";

	$activities_to_add = array();
    for($current_time = $start; $current_time < $end; $current_time = strtotime(date('Y-m-d', $current_time). " +1 $offset")) {

		$current_date = date('Y-m-d', $current_time);
		echo "$current_date<br>\n";

        // every N (days/weeks/months)
        if(0 == ($activities_count % $recurrence_freq)) {

			$month = date('m', $current_time);
			$year = date('Y', $current_time);

            switch($rec['period']) {
                case 'daily':
                    $activities_to_add[] = $current_time;
                    break;
                case 'weekly':
					foreach($weekly_week_days as $day) { 
                    	$activities_to_add[] = strtotime($current_date . " +$day days");
					}
                    break;
                case 'monthly':

					if($monthly_week_offset) {
						// Recur on the O W of the month (4th monday)
						// strtotime('+1 week Tuesday', strtotime('first May 2005'));"
						$activities_to_add[] = strtotime('+' . ($monthly_week_offset-1)  . ' week ' . $days_of_week_long[$monthly_week_days], strtotime('first ' . date('F Y', $current_time)));

					} elseif($monthly_day_offset) {
						// Recur on the D day of the month (23rd)
						$activities_to_add[] = strtotime("$year-$month-$monthly_day_offset");
					} else {
						// should never get here...
						echo "parse error M in edit_activity_recurrence";
					}
                    break;

                case 'yearly':

					if($yearly_day_offset) {
						// Recur on day D of M (31st of October)
						$activities_to_add[] = strtotime("$year-" . ($yearly_month_offset+1) . "-$yearly_day_offset");

					} elseif($yearly_week_offset) {
						// Recur on the O W of M (2nd Tuesday of February)
						// "+N week Sunday", strtotime(first 2005)
						$activities_to_add[] = strtotime('+' . ($yearly_week_offset-1)  . ' week ' . $days_of_week_long[$yearly_week_days], strtotime("first {$months_of_year[$yearly_month_offset2]} $year"));

					} elseif($yearly_day_offset2) {
						// Recur on the N day of the year
						$activities_to_add[] = strtotime('+' . ($yearly_day_offset2-1)  . " days $year-01-01");

					} else {
						echo "parse error Y in edit_activity_recurrence";
					}
                    break;
			}
			$activities_count++;
        }
    }

	// now, insert all these activities...gonna need the original to clone it
	foreach($activities_to_add as $add_datetime) {
		echo "inserting at " . date('D Y-m-d H:i', $add_datetime) . "<br>\n";
	}

}




/*
 * $Log: edit_activity_recurrence.php,v $
 * Revision 1.1  2005/05/24 16:41:39  daturaarutad
 * initial not-necessarily-working version
 *
 * Revision 1.1  2005/04/15 07:35:14  vanmer
 * -Initial Revision of an application for adding and removing contacts from activities
 *
*/
?>
