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

$con = get_xrms_dbconnection();
// $con->debug = 1;

$no_update = false;

//check to see if the status was changed (for workflow)
$sql = "select opportunity_status_id from opportunities where opportunity_id=$opportunity_id";
$rst = $con->execute($sql);


$old_status = $rst->fields['opportunity_status_id'];
if ($old_status != $opportunity_status_id) {

    $activity_data=array();
    $activity_data['on_what_status']=$old_status;
    $activity_data['on_what_table'] = 'opportunities';
    $activity_data['on_what_id']=$opportunity_id;
    $activity_data['contact_id']= $contact_id;
    $activity_data['company_id']=$company_id;
    $activity_data['activity_status']='o';

    $open_activities=get_activity($con, $activity_data);
    if ($open_activities){
//        print_r($open_activities);
        $first_activity=current($open_activities);
        $activity_id=$first_activity['activity_id'];
        header("Location: ../activities/one.php?msg=no_change&activity_id=$activity_id");
        $no_update=true;
    }
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
