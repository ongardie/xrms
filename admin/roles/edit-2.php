<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$role_id = $_POST['role_id'];
$role_short_name = $_POST['role_short_name'];
$role_pretty_name = $_POST['role_pretty_name'];
$role_pretty_plural = $_POST['role_pretty_plural'];
$role_display_html = $_POST['role_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM roles WHERE role_id = $role_id";
$rst = $con->execute($sql);

$rec = array();
$rec['role_short_name'] = $role_short_name;
$rec['role_pretty_name'] = $role_pretty_name;
$rec['role_pretty_plural'] = $role_pretty_plural;
$rec['role_display_html'] = $role_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: one.php?role_id=$role_id");
?>
