<?php
/**
 * Edit a note
 *
 * $Id: edit.php,v 1.5 2004/04/17 16:04:30 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$note_id = $_GET['note_id'];
$return_url = $_GET['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "select * from notes where note_id = $note_id";

$rst = $con->execute($sql);

if ($rst) {
    $note_description = $rst->fields['note_description'];
    $rst->close();
}

$con->close();

$page_title = "Edit Note";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type="hidden" name="note_id" value="<?php echo $note_id; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Edit Note</td>
            </tr>
            <tr>
                <td class=widget_label>Note Body</td>
            </tr>
            <tr>
                <td class=widget_content><textarea rows=5 cols=80 name=note_description><?php echo $note_description ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <input class=button type=submit value="Save Changes">
                    <input type=button class=button onclick="javascript: location.href='delete.php?return_url=<?php echo $return_url; ?>&note_id=<?php echo $note_id; ?>';" value="Delete">
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
 * Revision 1.5  2004/04/17 16:04:30  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.4  2004/04/16 22:22:26  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/08 16:59:46  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

