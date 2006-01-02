<?php
/**
 * activities/export.php
 *
 * Export Searched for activities to a CSV file.
 *
 * $Id: *
 */

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'adodb/toexport.inc.php');

$con = get_xrms_dbconnection();
// $con->debug = 1;

$session_user_id = session_check();

$title = $_POST['title'];
$contact = $_POST['contact'];
$company = $_POST['company'];
$before_after = $_POST['before_after'];
$activity_type_id = $_POST['activity_type_id'];
$completed = $_POST['completed'];
$user_id = $_POST['user_id'];

// undefines
//$owner = $_POST['owner'];
//$date = $_POST['date'];
$date = '';

$sql = "SELECT
 a.*,
 (CASE WHEN (activity_status = 'o') AND (ends_at < " . $con->SQLDate('Y-m-d') . ") THEN 1 ELSE 0 END) AS is_overdue, " .
 $con->concat("cont.first_names", "' '", "cont.last_name") . " AS 'Contact',
 c.company_name AS 'Company',
 activity_type_pretty_name AS 'Type',
 u.username AS 'Owner'
FROM companies c, users u, activity_types at, activities a
LEFT OUTER JOIN contacts cont ON cont.contact_id = a.contact_id
WHERE a.company_id = c.company_id
AND at.activity_type_id = a.activity_type_id
AND a.user_id = u.user_id";

$criteria_count = 0;

if (strlen($title) > 0) {
    $criteria_count++;
    $sql .= " AND a.activity_title LIKE "
    . $con->qstr('%' . $title . '%', get_magic_quotes_gpc());
}

if (strlen($contact) > 0) {
    $criteria_count++;
    $sql .= " AND cont.last_name LIKE "
    . $con->qstr('%' . $contact . '%', get_magic_quotes_gpc());
}

if (strlen($company) > 0) {
    $criteria_count++;
    $sql .= " AND c.company_name LIKE "
    . $con->qstr('%' . $company . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $criteria_count++;
    $sql .= " AND a.entered_by LIKE "
    . $con->qstr('%' . $user_id . '%', get_magic_quotes_gpc());
}

if (strlen($activity_type_id) > 0) {
    $criteria_count++;
    $sql .= " AND a.activity_type_id LIKE "
    . $con->qstr('%' . $activity_type_id . '%', get_magic_quotes_gpc());
}

if (strlen($completed) > 0) {
    $criteria_count++;
    $sql .= " AND a.activity_status = " . $con->qstr($completed, get_magic_quotes_gpc());
}

if (strlen($date) > 0) {
    $criteria_count++;
    if (!$before_after) {
        $sql .= " AND a.scheduled_at <= " . $con->qstr($date . " 23:59:59", get_magic_quotes_gpc());
    } else {
        $sql .= " AND a.scheduled_at >= " . $con->qstr($date . " 00:00:00", get_magic_quotes_gpc());
    }
}

$sql .= " ORDER BY is_overdue DESC, a.scheduled_at, a.entered_at DESC";

$rst = $con->execute($sql);

$filename =  'activities_' . date('Y-m-d_H-i') . '.csv';

if ($rst) {
    $csvdata= rs2csv($rst);
    if ($csvdata) {
      $filesize = strlen($csvdata);
    }  
    $rst->close();
} else {
    echo "<p>" . _("There was a problem with your export") . ":\n";
    if (!$csvdata) {
        echo "<br>" . _("Unable to create file") . ": $filename \n";
    }
    if (!$rst) {
        db_error_handler($con,$sql);
    }
}

$con->close();

SendDownloadHeaders('text', 'csv', $filename, true, $filesize);
echo $csvdata;

/**
 * $Log: export.php,v $
 * Revision 1.8  2006/01/02 21:23:18  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.7  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.6  2005/01/29 13:08:45  braverock
 * - change error message to reflect the fact that we no longer use the export directory here
 *
 * Revision 1.5  2005/01/09 03:38:34  braverock
 * - modified to use SendDownLoadHeaders
 * - modified to send data directly,rather than writing a file
 * - use timestamp in the filename
 *
 * Revision 1.4  2004/08/03 11:39:52  cpsource
 * - Get rid of ^m's
 *   Add newline at end
 *   Stub date and user as they were not passed in from POST
 *
 * Revision 1.3  2004/06/03 16:11:00  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.2  2004/04/20 12:52:20  braverock
 *  - add owner in the list
 *  - change date by session_user_id in the filename
 *    - apply SF patch 938385 submitted by frenchman
 *
 */
?>