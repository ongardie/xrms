<?php

/**
 * Activity Recurrance Delete Activities
 *
 * @author Randy Martinsen
 *
 * $Id: delete_recurrence.php,v 1.1 2009/01/23 01:02:20 randym56 Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$activity_recurrence_id = $_GET['activity_recurrence_id'];

$session_user_id = session_check();

$con = get_xrms_dbconnection();

//delete related activities and activities associated with this campaign
$sql = "SELECT * from activities WHERE activity_recurrence_id = $activity_recurrence_id AND activity_status = 'o' AND activity_record_status = 'a'";
$rst = $con->execute($sql);
if ($rst) {
    while (!$rst->EOF) {
		$activity_id = $rst->fields['activity_id'];
		$sql2 = "UPDATE activities SET activity_record_status = 'd' WHERE activity_id = $activity_id";
		$rst2 = $con->execute($sql2);
		
		$rst->movenext();
		}
	}
	
$con->close();

header("Location: some.php?msg=Open activities for recurring activity were deleted.");

/**
 * $Log: delete_recurrence.php,v $
 * Revision 1.1  2009/01/23 01:02:20  randym56
 * - Update to allow for deleting recurring entries - fix date-time problems with new activities
 *
 * Revision 1.0  2009/01/22 21:23:18  randym56
*/
?>
