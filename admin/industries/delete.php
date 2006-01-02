<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$industry_id = $_POST['industry_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM industries WHERE industry_id = $industry_id";
$rst = $con->execute($sql);

$rec = array();
$rec['industry_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

header("Location: some.php");

?>
