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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$session_user_id = session_check();

$title = $_POST['title'];
$contact = $_POST['contact'];
$company = $_POST['company'];
$owner = $_POST['owner'];
$date = $_POST['date'];
$before_after = $_POST['before_after'];
$activity_type_id = $_POST['activity_type_id'];
$completed = $_POST['completed'];
$user_id = $_POST['user_id'];

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

$filename =  'activities_' . $session_user_id . '.csv';

$fp = fopen($tmp_export_directory . $filename, 'w');

if (($fp) && ($rst)) {
    rs2csvfile($rst, $fp);
    $rst->close();
    fclose($fp);
} else {
    echo "<p>There was a problem with your export:\n";
    if (!$fp) {
        echo "<br>Unable to open file: $tmp_export_directory . $filename \n";
    }
    if (!$rst) {
        echo "<br> No results returned from database by query: \n";
        echo "<br> $sql \n";
    }
}

$con->close();

header("Location: {$http_site_root}/export/{$filename}");


/**
 * $Log: export.php,v $
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