<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$contact_id = $_GET['contact_id'];
$category_id = $_GET['category_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "delete from entity_category_map 
where category_id = $category_id 
and on_what_table = 'contacts' 
and on_what_id = $contact_id";
$con->execute($sql);

$con->close();

header("Location: categories.php?contact_id=$contact_id");

?>