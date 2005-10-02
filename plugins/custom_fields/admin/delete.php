<?php
/**
 * Delete a custom fields element definition
 *
 * $Id: delete.php,v 1.1 2005/10/02 23:57:33 vanmer Exp $
 */
 
require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('../cf_functions.php');

$session_user_id = session_check( 'Admin' );

$object_id = $_POST['object_id'];

# Create skeletal record
$rec = array();
$rec['record_status'] = 'd';

# Update database
$con = connect();
$tbl = "cf_objects";
if (!$con->AutoExecute($tbl, $rec, 'UPDATE', "object_id = $object_id")) {
	db_error_handler ($con, $upd);
}

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.1  2005/10/02 23:57:33  vanmer
 * - Initial Revison of the custom_fields plugin, thanks to Keith Edmunds
 *
 * Revision 1.3  2005/02/11 00:54:55  braverock
 * - add phpdoc where neccessary
 * - fix code formatting and comments
 *
 */
?>
