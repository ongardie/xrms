<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$rating_short_name = $_POST['rating_short_name'];
$rating_pretty_name = $_POST['rating_pretty_name'];
$rating_pretty_plural = $_POST['rating_pretty_plural'];
$rating_display_html = $_POST['rating_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values (" . $con->qstr($rating_short_name) . ", " . $con->qstr($rating_pretty_name) . ", " . $con->qstr($rating_pretty_plural) . ", " . $con->qstr($rating_display_html) . ")";
$con->execute($sql);

$con->close();

header("Location: some.php");

?>
