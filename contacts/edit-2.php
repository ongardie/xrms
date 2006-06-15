<?php
/**
 * Insert changes to a contact into the database.
 *
 * $Id: edit-2.php,v 1.33 2006/06/15 21:32:59 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$contact_id = $_POST['contact_id'];
$on_what_id=$contact_id;

$session_user_id = session_check('','Update');

$address_id = $_POST['address_id'];
$home_address_id = $_POST['home_address_id'];
$division_id = $_POST['division_id'];
if (!$address_id) { $address_id=1; };
if (!$home_address_id) { $home_address_id=1; };
$first_names = $_POST['first_names'];
$last_name = $_POST['last_name'];
$summary = $_POST['summary'];
$title = $_POST['title'];
$description = $_POST['description'];
$date_of_birth = $_POST['date_of_birth'];
$tax_id = $_POST['tax_id'];
$gender = $_POST['gender'];
$salutation = $_POST['salutation'];
$email = $_POST['email'];
$work_phone = $_POST['work_phone'];
$work_phone_ext = $_POST['work_phone_ext'];
$cell_phone = $_POST['cell_phone'];
$home_phone = $_POST['home_phone'];
$fax = $_POST['fax'];
$user_id = $_POST['user_id'];
/*
IM fields, now handled through plugin
$aol_name = $_POST['aol_name'];
$yahoo_name = $_POST['yahoo_name'];
$msn_name = $_POST['msn_name'];
*/
$interests = $_POST['interests'];
$profile = $_POST['profile'];
$custom1 = $_POST['custom1'];
$custom2 = $_POST['custom2'];
$custom3 = $_POST['custom3'];
$custom4 = $_POST['custom4'];
//$return_url=$_POST['return_url'];

getGlobalVar($address_return_url, 'address_return_url');

$url_return_url=urlencode($address_return_url);
$con = get_xrms_dbconnection();
// $con->debug=1;

$contact_rst=get_contact($con, $contact_id, true);
$company_id=$contact_rst->fields['company_id'];

if ($_POST['btChangeAddress']) {
    $return_url="../companies/addresses.php?company_id=$company_id&edit_contact_id=$contact_id"; 
}
if ($_POST['btEditBusinessAddress']) {
    $return_url="../companies/one-address.php?form_action=edit&return_url=$url_return_url&company_id=$company_id&address_id=$address_id";
}
if ($_POST['btEditHomeAddress']) {
    $return_url="../companies/one-address.php?form_action=edit&return_url=$url_return_url&company_id=0&contact_id=$contact_id&address_id=$home_address_id";
}
if ($_POST['btNewHomeAddress']) {
    $return_url="../companies/one-address.php?form_action=new&return_url=$url_return_url&company_id=0&contact_id=$contact_id";
}

if ($salutation == '0') {
    $salutation = '';
}


$validationsPassed = do_hook_function('contact_custom_inline_edit_validate',$_POST);
if ($validationsPassed) {
        if ($validationsPassed!= 1) {
                $con->close();
                header("Location: edit.php?msg=$validationsPassed&contact_id=$contact_id");
                return;
        }
}

$rec = array();
$rec['contact_id']=$contact_id;
$rec['address_id'] = $address_id;
$rec['home_address_id'] = $home_address_id;
$rec['division_id'] = $division_id;
$rec['last_name'] = $last_name;
$rec['first_names'] = $first_names;
$rec['summary'] = $summary;
$rec['title'] = $title;
$rec['description'] = $description;
$rec['email'] = $email;
$rec['work_phone'] = $work_phone;
$rec['work_phone_ext'] =$work_phone_ext;
$rec['cell_phone'] = $cell_phone;
$rec['home_phone'] = $home_phone;
$rec['fax'] = $fax;
$rec['user_id'] = $user_id;
/*
IM fields, now handled through plugin
$rec['aol_name'] = $aol_name;
$rec['yahoo_name'] = $yahoo_name;
$rec['msn_name'] = $msn_name;
*/
$rec['interests'] = $interests;
$rec['gender'] = $gender;
$rec['date_of_birth'] = $date_of_birth;
$rec['tax_id'] = $tax_id;
$rec['profile'] = $profile;

if ($custom1)
    $rec['custom1'] = $custom1;
