<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$role_id = $_POST['role_id'];
$role_short_name = $_POST['role_short_name'];
$role_pretty_name = $_POST['role_pretty_name'];
$role_pretty_plural = $_POST['role_pretty_plural'];
$role_display_html = $_POST['role_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update roles set role_short_name = " . $con->qstr($role_short_name, get_magic_quotes_gpc()) . ", role_pretty_name = " . $con->qstr($role_pretty_name, get_magic_quotes_gpc()) . ", role_pretty_plural = " . $con->qstr($role_pretty_plural, get_magic_quotes_gpc()) . ", role_display_html = " . $con->qstr($role_display_html, get_magic_quotes_gpc()) . " WHERE role_id = $role_id";
$con->execute($sql);

$con->close();

header("Location: one.php?role_id=$role_id");

?>
