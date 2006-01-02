<?php
/**
 * Insert a new activity template, linked to a status, into the database
 *
 * @author Brad Marshall
 *
 * $Id: new.php,v 1.8 2006/01/02 21:27:56 vanmer Exp $
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
$role_id = $_POST['role_id'];
$sort_order = $_POST['sort_order'];
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

$con = get_xrms_dbconnection();
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
$rec['role_id'] = $role_id;

$tbl = "activity_templates";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$activity_template_id=$con->Insert_ID();

$on_what_table = str_replace("_", "-", $on_what_table);
$table_name = substr($on_what_table, 0, strpos($on_what_table, "-"));

$return_url="/admin/$on_what_table/one.php?{$table_name}_status_id=$on_what_id";

$sql = "SELECT activity_type_short_name FROM activity_types WHERE activity_type_id=$activity_type_id";
$rst=$con->execute($sql);

if (!$rst) { db_error_handler($con, $sql); }

$con->close();

if ((!$rst->EOF) AND ($rst->fields['activity_type_short_name']=='PRO')) {
    $return_url=urlencode($return_url);
    $msg=urlencode(_("Please select a workflow entity and workflow entity type for the new activity template"));
    $return_url="/admin/activity-templates/edit.php?on_what_table=$on_what_table&on_what_id=$on_what_d&activity_template_id=$activity_template_id&msg=$msg&return_url=$return_url";
} 
//go back to the status edit page after updating
header("Location: $http_site_root$return_url");

/**
 * $Log: new.php,v $
 * Revision 1.8  2006/01/02 21:27:56  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2005/09/29 14:58:36  vanmer
 * - changed to redirect to edit page if process activity type is selected for the new activity template
 *
 * Revision 1.6  2005/07/08 17:16:07  braverock
 * - add sort_order and role_id
 *
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