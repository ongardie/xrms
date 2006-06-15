<?php
/**
 * Insert a new contact into the database
 *
 * $Id: new-2.php,v 1.36 2006/06/15 21:32:59 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-contacts.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('','Create');

// declare passed in variables
$arr_vars = array ( // local var name             // session variable name, flag
           'company_id' => array ( 'company_id' , arr_vars_SESSION ),
           'address_id' => array ( 'address_id' , arr_vars_SESSION ),
           'division_id' => array ( 'division_id' , arr_vars_SESSION ),
           'salutation' => array ( 'salutation' , arr_vars_SESSION ),
           'last_name' => array ( 'last_name' , arr_vars_SESSION ),
           'first_names' => array ( 'first_names' , arr_vars_SESSION ),
           'gender' => array ( 'gender' , arr_vars_SESSION ),
           'date_of_birth' => array ( 'date_of_birth' , arr_vars_SESSION ),
           'tax_id' => array ( 'tax_id' , arr_vars_SESSION ),
           'summary' => array ( 'summary' , arr_vars_SESSION ),
           'title' => array ( 'title' , arr_vars_SESSION ),
           'description' => array ( 'description' , arr_vars_SESSION ),
           'email' => array ( 'email' , arr_vars_SESSION ),
           'email2' => array ( 'email2' , arr_vars_SESSION ),
           'work_phone' => array ( 'work_phone' , arr_vars_SESSION ),
           'work_phone_ext' => array ( 'work_phone_ext' , arr_vars_SESSION ),
           'cell_phone' => array ( 'cell_phone' , arr_vars_SESSION ),
           'home_phone' => array ( 'home_phone' , arr_vars_SESSION ),
           'fax' => array ( 'fax' , arr_vars_SESSION ),
	   'user_id' => array ('user_id' , arr_vars_SESSION ),
           'interests' => array ( 'interests' , arr_vars_SESSION ),
           'profile' => array ( 'profile' , arr_vars_SESSION ),
           'edit_address' => array ( 'edit_address' , arr_vars_SESSION ),
           'return_url' => array ( 'return_url' , arr_vars_SESSION ),
           );

// get all posted in variables
arr_vars_get_all ( $arr_vars , true);

$con = get_xrms_dbconnection();

// uncomment the following line to turn on debugging
//$con->debug=1;

getGlobalVar($home_address_id, 'home_address_id');
if (!$home_address_id) {

    getGlobalVar($address_name, 'address_name');
    getGlobalVar($address_country_id, 'address_country_id');
    getGlobalVar($line1, 'line1');
    getGlobalVar($line2, 'line2');
    getGlobalVar($city, 'city');
    getGlobalVar($province, 'province');
    getGlobalVar($postal_code, 'postal_code');
    getGlobalVar($address_type, 'address_type');
    getGlobalVar($address_body, 'address_body');
    getGlobalVar($use_pretty_address, 'use_pretty_address');

    if (!$city AND $_POST['city']) $city=$_POST['city'];

    if ($line1 AND $city AND $address_country_id) {
        $use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

        $rec = array();
        $rec['country_id'] = $address_country_id;
//Do not set company ID for a contact's home address
//            $rec['company_id']=$company_id;
        $rec['line1'] = $line1;
        $rec['line2'] = $line2;
        $rec['city'] = $city;
        $rec['province'] = $province;
        $rec['postal_code'] = $postal_code;
        $rec['address_type'] = $address_type;
        $rec['address_name'] = $address_name;
        $rec['address_body'] = $address_body;
        $rec['use_pretty_address'] = $use_pretty_address;
        $tbl = 'addresses';
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        $rst=$con->execute($ins);

        if (!$rst) { db_error_handler( $con, $ins); }
        $home_address_id = $con->insert_id();
        if ($home_address_id!=0) {
            add_audit_item($con, $session_user_id, 'created', 'addresses', $home_address_id, 1);
        } else $home_address_id=1;
    } else $home_address=1;
}
else $home_address_id=1;

$last_name = (strlen($last_name) > 0) ? $last_name : "[last name]";
$first_names = (strlen($first_names) > 0) ? $first_names : "[first names]";
// If salutation is 0, make sure you replace it with an empty string
if(!$salutation) {
    $salutation = "";
}


//save to database
$rec = array();
$rec['company_id'] = $company_id;
$rec['address_id'] = $address_id;
$rec['division_id'] = $division_id;
$rec['last_name'] = $last_name;
$rec['first_names'] = $first_names;
$rec['summary'] = $summary;
$rec['title'] = $title;
$rec['description'] = $description;
$rec['email'] = $email;
$rec['work_phone'] = $work_phone;
$rec['work_phone_ext'] = $work_phone_ext;
$rec['cell_phone'] = $cell_phone;
$rec['home_phone'] = $home_phone;
$rec['fax'] = $fax;
$rec['user_id'] = $user_id;
/*
ignore IM fields, now done through plugin
$rec['aol_name'] = $aol_name;
$rec['yahoo_name'] = $yahoo_name;
$rec['msn_name'] = $msn_name;
*/
$rec['interests'] = $interests;
$rec['salutation'] = $salutation;
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
$rec['entered_by'] = $session_user_id;
$rec['entered_at'] = time();
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;
$rec['home_address_id']=$home_address_id;

