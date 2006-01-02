<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$company_source_short_name = $_POST['company_source_short_name'];
$company_source_pretty_name = $_POST['company_source_pretty_name'];
$company_source_pretty_plural = $_POST['company_source_pretty_plural'];
$company_source_display_html = $_POST['company_source_display_html'];
$company_source_score_adjustment = $_POST['company_source_score_adjustment'];

$company_source_score_adjustment = ($company_source_score_adjustment > 0) ? $company_source_score_adjustment : 0;

$con = get_xrms_dbconnection();

// $con->debug=1;

//save to database
$rec = array();
$rec['company_source_short_name'] = $company_source_short_name;
$rec['company_source_pretty_name'] = $company_source_pretty_name;
$rec['company_source_pretty_plural'] = $company_source_pretty_plural;
$rec['company_source_display_html'] = $company_source_display_html;
$rec['company_source_score_adjustment'] = $company_source_score_adjustment;

$tbl = "company_sources";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
