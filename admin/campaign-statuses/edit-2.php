<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_status_id = $_POST['campaign_status_id'];
$campaign_status_short_name = $_POST['campaign_status_short_name'];
$campaign_status_pretty_name = $_POST['campaign_status_pretty_name'];
$campaign_status_pretty_plural = $_POST['campaign_status_pretty_plural'];
$campaign_status_display_html = $_POST['campaign_status_display_html'];
$status_open_indicator = $_POST['status_open_indicator'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "SELECT * FROM campaign_statuses WHERE campaign_status_id = $campaign_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['campaign_status_short_name'] = $campaign_status_short_name;
$rec['campaign_status_pretty_name'] = $campaign_status_pretty_name;
$rec['campaign_status_pretty_plural'] = $campaign_status_pretty_plural;
$rec['campaign_status_display_html'] = $campaign_status_display_html;
$rec['status_open_indicator'] = $status_open_indicator;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

?>
