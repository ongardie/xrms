<?php
/**
 * Update database with changes to Case Priority
 *
 * $Id: edit-2.php,v 1.2 2004/03/22 02:14:45 braverock Exp $
 */

//include required files

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$case_priority_id = $_POST['case_priority_id'];
$case_priority_short_name = $_POST['case_priority_short_name'];
$case_priority_pretty_name = $_POST['case_priority_pretty_name'];
$case_priority_pretty_plural = $_POST['case_priority_pretty_plural'];
$case_priority_display_html = $_POST['case_priority_display_html'];
$case_priority_score_adjustment = $_POST['case_priority_score_adjustment'];

$case_priority_score_adjustment = ($case_priority_score_adjustment > 0) ? $case_priority_score_adjustment : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update case_priorities set case_priority_short_name = " . $con->qstr($case_priority_short_name, get_magic_quotes_gpc()) . ", case_priority_pretty_name = " . $con->qstr($case_priority_pretty_name, get_magic_quotes_gpc()) . ", case_priority_pretty_plural = " . $con->qstr($case_priority_pretty_plural, get_magic_quotes_gpc()) . ", case_priority_display_html = " . $con->qstr($case_priority_display_html, get_magic_quotes_gpc()) . ", case_priority_score_adjustment = $case_priority_score_adjustment where case_priority_id = $case_priority_id";
$con->execute($sql);

$con->close();

header("Location: one.php?case_priority_id=$case_priority_id");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2004/03/22 02:14:45  braverock
 * - debug SF bug 906413
 * - add phpdoc
 *
 */
?>