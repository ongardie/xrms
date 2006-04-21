<?php
/**
 * Add an address
 *
 * $Id: add-address.php,v 1.19 2006/04/21 22:09:58 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                   'company_id'         => array('companies_company_id',arr_vars_SESSION),
                   'country_id'         => array('companies_country_id',arr_vars_SESSION),
                   'address_name'       => array('companies_address_name',arr_vars_SESSION),
                   'line1'              => array('companies_line1',arr_vars_SESSION),
                   'line2'              => array('companies_line2',arr_vars_SESSION),
                   'city'               => array('companies_city',arr_vars_SESSION),
                   'province'           => array('companies_province',arr_vars_SESSION),
                   'postal_code'        => array('companies_postal_code',arr_vars_SESSION),
                   'address_type'       => array('companies_address_type',arr_vars_SESSION),
                   'address_body'       => array('companies_address_body',arr_vars_SESSION),
                   'use_pretty_address' => array('companies_use_pretty_address',arr_vars_SESSION),
		   );

// get all POST'ed variables, null out any not sent over
arr_vars_get_all ( $arr_vars, true );

$address_name = (strlen($address_name) > 0) ? $address_name : '[address]';
$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

$con = get_xrms_dbconnection();
// $con->debug = 1;

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

$address_id = add_address($con, $rec, get_magic_quotes_gpc());
if ($address_id) {
    add_audit_item($con, $session_user_id, 'created', 'addresses', $address_id, 1);
} else {
    $msg=urlencode(_("Creating Address Failed"));
    header("Location: addresses.php?msg=$msg&company_id=$company_id");
}

/*
//I think that the API handles this correctly, but until that can be verified, I'm going to leave this code here...
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
*/
$con->close();

header("Location: addresses.php?msg=address_added&company_id=$company_id");

/**
 * $Log: add-address.php,v $
 * Revision 1.19  2006/04/21 22:09:58  braverock
 * - update to handle integer return from add_address()
 *
 * Revision 1.18  2006/04/21 22:04:27  braverock
 * - update to use add_address fn
 *
 * Revision 1.16  2006/04/21 20:26:07  braverock
 * - modify to use addresses API
 *
 * Revision 1.15  2006/04/21 20:12:12  braverock
 * - modify to use addresses API
 *
 * Revision 1.14  2006/01/02 22:56:26  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.13  2005/12/18 02:57:20  vanmer
 * - changed to use gmt_offset instead of offset field
 * - Thanks to kennyholden for this patch
 *
 * Revision 1.12  2005/04/11 02:06:37  maulani
 * - Add address type.  RFE 862049 (maulani)
 *
 * Revision 1.11  2004/08/09 19:28:07  neildogg
 * - Now adds daylight savings information to new
 * company addresses
 *
 * Revision 1.10  2004/07/21 15:27:20  introspectshun
 * - Removed $con->execute($sql). Was replaced with $con->execute($ins) earlier.
 *
 * Revision 1.9  2004/07/19 14:25:09  cpsource
 * - General cleanup
 *   Also noted bug with $sql not defined.
 *
 * Revision 1.8  2004/07/07 21:53:13  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.7  2004/06/16 19:22:36  gpowers
 * - removed double quoting from t/f
 *   - did not work with MySQL
 *
 * Revision 1.6  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.5  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.4  2004/03/26 20:55:59  maulani
 * - Add audit trail to company-related items
 * - Add phpdoc
 */
?>
