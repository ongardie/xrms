<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_type_id = $_POST['case_type_id'];
$case_type_short_name = $_POST['case_type_short_name'];
$case_type_pretty_name = $_POST['case_type_pretty_name'];
$case_type_pretty_plural = $_POST['case_type_pretty_plural'];
$case_type_display_html = $_POST['case_type_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update case_types set case_type_short_name = " . $con->qstr($case_type_short_name, get_magic_quotes_gpc()) . ", case_type_pretty_name = " . $con->qstr($case_type_pretty_name, get_magic_quotes_gpc()) . ", case_type_pretty_plural = " . $con->qstr($case_type_pretty_plural, get_magic_quotes_gpc()) . ", case_type_display_html = " . $con->qstr($case_type_display_html, get_magic_quotes_gpc()) . " WHERE case_type_id = $case_type_id";
$con->execute($sql);

$con->close();

header("Location: one.php?case_type_id=$case_type_id");

?>
