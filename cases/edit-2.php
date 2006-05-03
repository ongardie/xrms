<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-cases.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$case_id = $_POST['case_id'];
$on_what_id=$case_id;
$session_user_id = session_check('','Update');

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
$return_url = (array_key_exists('return_url',$_GET) ? $_GET['return_url'] : $_POST['return_url']);

$con = get_xrms_dbconnection();
// $con->debug = 1;

$no_update = false;

//check to see if the status was changed (for workflow)
$open_activities=get_open_workflow_activities_on_status_change($con, 'cases', $case_id, $case_status_id, $company_id, $contact_id);
if ($open_activities){
    $first_activity=current($open_activities);
    $activity_id=$first_activity['activity_id'];
    $return_url=urlencode($return_url);
    header("Location: ../activities/one.php?msg=no_change&activity_id=$activity_id&return_url=$return_url");
    $no_update = true;
}


if ( ! $no_update ) {
    //update the information from edit.php
    $rec = array();
    $rec['case_type_id'] = $case_type_id;
    $rec['case_status_id'] = $case_status_id;
    $rec['case_priority_id'] = $case_priority_id;
    $rec['contact_id'] = $contact_id;
    $rec['division_id'] = $division_id;
    $rec['user_id'] = $user_id;
    $rec['case_title'] = $case_title;
    $rec['company_id']=$company_id;
    $rec['case_description'] = $case_description;
    $rec['due_at'] = strtotime('+23 hours 59 minutes',strtotime($due_at));

    $ret=update_case($con, $rec, $case_id, false, get_magic_quotes_gpc());

    if (!$return_url) $return_url="one.php?msg=saved&case_id=$case_id";
    
    header("Location: $return_url");
}

$con->close();

?>
