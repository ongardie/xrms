<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$user_id = $_POST['user_id'];
$password = $_POST['password'];

$password = md5($password);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update users set password = " . $con->qstr($password, get_magic_quotes_gpc()) . " where user_id = $user_id";

$con->execute($sql);

$con->close();

header("Location: some.php?msg=password_changed");

?>