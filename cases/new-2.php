<?php
/**
 * Insert a new Case into the Database
 *
 * $Id: new-2.php,v 1.8 2005/01/10 23:32:32 braverock Exp $
 */
 
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$case_type_id = $_POST['case_type_id'];
$case_status_id = $_POST['case_status_id'];
$case_priority_id = $_POST['case_priority_id'];
$user_id = $_POST['user_id'];
$company_id = $_POST['company_id'];
$division_id = $_POST['division_id'];
$contact_id = $_POST['contact_id'];
$case_title = $_POST['case_title'];
$due_at = $_POST['due_at'];
$case_description = $_POST['case_description'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

//save to database
$rec = array();
$rec['case_type_id'] = $case_type_id;
$rec['case_status_id'] = $case_status_id;
$rec['case_priority_id'] = $case_priority_id;
$rec['user_id'] = $user_id;
$rec['company_id'] = $company_id;
$rec['division_id'] = $division_id;
$rec['contact_id'] = $contact_id;
$rec['case_title'] = $case_title;
$rec['case_description'] = $case_description;
$rec['due_at'] = strtotime($due_at);
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$tbl = 'cases';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$case_id = $con->insert_id();

//generate activities for the new case
$on_what_table = "cases";
$on_what_id = $case_id;
$on_what_table_template = "case_statuses";
$on_what_id_template = $case_status_id;
require_once("../activities/workflow-activities.php");

$con->close();

header("Location: one.php?msg=case_added&case_id=$case_id");

/**
 * $Log: new-2.php,v $
 * Revision 1.8  2005/01/10 23:32:32  braverock
 * - add phpdoc
 *
 */
?>