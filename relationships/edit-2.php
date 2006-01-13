<?php
/**
 * Unassociate
 *
 * Unassociate from edit.php
 *
 * @author Neil Roberts
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once($include_directory . 'utils-relationships.php');

$session_user_id = session_check();

$relationship_id = $_POST['relationship_id'];
$return_url = $_POST['return_url'];

$con = get_xrms_dbconnection();
delete_relationship($con, $relationship_id);

 //$con->debug=1;

$con->close();

header("Location: " . $http_site_root . "/" . $return_url);

/**
 * $Log: edit-2.php,v $
 * Revision 1.3  2006/01/13 00:01:00  vanmer
 * - changed to use newly created include/utils-relationships.php instead of relationships/relationship_functions.php
 * - removed deprecated relationship_functions.php
 *
 * Revision 1.2  2006/01/02 23:31:01  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2004/07/09 15:33:42  neildogg
 * New, generic programs that utilize the new relationships table
 *
 * Revision 1.2  2004/07/05 21:54:54  introspectshun
 * - Now uses GetUpdateSQL
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>
