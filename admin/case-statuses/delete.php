<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_status_id = (int)$_POST['case_status_id'];

$con = get_xrms_dbconnection();

// Lazy delete the selected record
$sql = "SELECT case_type_id, case_status_record_status
        FROM case_statuses
        WHERE case_status_id = $case_status_id";
$rst = $con->execute($sql);

// Get the case_type_id so we can send the user back to where they came from
$case_type_id = $rst->fields['case_type_id'];

$rec = array();
$rec['case_status_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

// Lazy delete all activity templates associated with this status
$sql = "SELECT activity_template_record_status
        FROM activity_templates
        WHERE on_what_table = 'case_statuses'
        AND on_what_id = $case_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_template_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

// Update the sort_order field - re-initialize the values
$sql = "SELECT case_status_id, sort_order
        FROM case_statuses
        WHERE case_type_id = $case_type_id
        AND case_status_record_status = 'a'
        ORDER BY sort_order";
$rst = $con->execute($sql);

$max = $rst->rowcount();
for ($i = 1; $i <= $max; $i++) {
    $case_status_id = $rst->fields['case_status_id'];
    $sql = "SELECT sort_order
            FROM case_statuses
            WHERE case_status_id = $case_status_id";
    $rst2 = $con->execute($sql);

    $rec = array();
    $rec['sort_order'] = $i;

    $upd = $con->GetUpdateSQL($rst2, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    $rst2->close();
    $rst->movenext();
}
$rst->close();

$con->close();

header("Location: some.php?case_type_id=".$case_type_id);

?>