<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$account_status_short_name = $_POST['account_status_short_name'];
$account_status_pretty_name = $_POST['account_status_pretty_name'];
$account_status_pretty_plural = $_POST['account_status_pretty_plural'];
$account_status_display_html = $_POST['account_status_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//save to database
$rec = array();
$rec['account_status_short_name'] = $account_status_short_name;
$rec['account_status_pretty_name'] = $account_status_pretty_name;
$rec['account_status_pretty_plural'] = $account_status_pretty_plural;
$rec['account_status_display_html'] = $account_status_display_html;

$tbl = "account_statuses";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
