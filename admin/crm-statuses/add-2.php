<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$crm_status_short_name = $_POST['crm_status_short_name'];
$crm_status_pretty_name = $_POST['crm_status_pretty_name'];
$crm_status_pretty_plural = $_POST['crm_status_pretty_plural'];
$crm_status_display_html = $_POST['crm_status_display_html'];

$con = get_xrms_dbconnection();

//get next sort_order value, put it at the bottom of the list
$sql = "select sort_order from crm_statuses where crm_status_record_status='a' order by sort_order desc";
$rst = $con->execute($sql);
$sort_order = $rst->fields['sort_order'] + 1;

//save to database
$rec = array();
$rec['crm_status_short_name'] = $crm_status_short_name;
$rec['crm_status_pretty_name'] = $crm_status_pretty_name;
$rec['crm_status_pretty_plural'] = $crm_status_pretty_plural;
$rec['crm_status_display_html'] = $crm_status_display_html;
$rec['sort_order'] = $sort_order;

$tbl = 'crm_statuses';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");
?>
