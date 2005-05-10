<?php
/**
 * Insert a new contact into the database
 *
 * $Id: new-2.php,v 1.21 2005/05/10 16:28:16 braverock Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
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
           'aol_name' => array ( 'aol_name' , arr_vars_SESSION ),
           'yahoo_name' => array ( 'yahoo_name' , arr_vars_SESSION ),
           'msn_name' => array ( 'msn_name' , arr_vars_SESSION ),
           'interests' => array ( 'interests' , arr_vars_SESSION ),
           'profile' => array ( 'profile' , arr_vars_SESSION ),
           'custom1' => array ( 'custom1' , arr_vars_SESSION ),
           'custom2' => array ( 'custom2' , arr_vars_SESSION ),
           'custom3' => array ( 'custom3' , arr_vars_SESSION ),
           'custom4' => array ( 'custom4' , arr_vars_SESSION ),
           'edit_address' => array ( 'edit_address' , arr_vars_SESSION ),
           );

// get all posted in variables
arr_vars_get_all ( $arr_vars , true);


$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

getGlobalVar($home_address_id, 'home_address_id');
if (!$home_address_id) {
    getGlobalVar($address_name, 'address_name');

    if ($address_name) {
        $sql = "SELECT address_id FROM addresses WHERE company_id=$company_id AND address_name=".$con->qstr($address_name, get_magic_quotes_gpc());
        $address_rst=$con->execute($sql);
        if (!$address_rst) { db_error_handler($con, sql); exit; }
        if (!$address_rst->EOF) {
            $home_address_id=$address_rst->fields['address_id'];
        } else {
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

            $use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

            $rec = array();
            $rec['country_id'] = $address_country_id;
            $rec['company_id']=$company_id;
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
        }
    }
    else $home_address_id=1;
}
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
$rec['work_phone'] = preg_replace("/[^\d]/", '', $work_phone);
$rec['work_phone_ext'] = preg_replace("/[^\d]/", '', $work_phone_ext);
$rec['cell_phone'] = $cell_phone;
$rec['home_phone'] = $home_phone;
$rec['fax'] = $fax;
$rec['aol_name'] = $aol_name;
$rec['yahoo_name'] = $yahoo_name;
$rec['msn_name'] = $msn_name;
$rec['interests'] = $interests;
$rec['salutation'] = $salutation;
$rec['gender'] = $gender;
$rec['date_of_birth'] = $date_of_birth;
$rec['profile'] = $profile;
$rec['custom1'] = $custom1;
$rec['custom2'] = $custom2;
$rec['custom3'] = $custom3;
$rec['custom4'] = $custom4;
$rec['entered_by'] = $session_user_id;
$rec['entered_at'] = time();
$rec['last_modified_at'] = time();
$rec['last_modified_by'] = $session_user_id;
$rec['home_address_id']=$home_address_id;

$tbl = 'contacts';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$contact_id = $con->Insert_ID();

add_audit_item($con, $session_user_id, 'created', 'contacts', $contact_id, 1);

//add to recently viewed list
update_recent_items($con, $session_user_id, "contacts", $contact_id);

$con->close();

if ($edit_address == "on") {
    header("Location: edit-address.php?msg=contact_added&contact_id=$contact_id");
    } else {
    header("Location: ../companies/one.php?msg=contact_added&company_id=$company_id");
}

/**
 * $Log: new-2.php,v $
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
