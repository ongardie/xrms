<?php
/**
 * /admin/salutations/add-2.php
 *
 * Add salutation
 *
 * $Id: add-2.php,v 1.1 2005/04/10 17:33:36 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$salutation = $_POST['salutation'];
$salutation_sort_value = $_POST['salutation_sort_value'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

if (!$salutation_sort_value) { $salutation_sort_value = $salutation; }

//save to database
$rec = array();
$rec['salutation'] = $salutation;
$rec['salutation_sort_value'] = $salutation_sort_value;

$tbl = "salutations";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.1  2005/04/10 17:33:36  maulani
 * - Add administrative tool to modify salutations popup list
 *
 *
 */
?>
