<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$campaign_status_id = $_POST['campaign_status_id'];
$campaign_status_short_name = $_POST['campaign_status_short_name'];
$campaign_status_pretty_name = $_POST['campaign_status_pretty_name'];
$campaign_status_pretty_plural = $_POST['campaign_status_pretty_plural'];
$campaign_status_display_html = $_POST['campaign_status_display_html'];
$status_open_indicator = $_POST['status_open_indicator'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "update campaign_statuses set
        campaign_status_short_name = " . $con->qstr($campaign_status_short_name, get_magic_quotes_gpc()) . ", 
        campaign_status_pretty_name = " . $con->qstr($campaign_status_pretty_name, get_magic_quotes_gpc()) . ",
        campaign_status_pretty_plural = " . $con->qstr($campaign_status_pretty_plural, get_magic_quotes_gpc()) . ",
        campaign_status_display_html = " . $con->qstr($campaign_status_display_html, get_magic_quotes_gpc()) . ",
        status_open_indicator = " . $con->qstr($status_open_indicator, get_magic_quotes_gpc()) . "
        WHERE campaign_status_id = $campaign_status_id";

$con->execute($sql);
$con->close();

header("Location: one.php?campaign_status_id=$campaign_status_id");

?>
