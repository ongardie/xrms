<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$return_url = $_POST['return_url'];

$activity_type_id = $_POST['activity_type_id'];
$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$activity_title = $_POST['activity_title'];
$activity_description = $_POST['activity_description'];
$activity_status = $_POST['activity_status'];
$scheduled_at = $_POST['scheduled_at'];
$company_id = $_POST['company_id'];
$contact_id = $_POST['contact_id'];
$user_id    = $_POST['user_id'];

$activity_title = (strlen($activity_title) > 0) ? $activity_title : "[none]";
$on_what_table = (strlen($on_what_table) > 0) ? $on_what_table : '';
$on_what_id = ($on_what_id > 0) ? $on_what_id : 0;
$company_id = ($company_id > 0) ? $company_id : 0;
$contact_id = ($contact_id > 0) ? $contact_id : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into activities (activity_type_id, user_id, company_id, contact_id, on_what_table, on_what_id, activity_title, activity_description, entered_at, entered_by, scheduled_at, activity_status) values ($activity_type_id, $user_id, $company_id, $contact_id, " . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ", $on_what_id, " . $con->qstr($activity_title, get_magic_quotes_gpc()) . ", " . $con->qstr($activity_note, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp($scheduled_at . ' 23:59:59') . ", " . $con->qstr($activity_status, get_magic_quotes_gpc()) . ")";
$con->execute($sql);

$con->close();

header("Location: " . $http_site_root . $return_url);

?>
