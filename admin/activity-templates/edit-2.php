<?php
/**
 * save an updated an activity template to database after editing it.
 *
 * $Id: edit-2.php,v 1.8 2006/01/02 21:27:56 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_template_id = $_POST['activity_template_id'];
$activity_type_id = $_POST['activity_type_id'];
$activity_description = $_POST['activity_description'];
$default_text = $_POST['default_text'];
$activity_title = $_POST['activity_title'];
$role_id = $_POST['role_id'];
$duration = $_POST['duration'];
$workflow_entity=$_POST['workflow_entity'];
$workflow_entity_type=$_POST['workflow_entity_type'];
$sort_order = $_POST['sort_order'];
$return_url = $_POST['return_url'];

if (strlen($return_url) == 0) {
    $return_url = "/admin/activity-templates/some.php";
}

$con = get_xrms_dbconnection();
//$con->debug=1;

$sql = "SELECT * FROM activity_templates WHERE activity_template_id = $activity_template_id";
$rst = $con->execute($sql);

if (!$workflow_entity OR !$workflow_entity_type) { $workflow_entity=NULL; $workflow_entity_type=NULL; }

$rec = array();
$rec['activity_type_id'] = $activity_type_id;
$rec['activity_description'] = $activity_description;
$rec['default_text'] = $default_text;
$rec['activity_title'] = $activity_title;
$rec['role_id'] = $role_id;
$rec['sort_order'] = $sort_order;
$rec['duration'] = $duration;
$rec['workflow_entity']=$workflow_entity;
$rec['workflow_entity_type']=$workflow_entity_type;

$upd = $con->GetUpdateSQL($rst, $rec, false, $magicq=get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

//go back to the main activity template page after updating
header("Location: " . $http_site_root . $return_url);

?>
