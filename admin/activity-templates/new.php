<?php
/**
 * Insert a new activity template, linked to a status, into the database
 *
 * @author Brad Marshall
 *
 * $Id: new.php,v 1.1 2004/06/03 16:11:53 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$activity_title = $_POST['title'];
$duration = $_POST['duration'];
$activity_type_id = $_POST['activity_type_id'];
$on_what_id = $_POST['on_what_id'];
$on_what_table = $_POST['on_what_table'];

//set defaults if we didn't get everything we need
if (strlen($activity_title) == 0) {
    $activity_title = ucwords(str_replace("_", " ", $on_what_table));
}

if (strlen($duration) == 0) {
    $duration = 1;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

//get next sort_order value, put it at the bottom of the list
$sql = "select sort_order from activity_templates
        where on_what_id=$on_what_id
        and on_what_table='$on_what_table'
        order by sort_order desc";
$rst = $con->execute($sql);
$sort_order = $rst->rowcount() + 1;
$rst->close();

$sql = "insert into activity_templates set
        activity_title = ". $con->qstr($activity_title, get_magic_quotes_gpc()) . ",
        on_what_id = " . $con->qstr($on_what_id, get_magic_quotes_gpc()) . ",
        on_what_table = " . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ",
        activity_type_id = " . $con->qstr($activity_type_id, get_magic_quotes_gpc()) . ",
        duration = ". $con->qstr($duration, get_magic_quotes_gpc()) . ",
        sort_order = " . $con->qstr($sort_order, get_magic_quotes_gpc());

$con->execute($sql);

$con->close();

$on_what_table = str_replace("_", "-", $on_what_table);
$table_name = substr($on_what_table, 0, strpos($on_what_table, "-"));

//go back to the status edit page after updating
header("Location: ".$http_site_root.'/'.$on_what_table.'/one.php?'.$table_name.'_status_id='.$on_what_id);


/**
 * $Log: new.php,v $
 * Revision 1.1  2004/06/03 16:11:53  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 */
?>