<?php
/**
 * Delete Category Scopes
 *
 * $Id: delete.php,v 1.1 2007/12/31 19:05:25 randym56 Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$category_scope_id = $_POST['category_scope_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM category_scopes WHERE category_scope_id = $category_scope_id";
$rst = $con->execute($sql);

$rec = array();
$rec['category_scope_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: delete.php,v $
 * Revision 1.1  2007/12/31 19:05:25  randym56
 * Function to add/edit Category Scopes table
 *
 * Revision v 1.0 2007/12/31 11:09:59 randym56 Exp $
 * Add function
 *
 */
?>
