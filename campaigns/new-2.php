<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$campaign_type_id = $_POST['campaign_type_id'];
$campaign_status_id = $_POST['campaign_status_id'];
$user_id = $_POST['user_id'];
$campaign_title = $_POST['campaign_title'];
$campaign_description = $_POST['campaign_description'];
$starts_at = $_POST['starts_at'];
$ends_at = $_POST['ends_at'];
$cost = $_POST['cost'];

$cost = ($cost > 0) ? $cost : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into campaigns (campaign_type_id, campaign_status_id, user_id, campaign_title, campaign_description, starts_at, ends_at, cost, entered_at, entered_by, last_modified_at, last_modified_by) values ($campaign_type_id, $campaign_status_id, $user_id, " . $con->qstr($campaign_title, get_magic_quotes_gpc()) . ", " . $con->qstr($campaign_description, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp($starts_at) . ", " . $con->dbtimestamp($ends_at) . ", " . $con->qstr($cost, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";

$con->execute($sql);

$campaign_id = $con->insert_id();

$con->close();

header("Location: one.php?msg=campaign_added&campaign_id=$campaign_id");

?>