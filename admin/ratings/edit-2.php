<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$rating_id = $_POST['rating_id'];
$rating_short_name = $_POST['rating_short_name'];
$rating_pretty_name = $_POST['rating_pretty_name'];
$rating_pretty_plural = $_POST['rating_pretty_plural'];
$rating_display_html = $_POST['rating_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update ratings set rating_short_name = " . $con->qstr($rating_short_name) . ", rating_pretty_name = " . $con->qstr($rating_pretty_name) . ", rating_pretty_plural = " . $con->qstr($rating_pretty_plural) . ", rating_display_html = " . $con->qstr($rating_display_html) . " WHERE rating_id = $rating_id";
$con->execute($sql);

$con->close();

header("Location: one.php?rating_id=$rating_id");

?>
