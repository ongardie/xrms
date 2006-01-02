<?php
/**
 * Insert the updated information into the database
 *
 * $Id: edit-2.php,v 1.5 2006/01/02 21:48:37 vanmer Exp $
 */

// include required files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$crm_status_id = $_POST['crm_status_id'];
$crm_status_short_name = $_POST['crm_status_short_name'];
$crm_status_pretty_name = $_POST['crm_status_pretty_name'];
$crm_status_pretty_plural = $_POST['crm_status_pretty_plural'];
$crm_status_display_html = $_POST['crm_status_display_html'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM crm_statuses WHERE crm_status_id = $crm_status_id";
$rst = $con->execute($sql);

$rec = array();
$rec['crm_status_short_name'] = $crm_status_short_name;
$rec['crm_status_pretty_name'] = $crm_status_pretty_name;
$rec['crm_status_pretty_plural'] = $crm_status_pretty_plural;
$rec['crm_status_display_html'] = $crm_status_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.5  2006/01/02 21:48:37  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/06/14 22:14:42  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/03/22 02:52:36  braverock
 * - redirect to some.php
 *
 */
?>