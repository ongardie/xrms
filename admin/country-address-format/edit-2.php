<?php
/**
 * Address Format Edit Screens - Insert Edited Format into the Database
 *
 * @author Glenn Powers
 *
 * $Id: edit-2.php,v 1.7 2006/01/02 21:46:52 vanmer Exp $
 */
//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check( 'Admin' );

$msg = $_GET['msg'];
$address_format_string_id = $_GET['address_format_string_id'];
$country_id = $_REQUEST['country_id'];
$phone_format = $_POST['phone_format'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

if ($country_id) {

    $sql = "SELECT * FROM countries WHERE country_id = $country_id";
    $rst = $con->execute($sql);

    $rec = array();
    if($address_format_string_id) {
        $rec['address_format_string_id'] = $address_format_string_id;
    }
    elseif($phone_format) {
        $rec['phone_format'] = $phone_format;
    }

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $rst = $con->execute($upd);

    if ($rst) {
        $rst->close();
    }
}

$return_url = "/admin/country-address-format/index.php";
header("Location: {$http_site_root}/{$return_url}");

/**
 * $Log: edit-2.php,v $
 * Revision 1.7  2006/01/02 21:46:52  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.6  2004/07/25 12:30:58  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.5  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.4  2004/07/07 20:46:26  neildogg
 * - Added support for phone format editing
 *
 * Revision 1.3  2004/06/16 20:57:25  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.2  2004/06/14 22:12:04  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.1  2004/04/20 22:31:40  braverock
 * - add country address formats
 *   - modified from SF patch 938811 to fix SF bug 925470
 *
 */
?>
