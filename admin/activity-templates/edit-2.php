<?php
/**
 * save an updated an activity template to database after editing it.
 *
 * $Id: edit-2.php,v 1.1 2004/06/03 16:11:53 braverock Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

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

$sql = "update activity_templates set
        activity_type_id = " . $con->qstr($activity_type_id, get_magic_quotes_gpc()) . ",
        activity_description = " . $con->qstr($activity_description, get_magic_quotes_gpc()) . ",
        activity_title = " . $con->qstr($activity_title, get_magic_quotes_gpc()) . ",
        duration = " . $con->qstr($duration, get_magic_quotes_gpc()) . "        
        WHERE activity_template_id = $activity_template_id";

$con->execute($sql);

$con->close();

//go back to the main activity template page after updating
header("Location: " . $http_site_root . $return_url);

?>
