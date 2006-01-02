<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_type_id = $_POST['activity_type_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM activity_types WHERE activity_type_id = $activity_type_id";
$rst = $con->execute($sql);

$sort_order = $rst->fields["sort_order"];

$rec = array();
$rec['activity_type_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

// Fix the sort order (decrease by one) when the element is deleted.
$sql = "UPDATE activity_types SET sort_order = sort_order - 1 " .
       " WHERE sort_order > " . $sort_order .
       " AND activity_type_record_status = 'a'";
$rst = $con->execute($sql);

if (!$rst) {
    db_error_handler($con,$sql);
}

$con->close();

header("Location: some.php");

?>