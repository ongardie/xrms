<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$company_id = $_POST['company_id'];
$former_name = $_POST['former_name'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into company_former_names (company_id, namechange_at, former_name) values ($company_id, now(), " . $con->qstr($former_name, get_magic_quotes_gpc()) . ")";

$con->execute($sql);
$con->close();

header("Location: relationships.php?company_id=$company_id");

?>
