<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$industry_short_name = $_POST['industry_short_name'];
$industry_pretty_name = $_POST['industry_pretty_name'];
$industry_pretty_plural = $_POST['industry_pretty_plural'];
$industry_display_html = $_POST['industry_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM industries WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['industry_short_name'] = $industry_short_name;
$rec['industry_pretty_name'] = $industry_pretty_name;
$rec['industry_pretty_plural'] = $industry_pretty_plural;
$rec['industry_display_html'] = $industry_display_html;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

header("Location: some.php");

?>
