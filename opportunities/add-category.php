<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$opportunity_id = $_GET['opportunity_id'];
$category_id = $_GET['category_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "delete from entity_category_map 
where category_id = $category_id 
and on_what_table = 'opportunities' 
and on_what_id = $opportunity_id";
$con->execute($sql);

$sql = "SELECT * FROM entity_category_map WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['category_id'] = $category_id;
$rec['on_what_table'] = 'opportunities';
$rec['on_what_id'] = $opportunity_id;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: categories.php?opportunity_id=$opportunity_id");

?>
