<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$return_url = $_POST['return_url'];

$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$note_description = $_POST['note_description'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "SELECT * FROM notes WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['on_what_table'] = $on_what_table;
$rec['on_what_id'] = $on_what_id;
$rec['note_description'] = $note_description;
$rec['entered_at'] = time();
$rec['entered_by'] = $session_user_id;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: " . $http_site_root . $return_url);

?>
