<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_type_id = $_POST['case_type_id'];
$case_status_id = $_POST['case_status_id'];
$case_priority_id = $_POST['case_priority_id'];
$user_id = $_POST['user_id'];
$company_id = $_POST['company_id'];
$contact_id = $_POST['contact_id'];
$case_title = $_POST['case_title'];
$due_at = $_POST['due_at'];
$case_description = $_POST['case_description'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into cases (case_type_id, case_status_id, case_priority_id, user_id, company_id, contact_id, case_title, case_description, due_at, entered_at, entered_by, last_modified_at, last_modified_by) values ($case_type_id, $case_status_id, $case_priority_id, $user_id, $company_id, $contact_id, " . $con->qstr($case_title, get_magic_quotes_gpc()) . ", " . $con->qstr($case_description, get_magic_quotes_gpc()) . ", " . $con->dbdate($due_at) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";

$con->execute($sql);

$case_id = $con->insert_id();


//generate activities for the new case
$on_what_table = "cases";
$on_what_id = $case_id;
$on_what_table_template = "case_statuses";
$on_what_id_template = $case_status_id;
require_once("../activities/workflow-activities.php");


$con->close();

header("Location: one.php?msg=case_added&case_id=$case_id");

?>
