<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_source_id = $_POST['company_source_id'];
$company_source_short_name = $_POST['company_source_short_name'];
$company_source_pretty_name = $_POST['company_source_pretty_name'];
$company_source_pretty_plural = $_POST['company_source_pretty_plural'];
$company_source_display_html = $_POST['company_source_display_html'];
$company_source_score_adjustment = $_POST['company_source_score_adjustment'];

$company_source_score_adjustment = ($company_source_score_adjustment > 0) ? $company_source_score_adjustment : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update company_sources set company_source_short_name = " . $con->qstr($company_source_short_name, get_magic_quotes_gpc()) . ", company_source_pretty_name = " . $con->qstr($company_source_pretty_name, get_magic_quotes_gpc()) . ", company_source_pretty_plural = " . $con->qstr($company_source_pretty_plural, get_magic_quotes_gpc()) . ", company_source_display_html = " . $con->qstr($company_source_display_html, get_magic_quotes_gpc()) . ", company_source_score_adjustment = $company_source_score_adjustment where company_source_id = $company_source_id";
$con->execute($sql);

$con->close();

header("Location: one.php?company_source_id=$company_source_id");

?>
