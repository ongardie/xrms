<?php
/**
 * Database updates for transfer contact
 *
 * $Id: transfer-3.php,v 1.9 2011/03/09 17:00:43 gopherit Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$old_company_id = (int)$_POST['old_company_id'];
$contact_id     = (int)$_POST['contact_id'];
$new_company_id = (int)$_POST['new_company_id'];
$transfer_mode = $_POST['transfer_mode'];

// Get the database connection
$con = get_xrms_dbconnection();
// $con->debug = 1;

// Prepare to transfer all the records of this contact to the new company or to another contact in the old company
switch ($transfer_mode) {
    case 'company':  // We are transfering the contact and its records to the new company
        $update_record_fields = "company_id = $new_company_id";
        break;
    case 'contact':  // We are transfering the contact to a new company but we are transfering its records to a new contact in the old company
        $new_contact_id = (int)$_POST['new_contact_id'];
        $update_record_fields = "contact_id = $new_contact_id";
        break;
    default:
        header("Location: one.php?contact_id=$contact_id&msg=". urlencode(_('ERROR') .': '. _('Cound not transfer contact')));
        exit;
        break;
}

// set the business address for this contact to the primary address of the new company.
// not perfect, but the user will have a chance to change it from the contacts/one.php page
$rec = array();
$rec['company_id'] = $new_company_id;
$rec['address_id'] = fetch_default_address($con, $new_company_id);
$con->AutoExecute('contacts', $rec, 'UPDATE', "contact_id = $contact_id");

// Leave audit trail for the contact transfer
add_audit_item($con, $session_user_id, 'transferred', 'contacts', $contact_id, 1);
// Record the transfer in the contact_former_companies table
add_contact_company_history($con, $contact_id, $old_company_id);

// Transfer all the records over
if($transfer_mode) {

    // If we are transferring the activities to a new contact, we should add the new contact as a participant to all of them
    if($transfer_mode == 'contact') {
        // We'll have to use the API add_activity_participant() method and do this one activity at a time
        // to ensure we don't mess up the activity_participants table
        $sql = "SELECT activity_id
                FROM activities
                WHERE contact_id = $contact_id";
        $activity_id_arrays = $con->GetAll($sql);

        if (is_array($activity_id_arrays) AND count($activity_id_arrays)) {
            foreach ($activity_id_arrays as $activity_id_array) {
                add_activity_participant($con, $activity_id_array['activity_id'], $new_contact_id);
            }
        }
    }

    // Now proceed to update all the records
    $records_tables = array( 'activities',
                            'cases',
                            'opportunities');

    foreach ($records_tables as $records_table) {
        $sql = "UPDATE $records_table
                SET $update_record_fields
                WHERE contact_id = $contact_id";
        $con->Execute($sql);
    }

}

do_hook('contact_transfer_3');

$con->close();

$final_return_url="one.php?contact_id=$contact_id&msg=saved";
if ($transfer_mode == 'company') {
    // If we have transferred the contact to a new company, let the user make address changes, if necessary
    $return_url="$http_site_root/companies/addresses.php?company_id=$new_company_id&edit_contact_id=$contact_id&final_return_url=".urlencode($final_return_url);
    header("Location: $return_url");
} else {
    // Otherwise, send the user back to the contact page
    header("Location: $final_return_url");
}
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
 * Revision 1.9  2011/03/09 17:00:43  gopherit
 * FIXED Bug Artifact #3204309  When a contact is transfered, the user now has two choices:
 *  - move all records with the contact to the new company
 *  - leave all the records with another contact at the old company.  In this case, the activity will now also properly track its additional participants.
 *
 * Revision 1.8  2010/08/17 18:40:16  gopherit
 * Minor interface improvements
 *
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