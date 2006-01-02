<?php
/**
 * Browse to the next Activity in the list
 *
 * Peruses a saved search using "Save and Next"
 *
 * @params int $saved_id or $activity_id
 *
 * @author Aaron van Meerten
 * @author Neil Roberts
 *
 * $Id: browse-next.php,v 1.25 2006/01/02 21:23:18 vanmer Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$con = get_xrms_dbconnection();
//$con->debug=1;

// An array of activity IDs within activity type. Allows changes to be made without activities repeating.
$next_to_check = isset($_SESSION['next_to_check']) ? $_SESSION['next_to_check'] : array();

// The current activity ID that was being viewed through activities/one.php.
$activity_id = isset($_GET['activity_id']) ? $_GET['activity_id'] : '';

// If the browse button was pressed from a page (starts new browse of activities)
getGlobalVar($new_browse, 'browse');
getGlobalVar($sql_session_var, 'sql_session_var');
if (!$sql_session_var) { $sql_session_var='search_sql'; }

// The last position in the activity IDs
$pos = isset($_SESSION['pos']) ? $_SESSION['pos']: '';
$pos = isset($_GET['pos']) ? $_GET['pos'] - 1: $pos;


if(isset($_GET['pos']) and !$pos and $pos!==0) {
    $pos = 1;
}

if($new_browse) {
    $next_to_check = array();
    $pos = 0;
    $_SESSION['browse_start'] = time(); 

    //code to fetch the sql from a saved_search snipped, now uses session variable
     
    $sql = $_SESSION[$sql_session_var];
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif($rst->rowcount()) {
        while(!$rst->EOF) {
            $next_to_check[] = $rst->fields['activity_id'];
            $rst->movenext();
        }
        //ensure that any duplicates get removed
        $next_to_check=array_merge(array_unique($next_to_check), array());
    }
}

//broken code to rearrange list of next_to_check activities removed

//If we've created the next_to_check array from outside (ie the saved query)
if($pos >= count($next_to_check)) {
    header("Location: some.php");
}
// If the activity is part of the array, ie if they have already obtained an array, use the array.   
//This moves the activities around based on their recently viewed status.  This is confusing
elseif($new_browse or ($next_to_check[$pos] and is_array($next_to_check) and in_array($activity_id, $next_to_check) and ($pos >= 0) and ($pos < count($next_to_check)))) {
    
    // totally broken code trying to keep multiple users browsing same list from colliding removed
    
    if($i == count($next_to_check)) {
        header("Location: some.php");
    }
    else {
        header("Location: one.php?save_and_next=true&activity_id=" . $next_to_check[$pos]);
    }
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
 * Revision 1.25  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.24  2005/07/15 23:56:57  daturaarutad
 * fix next_to_check array
 *
 * Revision 1.23  2005/07/15 23:31:10  vanmer
 * - allow arbitrary session variable to be used to store new browse list sql
 * - defaults to search_sql, set by all activity widgets
 *
 * Revision 1.22  2005/07/15 22:47:24  vanmer
 * - changed browse-next functionality to no longer use saved searches, instead uses sql from last pager view, if
 * specified
 * - removed broken code to move order of activities based on recently viewed timestamps, originally intended to
 * ensure that users do not edit the same activities at the same time
 *
 * Revision 1.21  2005/05/31 16:58:08  daturaarutad
 * changed activity_id => a.activity_id in browse-next.php
 *
 * Revision 1.20  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.19  2004/12/27 18:29:42  neildogg
 * - Array was not being cleared
 *
 * Revision 1.18  2004/12/23 16:05:54  neildogg
 * - Makes sure not to visit an activity already visited by another user
 *
 * Revision 1.17  2004/12/18 21:36:59  neildogg
 * Added support for current user search
 *
 * Revision 1.16  2004/08/23 13:39:39  neildogg
 * - Errand variable name
 *
 * Revision 1.15  2004/08/19 20:45:05  neildogg
 * - Added jump to position in save and next
 *  - Has bug that doesn't let you jump to position 1
 *
 * Revision 1.14  2004/07/28 20:44:43  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
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