$contact_data=add_contact($con, $rec, get_magic_quotes_gpc());

if ($contact_data) {
    if (is_array($contact_data)) { 
        $contact_id=$contact_data['contact_id']; 
//        $new_contact_record=$contact_data; 
    } else { 
        $contact_id=$contact_data;
//        $new_contact_record=get_contact($con, $contact_id);
    }
} else {
    $msg = urlencode(_("Failed to add contact"));
    if ($company_id) {
        $return_url="../companies/one.php?msg=$msg&company_id=$company_id";
    } else {
        $return_url="some.php?msg=$msg";
    }
}

$con->close();
if ($edit_address == "on") {
    header("Location: edit-address.php?msg=contact_added&contact_id=$contact_id");
} else {
    if (!$return_url) {
        if ($company_id) {
            $return_url="../companies/one.php?msg=contact_added&company_id=$company_id";
        } else {
            $return_url="one.php?msg=contact_added&contact_id=$contact_id";
        }
    }
    $return_url=str_replace('XXX-contact_id-XXX',$contact_id, $return_url);
    header("Location: $return_url");
}

/**
 * $Log: new-2.php,v $
 * Revision 1.36  2006/06/15 21:32:59  vanmer
 * - added owner to the UI for a contact
 *
 * Revision 1.35  2006/04/26 02:13:55  vanmer
 * - removed deprecated use_self_contacts option, now uses system preference controlling behavior
 *
 * Revision 1.34  2006/04/05 00:48:52  vanmer
 * - pass magic quotes value into contacts API
 *
 * Revision 1.33  2006/01/03 21:21:05  vanmer
 * - changed to take either an array or an integer back from add_contact, to reflect API changes
 *
 * Revision 1.32  2006/01/02 22:59:59  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.31  2005/11/18 20:35:07  vanmer
 * - changed to use contact API for adding new contact
 *
 * Revision 1.30  2005/09/25 05:42:06  vanmer
 * - removed IM field references from all contact pages (now handled by plugin)
 * - added custom field hook for contacts new.php
 *
 * Revision 1.29  2005/09/06 17:29:56  ycreddy
 * Added code to Strip off non digit characters from Cell, Home Phones and Fax
 *
 * Revision 1.28  2005/08/24 11:25:32  braverock
 * - check for successful insert and add db_error_handler to provide feedback
 * - add commented debug line for use in debugging
 *
 * Revision 1.27  2005/08/24 11:14:35  braverock
 * - avoid nulls on the custom and IM fields
 *
 * Revision 1.26  2005/08/04 21:03:38  vanmer
 * - uses return_url, if provided
 * - added replacement in return_url to allow new contact_id to be inserted, if XXX-contact_id-XXX is provided in the
 * return_url
 *
 * Revision 1.25  2005/07/27 23:11:28  vanmer
 * - changed to simply add new address for the contact, instead of searching company for existing addresses with this
 * name
 * - removed company_id from address, since addresses for contacts are no longer linked to the company
 *
 * Revision 1.24  2005/06/07 21:39:34  braverock
 * - remove EOF whitespace
 *
 * Revision 1.23  2005/06/05 13:07:38  braverock
 * - added 'standardized' hooks to pass record data to plugins
 *
 * Revision 1.22  2005/05/16 21:30:22  vanmer
 * - added tax_id handling to contacts pages
 *
 * Revision 1.21  2005/05/10 16:28:16  braverock
 * - add new contact to recently viewed list
 *   resolves SF bug 1119511 reported by Beth Maknick (maulani)
 *
 * Revision 1.20  2005/05/07 00:16:43  vanmer
 * - added check for duplicate address names for a company, uses specified address name if match is found
 *
 * Revision 1.19  2005/05/07 00:09:56  vanmer
 * - added handling for adding a new home address for a contact when adding a new contact
 * - added default home address to address 1 (unknown)
 *
 * Revision 1.18  2005/04/26 17:28:03  gpowers
 * - added Extension ("x") to contact work phone
 * - removed non-digits from phone numbers in edit-2's, new-2's
 * - updated work phone display to include Extension
 *
 * Revision 1.17  2005/03/29 18:23:54  maulani
 * - Add audit log entry for contact creation
 *
 * Revision 1.16  2005/01/13 18:42:30  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.15  2004/10/18 03:32:26  gpowers
 * - added "edit address" option
 *
 * Revision 1.14  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.13  2004/07/19 20:56:20  cpsource
 * - Use arr_vars for POSTED arguments
 *
 * Revision 1.12  2004/07/08 19:47:50  neildogg
 * - Salutation was inserting 0 on a null salutation choice
 *
 * Revision 1.11  2004/07/07 21:59:47  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.10  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.9  2004/06/15 14:29:00  gpowers
 * - correct time formats
 *
 * Revision 1.8  2004/01/26 19:13:34  braverock
 * - added company division fields
 * - added phpdoc
 *
 */
?>
