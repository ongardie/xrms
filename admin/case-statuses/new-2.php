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
//$con->debug = 1;

//get next sort_order value, put it at the bottom of the list
$sql = "select sort_order from case_statuses where case_status_record_status='a' order by sort_order desc";
$rst = $con->execute($sql);
$sort_order = $rst->fields['sort_order'] + 1;

//put in new record
$sql = "insert into case_statuses (case_status_short_name, 
	case_status_pretty_name, case_status_pretty_plural, 
	case_status_display_html, sort_order) 
	values (" . $con->qstr($case_status_short_name, get_magic_quotes_gpc()) . ", " 
	. $con->qstr($case_status_pretty_name, get_magic_quotes_gpc()) . ", " 
	. $con->qstr($case_status_pretty_plural, get_magic_quotes_gpc()) . ", " 
	. $con->qstr($case_status_display_html, get_magic_quotes_gpc()) . ", "
	. $sort_order . ")";
$con->execute($sql);

$con->close();

header("Location: some.php");

?>
