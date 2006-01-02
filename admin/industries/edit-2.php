<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$industry_id = $_POST['industry_id'];
$industry_short_name = $_POST['industry_short_name'];
$industry_pretty_name = $_POST['industry_pretty_name'];
$industry_pretty_plural = $_POST['industry_pretty_plural'];
$industry_display_html = $_POST['industry_display_html'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM industries WHERE industry_id = $industry_id";
$rst = $con->execute($sql);

$rec = array();
$rec['industry_short_name'] = $industry_short_name;
$rec['industry_pretty_name'] = $industry_pretty_name;
$rec['industry_pretty_plural'] = $industry_pretty_plural;
$rec['industry_display_html'] = $industry_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

header("Location: some.php");

?>
