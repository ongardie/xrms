<?php
/**
 * Associated Relationships
 *
 * Submit from new-relationship.php to initiate name search (code exceptions as needed).
 *
 * @author Neil Roberts
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$on_what_id = $_POST['on_what_id'];
$working_direction = $_POST['working_direction'];
$return_url = $_POST['return_url'];
$relationship_name = $_POST['relationship_name'];
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT from_what_table, to_what_table
        FROM relationship_types
        WHERE relationship_name='$relationship_name'";
$rst = $con->execute($sql);

if($working_direction == "from") {
    $opposite_direction = "to";
}
else {
    $opposite_direction = "from";
}

$working_table = $rst->fields[$working_direction . '_what_table'];
$what_table = $rst->fields[$opposite_direction . '_what_table'];
$what_table_singular = make_singular($what_table);
$display_name = ucfirst($what_table_singular);

if($working_table == "companies" and $what_table == "contacts") {
    $sql = "SELECT 'Enter other contact' AS name,
            0 AS contact_id
            UNION
            (SELECT " .
            $con->Concat("first_names", "' '", "last_name") . " AS name,
            contact_id
            FROM contacts
            WHERE company_id=" . $on_what_id . "
            AND contact_record_status='a'
            ORDER BY last_name, first_names)";

    $rst = $con->execute($sql);
    
    $contact_menu = $rst->getmenu2('possible_id', ' ', false);

}

$con->close();
$page_title = _("Add " . $display_name);

start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=new-relationship-2.php method=post>
        <input type="hidden" name="relationship_name" value="<?php echo $relationship_name; ?>">
        <input type="hidden" name="on_what_id" value="<?php echo $on_what_id; ?>">
        <input type="hidden" name="working_direction" value="<?php echo $working_direction; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Search for ".$display_name); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name or ID"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <?php
                        if($working_table == "companies") {
                            echo $contact_menu;
                        }
                    ?>
                    <input type=text size=18 maxlength=100 name="search_on"> <?php  echo $required_indicator ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=submit value="<?php echo _("Search"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: new-relationship.php,v $
 * Revision 1.5  2004/07/28 17:59:39  neildogg
 * - Added drop down box if added a contact to a company
 *
 * Revision 1.4  2004/07/25 22:48:30  johnfawcett
 * - updated gettext strings
 *
 * Revision 1.2  2004/07/18 18:10:22  braverock
 * - convert all strings for i18n/translation
 *   - applies i18n patch contributed by John Fawcett
 *
 * Revision 1.1  2004/07/14 14:08:53  neildogg
 * - Add new relationship now in /relationships directory
 *
 * Revision 1.3  2004/07/07 21:19:38  neildogg
 * -Added first/last name search
 *
 * Revision 1.2  2004/07/05 22:13:27  introspectshun
 * - Include adodb-params.php
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 */
?>

