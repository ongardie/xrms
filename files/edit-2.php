<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$file_id = $_POST['file_id'];
$file_pretty_name = $_POST['file_pretty_name'];
$file_description = $_POST['file_description'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "update files set file_pretty_name = " . $con->qstr($file_pretty_name, get_magic_quotes_gpc()) . ", file_description = " . $con->qstr($file_description, get_magic_quotes_gpc()) . " where file_id = $file_id";

$con->execute($sql);
$con->close();

header("Location: one.php?msg=saved&file_id=$file_id");

?>