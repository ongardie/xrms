<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$activity_type_short_name = $_POST['activity_type_short_name'];
$activity_type_pretty_name = $_POST['activity_type_pretty_name'];
$activity_type_pretty_plural = $_POST['activity_type_pretty_plural'];
$activity_type_display_html = $_POST['activity_type_display_html'];
$activity_type_score_adjustment = $_POST['activity_type_score_adjustment'];

$activity_type_score_adjustment = ($activity_type_score_adjustment > 0) ? $activity_type_score_adjustment : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM activity_types WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['activity_type_short_name'] = $activity_type_short_name;
$rec['activity_type_pretty_name'] = $activity_type_pretty_name;
$rec['activity_type_pretty_plural'] = $activity_type_pretty_plural;
$rec['activity_type_display_html'] = $activity_type_display_html;
$rec['activity_type_score_adjustment'] = $activity_type_score_adjustment;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
