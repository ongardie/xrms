<?php
/**
 * /admin/salutations/some.php
 *
 * Delete salutation
 *
 * $Id: delete.php,v 1.1 2005/04/10 17:33:36 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$salutation_id = $_POST['salutation_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "DELETE * FROM salutations WHERE salutation_id = $salutation_id";
$rst = $con->execute($sql);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.1  2005/04/10 17:33:36  maulani
 * - Add administrative tool to modify salutations popup list
 *
 *
 */
?>
