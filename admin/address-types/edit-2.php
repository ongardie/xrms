<?php
/**
 * /admin/address-types/edit-2.php
 *
 * Edit address-type
 *
 * $Id: edit-2.php,v 1.1 2005/04/11 00:43:25 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$address_type_id = $_POST['address_type_id'];
$address_type = $_POST['address_type'];
$address_type_sort_value = $_POST['address_type_sort_value'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM address_types WHERE address_type_id = $address_type_id";
$rst = $con->execute($sql);

$rec = array();
$rec['address_type'] = $address_type;
$rec['address_type_sort_value'] = $address_type_sort_value;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: some.php");

/**
 * $Log: edit-2.php,v $
 * Revision 1.1  2005/04/11 00:43:25  maulani
 * - Add address type admin tool
 *
 */
?>
