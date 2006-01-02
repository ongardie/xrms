<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$on_what_table='campaigns';
$session_user_id = session_check('','Create');

$campaign_type_id = $_POST['campaign_type_id'];
$campaign_status_id = $_POST['campaign_status_id'];
$user_id = $_POST['user_id'];
$campaign_title = $_POST['campaign_title'];
$campaign_description = $_POST['campaign_description'];
$starts_at = $_POST['starts_at'];
$ends_at = $_POST['ends_at'];
$cost = $_POST['cost'];

$cost = ($cost > 0) ? $cost : 0;

$con = get_xrms_dbconnection();
// $con->debug = 1;

//save to database
$rec = array();
$rec['campaign_type_id'] = $campaign_type_id;
$rec['campaign_status_id'] = $campaign_status_id;
$rec['user_id'] = $user_id;
$rec['campaign_title'] = $campaign_title;
$rec['campaign_description'] = $campaign_description;
$rec['starts_at'] = $starts_at;
$rec['ends_at'] = $ends_at;
$rec['cost'] = $cost;
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$tbl = 'campaigns';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$campaign_id = $con->insert_id();

$con->close();

header("Location: one.php?msg=campaign_added&campaign_id=$campaign_id");

?>
