<?php
/**
 * Address Format Edit Screens - Show Address Formats, and allow them to be sleected for editing
 *
 *  Show Address Formats, and allow them to be sleected for editing
 *
 * @author Glenn Powers
 *
 * $Id: index.php,v 1.9 2006/01/02 21:46:52 vanmer Exp $
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

$country_id = $_GET['country_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$page_title = _("Country Localization Formats");
start_page($page_title, true, $msg);


$sql = "select country_id, country_name, phone_format, countries.address_format_string_id, address_format_strings.address_format_string_id, address_format_string from address_format_strings, countries where address_format_strings.address_format_string_id = countries.address_format_string_id";

$rst = $con->execute($sql);

if ($rst) {
    echo "<table align=center>
<tr>
<td class=widget_header>"._("Country")."</td>
<td class=widget_header>"._("Phone Format")."</td>
<td class=widget_header>"._("Address Format")."</td>
<td class=widget_header></td>
</tr>
";
    while (!$rst->EOF) {
        $country = $rst->fields['country_name'];
        $address_format_string = $rst->fields['address_format_string'];
        $address_format_string_id = $rst->fields['address_format_string_id'];
        $country_id = $rst->fields['country_id'];
        $phone_format = $rst->fields['phone_format'];
        echo "<tr>
<td class=widget_label_right>$country</td>
<td class=widget_content nowrap><form method=post action=edit-2.php>
    <input type=hidden name=country_id value=$country_id>
    <input size=18 maxlength=100 name=phone_format value=\"". htmlspecialchars($phone_format) . "\">
    <input type=submit value="._("Update")." class=button>
</form></td>
<td class=widget_content>". $address_format_string."</td>
<td class=widget_content><a href=edit.php?address_format_string_id=" . $address_format_string_id ."&country_id=" . $country_id . ">"._("Edit")."</a></td>
</tr>";
        $rst->movenext();
    }
    $rst->close();
echo "</table>";
}

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.9  2006/01/02 21:46:52  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.8  2005/05/02 12:39:10  braverock
 * - double quote a couple of errant records and use htmlspecialchars where needed
 *
 * Revision 1.7  2004/07/25 12:33:10  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.6  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:57  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/07/07 20:46:26  neildogg
 * - Added support for phone format editing
 *
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