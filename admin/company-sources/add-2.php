<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$company_source_short_name = $_POST['company_source_short_name'];
$company_source_pretty_name = $_POST['company_source_pretty_name'];
$company_source_pretty_plural = $_POST['company_source_pretty_plural'];
$company_source_display_html = $_POST['company_source_display_html'];
$company_source_score_adjustment = $_POST['company_source_score_adjustment'];

$company_source_score_adjustment = ($company_source_score_adjustment > 0) ? $company_source_score_adjustment : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// $con->debug=1;

$sql = "SELECT * FROM company_sources WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['company_source_short_name'] = $company_source_short_name;
$rec['company_source_pretty_name'] = $company_source_pretty_name;
$rec['company_source_pretty_plural'] = $company_source_pretty_plural;
$rec['company_source_display_html'] = $company_source_display_html;
$rec['company_source_score_adjustment'] = $company_source_score_adjustment;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
