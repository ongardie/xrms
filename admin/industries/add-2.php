<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$industry_short_name = $_POST['industry_short_name'];
$industry_pretty_name = $_POST['industry_pretty_name'];
$industry_pretty_plural = $_POST['industry_pretty_plural'];
$industry_display_html = $_POST['industry_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values (" . $con->qstr($industry_short_name) . ", " . $con->qstr($industry_pretty_name) . ", " . $con->qstr($industry_pretty_plural) . ", " . $con->qstr($industry_display_html) . ")";
$con->execute($sql);

header("Location: some.php");

?>
