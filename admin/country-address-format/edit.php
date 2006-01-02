<?php
/**
 * Address Format Edit Screens - Edit a Single Address Format
 *
 * @author Glenn Powers
 *
 * $Id: edit.php,v 1.8 2006/01/02 21:46:52 vanmer Exp $
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
$country_id = $_GET['country_id'];

$con = get_xrms_dbconnection();
//$con->debug = 1;

$page_title = _("Edit Address Country Format");
start_page($page_title, true, $msg);


$sql = "select country_id, country_name, countries.address_format_string_id, afs.address_format_string_id, address_format_string
        from countries, address_format_strings afs
        where
            countries.address_format_string_id =  afs.address_format_string_id
            and country_id = $country_id limit 1";

$rst = $con->execute($sql);

if ($rst) {
    echo "<p>"._("Country").': ' .$rst->fields['country_name'] . "<br></p>
    <form action=edit-2.php method=get>
    <input type=hidden name=country_id value=$country_id>
    <input type=hidden name=address_format_string_id value=$address_format_string_id>
    " .
        "Format String:<br><hr> " . $rst->fields['address_format_string'] . "<hr><br>" .
    "Select Format:<br><br>";
    $sql2 = "select * from address_format_strings where address_format_string_record_status = 'a'";
    $rst2 = $con->execute($sql2);
    if ($rst2) {
        while (!$rst2->EOF) {
            echo "<input type=radio name=address_format_string_id value="
                . $rst2->fields['address_format_string_id'] . "><br>"
                . $rst2->fields['address_format_string'] . "<br>"
                . "<a href=delete.php?address_format_string_id="
                . $rst2->fields['address_format_string_id'] . ">"._("Delete")."</a><br><hr><br>";
        $rst2->movenext();
        }
    }
    echo "
<input class=button type=submit value="._("Change")."></form><br><br>";

echo "<b> "._("OR")."</b> "._("Enter New format").":
<form action=new.php method=post>
<input type=hidden name=country_id value=$country_id>
<textarea rows=5 cols=40 name=address_format_string></textarea><br>
<input class=button type=submit value="._("New").">
</form>";
    $rst->close();
} else {
echo _("Error. No Data.");
};

end_page();

/**
 * $Log: edit.php,v $
 * Revision 1.8  2006/01/02 21:46:52  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.7  2004/07/25 14:45:57  johnfawcett
 * - removed some punctuation from gettext strings
 *
 * Revision 1.6  2004/07/25 12:30:58  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.5  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.4  2004/07/16 13:51:57  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
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
