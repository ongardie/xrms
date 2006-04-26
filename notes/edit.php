<?php
/**
 * Edit a note
 *
 * $Id: edit.php,v 1.11 2006/04/26 21:50:09 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

getGlobalVar($note_id, 'note_id');
getGlobalVar($return_url, 'return_url');
if (!$return_url) $return_url='companies/some.php';
$return_url_button = $http_site_root.$return_url;

$con = get_xrms_dbconnection();
// $con->debug = 1;

$sql = "select * from notes where note_id = $note_id";

$rst = $con->execute($sql);

if ($rst) {
    $note_description = $rst->fields['note_description'];
    $rst->close();
}

$con->close();

$page_title = _("Edit Note");
start_page($page_title, true, $msg);

//pull out some strings so gettext will see them
$save = _("Save Changes");
$delete = _("Delete");
$edit = _("Edit Note");
$body = _("Note Body");

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type="hidden" name="note_id" value="<?php echo $note_id; ?>">
        <input type="hidden" name="return_url" value="<?php echo $return_url; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo $edit; ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo $body; ?></td>
            </tr>
            <tr>
                <td class=widget_content><textarea rows=25 cols=80 name=note_description><?php echo $note_description ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
                    <input class=button type=submit value="<?php echo $save; ?>">
		    <input class=button type=button onclick="javascript: location.href='<?php echo $return_url_button; ?>'" value="<?php echo _("Cancel"); ?>">
                    <input type=button class=button onclick="javascript: location.href='delete.php?return_url=<?php echo $return_url; ?>&note_id=<?php echo $note_id; ?>';" value="<?php echo $delete; ?>">
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
 * Revision 1.11  2006/04/26 21:50:09  vanmer
 * - resized textarea to be 25 rows tall
 * - added cancel button to redirect to return_url
 * - changed all _GET and _POST references to getGlobalVar function calls
 *
 * Revision 1.10  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.9  2004/07/25 13:00:13  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.8  2004/07/16 14:59:57  braverock
 * - localize $page_title
 *
 * Revision 1.7  2004/06/21 14:25:00  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 * Revision 1.6  2004/06/12 06:23:27  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.5  2004/04/17 16:04:30  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.4  2004/04/16 22:22:26  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/08 16:59:46  maulani
 * - Update javascript declaration
 * - Add phpdoc
 */
?>