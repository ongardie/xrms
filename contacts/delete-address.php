<?php
/**
 * delete address for a contact
 *
 * $Id: delete-address.php,v 1.4 2006/01/02 22:59:59 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

$contact_id = $_GET['contact_id'];
$address_id = $_GET['address_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "SELECT * FROM addresses WHERE address_id = $address_id";
$rst = $con->execute($sql);

$rec = array();
$rec['address_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);


$sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
$rst = $con->execute($sql);

$rec = array();
$rec['address_id'] = '';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

header("Location: one.php?msg=deleted&contact_id=$contact_id");


/**
 * $Log: delete-address.php,v $
 * Revision 1.4  2006/01/02 22:59:59  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.2  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
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
