<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_status_id = $_POST['case_status_id'];

$con = get_xrms_dbconnection();

//lazy delete the record
$sql = "SELECT * FROM case_statuses WHERE case_status_id = $case_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['case_status_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

//lazy delete all activity_templates linked to this status
$sql = "SELECT * FROM activity_templates WHERE on_what_id = $case_status_id and on_what_table = 'case_statuses'";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_template_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

//update the sort_order field - re-initialize the values
$sql = "select case_status_id, sort_order from case_statuses where case_status_record_status='a' order by sort_order";

$rst = $con->execute($sql);
                                                                                                                             
$max = $rst->rowcount();
for ($i = 1; $i <= $max; $i++) {
    $case_status_id = $rst->fields['case_status_id'];
    $sql = "SELECT * FROM case_statuses WHERE case_status_id = $case_status_id";
    $rst2 = $con->execute($sql);

    $rec = array();
    $rec['sort_order'] = $i;

    $upd = $con->GetUpdateSQL($rst2, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $rst->movenext();
}
$rst->close();


$con->close();

header("Location: some.php");

?>
