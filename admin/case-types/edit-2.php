<?php
/**
 * save the updated information for a single case
 *
 * $Id: edit-2.php,v 1.4 2004/07/16 23:51:35 cpsource Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_type_id = $_POST['case_type_id'];
$case_type_short_name = $_POST['case_type_short_name'];
$case_type_pretty_name = $_POST['case_type_pretty_name'];
$case_type_pretty_plural = $_POST['case_type_pretty_plural'];
$case_type_display_html = $_POST['case_type_display_html'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM case_types WHERE case_type_id = $case_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['case_type_short_name'] = $case_type_short_name;
$rec['case_type_pretty_name'] = $case_type_pretty_name;
$rec['case_type_pretty_plural'] = $case_type_pretty_plural;
$rec['case_type_display_html'] = $case_type_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.4  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 21:48:25  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/03/21 23:55:51  braverock
 * - fix SF bug 906413
 * - add phpdoc
 *
 */
?>