if ($custom2)
    $rec['custom2'] = $custom2;
if ($custom3)
    $rec['custom3'] = $custom3;
if ($custom4)
    $rec['custom4'] = $custom4;

if ($salutation != '0') {
    $rec['salutation'] = $salutation;
} else {
    $rec['salutation'] = '';
}

$ret=update_contact($con, $rec, $contact_id, $contact_rst, get_magic_quotes_gpc());

if (!$return_url) {
    $return_url="one.php?msg=saved&contact_id=$contact_id";
}
header("Location: $return_url");

/**
 * $Log: edit-2.php,v $
 * Revision 1.33  2006/06/15 21:32:59  vanmer
 * - added owner to the UI for a contact
 *
 * Revision 1.32  2006/04/05 00:48:31  vanmer
 * - pass magic quotes value into contacts API
 *
 * Revision 1.31  2006/01/02 22:59:59  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.30  2005/11/18 20:35:23  vanmer
 * - changed to use contact API for updating contact
 *
 * Revision 1.29  2005/09/25 05:42:06  vanmer
 * - removed IM field references from all contact pages (now handled by plugin)
 * - added custom field hook for contacts new.php
 *
 * Revision 1.28  2005/09/06 17:31:22  ycreddy
 * Added code to Strip off non digit characters from Cell, Home Phones and Fax
 *
 * Revision 1.27  2005/07/14 21:06:08  daturaarutad
 * add contact_id to btnEditHomeAddress handler
 *
 * Revision 1.26  2005/07/07 23:15:42  vanmer
 * - changed to take a return_rul
 * - changed to redirect to edit address pages based on submit button pressed
 *
 * Revision 1.25  2005/06/30 15:52:44  ycreddy
 * Removing the depracated hook for save
 *
 * Revision 1.24  2005/06/06 18:20:45  braverock
 * - change contact_custom_inline_edit_validate hook to pass $_POST as parameter
 *   to avoid second parameter warning.
 *
 * Revision 1.23  2005/06/06 18:18:22  braverock
 * - change contact_custom_inline_edit_validate hook to use do_hook
 *   and not pass parameters
 *
 * Revision 1.22  2005/06/06 18:16:11  braverock
 * - deprecate contact_accounting_inline_edit_2 hook in favor of contact_edit_2 hook
 *   $accounting_rows wasn't defined in this page, causing parameter warnings
 *   on do_hook_function
 *
 * Revision 1.21  2005/06/06 18:12:26  braverock
 * - change inline hook to use do_hook and not pass parameters
 *   @todo this hook should be deprecated by the new contact_edit_2 hook
 *
 * Revision 1.20  2005/06/05 13:07:38  braverock
 * - added 'standardized' hooks to pass record data to plugins
 *
 * Revision 1.19  2005/05/31 16:47:34  ycreddy
 * Added plugin hooks for validate and save. Validate hook is used to make sure validation checks are satisified before doing a save
 *
 * Revision 1.18  2005/05/16 21:30:22  vanmer
 * - added tax_id handling to contacts pages
 *
 * Revision 1.17  2005/05/02 13:51:51  braverock
 * - add support for home address
 *
 * Revision 1.16  2005/04/26 17:28:03  gpowers
 * - added Extension ("x") to contact work phone
 * - removed non-digits from phone numbers in edit-2's, new-2's
 * - updated work phone display to include Extension
 *
 * Revision 1.15  2005/03/18 21:10:33  gpowers
 * - removed (commented) debug code
 *
 * Revision 1.14  2005/03/18 20:53:32  gpowers
 * - added hooks for inline info plugin
 *
 * Revision 1.13  2005/02/10 21:16:49  maulani
 * - Add audit trail entries
 *
 * Revision 1.12  2005/01/13 18:42:30  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.11  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.10  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.9  2004/06/15 14:30:20  gpowers
 * - correct time formats
 *
 * Revision 1.8  2004/02/24 19:59:39  braverock
 * - fixed salutation sql to not insert zero
 *
 * Revision 1.7  2004/02/21 00:17:33  maulani
 * If no salutation chosen, leave field blank
 *
 * Revision 1.6  2004/01/26 19:13:33  braverock
 * - added company division fields
 * - added phpdoc
 *
 */
?>
