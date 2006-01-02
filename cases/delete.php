<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

if ( !isset($_GET['case_id']) ) {
  header("Location: some.php?msg=no_case");
}

$case_id = $_GET['case_id'];
$on_what_id=$case_id;

$session_user_id = session_check('','Delete');

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM cases WHERE case_id = $case_id";
$rst = $con->execute($sql);

$rec = array();
$rec['case_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'deleted', 'cases', $case_id, 1);

$con->close();

header("Location: some.php?msg=case_deleted");

?>