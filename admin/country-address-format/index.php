<?php
/**
 * Address Format Edit Screens - Show Address Formats, and allow them to be sleected for editing
 *
 *  Show Address Formats, and allow them to be sleected for editing
 *
 * @author Glenn Powers
 *
 * $Id: index.php,v 1.3 2004/06/16 20:57:25 gpowers Exp $
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

$country_id = $_GET['country_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$page_title = "Address Country Format";
start_page($page_title, true, $msg);


$sql = "select country_id, country_name, countries.address_format_string_id, address_format_strings.address_format_string_id, address_format_string from address_format_strings, countries where address_format_strings.address_format_string_id = countries.address_format_string_id";

$rst = $con->execute($sql);

if ($rst) {
    echo "<table align=center>
<tr>
<td class=widget_header>Country</td>
<td class=widget_header>Address Format</td>
<td class=widget_header></td>
</tr>
";
    while (!$rst->EOF) {
        $country = $rst->fields['country_name'];
        $address_format_string = $rst->fields['address_format_string'];
        $address_format_string_id = $rst->fields['address_format_string_id'];
        $country_id = $rst->fields['country_id'];
        echo "<tr>
<td class=widget_label_right>$country</td>
<td class=widget_content>$address_format_string</td>
<td class=widget_content><a href=edit.php?address_format_string_id=" . $address_format_string_id ."&country_id=" . $country_id . ">EDIT</a></td>
</tr>";
        $rst->movenext();
    }
    $rst->close();
echo "</table>";
}

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.3  2004/06/16 20:57:25  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.2  2004/06/14 22:12:05  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.1  2004/04/20 22:31:42  braverock
 * - add country address formats
 *   - modified from SF patch 938811 to fix SF bug 925470
 *
 */
?>
