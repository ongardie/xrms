<?php
/**
 * /admin/address-types/add-2.php
 *
 * Add address-type
 *
 * $Id: add-2.php,v 1.2 2006/01/02 22:35:33 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$address_type = $_POST['address_type'];
$address_type_sort_value = $_POST['address_type_sort_value'];

$con = get_xrms_dbconnection();

if (!$address_type_sort_value) { $address_type_sort_value = $address_type; }

//save to database
$rec = array();
$rec['address_type'] = $address_type;
$rec['address_type_sort_value'] = $address_type_sort_value;

$tbl = "address_types";
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$con->close();

header("Location: some.php");

/**
 * $Log: add-2.php,v $
 * Revision 1.2  2006/01/02 22:35:33  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/04/11 00:43:25  maulani
 * - Add address type admin tool
 *
 */
?>
