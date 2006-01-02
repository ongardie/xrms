<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_status_short_name = $_POST['case_status_short_name'];
$case_status_pretty_name = $_POST['case_status_pretty_name'];
$case_status_pretty_plural = $_POST['case_status_pretty_plural'];
$case_status_display_html = $_POST['case_status_display_html'];
$case_status_long_desc = $_POST['case_status_long_desc'];
$case_type_id = $_POST['case_type_id'];

$con = get_xrms_dbconnection();
//$con->debug = 1;

//get next sort_order value, put it at the bottom of the list
$sql = "select sort_order from case_statuses where case_status_record_status='a' AND case_type_id=$case_type_id order by sort_order desc";
$rst = $con->execute($sql);
$sort_order = $rst->fields['sort_order'] + 1;

//save to database
$rec = array();
$rec['case_status_short_name'] = $case_status_short_name;
$rec['case_status_pretty_name'] = $case_status_pretty_name;
$rec['case_status_pretty_plural'] = $case_status_pretty_plural;
$rec['case_status_display_html'] = $case_status_display_html;
$rec['case_status_long_desc'] = $case_status_long_desc;
$rec['case_type_id']=$case_type_id;
$rec['sort_order'] = $sort_order;

$tbl = "case_statuses";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php?acase_type_id=$case_type_id");

?>
