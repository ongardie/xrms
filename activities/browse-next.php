<?php
/**
 * Browse to the next Activity in the list
 *
 * This script can be reached through two different ways.
 * The first is by using the browse-sidebar.php file and clicking on an activity type.
 * The second is by using the save and next button on an activity.
 * In either case, we generate a session list of activity types and activity IDs
 * The activity list is ordered by: expired activities, activities with probabilities, activities ending first
 * We traverse the activities until there are none left. If we skip ahead, the list gets reordered.
 * When there are none left, it drops to the next activity type. When there are none left, it returns to the activities main page.
 *
 * @params int $activity_type_id or $activity_id
 *
 * @author Neil Roberts
 *
 * $Id: browse-next.php,v 1.11 2004/07/23 12:45:47 neildogg Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug=1;

// An array of activity IDs within activity type. Allows changes to be made without activities repeating.
$next_to_check = $_SESSION['next_to_check'];
// The array of activity type IDs that will be traversed
$activity_type_ids = $_SESSION['activity_type_ids'];
// The current activity ID that was being viewed through activities/one.php. Will not be set if using "Browse" on activities/some.php
$activity_id = $_GET['activity_id'];
// The activity type used if using "Browse"
$activity_type_id = $_POST['activity_type_id'];
// The saved ID used if using "Saved Search Browse"
$saved_id = $_POST['saved_id'];
// The last position in the activity IDs
$pos = $_SESSION['pos'];

if($saved_id) {
    $next_to_check = array();
    $sql = "SELECT saved_data
            FROM saved_actions
            WHERE saved_id=" . $saved_id . "
            AND (user_id=" . $session_user_id . "
            OR group_item=1)
            AND saved_status='a'";
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif($rst->rowcount()) {
        $sql = unserialize($rst->fields['saved_data']);
        $sql = $sql['sql'];
        $sql = preg_replace("|^select|i", "select activity_id,", $sql);
    }
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif($rst->rowcount()) {
        while(!$rst->EOF) {
            $next_to_check[] = $rst->fields['activity_id'];
            $rst->movenext();
        }
        header("Location: one.php?save_and_next=true&activity_id=" . $next_to_check[0]);
        $pos = 1;
    }
    else {
        header("Location: some.php");
    }
}
//If we've created the next_to_check array from outside (ie the saved query)
elseif((!count($activity_type_ids)) and ($pos >= count($next_to_check))) {
    header("Location: some.php");
}
// If the activity is part of the array, ie if they have already obtained an array, use the array.   
elseif($next_to_check[$pos] and is_array($next_to_check) and in_array($activity_id, $next_to_check) and ($pos > 0) and ($pos < count($next_to_check))) {
    // If they try to traverse it out of order, simply move the array to around
    $temp_activity_id = $activity_id;
    for($i = $pos; $i < count($next_to_check); $i++) {
        $mktime = mktime();
        $sql = "SELECT (last_modified_at > (now() - interval 15 minute)) as time
                FROM companies c, activities a
                WHERE a.activity_id=$temp_activity_id
                AND a.company_id=c.company_id";
        $rst = $con->execute($sql);
        if(!$rst) {
            db_error_handler($con, $sql);
        }
        elseif($rst->rowcount()) {
            if($rst->fields['time']) {
                $temp_activity_id = $next_to_check[$i+1];
            }
            else {
                $input = array_splice($next_to_check, array_search($temp_activity_id, $next_to_check), 1);
                array_splice($next_to_check, $pos, 0, $input[0]);
                break;
            }
        }
    }
    
    $input = array_splice($next_to_check, array_search($activity_id, $next_to_check), 1);
    array_splice($next_to_check, $pos-1, 0, $input[0]);
    if($i == count($next_to_check)) {
        header("Location: some.php");
    }
    else {
        header("Location: one.php?save_and_next=true&activity_id=" . $next_to_check[$pos]);
    }
    $pos++;
}
else {
    // If someone skips between categories, they may still have a position. If they do, it would force a type traversal. So reset it.
    $pos = 0;
    // If we're not coming from the "Browse" functionality, get the activity ID
    if(!$activity_type_id) {
        $rst = $con->execute("select activity_type_id from activities where activity_id = '$activity_id'");
        $activity_type_id = $rst->fields['activity_type_id'];
        $rst->close();
    }
    else {
        $pos = 0;
    }
    // If there is not yet an array of IDs (either coming from the browse, or from the first save & next), create one 
    if(!count($activity_type_ids)) {
        $sql = "select activity_type_id
            from activity_types
            order by sort_order";
        $rst = $con->execute($sql);
        if(!$rst) {
            db_error_handler($con, $sql);
        }
        elseif($rst->rowcount()){
            while(!$rst->EOF) {
                $activity_type_ids[] = $rst->fields['activity_type_id'];
                $rst->movenext();
            }
        }
        $rst->close();
    }

    $next_to_check = array();
    $more_to_check = true;

    // Please check the logic before you decide that an infinite loop might result. I've taken great care to make sure there is none.
    $start = time();
    while($more_to_check and (time() - $start < 3)) {
        // $pos is greater than zero only if it has traversed outside of the array
        if($pos > 0) {
            // Go to the next activity_type_id
            $activity_type_id = $activity_type_ids[array_search($activity_type_id, $activity_type_ids) + 1];
        }
        // Here is an example of a loop break. If there are no activites left to check, we go to the main activites page.
        if(!$activity_type_id) {
            header("Location: some.php");
            $more_to_check = false;
        }
        else {
            //Find items within activity_type_id, that have expired
            //Important because it's sorted by lateness first
            $sql = "select activity_id
                from activities
                where activity_status = 'o'
                and activity_record_status='a'
                and ends_at < " . $con->DBTimestamp(time()) . "
                and user_id = $session_user_id
                and activity_type_id=$activity_type_id
                order by ends_at desc"; 
            $rst = $con->execute($sql);
            if(!$rst) {
                $more_to_check = false;
                db_error_handler($con, $sql);
            }
            elseif($rst->rowcount() > 0) {
                while(!$rst->EOF) {
                    if($rst->fields['activity_id']) {
                        $next_to_check[] = $rst->fields['activity_id'];
                    }
                    $rst->movenext();
                }
                $more_to_check = false;
                $rst->close();
            }
            else {
                // We don't break the loop, because if we don't find any results, we want to drop to the next lower activity_type_id (see top of loop)
            }
        
            //Get the remaining activities, if they have a probability, sorted by probability, then date 
            $sql = "select a.activity_id
                from activities as a,
                opportunities as o
                where a.activity_status = 'o'
                and a.activity_record_status = 'a'
                and a.ends_at >= " . $con->DBTimestamp(time()) . "
                and a.user_id = $session_user_id
                and a.activity_type_id=$activity_type_id
                and a.on_what_table='opportunities'
                and o.probability > 0
                and a.on_what_id=o.opportunity_id
                order by probability desc, a.ends_at asc";
            $rst = $con->execute($sql);
            if(!$rst) {
                $more_to_check = false;
                db_error_handler($con, $sql);
            }
            elseif($rst->rowcount() > 0) {
                while(!$rst->EOF) {
                    if($rst->fields['activity_id']) {
                        $next_to_check[] = $rst->fields['activity_id'];
                    }
                    $rst->movenext();
                }
                $more_to_check = false;
                $rst->close();
            }

            //Get the remaining activities, including those with probability (we will error check in a second) 
            $sql = "select activity_id
                from activities
                where activity_status = 'o'
                and activity_record_status = 'a'
                and ends_at >= " . $con->DBTimestamp(time()) . "
                and user_id = $session_user_id
                and activity_type_id=$activity_type_id
                order by ends_at asc";
            $rst = $con->execute($sql);
            if(!$rst) {
                $more_to_check = false;
                db_error_handler($con, $sql);
            }
            elseif($rst->rowcount() > 0) {
                while(!$rst->EOF) {
                    if($rst->fields['activity_id'] and !in_array($rst->fields['activity_id'], $next_to_check)) {
                      $next_to_check[] = $rst->fields['activity_id'];
                    }
                    $rst->movenext();
                }
                $more_to_check = false;
                $rst->close();
            }

            // If the loop was broken, go to the "first" element in the array
            if(!$more_to_check) {
                // If we're doing save and next from a random page, we don't want the starting activity ID in the list
                if(!$pos and $activity_id) {
                    array_splice($next_to_check, array_search($activity_id, $next_to_check), 1);
                }    
                if($next_to_check[0]) {
                    header("Location: one.php?save_and_next=true&activity_id=" . $next_to_check[0]);
                }
                else {
                    $more_to_check = true;
                }
                $pos = 1;
            }   
            else {
                // If the first loop on the first occurrence produces nothing (unlikely) drop through activity type IDs
                $pos = 1;
            }
        }
    }
}

$_SESSION['activity_type'] = $activity_type;
$_SESSION['next_to_check'] = $next_to_check;
$_SESSION['activity_type_ids'] = $activity_type_ids;
$_SESSION['pos'] = $pos;

$con->close();

/**
 * $Log: browse-next.php,v $
 * Revision 1.11  2004/07/23 12:45:47  neildogg
 * - Avoid the viewing of companies recently viewed
 *
 * Revision 1.10  2004/07/21 23:27:39  neildogg
 * - General bug fixes (if only 1 element in search)
 *  - No need for opportunities table in first search
 *
 * Revision 1.9  2004/07/21 22:54:06  neildogg
 * - Go back if there are none
 *
 * Revision 1.8  2004/07/21 22:21:41  neildogg
 * - Rearranged sidebar
 *  - Now can browse saved searches
 *
 * Revision 1.7  2004/07/07 21:43:33  neildogg
 * - Fixed inexplicable empty [0] error
 *
 * Revision 1.6  2004/07/02 17:55:14  neildogg
 * Massive logic change, cleaned up code significantly. Now works for all activities
 *
 * Revision 1.5  2004/06/25 03:11:47  braverock
 * - add error handling to avoid empty result sets and endless loops
 *
 * Revision 1.4  2004/06/24 20:19:01  introspectshun
 * - Updated time formats in SELECTs to use DBTimestamp()
 *
 */
?>
