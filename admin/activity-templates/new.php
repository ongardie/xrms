<?php
/**
 * Insert a new activity template, linked to a status, into the database
 *
 * @author Brad Marshall
 *
 * $Id: new.php,v 1.13 2010/12/01 22:14:28 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$on_what_id             = (int)$_POST['on_what_id'];
$on_what_table          = $_POST['on_what_table'];
$activity_title         = $_POST['title'];
$start_delay            = $_POST['start_delay_days'] * 86400 +
                          $_POST['start_delay_hrs'] * 3600 +
                          $_POST['start_delay_mins'] * 60;
$fixed_date             = $_POST['fixed_date'];
$duration               = $_POST['duration_days'] * 86400 +
                          $_POST['duration_hrs'] * 3600 +
                          $_POST['duration_mins'] * 60;
$activity_type_id       = (int)$_POST['activity_type_id'];
$role_id                = (int)$_POST['role_id'];
$sort_order             = $_POST['sort_order'];

//set defaults if we didn't get everything we need
if (strlen($activity_title) == 0) {
    $activity_title = ucwords(str_replace("_", " ", $on_what_table));
}

// Force a duration of 15 minute.
// @TODO: Should be set at the default activity duration preference value instead.
if ($duration == 0) {
    $duration = 900;
}

$con = get_xrms_dbconnection();
//$con->debug = 1;

// It is useful to have $sort_order as a string so we can validate it here
if ($sort_order == '') {
    // Get the last sort_order value, so we can insert the new record after it
    $sql = "SELECT sort_order FROM activity_templates
            WHERE on_what_id = $on_what_id
            AND on_what_table=". $con->qstr($on_what_table)."
            AND activity_template_record_status = 'a'
            ORDER BY sort_order DESC";
    $rst = $con->execute($sql);
    if (!$rst) {
        db_error_handler($con, $sql);
    } else {
        $sort_order = $rst->fields['sort_order'] + 1;
        $rst->close();
    }
} else {
    $sort_order = (int)$sort_order;
}

//save to database
$rec = array();
$rec['role_id'] = $role_id;
$rec['activity_type_id'] = $activity_type_id;
$rec['on_what_table'] = $on_what_table;
$rec['on_what_id'] = $on_what_id;
$rec['activity_title'] = $activity_title;
$rec['activity_description'] = "";
$rec['start_delay'] = $start_delay;
$rec['fixed_date'] = $fixed_date;
$rec['duration'] = $duration;
$rec['sort_order'] = $sort_order;
$tbl = "activity_templates";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$activity_template_id=$con->Insert_ID();

$on_what_table_url = str_replace("_", "-", $on_what_table);
$table_name = substr($on_what_table_url, 0, strpos($on_what_table_url, "-"));

$return_url="/admin/$on_what_table_url/one.php?{$table_name}_status_id=$on_what_id";

$sql = "SELECT activity_type_short_name FROM activity_types WHERE activity_type_id=$activity_type_id";
$rst=$con->execute($sql);

$con->close();

/*
 * @TODO: The part of the code which handles activites of the type PRO (process)
 * is, as of yet, incomplete
if ((!$rst->EOF) AND ($rst->fields['activity_type_short_name']=='PRO')) {
    $return_url=urlencode($return_url);
    $msg=urlencode(_("Please select a workflow entity and workflow entity type for the new activity template"));
    $return_url="/admin/activity-templates/edit.php?on_what_table=$on_what_table&on_what_id=$on_what_id&activity_template_id=$activity_template_id&msg=$msg&return_url=$return_url";
}
 * 
 */

//go back to the status edit page after updating
header("Location: $http_site_root$return_url");

/**
 * $Log: new.php,v $
 * Revision 1.13  2010/12/01 22:14:28  gopherit
 * Fixed SQL overquoting
 *
 * Revision 1.12  2010/11/26 21:44:18  gopherit
 * Fixed $sort_order $_POST value being ignored by the store script.
 *
 * Revision 1.11  2010/11/24 22:39:37  gopherit
 * Revised the store script for creating Templates attached to an Opportunity:
 * - provided support for the new start_delay field which allows workflow activities to have gaps between them, measured in seconds by start_delay
 * - finished the fixed_date functionality which lay dormant in the code base until now
 *
 * Revision 1.10  2008/02/27 01:44:57  randym56
 * Turned off debug set by other developers.  Required updates to tables (/admin/updateto2.1.php) to fix errors.
 *
 * Revision 1.9  2006/12/05 11:09:59  jnhayart
 * Add cosmetics display, and control localisation
 *
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