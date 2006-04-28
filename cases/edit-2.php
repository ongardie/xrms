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
$sql = "select case_status_id from cases where case_id=$case_id";
$rst = $con->execute($sql);

$old_status = $rst->fields['case_status_id'];
if ($old_status != $case_status_id) {

    $on_what_id = $case_id;
    $on_what_id_template = $case_status_id;
    $on_what_table_template = "case_statuses";

    /* ADD CHECK TO SEE IF THERE ARE STILL OPEN ACTIVITIES FROM
        THE PREVIOUS STATUS, THEN GIVE THEM OPTIONS  */
    $activity_data=array();
    $activity_data['on_what_status']=$old_status;
    $activity_data['on_what_table'] = $on_what_table;
    $activity_data['on_what_id']=$on_what_id;
    $activity_data['contact_id']= $contact_id;
    $activity_data['company_id']=$company_id;
    $activity_data['activity_status']='o';

    $open_activities=get_activity($con, $activity_data);
    if ($open_activities){
        $first_activity=current($open_activities);
        $activity_id=$first_activity['activity_id'];
        header("Location: ../activities/one.php?msg=no_change&activity_id=$activity_id");
        $no_update = true;
    }

    if (!$no_update) {
        //run workflow case edit functions
        require_once("../activities/workflow-activities.php");
    }

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
