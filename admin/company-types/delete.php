<?php

require_once('vars.php');
require_once('utils-interface.php');
require_once('utils-misc.php');
require_once('adodb/adodb.inc.php');

$session_user_id = session_check();

$company_type_id = $_POST['company_type_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update company_types set company_type_record_status = 'del' where company_type_id = $company_type_id";
$con->execute($sql);

header("Location: some.php");

?>
