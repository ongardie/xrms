<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$campaign_id = $_POST['campaign_id'];
$campaign_type_id = $_POST['campaign_type_id'];
$campaign_status_id = $_POST['campaign_status_id'];
$user_id = $_POST['user_id'];
$campaign_title = $_POST['campaign_title'];
$campaign_description = $_POST['campaign_description'];
$starts_at = $_POST['starts_at'];
$ends_at = $_POST['ends_at'];
$cost = $_POST['cost'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "SELECT * FROM campaigns WHERE campaign_id = $campaign_id";
$rst = $con->execute($sql);

$rec = array();
$rec['campaign_type_id'] = $campaign_type_id;
$rec['campaign_status_id'] = $campaign_status_id;
$rec['user_id'] = $user_id;
$rec['campaign_title'] = $campaign_title;
$rec['campaign_description'] = $campaign_description;
$rec['starts_at'] = strtotime($starts_at);
$rec['ends_at'] = strtotime('+23 hours 59 minutes',strtotime($ends_at));
$rec['cost'] = $cost;
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: one.php?msg=saved&campaign_id=$campaign_id");

?>
