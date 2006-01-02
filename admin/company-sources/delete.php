<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$company_source_id = $_POST['company_source_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM company_sources WHERE company_source_id = $company_source_id";
$rst = $con->execute($sql);

$rec = array();
$rec['company_source_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

?>
