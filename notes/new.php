<?php
/**
 * Create a note
 *
 * $Id: new.php,v 1.4 2004/04/17 16:04:30 maulani Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
require_once($include_directory . 'lang/' . $_SESSION['language'] . '.php');

$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$return_url = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$con->close();

$page_title = "Attach Note";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=new-2.php method=post>
        <input type="hidden" name="on_what_table" value="<?php echo $on_what_table; ?>">
        <input type="hidden" name="on_what_id" value="<?php echo $on_what_id; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Attach Note</td>
            </tr>
            <tr>
                <td class=widget_label>Note Body</td>
            </tr>
            <tr>
                <td class=widget_content><textarea rows=5 cols=80 name=note_description></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=submit value="Save Changes"></td>
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
 * $Log: new.php,v $
 * Revision 1.4  2004/04/17 16:04:30  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/16 22:22:26  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.2  2004/04/08 16:59:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

