<?php
/**
 * Add an address
 *
 * $Id: add-address.php,v 1.11 2004/08/09 19:28:07 neildogg Exp $
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
                   'address_body'       => array('companies_address_body',arr_vars_SESSION),
                   'use_pretty_address' => array('companies_use_pretty_address',arr_vars_SESSION),
		   );

// get all POST'ed variables, null out any not sent over
arr_vars_get_all ( $arr_vars, true );

$address_name = (strlen($address_name) > 0) ? $address_name : '[address]';
$use_pretty_address = ($use_pretty_address == 'on') ? "t" : "f";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
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

$con->close();

header("Location: addresses.php?msg=address_added&company_id=$company_id");

/**
 * $Log: add-address.php,v $
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
 *
 *
 */
?>
