<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

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
$company_id = $_POST['company_id'];
$division_id = $_POST['division_id'];
$on_what_table = $_POST['on_what_table'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$no_update = false;

//check to see if the status was changed (for workflow)
$sql = "select case_status_id from cases where case_id=$case_id";
$rst = $con->execute($sql);

$old_status = $rst->fields['case_status_id'];
if ($old_status != $case_status_id) {

    $on_what_id = $case_id;
    $on_what_id_template = $case_status_id;
    $on_what_table_template = "case_statuses";

    /* ADD CHECK TO SEE IF THERE ARE STILL OPEN ACTIVITIES FROM
        THE PREVIOUS STATUS, THEN GIVE THEM OPTIONS  */
    $sql = "select * from activities
        where on_what_status=$old_status
        and on_what_table='$on_what_table'
        and on_what_id=$on_what_id
        and contact_id=$contact_id
        and company_id=$company_id
        and activity_status='o'
        and activity_record_status='a'";

    $rst = $con->execute($sql);
    $activity_id = $rst->fields['activity_id'];

    //if there is only one field, the result set is empty (no old activities)
    //  otherwise prompt the user
    if (count($rst->fields) > 1) {
        header("Location: ../activities/one.php?msg=no_change&activity_id=$activity_id");
        $no_update = true;
    }
    $rst->close();

    if (!$no_update) {
        require_once("../activities/workflow-activities.php");
    }

}

if ( ! $no_update ) {
    //update the information from edit.php
    $sql = "SELECT * FROM cases WHERE case_id = $case_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['case_type_id'] = $case_type_id;
    $rec['case_status_id'] = $case_status_id;
    $rec['case_priority_id'] = $case_priority_id;
    $rec['contact_id'] = $contact_id;
    $rec['division_id'] = $division_id;
    $rec['user_id'] = $user_id;
    $rec['case_title'] = $case_title;
    $rec['case_description'] = $case_description;
    $rec['due_at'] = strtotime('+23 hours 59 minutes',strtotime($due_at));
    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    header("Location: one.php?msg=saved&case_id=$case_id");
}

$con->close();

?>
