<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$opportunity_id = $_POST['opportunity_id'];
$opportunity_status_id = $_POST['opportunity_status_id'];
$contact_id = $_POST['contact_id'];
$campaign_id = $_POST['campaign_id'];
$user_id = $_POST['user_id'];
$opportunity_title = $_POST['opportunity_title'];
$opportunity_description = $_POST['opportunity_description'];
$size = $_POST['size'];
$probability = $_POST['probability'];
$close_at = $_POST['close_at'];

$campaign_id = ($campaign_id > 0) ? $campaign_id : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "update opportunities set opportunity_status_id = $opportunity_status_id, contact_id = $contact_id, campaign_id = $campaign_id, user_id = $user_id, size = $size, probability = $probability, opportunity_title = " . $con->qstr($opportunity_title, get_magic_quotes_gpc()) . ", opportunity_description = " . $con->qstr($opportunity_description, get_magic_quotes_gpc()) . ", close_at = " . $con->dbdate($close_at . ' 23:59:59') . ", last_modified_at = " . $con->dbtimestamp(mktime()) . ", last_modified_by = $session_user_id where opportunity_id = $opportunity_id";

$con->execute($sql);
$con->close();

header("Location: one.php?msg=saved&opportunity_id=$opportunity_id");

?>
