<?php
/**
 * Database updates for transfer contact
 *
 * $Id: transfer-3.php,v 1.7 2006/01/02 23:00:00 vanmer Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$new_company_id = $_POST['company_id'];
$old_company_id = $_POST['old_company_id'];
$contact_id = $_POST['contact_id'];

$everywhere = (isset($_POST['everywhere'])) ? $_POST['everywhere'] : '';

$con = get_xrms_dbconnection();
// $con->debug = 1;

// set the business address for this contact to the primary address of the new company.
// not perfect, but the user will have a chance to change it from the contacts/one.php page
$new_address_id = 0;
$new_address_id = fetch_default_address($con, $new_company_id);

$sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
$rst = $con->execute($sql);

$rec = array();
$rec['company_id'] = $new_company_id;
$rec['address_id'] = $new_address_id;
$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

add_audit_item($con, $session_user_id, 'transferred', 'contacts', $contact_id, 1);
add_contact_company_history($con, $contact_id, $old_company_id);

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

do_hook('contact_transfer_3');

$con->close();

//now go to the Contact Address select  page so the user can make address changes
$final_return_url="one.php?msg=saved&contact_id=$contact_id";
$return_url="$http_site_root/companies/addresses.php?company_id=$new_company_id&edit_contact_id=$contact_id&final_return_url=".urlencode($final_return_url);
header("Location: $return_url");
exit;
function add_contact_company_history($con, $contact_id, $company_id) {
    //save to database
    $rec = array();
    $rec['contact_id'] = $contact_id;
    $rec['companychange_at'] = time();
    $rec['former_company_id'] = $company_id;
    
    $tbl = 'contact_former_companies';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);
    return $con->Insert_ID();
}
/**
 * $Log: transfer-3.php,v $
 * Revision 1.7  2006/01/02 23:00:00  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.6  2005/08/04 20:08:08  vanmer
 * - changed final transfer step to redirect to address selection to select new address for the new contact
 *
 * Revision 1.5  2005/08/04 18:59:22  vanmer
 * - added history function to track former companies for a contact
 *
 * Revision 1.4  2005/06/15 18:29:50  ycreddy
 * Added a plugin hook contact_transfer_3
 *
 * Revision 1.3  2005/06/07 21:15:28  braverock
 * - syntax error
 *
 * Revision 1.2  2005/06/07 21:14:43  braverock
 * - change to use fetch_default_address fn to set new business address for contact
 *
 * Revision 1.1  2004/07/19 22:18:09  neildogg
 * - Added company search box
 *  - Added move all records with contact
 */
?>
