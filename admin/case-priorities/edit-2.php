<?php
/**
 * Update database with changes to Case Priority
 *
 * $Id: edit-2.php,v 1.5 2004/12/31 15:31:59 braverock Exp $
 */

//include required files

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_priority_id = $_POST['case_priority_id'];
$case_priority_short_name = $_POST['case_priority_short_name'];
$case_priority_pretty_name = $_POST['case_priority_pretty_name'];
$case_priority_pretty_plural = $_POST['case_priority_pretty_plural'];
$case_priority_display_html = $_POST['case_priority_display_html'];
$case_priority_score_adjustment = $_POST['case_priority_score_adjustment'];

$case_priority_score_adjustment = ($case_priority_score_adjustment > 0) ? $case_priority_score_adjustment : 0;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM case_priorities WHERE case_priority_id = $case_priority_id";
$rst = $con->execute($sql);

$rec = array();
$rec['case_priority_short_name'] = $case_priority_short_name;
$rec['case_priority_pretty_name'] = $case_priority_pretty_name;
$rec['case_priority_pretty_plural'] = $case_priority_pretty_plural;
$rec['case_priority_display_html'] = $case_priority_display_html;
$rec['case_priority_score_adjustment'] = $case_priority_score_adjustment;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.5  2004/12/31 15:31:59  braverock
 * - return to some.php after change
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.4  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 21:17:06  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/03/22 02:14:45  braverock
 * - debug SF bug 906413
 * - add phpdoc
 *
 */
?>
