<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$note_id = $_POST['note_id'];
$note_description = $_POST['note_description'];
$return_url = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update notes set note_description = " . $con->qstr($note_description, get_magic_quotes_gpc()) . " where note_id = $note_id";

$con->execute($sql);

$con->close();

header("Location: " . $http_site_root . $return_url);

?>