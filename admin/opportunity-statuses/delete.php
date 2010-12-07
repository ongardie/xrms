<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$opportunity_status_id = (int)$_POST['opportunity_status_id'];

$con = get_xrms_dbconnection();

// Lazy delete the selected record
$sql = "SELECT opportunity_type_id, opportunity_status_record_status
        FROM opportunity_statuses
        WHERE opportunity_status_id = $opportunity_status_id";
$rst = $con->execute($sql);

// Get the opportunity_type_id so we can send the user back to where they came from
$opportunity_type_id = $rst->fields['opportunity_type_id'];

$rec = array();
$rec['opportunity_status_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

// Lazy delete all activity templates associated with this status
$sql = "SELECT activity_template_record_status
        FROM activity_templates
        WHERE on_what_table = 'opportunity_statuses'
        AND on_what_id = $opportunity_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_template_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

// Update the sort_order field - re-initialize the values
$sql = "SELECT opportunity_status_id, sort_order
        FROM opportunity_statuses
        WHERE opportunity_type_id = $opportunity_type_id
        AND opportunity_status_record_status = 'a'
        ORDER BY sort_order";
$rst = $con->execute($sql);

$max = $rst->rowcount();
for ($i = 1; $i <= $max; $i++) {
    $opportunity_status_id = $rst->fields['opportunity_status_id'];
    $sql = "SELECT sort_order
            FROM opportunity_statuses
            WHERE opportunity_status_id = $opportunity_status_id";
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

header("Location: some.php?opportunity_type_id=".$opportunity_type_id);

?>