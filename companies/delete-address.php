<?php
/**
 * delete address for a company
 *
 * $Id: delete-address.php,v 1.3 2004/06/09 17:43:59 gpowers Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$company_id = $_GET['company_id'];
$address_id = $_GET['address_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "update addresses set address_record_status = 'd' where address_id = $address_id";
$con->execute($sql);

$con->close();

header("Location: addresses.php?msg=address_deleted&company_id=$company_id");

/**
 * $Log: delete-address.php,v $
 * Revision 1.3  2004/06/09 17:43:59  gpowers
 * - added $Id and $Log tags
 *
 *
*/
?>
