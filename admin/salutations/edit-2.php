<?php
/**
 * /admin/salutations/edit-2.php
 *
 * Edit salutation
 *
 * $Id: edit-2.php,v 1.2 2006/01/02 22:11:29 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$salutation_id = $_POST['salutation_id'];
$salutation = $_POST['salutation'];
$salutation_sort_value = $_POST['salutation_sort_value'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM salutations WHERE salutation_id = $salutation_id";
$rst = $con->execute($sql);

$rec = array();
$rec['salutation'] = $salutation;
$rec['salutation_sort_value'] = $salutation_sort_value;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2006/01/02 22:11:29  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.1  2005/04/10 17:33:36  maulani
 * - Add administrative tool to modify salutations popup list
 *
 *
 */
?>
