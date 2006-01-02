<?php
/**
 * save the updated information for a single opportunity type
 *
 * $Id: edit-2.php,v 1.2 2006/01/02 21:59:08 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$opportunity_type_id = $_POST['opportunity_type_id'];
$opportunity_type_short_name = $_POST['opportunity_type_short_name'];
$opportunity_type_pretty_name = $_POST['opportunity_type_pretty_name'];
$opportunity_type_pretty_plural = $_POST['opportunity_type_pretty_plural'];
$opportunity_type_display_html = $_POST['opportunity_type_display_html'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM opportunity_types WHERE opportunity_type_id = $opportunity_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['opportunity_type_short_name'] = $opportunity_type_short_name;
$rec['opportunity_type_pretty_name'] = $opportunity_type_pretty_name;
$rec['opportunity_type_pretty_plural'] = $opportunity_type_pretty_plural;
$rec['opportunity_type_display_html'] = $opportunity_type_display_html;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2006/01/02 21:59:08  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.1  2005/07/06 21:08:57  braverock
 * - Initial Revision of Admin screens for opportunity types
 *
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