<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$return_url = $_POST['return_url'];

$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$note_description = $_POST['note_description'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into notes (on_what_table, on_what_id, note_description, entered_at, entered_by) values (" . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ", $on_what_id, " . $con->qstr($note_description, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(mktime()) . ", $session_user_id)";
// print $sql;
$con->execute($sql);

$con->close();

header("Location: " . $http_site_root . $return_url);

?>