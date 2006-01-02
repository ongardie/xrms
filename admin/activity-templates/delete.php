<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_template_id = $_POST['activity_template_id'];
$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$return_url = $_POST['return_url'];

if (strlen($return_url) == 0) {
    $return_url = "/admin/activity-templates/some.php";
}

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM activity_templates WHERE activity_template_id = $activity_template_id";
$rst = $con->execute($sql);
//$con->debug = 1;

$rec = array();
$rec['activity_template_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, $magicq=get_magic_quotes_gpc());
$con->execute($upd);

//update the sort_order field - re-initialize the values
$sql = "select activity_template_id, sort_order 
	from activity_templates 
	where activity_template_record_status='a' 
	and on_what_table='$on_what_table'
	and on_what_id=$on_what_id
	order by sort_order";
$rst = $con->execute($sql);
                                                                                                                             
$max = $rst->rowcount();
for ($i = 1; $i <= $max; $i++) {
    $activity_template_id = $rst->fields['activity_template_id'];

    $sql = "SELECT * FROM activity_templates WHERE activity_template_id = $activity_template_id";
    $rst2 = $con->execute($sql);
    
    $rec = array();
    $rec['sort_order'] = $i;
    
    $upd = $con->GetUpdateSQL($rst2, $rec, false, $magicq=get_magic_quotes_gpc());
    $con->execute($upd);

    $rst->movenext();
}
$rst->close();

$con->close();

header("Location: " . $http_site_root . $return_url);

?>
