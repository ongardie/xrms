<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$opportunity_status_id = $_POST['opportunity_status_id'];
$size = $_POST['size'];
$probability = $_POST['probability'];
$user_id = $_POST['user_id'];
$company_id = $_POST['company_id'];
$contact_id = $_POST['contact_id'];
$campaign_id = $_POST['campaign_id'];
$opportunity_title = $_POST['opportunity_title'];
$close_at = $_POST['close_at'];
$opportunity_description = $_POST['opportunity_description'];

$campaign_id = ($campaign_id > 0) ? $campaign_id : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into opportunities (opportunity_status_id, user_id, company_id, contact_id, campaign_id, opportunity_title, opportunity_description, size, probability, close_at, entered_at, entered_by, last_modified_at, last_modified_by) values ($opportunity_status_id, $user_id, $company_id, $contact_id, $campaign_id, " . $con->qstr($opportunity_title, get_magic_quotes_gpc()) . ", " . $con->qstr($opportunity_description, get_magic_quotes_gpc()) . ", $size, $probability, " . $con->dbdate($close_at . ' 23:59:59') . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";

$con->execute($sql);

$opportunity_id = $con->insert_id();

$con->close();

header("Location: one.php?msg=opportunity_added&opportunity_id=$opportunity_id");

?>