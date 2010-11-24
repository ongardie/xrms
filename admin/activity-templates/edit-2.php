<?php
/**
 * save an updated an activity template to database after editing it.
 *
 * $Id: edit-2.php,v 1.9 2010/11/24 22:41:57 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_template_id   = $_POST['activity_template_id'];
$activity_type_id       = $_POST['activity_type_id'];
$activity_description   = $_POST['activity_description'];
$default_text           = $_POST['default_text'];
$activity_title         = $_POST['activity_title'];
$start_delay            = $_POST['start_delay_days'] * 86400 +
                          $_POST['start_delay_hrs'] * 3600 +
                          $_POST['start_delay_mins'] * 60;
$fixed_date             = $_POST['fixed_date'];
$duration               = $_POST['duration_days'] * 86400 +
                          $_POST['duration_hrs'] * 3600 +
                          $_POST['duration_mins'] * 60;
$role_id                = $_POST['role_id'];
$workflow_entity        = $_POST['workflow_entity'];
$workflow_entity_type   = $_POST['workflow_entity_type'];
$sort_order             = $_POST['sort_order'];
$return_url             = $_POST['return_url'];

if (strlen($return_url) == 0) {
    $return_url = "/admin/activity-templates/some.php";
}

// Force a duration of 15 minute.
// @TODO: Should be set at the default activity duration preference value instead.
if ($duration == 0) {
    $duration = 900;
}

$con = get_xrms_dbconnection();
//$con->debug=1;

$sql = "SELECT * FROM activity_templates WHERE activity_template_id = $activity_template_id";
$rst = $con->execute($sql);

if (!$workflow_entity OR !$workflow_entity_type) { $workflow_entity=NULL; $workflow_entity_type=NULL; }

$rec = array();
$rec['activity_type_id'] = $activity_type_id;
$rec['activity_title'] = $activity_title;
$rec['role_id'] = $role_id;
$rec['start_delay'] = $start_delay;
$rec['fixed_date'] = $fixed_date;
$rec['duration'] = $duration;
$rec['workflow_entity']=$workflow_entity;
$rec['workflow_entity_type']=$workflow_entity_type;
$rec['activity_description'] = $activity_description;
$rec['default_text'] = $default_text;
$rec['sort_order'] = $sort_order;

$upd = $con->GetUpdateSQL($rst, $rec, false, $magicq=get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

//go back to the main activity template page after updating
header("Location: " . $http_site_root . $return_url);

?>