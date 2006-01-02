<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$company_type_short_name = $_POST['company_type_short_name'];
$company_type_pretty_name = $_POST['company_type_pretty_name'];
$company_type_pretty_plural = $_POST['company_type_pretty_plural'];
$company_type_display_html = $_POST['company_type_display_html'];

$con = get_xrms_dbconnection();

//save to database
$rec = array();
$rec['company_type_short_name'] = $company_type_short_name;
$rec['company_type_pretty_name'] = $company_type_pretty_name;
$rec['company_type_pretty_plural'] = $company_type_pretty_plural;
$rec['company_type_display_html'] = $company_type_display_html;

$tbl = "company_types";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

header("Location: some.php");

?>
