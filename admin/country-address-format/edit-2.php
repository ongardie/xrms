<?php
/**
 * Address Format Edit Screens - Insert Edited Format into the Database
 *
 * @author Glenn Powers
 *
 * $Id: edit-2.php,v 1.1 2004/04/20 22:31:40 braverock Exp $
 */
//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$msg = $_GET['msg'];
$address_format_string_id = $_GET['address_format_string_id'];
$country_id = $_GET['country_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if (($country_id) && ($address_format_string_id)) {
    $sql = "update countries set address_format_string_id=$address_format_string_id where country_id=$country_id";
    $rst = $con->execute($sql);
    if ($rst) {
        $rst->close();
    }
}

$return_url = "/admin/country-address-format/index.php";
header("Location: {$http_site_root}/{$return_url}");

/**
 * $Log: edit-2.php,v $
 * Revision 1.1  2004/04/20 22:31:40  braverock
 * - add country address formats
 *   - modified from SF patch 938811 to fix SF bug 925470
 *
 */
?>