<?php
/**
 * Save the updated activity information to the database
 *
 * @todo: potential security risk in pulling some of these variables from the submit
 *        should eventually do a select to get the variables if we are going
 *        to post a followup
 *
 * $Id: edit-2.php,v 1.8 2004/04/27 16:29:34 gpowers Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$return_url = $_POST['return_url'];
$activity_id = $_POST['activity_id'];
$activity_type_id = $_POST['activity_type_id'];
$contact_id = $_POST['contact_id'];
$activity_title = $_POST['activity_title'];
$activity_description = $_POST['activity_description'];
$scheduled_at = $_POST['scheduled_at'];
$ends_at = $_POST['ends_at'];
$activity_status = $_POST['activity_status'];
$current_activity_status = $_POST['current_activity_status'];
$user_id    = $_POST['user_id'];
$followup   = $_POST['followup'];
$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$company_id = $_POST['company_id'];
$email_to = $_POST['email_to'];

//mark this activity as completed if follow up is to be scheduled
if ($followup) { $activity_status = 'c'; }

//set scheduled_at to today if it is empty
if (!$scheduled_at) {
    $scheduled_at = date ("y-m-d");
}

// set ends_at to scheduled_at if it is empty
if (!$ends_at) {
    $ends_at = $scheduled_at;
}

// set the correct activity status flag
$activity_status = ($activity_status == 'on') ? 'c' : 'o';
//mark this activity as completed if follow up is to be scheduled
if ($followup) { $activity_status = 'c'; }
// if it's closed but wasn't before, update the closed_at timestamp
$completed_at = ($activity_status == 'c') && ($current_activity_status != 'c') ? date('Y-m-d h:i:s') : 'NULL';

$contact_id = ($contact_id > 0) ? $contact_id : 'NULL';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update activities set
        activity_type_id = $activity_type_id,
        contact_id = $contact_id,
        activity_title = " . $con->qstr($activity_title, get_magic_quotes_gpc()) . ",
        activity_description = " . $con->qstr($activity_description, get_magic_quotes_gpc()) . ",
        user_id = " . $con->qstr($user_id, get_magic_quotes_gpc()) . ",
        scheduled_at = " . $con->dbtimestamp(date ('Y-m-d H:i:s', strtotime($scheduled_at))) . ",
        ends_at = " . $con->dbtimestamp(date ('Y-m-d H:i:s', strtotime($ends_at))) . ",
        completed_at = " . $con->dbtimestamp(date ('Y-m-d H:i:s', strtotime($completed_at))) . ",
        activity_status = " . $con->qstr($activity_status, get_magic_quotes_gpc()) . "
        where activity_id = $activity_id";

//$con->debug = 1;
$con->execute($sql);
add_audit_item($con, $session_user_id, 'updated', 'activity', $activity_id);

$sql = "select username from users where user_id = $user_id limit 1";
$rst = $con->execute($sql);
if ($rst) { $username = $rst->fields['username']; }
$rst -> close();

$sql = "select company_name, phone from companies where company_id = $company_id limit 1";
$rst = $con->execute($sql);
if ($rst) {
    $company_name = $rst->fields['company_name'];
    $company_phone = $rst->fields['phone'];
    $rst -> close();
}

$sql = "select activity_type_pretty_name from activity_types where activity_type_id = $activity_type_id limit 1";
$rst = $con->execute($sql);
if ($rst) {
    $activity_type = $rst->fields['activity_type_pretty_name'];
    $rst->close();
}

$sql = "select concat(first_names, ' ', last_name) as contact_name, work_phone from contacts where contact_id = $contact_id limit 1";
$rst = $con->execute($sql);
if ($rst) {
    $contact_name = $rst->fields['contact_name'];
    $contact_phone = $rst->fields['work_phone'];
    $rst->close();
}

if ($activity_status == 'o') {
    $activity_status_long = "Open";
}

if ($activity_status == 'c') {
    $activity_status_long = "Closed";
}

$email_message="
This is an UPDATED $app_title activity:

$http_site_root/activities/one.php?return_url=/companies/one.php?company_id=$company_id&activity_id=$activity_id

Title: $activity_title
Description: $activity_description

Contact: $contact_name
Contact Phone: $contact_phone

Company: $company_name
Company Phone: $company_phone

User: $username
Type: $activity_type
Status: $activity_status_long
Scheduled At: $scheduled_at
Ends At: $ends_at

";

$con->close();

if ($email_to) {
    mail($email_to, "$app_title: $activity_title", $email_message);
}

if ($followup) {
    header ('Location: '.$http_site_root."/activities/new-2.php?user_id=$session_user_id&activity_type_id=$activity_type_id&on_what_id=$on_what_id&contact_id=$contact_id&on_what_table=$on_what_table&company_id=$company_id&user_id=$user_id&activity_title=".htmlspecialchars('Follow-up '.$activity_title)."&company_id=$company_id&activity_status=o&return_url=$return_url" );
} else {
    header("Location: " . $http_site_root . $return_url);
}

/**
 * $Log: edit-2.php,v $
 * Revision 1.8  2004/04/27 16:29:34  gpowers
 * added support for activity emails.
 *
 * Revision 1.7  2004/04/27 15:18:04  gpowers
 * added support for activity times
 * added audit item
 *
 * Revision 1.6  2004/04/26 01:54:45  braverock
 * add ability to schedule a followup activity based on the current activity
 *
 * Revision 1.5  2004/03/17 21:36:48  braverock
 * -fixed strlen bug
 *
 * Revision 1.4  2004/03/15 14:51:27  braverock
 * - fix ends-at display bug
 * - make sure both scheduled_at and ends_at have legal values
 * - add phpdoc
 *
 */
?>
