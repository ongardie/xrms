<?php
/**
 * save an updated an activity template to database after editing it.
 *
 * $Id: edit-2.php,v 1.2 2004/06/14 20:50:11 introspectshun Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$activity_template_id = $_POST['activity_template_id'];
$activity_type_id = $_POST['activity_type_id'];
$activity_description = $_POST['activity_description'];
$activity_title = $_POST['activity_title'];
$duration = $_POST['duration'];
$return_url = $_POST['return_url'];

if (strlen($return_url) == 0) {
    $return_url = "/admin/activity-templates/some.php";
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug=1;

$sql = "SELECT * FROM activity_templates WHERE activity_template_id = $activity_template_id";
$rst = $con->execute($sql);

$rec = array();
$rec['activity_type_id'] = $activity_type_id;
$rec['activity_description'] = $activity_description;
$rec['activity_title'] = $activity_title;
$rec['duration'] = $duration;

$upd = $con->GetUpdateSQL($rst, $rec, false, $magicq=get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

//go back to the main activity template page after updating
header("Location: " . $http_site_root . $return_url);

?>
