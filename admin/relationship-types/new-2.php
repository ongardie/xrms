<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$relationship_name = $_POST['relationship_name'];
$from_what_table = $_POST['from_what_table'];
$to_what_table = $_POST['to_what_table'];
$from_what_text = $_POST['from_what_text'];
$to_what_text = $_POST['to_what_text'];
$pre_formatting = $_POST['pre_formatting'];
$post_formatting = $_POST['post_formatting'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM relationship_types WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['relationship_name'] = $relationship_name;
$rec['from_what_table'] = $from_what_table;
$rec['to_what_table'] = $to_what_table;
$rec['from_what_text'] = $from_what_text;
$rec['to_what_text'] = $to_what_text;
$rec['pre_formatting'] = $pre_formatting;
$rec['post_formatting'] = $post_formatting;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

?>
