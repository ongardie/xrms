<?php
/**
 * Browse to the next Activity in the list
 *
 * Peruses a saved search using "Save and Next"
 *
 * @params int $saved_id or $activity_id
 *
 * @author Neil Roberts
 *
 * $Id: browse-next.php,v 1.13 2004/07/27 22:02:45 cpsource Exp $
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
$next_to_check = isset($_SESSION['next_to_check']) ? $_SESSION['next_to_check'] : '';
// The current activity ID that was being viewed through activities/one.php.
$activity_id = isset($_GET['activity_id']) ? $_GET['activity_id'] : '';
// The saved ID used if using "Saved Search Browse"
$saved_id = isset($_GET['saved_id']) ? $_GET['saved_id'] : '';
// The last position in the activity IDs
$pos = isset($_SESSION['pos']) ? $_SESSION['pos'] : '';

if($saved_id) {
    $pos = 0;
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
    }
}

//If we've created the next_to_check array from outside (ie the saved query)
if($pos >= count($next_to_check)) {
    header("Location: some.php");
}
// If the activity is part of the array, ie if they have already obtained an array, use the array.   
elseif($saved_id or ($next_to_check[$pos] and is_array($next_to_check) and in_array($activity_id, $next_to_check) and ($pos > 0) and ($pos < count($next_to_check)))) {

    // If they try to traverse it out of order, simply move the array around
    if(isset($activity_id) and ($activity_id)) {
        $input = array_splice($next_to_check, array_search($activity_id, $next_to_check), 1);
        array_splice($next_to_check, $pos-1, 0, $input[0]);
    }

    header("Location: one.php?save_and_next=true&activity_id=" . $next_to_check[$pos]);

    $pos++;
}
else {
    header("Location: some.php");
}

$_SESSION['next_to_check'] = $next_to_check;
$_SESSION['pos'] = $pos;

$con->close();

/**
 * $Log: browse-next.php,v $
 * Revision 1.13  2004/07/27 22:02:45  cpsource
 * - Remove undefines
 *
 * Revision 1.12  2004/07/27 19:50:41  neildogg
 * - Major changes to browse functionality
 *  - Removal of sidebar for "browse" button
 *  - Removal of activity_type browse
 *  - Aesthetic modifications
 *  - Date in some.php is now mySQL curdate()
 *
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
