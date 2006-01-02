<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$account_status_id = $_POST['account_status_id'];
$account_status_short_name = $_POST['account_status_short_name'];
$account_status_pretty_name = $_POST['account_status_pretty_name'];
$account_status_pretty_plural = $_POST['account_status_pretty_plural'];
$account_status_display_html = $_POST['account_status_display_html'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM account_statuses WHERE account_status_id = $account_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['account_status_short_name'] = $account_status_short_name;
$rec['account_status_pretty_name'] = $account_status_pretty_name;
$rec['account_status_pretty_plural'] = $account_status_pretty_plural;
$rec['account_status_display_html'] = $account_status_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

?>
