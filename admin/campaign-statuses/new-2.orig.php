<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_status_short_name = $_POST['case_status_short_name'];
$case_status_pretty_name = $_POST['case_status_pretty_name'];
$case_status_pretty_plural = $_POST['case_status_pretty_plural'];
$case_status_display_html = $_POST['case_status_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into case_statuses (case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (" . $con->qstr($case_status_short_name, get_magic_quotes_gpc()) . ", " . $con->qstr($case_status_pretty_name, get_magic_quotes_gpc()) . ", " . $con->qstr($case_status_pretty_plural, get_magic_quotes_gpc()) . ", " . $con->qstr($case_status_display_html, get_magic_quotes_gpc()) . ")";
$con->execute($sql);

$con->close();

header("Location: some.php");

?>
