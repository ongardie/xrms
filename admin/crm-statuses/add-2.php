<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$crm_status_short_name = $_POST['crm_status_short_name'];
$crm_status_pretty_name = $_POST['crm_status_pretty_name'];
$crm_status_pretty_plural = $_POST['crm_status_pretty_plural'];
$crm_status_display_html = $_POST['crm_status_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM crm_statuses WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['crm_status_short_name'] = $crm_status_short_name;
$rec['crm_status_pretty_name'] = $crm_status_pretty_name;
$rec['crm_status_pretty_plural'] = $crm_status_pretty_plural;
$rec['crm_status_display_html'] = $crm_status_display_html;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
