<?php
/**
 * Insert a new activity template, linked to a status, into the database
 *
 * @author Brad Marshall
 *
 * $Id: new.php,v 1.5 2004/07/16 23:51:34 cpsource Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

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

//save to database
$rec = array();
$rec['activity_title'] = $activity_title;
$rec['on_what_id'] = $on_what_id;
$rec['on_what_table'] = $on_what_table;
$rec['activity_type_id'] = $activity_type_id;
$rec['duration'] = $duration;
$rec['sort_order'] = $sort_order;

$tbl = "activity_templates";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

$on_what_table = str_replace("_", "-", $on_what_table);
$table_name = substr($on_what_table, 0, strpos($on_what_table, "-"));

//go back to the status edit page after updating
header("Location: ".$http_site_root.'/admin/'.$on_what_table.'/one.php?'.$table_name.'_status_id='.$on_what_id);

/**
 * $Log: new.php,v $
 * Revision 1.5  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.4  2004/07/15 20:36:36  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.3  2004/06/24 20:13:46  braverock
 * - update to Header for synthetic url
 *   - patch provided by Neil Roberts
 *
 * Revision 1.2  2004/06/14 20:50:11  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.1  2004/06/03 16:11:53  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 */
?>