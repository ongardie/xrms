<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$user_id = $_POST['user_id'];
$role_id = $_POST['role_id'];
$username = $_POST['username'];
$last_name = $_POST['last_name'];
$first_names = $_POST['first_names'];
$email = $_POST['email'];
$gmt_offset = $_POST['gmt_offset'];

$gmt_offset = (strlen($gmt_offset) > 0) ? $gmt_offset : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update users set role_id = $role_id, last_name = " . $con->qstr($last_name, get_magic_quotes_gpc()) . ", first_names = " . $con->qstr($first_names, get_magic_quotes_gpc()) . ", username = " . $con->qstr($username, get_magic_quotes_gpc()) . ", email = " . $con->qstr($email, get_magic_quotes_gpc()) . ", gmt_offset = $gmt_offset where user_id = $user_id";
$con->execute($sql);

$con->close();

header("Location: some.php");

?>
