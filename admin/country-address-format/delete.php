<?php
/**
 * Address Format Edit Screens - Delete an address format String
 *
 * @author Glenn Powers
 *
 * $Id: delete.php,v 1.3 2004/06/16 20:57:25 gpowers Exp $
 */
//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();

require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$msg = $_GET['msg'];
$address_format_string_id = $_GET['address_format_string_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($address_format_string_id) {

    $sql = "SELECT * FROM address_format_strings WHERE address_format_string_id = $address_format_string_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['address_format_string_record_status'] = 'd';

    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $con->execute($upd);
}

$rst = $con->execute($sql);

$return_url = "/admin/country-address-format/index.php";
header("Location: {$http_site_root}/{$return_url}");

/**
 * $Log: delete.php,v $
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
