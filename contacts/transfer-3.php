<?php
/**
 * Database updates for transfer contact
 *
 * $Id: transfer-3.php,v 1.2 2005/06/07 21:14:43 braverock Exp $
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

// set the business address for this contact to the primary address of the new company.
// not perfect, but the user will have a chance to change it from the contacts/one.php page
$new_address_id = 0;
$new_address_id = fetch_default_address($con, $new_company_id)

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

//now go to the Contact Details page the user can make additional changes if they need to
header("Location: one.php?msg=saved&contact_id=$contact_id");

/**
 * $Log: transfer-3.php,v $
 * Revision 1.2  2005/06/07 21:14:43  braverock
 * - change to use fetch_default_address fn to set new business address for contact
 *
 * Revision 1.1  2004/07/19 22:18:09  neildogg
 * - Added company search box
 *  - Added move all records with contact
 */
?>