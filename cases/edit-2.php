<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_id = $_POST['case_id'];
$case_type_id = $_POST['case_type_id'];
$case_status_id = $_POST['case_status_id'];
$case_priority_id = $_POST['case_priority_id'];
$contact_id = $_POST['contact_id'];
$user_id = $_POST['user_id'];
$case_title = $_POST['case_title'];
$case_description = $_POST['case_description'];
$due_at = $_POST['due_at'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "update cases set case_type_id = $case_type_id, case_status_id = $case_status_id, case_priority_id = $case_priority_id, contact_id = $contact_id, user_id = $user_id, case_title = " . $con->qstr($case_title, get_magic_quotes_gpc()) . ", case_description = " . $con->qstr($case_description, get_magic_quotes_gpc()) . ", due_at = " . $con->dbdate($due_at . ' 23:59:59') . ", last_modified_at = " . $con->dbtimestamp(mktime()) . ", last_modified_by = $session_user_id where case_id = $case_id";

$con->execute($sql);
$con->close();

header("Location: one.php?msg=saved&case_id=$case_id");

?>