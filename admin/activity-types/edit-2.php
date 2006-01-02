<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_type_id = $_POST['activity_type_id'];
$activity_type_short_name = $_POST['activity_type_short_name'];
$activity_type_pretty_name = $_POST['activity_type_pretty_name'];
$activity_type_pretty_plural = $_POST['activity_type_pretty_plural'];
$activity_type_display_html = $_POST['activity_type_display_html'];
$activity_type_score_adjustment = $_POST['activity_type_score_adjustment'];

$activity_type_score_adjustment = ($activity_type_score_adjustment > 0) ? $activity_type_score_adjustment : 0;

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM activity_types WHERE activity_type_id = $activity_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_type_short_name'] = $activity_type_short_name;
$rec['activity_type_pretty_name'] = $activity_type_pretty_name;
$rec['activity_type_pretty_plural'] = $activity_type_pretty_plural;
$rec['activity_type_display_html'] = $activity_type_display_html;
$rec['activity_type_score_adjustment'] = $activity_type_score_adjustment;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

?>