<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$industry_short_name = $_POST['industry_short_name'];
$industry_pretty_name = $_POST['industry_pretty_name'];
$industry_pretty_plural = $_POST['industry_pretty_plural'];
$industry_display_html = $_POST['industry_display_html'];

$con = get_xrms_dbconnection();

//save to database
$rec = array();
$rec['industry_short_name'] = $industry_short_name;
$rec['industry_pretty_name'] = $industry_pretty_name;
$rec['industry_pretty_plural'] = $industry_pretty_plural;
$rec['industry_display_html'] = $industry_display_html;

$tbl = 'industries';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

header("Location: some.php");

?>
