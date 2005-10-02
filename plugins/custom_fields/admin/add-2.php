<?php
/**
 * Commit the new Info Type to the database
 *
 * $Id: add-2.php,v 1.1 2005/10/02 23:57:33 vanmer Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('../cf_functions.php');

$session_user_id = session_check( 'Admin' );

// Create record for new object
$rec = array();
$rec['object_name'] = $_POST['object_name'];
$rec['type_name'] = $_POST['type_name'];

//commit it
$con = connect();
$tbl = "cf_objects";
if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
	db_error_handler ($con, "Insert new custom fields object");
}
$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.5  2005/03/18 21:11:37  gpowers
 * - removed (commented) notes
 *
 * Revision 1.4  2005/03/18 20:54:37  gpowers
 * - added support for inline (custom fields) info
 *
 * Revision 1.3  2005/02/11 00:54:55  braverock
 * - add phpdoc where neccessary
 * - fix code formatting and comments
 *
 * Revision 1.2  2004/11/12 06:36:37  gpowers
 * - added support for single display_on add/edit/delete/show
 *
 * Revision 1.1  2004/11/10 07:27:49  gpowers
 * - added admin screens for info types
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 15:04:45  braverock
 * - add phpdoc
 *
 */
?>
