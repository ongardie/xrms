<?php
/**
 * Insert the updated information into the database
 *
 * $Id: edit-2.php,v 1.8 2006/01/02 21:41:51 vanmer Exp $
 */

// include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$case_status_id = $_POST['case_status_id'];
$case_status_short_name = $_POST['case_status_short_name'];
$case_status_pretty_name = $_POST['case_status_pretty_name'];
$case_status_pretty_plural = $_POST['case_status_pretty_plural'];
$case_status_display_html = $_POST['case_status_display_html'];
$case_status_long_desc = $_POST['case_status_long_desc'];
$case_type_id = $_POST['case_type_id'];
$status_open_indicator = $_POST['status_open_indicator'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM case_statuses WHERE case_status_id = $case_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['case_status_short_name'] = $case_status_short_name;
$rec['case_status_pretty_name'] = $case_status_pretty_name;
$rec['case_status_pretty_plural'] = $case_status_pretty_plural;
$rec['case_status_display_html'] = $case_status_display_html;
$rec['case_status_long_desc'] = $case_status_long_desc;
$rec['status_open_indicator'] = $status_open_indicator;

// $con->debug=1;
$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();
if (!$return_url) $return_url="some.php?acase_type_id=$case_type_id";
header("Location: $return_url");

/**
 * $Log: edit-2.php,v $
 * Revision 1.8  2006/01/02 21:41:51  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2005/01/10 23:34:05  vanmer
 * - added open_indicator to status save
 *
 * Revision 1.6  2005/01/10 21:38:56  vanmer
 * - added case_type, needed for distinguishing between statuses
 *
 * Revision 1.5  2004/12/31 17:52:56  braverock
 * - add description for consistency
 *
 * Revision 1.4  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 21:37:55  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/03/22 02:52:59  braverock
 * - redirect to some.php
 * - add phpdoc
 *
 */
?>