<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$campaign_id = $_GET['campaign_id'];
$category_id = $_GET['category_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "delete from entity_category_map 
where category_id = $category_id 
and on_what_table = 'campaigns' 
and on_what_id = $campaign_id";
$con->execute($sql);

$con->close();

header("Location: categories.php?campaign_id=$campaign_id");

?>