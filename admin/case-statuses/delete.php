<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_status_id = $_POST['case_status_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//lazy delete the record
$sql = "update case_statuses set case_status_record_status = 'd' where case_status_id = $case_status_id";
$con->execute($sql);

//lazy delete all activity_templates linked to this status
$sql = "update activity_templates set activity_template_record_status = 'd' where on_what_id = $case_status_id and on_what_table = 'case_statuses'";
$con->execute($sql);

//update the sort_order field - re-initialize the values
$sql = "select case_status_id, sort_order from case_statuses where case_status_record_status='a' order by sort_order";
$rst = $con->execute($sql);
                                                                                                                             
$max = $rst->rowcount();
for ($i = 1; $i <= $max; $i++) {
    $case_status_id = $rst->fields['case_status_id'];
    $sql = "update case_statuses set sort_order=$i where case_status_id=$case_status_id";
    $con->execute($sql);
    $rst->movenext();
}
$rst->close();


$con->close();

header("Location: some.php");

?>
