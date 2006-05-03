<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-opportunities.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$opportunity_id = $_POST['opportunity_id'];
$on_what_id=$opportunity_id;
$session_user_id = session_check('','Update');

getGlobalVar($return_url, 'return_url');


$opportunity_status_id = $_POST['opportunity_status_id'];
$opportunity_type_id = $_POST['opportunity_type_id'];
$contact_id = $_POST['contact_id'];
$division_id = $_POST['division_id'];
$campaign_id = $_POST['campaign_id'];
$user_id = $_POST['user_id'];
$opportunity_title = $_POST['opportunity_title'];
$opportunity_description = $_POST['opportunity_description'];
$size = $_POST['size'];
$probability = $_POST['probability'];
$close_at = $_POST['close_at'];
$company_id = $_POST['company_id'];
$on_what_table = $_POST['on_what_table'];

$campaign_id = ($campaign_id > 0) ? $campaign_id : 0;


if (!$return_url) $return_url="/opportunities/one.php?opportunity_id=$opportunity_id";

$con = get_xrms_dbconnection();
// $con->debug = 1;

$no_update = false;



    //check to see if the status was changed (for workflow)
    $open_activities=get_open_workflow_activities_on_status_change($con, 'opportunities', $opportunity_id, $opportunity_status_id, $company_id, $contact_id);
    if ($open_activities){
//        print_r($open_activities);
        $first_activity=current($open_activities);
        $activity_id=$first_activity['activity_id'];
        $return_url=urlencode($return_url);
        header("Location: ../activities/one.php?msg=no_change&activity_id=$activity_id&return_url=$return_url");
        $no_update=true;
    }

if (!$no_update) {
    $rec = array();
    $rec['opportunity_status_id'] = $opportunity_status_id;
    $rec['opportunity_type_id'] = $opportunity_type_id;
    $rec['contact_id'] = $contact_id;
    $rec['division_id'] = $division_id;
    $rec['campaign_id'] = $campaign_id;
    $rec['user_id'] = $user_id;
    $rec['size'] = $size;
    $rec['probability'] = $probability;
    $rec['opportunity_title'] = $opportunity_title;
    $rec['opportunity_description'] = $opportunity_description;
    $rec['close_at'] = strtotime('+23 hours 59 minutes',strtotime($close_at));

    update_opportunity($con, $rec, $opportunity_id, false,  get_magic_quotes_gpc());

    header("Location: one.php?msg=saved&opportunity_id=$opportunity_id");
}

$con->close();

?>
