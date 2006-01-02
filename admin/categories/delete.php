<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$category_id = $_POST['category_id'];
$category_record_status = "NULL";

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM categories WHERE category_id = $category_id";
$rst = $con->execute($sql);

$rec = array();
$rec['category_record_status'] = $category_record_status;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

?>
