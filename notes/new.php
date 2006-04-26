<?php
/**
 * Create a note
 *
 * $Id: new.php,v 1.9 2006/04/26 21:50:09 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

getGlobalVar($on_what_table,'on_what_table');
getGlobalVar($on_what_id, 'on_what_id');
getGlobalVar($return_url, 'return_url');

$return_url_button = $http_site_root.$return_url;
$con = get_xrms_dbconnection();

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
                <td class=widget_header><?php echo _("Attach Note"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Note Body"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><textarea rows=25 cols=80 name=note_description></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element>
			<input class=button type=submit value="<?php echo _("Save Changes"); ?>">
			<input class=button type=button value="<?php echo _("Cancel"); ?>" onclick="javascript: location.href='<?php echo $return_url_button; ?>'">
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
 * $Log: new.php,v $
 * Revision 1.9  2006/04/26 21:50:09  vanmer
 * - resized textarea to be 25 rows tall
 * - added cancel button to redirect to return_url
 * - changed all _GET and _POST references to getGlobalVar function calls
 *
 * Revision 1.8  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2004/07/25 13:00:13  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.6  2004/06/21 14:25:00  braverock
 * - localized strings for i18n/internationalization/translation support
 *
 * Revision 1.5  2004/06/12 06:23:27  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.4  2004/04/17 16:04:30  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/16 22:22:26  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.2  2004/04/08 16:59:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 */
?>