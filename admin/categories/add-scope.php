<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );
$msg = $_GET['msg'];

$category_id = $_GET['category_id'];
$category_scope_id = $_GET['category_scope_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "delete from category_category_scope_map where category_id = $category_id and category_scope_id = $category_scope_id";
$con->execute($sql);

$sql = "SELECT * FROM category_category_scope_map WHERE 1 = 2"; //select empty record as placeholder
$rst = $con->execute($sql);

$rec = array();
$rec['category_id'] = $category_id;
$rec['category_scope_id'] = $category_scope_id;

$ins = $con->GetInsertSQL($rst, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: one.php?category_id=$category_id");

?>
