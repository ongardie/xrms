<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$opportunity_id = $_GET['opportunity_id'];
$category_id = $_GET['category_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "delete from entity_category_map 
where category_id = $category_id 
and on_what_table = 'opportunities' 
and on_what_id = $opportunity_id";
$con->execute($sql);

//save to database
$rec = array();
$rec['category_id'] = $category_id;
$rec['on_what_table'] = 'opportunities';
$rec['on_what_id'] = $opportunity_id;

$tbl = 'entity_category_map';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: categories.php?opportunity_id=$opportunity_id");

?>
