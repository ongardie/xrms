<?php

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

// if it's closed but wasn't before, update the closed_at timestamp

$activity_status = ($activity_status == 'on') ? 'c' : 'o';
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
scheduled_at = " . $con->dbtimestamp($scheduled_at . ' 23:59:59') . ",
ends_at = " . $con->dbtimestamp($ends_at . ' 23:59:59') . ",
completed_at = " . $con->dbdate($completed_at) . ",
activity_status = " . $con->qstr($activity_status, get_magic_quotes_gpc()) . "
where activity_id = $activity_id";

//$con->debug = 1;
$con->execute($sql);
$con->close();

header("Location: " . $http_site_root . $return_url);

?>
