<?php
/**
 * delete address for a company
 *
 * $Id: delete-address.php,v 1.4 2004/06/12 05:03:16 introspectshun Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_GET['company_id'];
$address_id = $_GET['address_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM addresses WHERE address_id = $address_id";
$rst = $con->execute($sql);

$rec = array();
$rec['address_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: addresses.php?msg=address_deleted&company_id=$company_id");

/**
 * $Log: delete-address.php,v $
 * Revision 1.4  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.3  2004/06/09 17:43:59  gpowers
 * - added $Id and $Log tags
 *
 *
*/
?>
