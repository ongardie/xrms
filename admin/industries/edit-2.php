<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$industry_id = $_POST['industry_id'];
$industry_short_name = $_POST['industry_short_name'];
$industry_pretty_name = $_POST['industry_pretty_name'];
$industry_pretty_plural = $_POST['industry_pretty_plural'];
$industry_display_html = $_POST['industry_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update industries set industry_short_name = " . $con->qstr($industry_short_name) . ", industry_pretty_name = " . $con->qstr($industry_pretty_name) . ", industry_pretty_plural = " . $con->qstr($industry_pretty_plural) . ", industry_display_html = " . $con->qstr($industry_display_html) . " WHERE industry_id = $industry_id";
$con->execute($sql);

header("Location: one.php?industry_id=$industry_id");

?>
