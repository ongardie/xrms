<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$opportunity_status_id = $_POST['opportunity_status_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

//lazy delete the selected record
$sql = "SELECT * FROM opportunity_statuses WHERE opportunity_status_id = $opportunity_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['opportunity_status_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

//lazy delete all activity templates associated with this status
$sql = "SELECT * FROM activity_templates WHERE on_what_id = $opportunity_status_id AND on_what_table = 'opportunity_statuses'";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_template_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

//update the sort_order field - re-initialize the values
$sql = "select opportunity_status_id, sort_order from opportunity_statuses where opportunity_status_record_status='a' order by sort_order";
$rst = $con->execute($sql);

$max = $rst->rowcount();
for ($i = 1; $i <= $max; $i++) {
    $opportunity_status_id = $rst->fields['opportunity_status_id'];
    $sql = "SELECT * FROM opportunity_statuses WHERE opportunity_status_id = $opportunity_status_id";
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
