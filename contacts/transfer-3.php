<?php
/**
 * Database updates for transfer contact
 *
 * $Id: transfer-3.php,v 1.1 2004/07/19 22:18:09 neildogg Exp $
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

$everywhere = (isset($_POST['everywhere'])) ? $_POST['everywhere'] : '';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

//the order by ensure that the first address entered is used, this would normally be the main address except in unusual cirumstances
//if no record if found then the transfer contact will have there new adddress_id set as 0 which is better than still showing up in the used by field of the old company
$sql = "SELECT * FROM addresses where company_id = $new_company_id ORDER BY address_id ASC";
$rst = $con->execute($sql);
$new_address_id = 0;
if (!$rst->EOF) {
	$new_address_id = $rst->fields['address_id'];
}

$sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
$rst = $con->execute($sql);

$rec = array();
$rec['company_id'] = $new_company_id;
$rec['address_id'] = $new_address_id;
$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'transferred', 'contacts', $contact_id, 1);

if($everywhere) {
    $sql = "SELECT * FROM activities WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);
    
    $rec = array();
    $rec['company_id'] = $new_company_id;
    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
    
    $sql = "SELECT * FROM cases WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);
    
    $rec = array();
    $rec['company_id'] = $new_company_id;
    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
    
    $sql = "SELECT * FROM opportunities WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);
    
    $rec = array();
    $rec['company_id'] = $new_company_id;
    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
}

$con->close();

header("Location: one.php?msg=saved&contact_id=$contact_id");


/**
 * $Log: transfer-3.php,v $
 * Revision 1.1  2004/07/19 22:18:09  neildogg
 * - Added company search box
 *  - Added move all records with contact
 *
 * Revision 1.3  2004/06/30 11:38:16  braverock
 * - assign default address to contact after transfer
 *   - applies patch provided by David Uhlman
 *
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
