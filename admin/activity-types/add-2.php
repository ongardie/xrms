<?php
/**
 * Commit the new Activity Type to the database
 *
 * $Id: add-2.php,v 1.8 2005/01/06 23:18:55 introspectshun Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_type_short_name = $_POST['activity_type_short_name'];
$activity_type_pretty_name = $_POST['activity_type_pretty_name'];
$activity_type_pretty_plural = $_POST['activity_type_pretty_plural'];
$activity_type_display_html = $_POST['activity_type_display_html'];
$activity_type_score_adjustment = $_POST['activity_type_score_adjustment'];

$activity_type_score_adjustment = ($activity_type_score_adjustment > 0) ? $activity_type_score_adjustment : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//save to database
$rec = array();

//check for the sort order to use
$sort_sql = "select MAX(sort_order) from activity_types WHERE activity_type_record_status = 'a'";
$sort_order = $con->GetOne($sort_sql);
if ($sort_order) {
    $rec['sort_order'] = $sort_order+1;
} else {
    db_error_handler ($con, $sort_sql);
}

//set the other variables
$rec['activity_type_short_name'] = $activity_type_short_name;
$rec['activity_type_pretty_name'] = $activity_type_pretty_name;
$rec['activity_type_pretty_plural'] = $activity_type_pretty_plural;
$rec['activity_type_display_html'] = $activity_type_display_html;
$rec['activity_type_score_adjustment'] = $activity_type_score_adjustment;

//commit it
$tbl = "activity_types";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$rst = $con->execute($ins);
if (!$rst) {
    db_error_handler ($con, $ins);
}

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.8  2005/01/06 23:18:55  introspectshun
 * - Removed escape chars from query
 *
 * Revision 1.7  2004/12/30 18:57:58  braverock
 * - only work with active activity types
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 15:04:45  braverock
 * - add phpdoc
 *
 */
?>