<?php
/**
 * delete address for a contact
 *
 * $Id: delete-address.php,v 1.1 2004/06/09 17:38:12 gpowers Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$contact_id = $_GET['contact_id'];
$address_id = $_GET['address_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "update addresses set address_record_status = 'd' where address_id = $address_id";
$con->execute($sql);

$sql = "update contacts set address_id = '' where contact_id = $contact_id";
$con->execute($sql);

$con->close();

header("Location: one.php?msg=deleted&contact_id=$contact_id");


/**
 * $Log: delete-address.php,v $
 * Revision 1.1  2004/06/09 17:38:12  gpowers
 * - deletes contact addresses
 * - adapted from companies/delete-address.php
 * - added $Log and $Id tags
 *
 * Revision 1.1  2004/06/09 16:52:14  gpowers
 * - Contact Address Deleting
 * - adapted from companies/delete-address.php
 *
 */

?>
