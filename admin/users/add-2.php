<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$role_id = $_POST['role_id'];
$username = $_POST['username'];
$password = $_POST['password'];
$last_name = $_POST['last_name'];
$first_names = $_POST['first_names'];
$email = $_POST['email'];
$gmt_offset = $_POST['gmt_offset'];

$gmt_offset = ($gmt_offset < 0) || ($gmt_offset > 0) ? $gmt_offset : 0;
$password = md5($password);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "insert into users (role_id, last_name, first_names, username, password, email, gmt_offset, language) values ($role_id, " . $con->qstr($last_name) . ", " . $con->qstr($first_names) . ", " . $con->qstr($username) . ", " . $con->qstr($password) . ", " . $con->qstr($email) . ", $gmt_offset, 'english')";
$con->execute($sql);

$con->close();

header("Location: some.php");

?>
