<?php
/*
 * Author: Brad Marshall
 * Date: 05/26/04
 * Description: This file re-sorts the statuses when the user clicks
 *   the 'up' or 'down' button. 
 *
 */


require_once('../include-locations.inc');
                                                                                                                             
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
                                                                                                                             
$session_user_id = session_check();

$direction = $_GET['direction'];
$sort_order = $_GET['sort_order'];
$table_name = $_GET['table_name'];
$on_what_id = $_GET['on_what_id'];
$return_url = $_GET['return_url'];
$activity_template = $_GET['activity_template'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug=1;

//handle incoming data
if ($direction == 'down') {
    $swap = $sort_order + 1;
} else if ($direction == 'up') {
    $swap = $sort_order - 1;
}

//if it's sorting activity template links, set the vars
if ($activity_template == 1) {
    $table_name_plural = "activity_templates";
    $on_what_table = $table_name . "es";
    $table_name = "activity_template";
} else {
    $table_name_plural = $table_name . "es";
    $on_what_table = $table_name;
}


//retrieve the sort_order and id value in the two rows to be swapped
$sql = "select sort_order, " . $table_name . "_id from $table_name_plural 
	where (sort_order=$sort_order or sort_order=$swap) ";
if ($activity_template == 1) {
	$sql .= "and on_what_table='$on_what_table' and on_what_id=$on_what_id "; 
}
$sql .= "and (" . $table_name . "_record_status='a')";

$rst = $con->execute($sql);

//get field data for the first row
$source_id = $rst->fields[$table_name . '_id'];
$dest_sort_order = $rst->fields['sort_order'];

$rst->movenext();

//get field data for the second row
$dest_id = $rst->fields[$table_name . '_id'];
$source_sort_order = $rst->fields['sort_order'];

$rst->close();

//swap sort_order and insert into the table
$sql = "update " . $table_name_plural . " set sort_order=$source_sort_order " 
	. "where " . $table_name . "_id=$source_id";
$con->execute($sql);

$sql = "update " . $table_name_plural . " set sort_order=$dest_sort_order "
	. "where " . $table_name . "_id=$dest_id";
$con->execute($sql);

//reload the page to see the new order
header ('Location: ' . $http_site_root . $return_url);

?>
