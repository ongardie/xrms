<?php

require_once('vars.php');
require_once('utils-interface.php');
require_once('utils-misc.php');
require_once('adodb/adodb.inc.php');

$session_user_id = session_check();

$company_type_id = $_POST['company_type_id'];
$company_type_short_name = $_POST['company_type_short_name'];
$company_type_pretty_name = $_POST['company_type_pretty_name'];
$company_type_pretty_plural = $_POST['company_type_pretty_plural'];
$company_type_display_html = $_POST['company_type_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update company_types set company_type_short_name = " . $con->qstr($company_type_short_name) . ", company_type_pretty_name = " . $con->qstr($company_type_pretty_name) . ", company_type_pretty_plural = " . $con->qstr($company_type_pretty_plural) . ", company_type_display_html = " . $con->qstr($company_type_display_html) . " WHERE company_type_id = $company_type_id";
$con->execute($sql);

header("Location: one.php?company_type_id=$company_type_id");

?>
