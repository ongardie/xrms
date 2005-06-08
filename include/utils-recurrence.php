<?php
/**
 * Utility functions for manipulating activity recurrence
 *
 * @author Justin Cooper
 *
 * $Id: utils-recurrence.php,v 1.3 2005/06/08 00:09:25 daturaarutad Exp $
 */


global $period_to_span;

$period_to_span = array();

$period_to_span['daily1']   = 'days';
$period_to_span['daily2']   = 'days';
$period_to_span['weekly1']  = 'weeks';
$period_to_span['monthly1'] = 'months';
$period_to_span['monthly2'] = 'months';
$period_to_span['monthly3'] = 'months';
$period_to_span['yearly1']  = 'years';
$period_to_span['yearly2']  = 'years';
$period_to_span['yearly3']  = 'years';
$period_to_span['yearly4']  = 'years';

global $days_of_week_long;

$days_of_week_long = array(_('Sunday'), _('Monday'), _('Tuesday'), _('Wednesday'), _('Thursday'), _('Friday'), _('Saturday'));

global $months_of_year;

$months_of_year = array(_('January'), _('February'), _('March'), _('April'), _('May'), _('June'), _('July'), _('August'), _('September'), _('October'), _('November'), _('December'));




/********************************************************************************
* Builds an array of timestamps given various parameters for scheduling recurring activities
* See activities/edit_activity_recurrence.php for details.
*
* @param integer start time in seconds
* @param integer end time in seconds
* @param string period such as 'daily1', 'weekly1', 'monthly2', etc. from HTML form
* @param integer frequency (every N period)
* @param integer day_offset see activities/edit_activity_recurrence.php for details.
* @param integer week_offset see activities/edit_activity_recurrence.php for details.
* @param string week_days see activities/edit_activity_recurrence.php for details.
* @param integer month_offset see activities/edit_activity_recurrence.php for details.
* @param boolean only_future will skip all dates that are before today.
*/
function build_recurring_activities_list($start_datetime, $end_datetime, $end_count, $period, $frequency, $day_offset, $week_offset, $week_days, $month_offset) {

    global $period_to_span;
    global $days_of_week_long;
    global $months_of_year;


    $activities_list 	= array();
    $loop_count 		= -1;
    $offset 			= $period_to_span[$period];

	if(!$offset) return false;

    $current_time 		= strtotime($start_datetime); 
	$start_time			= strtotime($start_datetime);
	$end_time			= strtotime($end_datetime);
	$finished 			= false;

    //for($current_time = $starttime; $current_time <= $endtime; $current_time = strtotime(date('Y-m-d H:i:s', $current_time). " +1 $offset")) 
	while(!$finished) {

        $current_date = date('Y-m-d H:i:s', $current_time);

		$loop_count++;

		// skip weekends
		if('daily2' == $period) {
			$current_day = date('w', $current_time);
			if($current_day == 0 || $current_day == 6) {
				$loop_count--;
				//echo "skipping $current_day $current_date setting loop to $loop_count<br>";
				$current_time = strtotime(date('Y-m-d H:i:s', $current_time) . " +1 $offset");
				continue;
			}
		}



        // every N (days/weeks/months)

        if(0 == ($loop_count % $frequency)) {

            $year = date('Y', $current_time);
            $month = date('m', $current_time);
            $hms = date('H:i:s', $current_time);

            // this block maps the HTML form fields to DB fields
            switch($period) {
                case 'daily1':
                    $activities_list[] = $current_time;
                    break;
                case 'daily2':
                   	$activities_list[] = $current_time;
                    break;
                case 'weekly1':
                    foreach($week_days as $day) {
                        $activities_list[] = strtotime($days_of_week_long[$day], $current_time);
                    }
                    break;

                case 'monthly1':
                    // Recur on the D day of the month (23rd)
                    $activities_list[] = strtotime("$year-$month-$day_offset $hms");
                    break;

                case 'monthly2':
                    // Recur on the O W of the month (4th monday)
                    // strtotime('+1 week Tuesday', strtotime('first May 2005'));"
                    $activities_list[] = strtotime('+' . ($week_offset-1)  . ' week ' . $days_of_week_long[$week_days], strtotime('first ' . date('F Y', $current_time)));
                    break;

                case 'monthly3':
                    // Recur on the D business day of the month 
					// Note: highest value for D is 40 (8 weeks == 56 days)

					$day_count = 0;
					//echo "day offset is $day_offset<br>";

					for($i=1; $i<57; $i++) {
						$week_num = date('w', strtotime("$year-$month-$i"));
						if(0 != $week_num && 6 != $week_num) {
							$day_count++;
						} else {
							//echo "skipping this day: $week_num for $year-$month-$i<br>";
						}
						if($day_offset == $day_count) {
						//echo "adding at $year-$month-$i";
                    		$activities_list[] = strtotime("$year-$month-$i $hms");
							break;
						}

					}
                    break;

                case 'yearly1':
                        // Recur on day D of M (31st of October)
                        $activities_list[] = strtotime("$year-" . ($month_offset+1) . "-$day_offset $hms");
                    break;
                case 'yearly2':
                    // Recur on the O W of M (2nd Tuesday of February)
                    // "+N week Sunday", strtotime(first 2005)
                    $activities_list[] = strtotime('+' . ($week_offset-1)  . ' week ' . $days_of_week_long[$week_days], strtotime("$year-" . ($month_offset+1) . "-01 $hms"));
                    break;
                case 'yearly3':
                    // Recur on the N day of the year
                    $activities_list[] = strtotime("$year-01-01 $hms +" . ($day_offset-1) . " days");
                    break;
                case 'yearly4':
                    // Recur on business day D of M (15th business day of October)
					// Note: highest value for D is 40 (8 weeks == 56 days)
					$day_count = 0;

					$month = $month_offset + 1;
					//echo "day offset is $day_offset, month offset is $month<br>";

					for($i=1; $i<57; $i++) {
						$week_num = date('w', strtotime("$year-$month-$i"));
						if(0 != $week_num && 6 != $week_num) {
							$day_count++;
						} else {
							//echo "skipping this day: $week_num for $year-$month-$i<br>";
						}
						if($day_offset == $day_count) {
						//echo "adding at $year-$month-$i";
                    		$activities_list[] = strtotime("$year-$month-$i $hms");
							break;
						}
					}
                    break;

                default:
                    $msg=urlencode(_("Error: No period selected for recurring activity."));
                    Header("Location:{$http_site_root}$return_url&msg=$msg");
                    break;
            }
        }

		// are we finished?
		if($end_count) {
			if(($loop_count / $frequency) > $end_count) {
				$finished = true;
			}
		} else {
			if($current_time >= $end_time) {
				$finished = true;
			}
		}
		$current_time = strtotime(date('Y-m-d H:i:s', $current_time) . " +1 $offset");
    }

    // delete the first one (if it is the same time as starttime) because that is the original activity and we don't want to duplicate it.
	if(count($activities_list)) {
		if($activities_list[0] <= $start_time) {
			array_shift($activities_list);
		}
	}

    return $activities_list;
}


 
 /**
  * $Log: utils-recurrence.php,v $
  * Revision 1.3  2005/06/08 00:09:25  daturaarutad
  * added new periods to specify business days
  *
  * Revision 1.2  2005/06/06 23:24:36  daturaarutad
  * fixed deletion of first activity if same time as starttime issue
  * moved globals into this file for weekday and month names
  * fixed bugs with several recurrence types
  *
  * Revision 1.1  2005/06/06 18:11:01  daturaarutad
  * new file for activity recurrence functions
  *
  * Revision 1.7  2005/06/03 16:40:09  daturaarutad
  * added delete_activities (plural)
  *
  * Revision 1.6  2005/05/25 05:35:53  vanmer
  * - added update so that if activity is completed, completed_by is automatically set
  *
  * Revision 1.5  2005/05/06 20:50:43  vanmer
  * - added function for fetching activity types
  *
  * Revision 1.4  2005/05/06 00:43:16  vanmer
  * - fixed misnamed field when adding a new activity without any participants specified
  *
  * Revision 1.3  2005/04/23 17:49:25  vanmer
  * - changed activity_participant_record_status to ap_record_status to work around 30 character limit in mssql adodb driver
  *
  * Revision 1.2  2005/04/15 08:02:53  vanmer
  * - added flag to control delete of participants when activity is deleted through API
  * - added logic for allowing contact change in activity update code to update default participant
  *
  * Revision 1.1  2005/04/15 07:33:49  vanmer
  * - Initial revision of API for managing activities, participants, and participant positions
  *
  *
**/
?>