<?php
/**
 * Database updates for Edit address for a contact
 *
 * $Id: edit-address-2.php,v 1.9 2004/08/25 14:18:27 neildogg Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

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
		   'use_pretty_address' => array ( 'use_pretty_address' , arr_vars_SESSION ),
		   'new' => array ( 'new' , arr_vars_SESSION ),
		   'alt_address' => array ( 'alt_address' , arr_vars_SESSION ),

		   );

// get all passed in variables
arr_vars_get_all ( $arr_vars , true );

$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($alt_address) {
    $sql = "SELECT * FROM contacts WHERE contact_id = $contact_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_id'] = $alt_address;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

    add_audit_item($con, $session_user_id, 'changed address', 'contacts', $contact_id, 1);
} elseif ($address_id && !$new) {
    $sql = "SELECT * FROM addresses WHERE address_id = $address_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['country_id'] = $country_id;
    $rec['line1'] = $line1;
    $rec['line2'] = $line2;
    $rec['city'] = $city;
    $rec['province'] = $province;
    $rec['postal_code'] = $postal_code;
    $rec['address_name'] = $address_name;
    $rec['address_body'] = $address_body;
    $rec['use_pretty_address'] = $use_pretty_address;

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);

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
    $rec['address_body'] = $address_body;
    $rec['use_pretty_address'] = $use_pretty_address;

    $tbl = 'addresses';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);

    $address_id = $con->insert_id();
    add_audit_item($con, $session_user_id, 'created', 'addresses', $address_id, 1);

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
            $rec['offset'] = $time_zone_offset['offset'];

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

$con->close();


header("Location: edit-address.php?msg=saved&contact_id=$contact_id");


/**
 * $Log: edit-address-2.php,v $
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
 * - added $Id: edit-address-2.php,v 1.9 2004/08/25 14:18:27 neildogg Exp $Log: tags.
 *
 * Revision 1.1  2004/06/09 16:52:14  gpowers
 * - Contact Address Editing
 * - adapted from companies/edit-address.php
 *
 */

?>
