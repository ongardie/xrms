<?php
/**
 * View Campaign Details
 *
 * $Id: one.php,v 1.16 2005/01/11 13:25:46 braverock Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$campaign_id = $_GET['campaign_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "campaigns", $campaign_id);

$sql = "select cam.campaign_id,
cam.campaign_type_id, cam.campaign_status_id, cam.user_id, cam.campaign_title,
cam.campaign_description, cam.starts_at, cam.ends_at,
cam.cost, cam.entered_at, cam.entered_by, cam.last_modified_at, cam.last_modified_by, cam.campaign_record_status,
camt.campaign_type_display_html, cams.campaign_status_display_html, u1.username as entered_by_username, u2.username as last_modified_by_username, u3.username as campaign_owner_username
from campaigns cam, campaign_types camt, campaign_statuses cams, users u1, users u2, users u3
where cam.campaign_type_id = camt.campaign_type_id
and cam.campaign_status_id = cams.campaign_status_id
and cam.entered_by = u1.user_id
and cam.last_modified_by = u2.user_id
and cam.user_id = u3.user_id
and cam.campaign_id = '$campaign_id'";

$rst = $con->execute($sql);

if ($rst) {
    $campaign_title = $rst->fields['campaign_title'];
    $campaign_description = $rst->fields['campaign_description'];
    $campaign_type_display_html = $rst->fields['campaign_type_display_html'];
    $campaign_status_display_html = $rst->fields['campaign_status_display_html'];
    $starts_at = $con->userdate($rst->fields['starts_at']);
    $ends_at = $con->userdate($rst->fields['ends_at']);
    $cost = $rst->fields['cost'];
    $campaign_owner_username = $rst->fields['campaign_owner_username'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $last_modified_at = $con->userdate($rst->fields['last_modified_at']);
    $entered_by = $rst->fields['entered_by_username'];
    $last_modified_by = $rst->fields['last_modified_by_username'];
    $rst->close();
}

/*********************************/
/*** Include the sidebar boxes ***/

//set up our substitution variables for use in the siddebars
$on_what_table = 'campaigns';
$on_what_id = $campaign_id;

// include the categories sidebar
require_once($include_directory . 'categories-sidebar.php');

// include the notes sidebar
require_once($include_locations_location . 'notes/sidebar.php');

//include the files sidebar
require_once($include_locations_location . 'files/sidebar.php');

$con->close();

$page_title = _("Campaign Details") .': '. $campaign_title;
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Campaign Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>

                    <table border=0 cellpadding=0 cellspacing=0 width=100%>
                        <tr>
                            <td width=50% class=clear align=left valign=top>
                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                <tr>
                                    <td width=1% class=sublabel><?php echo _("Title"); ?></td>
                                    <td class=clear><?php echo $campaign_title; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Type"); ?></td>
                                    <td class=clear><?php echo $campaign_type_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Status"); ?></td>
                                    <td class=clear><?php echo $campaign_status_display_html; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Cost"); ?></td>
                                    <td class=clear><?php echo number_format($cost, 2); ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Starts"); ?></td>
                                    <td class=clear><?php echo $starts_at; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Ends"); ?></td>
                                    <td class=clear><?php echo $ends_at; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel>&nbsp;</td>
                                    <td class=clear>&nbsp;</td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Created"); ?></td>
                                    <td class=clear><?php echo $entered_at; ?> by <?php  echo $entered_by; ?></td>
                                </tr>
                                <tr>
                                    <td class=sublabel><?php echo _("Last Modified"); ?></td>
                                    <td class=clear><?php echo $last_modified_at; ?> by <?php  echo $last_modified_by; ?></td>
                                </tr>
                                </table>
                            </td>

                            <td width=50% class=clear align=left valign=top>

                                <table border=0 cellpadding=0 cellspacing=0 width=100%>
                                    </table>

                            </td>
                        </tr>
                    </table>

                    <p><?php  echo $campaign_description; ?>

                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input class=button type=button value="<?php echo _("Edit"); ?>" onclick="javascript: location.href='edit.php?campaign_id=<?php  echo $campaign_id; ?>';"></td>
            </tr>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- categories //-->
        <?php echo $category_rows; ?>

        <!-- notes //-->
            <?php echo $note_rows; ?>

        <!-- files //-->
        <?php echo $file_rows; ?>

        <!-- sidebar plugins //-->
        <?php echo $sidebar_rows; ?>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.16  2005/01/11 13:25:46  braverock
 * - removed on_what_string hack, changed to use standard make_singular function
 *
 * Revision 1.15  2004/10/22 20:48:43  introspectshun
 * - Added include-locations-location
 * - Now uses sidebars, including new category sidebar
 *
 * Revision 1.14  2004/07/30 10:30:44  cpsource
 * - Make sure msg can be optionally used.
 *
 * Revision 1.13  2004/07/25 20:51:34  braverock
 * - added semicolons to some echo statements that were missing them
 *
 * Revision 1.12  2004/07/25 15:26:31  johnfawcett
 * - unified page title
 * - removed punctuation from gettext string
 *
 * Revision 1.11  2004/07/19 17:19:52  cpsource
 * - Resolved undefs
 *
 * Revision 1.10  2004/07/16 05:28:14  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.9  2004/06/12 03:27:32  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.8  2004/04/17 16:02:40  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.7  2004/04/16 22:20:55  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.6  2004/04/08 16:58:23  maulani
 * - Update javascript declaration
 * - Add phpdoc
 */
?>