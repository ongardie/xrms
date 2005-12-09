<?php
/**
 * Mark a note as deleted
 *
 * $Id: delete-2.php,v 1.2 2005/12/09 07:49:55 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

require_once('cf_functions.php');

$session_user_id = session_check();

$return_url = $_GET['return_url'];
$instance_id = $_GET['instance_id'];

$con = connect();

$rec = array();
$rec['record_status'] = 'd';

# Delete from data table
$tbl = 'cf_data';
if (!$con->AutoExecute($tbl, $rec, 'UPDATE', "instance_id = $instance_id")) {
	db_error_handler ($con, "Error deleting record");
}
# Delete from instances table
$tbl = 'cf_instances';
if (!$con->AutoExecute($tbl, $rec, 'UPDATE', "instance_id = $instance_id")) {
	db_error_handler ($con, "Error deleting record");
}

connect($con);

$return_url = urldecode($return_url);
header("Location: $return_url");

/**
 * $Log: delete-2.php,v $
 * Revision 1.2  2005/12/09 07:49:55  vanmer
 * - changed to use new connect parameter to close dbconnection if opened by the custom fields plugin
 *
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.4  2005/02/11 00:49:11  braverock
 * - modified to correctly pass contact_id and return_url
 *
 * Revision 1.3  2005/02/10 13:42:18  braverock
 * - update to newest info plugin provided by Keith Edmunds
 *   - now uses ADOdb for database access
 *
 * Revision 1.2  2004/12/31 22:54:18  gpowers
 * - added ability to add info inside a larger content box
 *
 * Revision 1.1  2004/07/22 19:49:48  gpowers
 * - enables deletion of an info item
 *
 * Revision 1.2  2004/06/12 06:23:27  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.1  2004/03/07 14:03:31  braverock
 * - add delete functionality to Notes
 *
 */
?>
