<?php
/**
 * Database updates for transfer contact
 *
 * $Id: transfer-2.php,v 1.2 2004/06/15 17:26:21 introspectshun Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$new_company_id = $_POST['company_id'];
$contact_id = $_POST['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
$rst = $con->execute($sql);

$rec = array();
$rec['company_id'] = $new_company_id;

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'transferred', 'contacts', $contact_id, 1);

$con->close();

header("Location: one.php?msg=saved&contact_id=$contact_id");


/**
 * $Log: transfer-2.php,v $
 * Revision 1.2  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.1  2004/06/09 19:25:11  gpowers
 * - database updates for transfer of contact to new company
 *
 *
 */

?>
