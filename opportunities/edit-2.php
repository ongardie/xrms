<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$opportunity_id = $_POST['opportunity_id'];
$opportunity_status_id = $_POST['opportunity_status_id'];
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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$no_update = false;

//check to see if the status was changed (for workflow)
$sql = "select opportunity_status_id from opportunities where opportunity_id=$opportunity_id";
$rst = $con->execute($sql);


$old_status = $rst->fields['opportunity_status_id'];
if ($old_status != $opportunity_status_id) {

    $on_what_id = $opportunity_id;
    $on_what_id_template = $opportunity_status_id;
    $on_what_table_template = "opportunity_statuses";

    //check to see if there are open activities
    //  from the previous status
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
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif ($rst->rowcount()) {
        header("Location: ../activities/one.php?msg=no_change&activity_id=$activity_id");
        $no_update = true;
    }
    $rst->close();

    if (!$no_update) {
        require_once("../activities/workflow-activities.php");
    }
}

if (!$no_update) {
    //update the information from edit.php
    $sql = "SELECT * FROM opportunities WHERE opportunity_id = $opportunity_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['opportunity_status_id'] = $opportunity_status_id;
    $rec['contact_id'] = $contact_id;
    $rec['division_id'] = $division_id;
    $rec['campaign_id'] = $campaign_id;
    $rec['user_id'] = $user_id;
    $rec['size'] = $size;
    $rec['probability'] = $probability;
    $rec['opportunity_title'] = $opportunity_title;
    $rec['opportunity_description'] = $opportunity_description;
    $rec['close_at'] = strtotime('+23 hours 59 minutes',strtotime($close_at));
    $rec['last_modified_at'] = time();
    $rec['last_modified_by'] = $session_user_id;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    header("Location: one.php?msg=saved&opportunity_id=$opportunity_id");
}

$con->close();

?>
