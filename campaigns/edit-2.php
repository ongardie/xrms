<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

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

$sql = "update campaigns set campaign_type_id = $campaign_type_id, campaign_status_id = $campaign_status_id, user_id = $user_id, campaign_title = " . $con->qstr($campaign_title, get_magic_quotes_gpc()) . ", campaign_description = " . $con->qstr($campaign_description, get_magic_quotes_gpc()) . ", starts_at = " . $con->dbdate($starts_at . ' 00:00:00') . ", ends_at = " . $con->dbdate($ends_at . ' 23:59:59') . ", cost = $cost, last_modified_at = " . $con->dbtimestamp(mktime()) . ", last_modified_by = $session_user_id where campaign_id = $campaign_id";

$con->execute($sql);
$con->close();

header("Location: one.php?msg=saved&campaign_id=$campaign_id");

?>