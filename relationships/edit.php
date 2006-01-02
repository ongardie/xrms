<?php
/**
 * Delete Company - Verify Delete
 *
 * Submit from companies-sidebar to verify deletion.
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

$working_direction = $_GET['working_direction'];
$on_what_table = $_GET['on_what_table'];

if (!$on_what_table) { echo "ERROR viewing relationship: table not specified."; return false; }


$on_what_table_singular = make_singular($on_what_table);
$relationship_id = $_GET['relationship_id'];
$return_url = $_GET['return_url'];

$con = get_xrms_dbconnection();

$page_title = _("Edit Association");
start_page($page_title, true, $msg);

$rst = $con->execute("select * from relationships where relationship_id='$relationship_id'");
if (!$rst) db_error_handler($con, $sql);

$name_to_get = $con->Concat(implode(", ' ' , ", table_name($on_what_table)));
$sql = "SELECT " . $name_to_get . " as name
        FROM " . $on_what_table . "
        WHERE " . $on_what_table_singular . "_id=" . $rst->fields[$working_direction . '_what_id'];
$rst2 = $con->execute($sql);
if (!$rst2) db_error_handler($con, $sql);

$name = $rst2->fields['name'];
$rst2->close();
$rst->close();
?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type="hidden" name="return_url" value="<?php echo $return_url; ?>">
        <input type="hidden" name="relationship_id" value="<?php echo $relationship_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Name"); ?></td>
            </tr>
                <td class=widget_content_form_element>
                    <?php echo $name; ?>
               </td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <input class=button type=submit name=unassociate value="<?php echo _("Unassociate"); ?>">
                </td>
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
 * $Log: edit.php,v $
 * Revision 1.6  2006/01/02 23:31:01  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.5  2005/02/11 21:18:27  vanmer
 * - added error handling on failed queries
 * - added error handling if table not specified properly
 *
 * Revision 1.4  2004/07/25 13:18:48  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.3  2004/07/25 13:13:04  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.2  2004/07/18 18:10:22  braverock
 * - convert all strings for i18n/translation
 *   - applies i18n patch contributed by John Fawcett
 *
 * Revision 1.1  2004/07/09 15:33:42  neildogg
 * New, generic programs that utilize the new relationships table
 *
 * Revision 1.2  2004/07/05 22:13:27  introspectshun
 * - Include adodb-params.php
 *
 * Revision 1.1  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 */
?>