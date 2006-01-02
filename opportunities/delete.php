<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$opportunity_id = $_GET['opportunity_id'];
$on_what_id=$opportunity_id;

$session_user_id = session_check('', 'Delete');


$con = get_xrms_dbconnection();

$sql = "SELECT opportunity_record_status FROM opportunities WHERE opportunity_id = $opportunity_id";
$rst = $con->execute($sql);

$rec = array();
$rec['opportunity_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php?msg=opportunity_deleted");

?>