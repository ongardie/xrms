<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$activity_type_short_name = $_POST['activity_type_short_name'];
$activity_type_pretty_name = $_POST['activity_type_pretty_name'];
$activity_type_pretty_plural = $_POST['activity_type_pretty_plural'];
$activity_type_display_html = $_POST['activity_type_display_html'];
$activity_type_score_adjustment = $_POST['activity_type_score_adjustment'];

$activity_type_score_adjustment = ($activity_type_score_adjustment > 0) ? $activity_type_score_adjustment : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html, activity_type_score_adjustment) values (" . $con->qstr($activity_type_short_name, get_magic_quotes_gpc()) . ", " . $con->qstr($activity_type_pretty_name, get_magic_quotes_gpc()) . ", " . $con->qstr($activity_type_pretty_plural, get_magic_quotes_gpc()) . ", " . $con->qstr($activity_type_display_html, get_magic_quotes_gpc()) . ", $activity_type_score_adjustment)";
$con->execute($sql);

$con->close();

header("Location: some.php");

?>
