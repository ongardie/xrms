<?php
/**
 * Database updates for Edit address for a contact
 *
 * $Id: edit-address-2.php,v 1.17 2006/06/23 22:02:24 ongardie Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-addresses.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

getGlobalVar($return_url, 'return_url');

// declare passed in variables
$arr_vars = array ( // local var name             // session variable name, flag

		   'address_id' => array ( 'address_id' , arr_vars_SESSION ),
		   'company_id' => array ( 'company_id' , arr_vars_SESSION ),
		   'contact_id' => array ( 'contact_id' , arr_vars_SESSION ),
		   'country_id' => array ( 'country_id' , arr_vars_SESSION ),
		   'address_name' => array ( 'address_name' , arr_vars_SESSION ),
		   'address_body' => array ( 'address_body' , arr_vars_SESSION ),
		   'line1' => array ( 'line1' , arr_vars_SESSION ),
		   'line2' => array ( 'line2' , arr_vars_SESSION ),
		   'city' => array ( 'city' , arr_vars_SESSION ),
		   'province' => array ( 'province' , arr_vars_SESSION ),
		   'postal_code' => array ( 'postal_code' , arr_vars_SESSION ),
		   'address_type' => array ( 'address_type' , arr_vars_SESSION ),
		   'use_pretty_address' => array ( 'use_pretty_address' , arr_vars_SESSION ),
		   'new' => array ( 'new' , arr_vars_SESSION ),
		   'alt_address' => array ( 'alt_address' , arr_vars_SESSION ),
		   'home_address' => array ( 'home_address' , arr_vars_SESSION ),

		   );

// get all passed in variables
arr_vars_get_all ( $arr_vars , true );

$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

$con = get_xrms_dbconnection();
// $con->debug = 1;

if ($alt_address) {
    $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_id'] = $alt_address;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());

    $con->execute($upd);

    add_audit_item($con, $session_user_id, 'changed address', 'contacts', $contact_id, 1);
} elseif ($home_address) {
    $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['home_address_id'] = $home_address;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    add_audit_item($con, $session_user_id, 'changed home address', 'contacts', $contact_id, 1);
} elseif ($address_id && !$new) {
    $rec = array();
    $rec['country_id'] = $country_id;
    $rec['line1'] = $line1;
    $rec['line2'] = $line2;
    $rec['city'] = $city;
    $rec['province'] = $province;
    $rec['postal_code'] = $postal_code;
    $rec['address_type'] = $address_type;
    $rec['address_name'] = $address_name;
    $rec['address_body'] = $address_body;
    $rec['use_pretty_address'] = $use_pretty_address;

    update_address($con, $rec, false, get_magic_quotes_gpc());

    add_audit_item($con, $session_user_id, 'updated', 'addresses', $address_id, 1);

} else {

    //save to database
    $rec = array();
    $rec['company_id'] = $company_id;
    $rec['country_id'] = $country_id;
    $rec['address_name'] = $address_name;
    $rec['line1'] = $line1;
    $rec['line2'] = $line2;
    $rec['city'] = $city;
    $rec['province'] = $province;
    $rec['postal_code'] = $postal_code;
    $rec['address_type'] = $address_type;
    $rec['address_body'] = $address_body;
    $rec['use_pretty_address'] = $use_pretty_address;

    $tbl = 'addresses';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $address_id = add_address($con, $rec, get_magic_quotes_gpc());
    if ($address_id) {
        add_audit_item($con, $session_user_id, 'created', 'addresses', $address_id, 1);
    } else {
        $msg=urlencode(_("Creating Address Failed"));
    }

    if($time_zone_offset = time_zone_offset($con, $address_id)) {
        $sql = 'SELECT *
                FROM addresses
                WHERE address_id=' . $address_id;
        $rst = $con->execute($sql);
        if(!$rst) {
            db_error_handler($con, $sql);
        }
        elseif(!$rst->EOF) {
            $rec = array();
            $rec['daylight_savings_id'] = $time_zone_offset['daylight_savings_id'];
            $rec['gmt_offset'] = $time_zone_offset['offset'];

            $upd = $con->getUpdateSQL($rst, $rec, true, get_magic_quotes_gpc());
            $rst = $con->execute($upd);
            if(!$rst) {
                db_error_handler($con, $sql);
            }
        }
    }

    $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_id'] = $address_id;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    add_audit_item($con, $session_user_id, 'changed address', 'contacts', $contact_id, 1);
}

$param = array($_POST, $rst, $rec);
do_hook_function('contact_edit_address_2', $param);

$con->close();

if (!$return_url) {
    $return_url="edit-address.php?msg=saved&contact_id=$contact_id";
}
header("Location: $return_url");


/**
 * $Log: edit-address-2.php,v $
 * Revision 1.17  2006/06/23 22:02:24  ongardie
 * Needed the require of utils-addresses for add_address() to work.
 *
 * Revision 1.16  2006/04/21 22:16:51  braverock
 * - changed to use centralized API functions add_address() and update_address();
 *
 * Revision 1.15  2006/01/02 22:59:59  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.14  2005/12/18 02:57:20  vanmer
 * - changed to use gmt_offset instead of offset field
 * - Thanks to kennyholden for this patch
 *
 * Revision 1.13  2005/09/29 15:01:40  vanmer
 * - added code to allow change of home address for contact
 *
 * Revision 1.12  2005/07/06 02:07:29  vanmer
 * - changed to handle arbitrary return_url
 *
 * Revision 1.11  2005/06/15 17:12:30  ycreddy
 * Added a plugin hook contact_edit_address_2
 *
 * Revision 1.10  2005/04/11 02:08:44  maulani
 * - Add address types.  RFE 862049 (maulani)
 *
 * Revision 1.9  2004/08/25 14:18:27  neildogg
 * - Daylight savings now applied to all new addresses
 *
 * Revision 1.8  2004/07/22 11:21:13  cpsource
 * - All paths now relative to include-locations-location.inc
 *   Code cleanup for Create Contact for 'Self'
 *
 * Revision 1.7  2004/07/19 21:11:39  cpsource
 * - Use arr_vars for getting POST'ed data.
 *
 * Revision 1.6  2004/07/08 17:33:58  gpowers
 * - corrected quoting on ? "t" : "f";
 *
 * Revision 1.5  2004/07/07 21:59:47  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.4  2004/06/15 17:26:21  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL and Concat functions.
 *
 * Revision 1.3  2004/06/10 18:07:28  gpowers
 * - added processing for "Use Alternate Address" section
 *
 * Revision 1.2  2004/06/09 17:36:09  gpowers
 * - added $Id: edit-address-2.php,v 1.17 2006/06/23 22:02:24 ongardie Exp $Log: tags.
 *
 * Revision 1.1  2004/06/09 16:52:14  gpowers
 * - Contact Address Editing
 * - adapted from companies/edit-address.php
 *
 */

?>
