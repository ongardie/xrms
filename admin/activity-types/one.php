<?php
/**
 * /admin/account-types/one.php
 *
 * Edit account-types
 *
 * $Id: one.php,v 1.12 2006/01/02 21:30:02 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_type_id = $_GET['activity_type_id'];

$con = get_xrms_dbconnection();

$sql = "select * from activity_types where activity_type_id = $activity_type_id";

$rst = $con->execute($sql);

if ($rst) {
    
    $activity_type_short_name = $rst->fields['activity_type_short_name'];
    $activity_type_pretty_name = $rst->fields['activity_type_pretty_name'];
    $activity_type_pretty_plural = $rst->fields['activity_type_pretty_plural'];
    $activity_type_display_html = $rst->fields['activity_type_display_html'];
    $user_editable_flag = $rst->fields['user_editable_flag'];
    $activity_type_score_adjustment = $rst->fields['activity_type_score_adjustment'];
    
    $rst->close();
}

getGlobalVar($msg, 'msg');

$page_title = _("Activity Type Details").': '.$activity_type_pretty_name;
start_page($page_title, true, $msg);
echo '<div id="Main">';
require_once('participant_positions_sidebar.php');
?>
        
    <div id="Content">

<?php if ($user_editable_flag) { ?>
        <form action="edit-2.php" method=post>
<?php } ?>                
        <input type=hidden name=activity_type_id value="<?php  echo $activity_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Activity Type Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=activity_type_short_name value="<?php  echo $activity_type_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=activity_type_pretty_name value="<?php  echo $activity_type_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text name=activity_type_pretty_plural value="<?php  echo $activity_type_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=activity_type_display_html value="<?php  echo $activity_type_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Score Adjustment"); ?></td>
                <td class=widget_content_form_element><input type=text size=5 name=activity_type_score_adjustment value="<?php  echo $activity_type_score_adjustment; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                <?php if ($user_editable_flag) { ?>
                    <input class=button type=submit value="<?php echo _("Save Changes"); ?>">
                <? } ?>
                    <input type=button class=button value="<?php echo _("Cancel"); ?>" onclick="javascript: location.href='some.php'">
                </td>
            </tr>
        </table>
        </form>
<?php if ($user_editable_flag) { ?>
        <form action="delete.php" method=post onsubmit="javascript: return confirm('<?php echo _("Delete Activity Type?"); ?>');">
        <input type=hidden name=activity_type_id value="<?php  echo $activity_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Activity Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
	          <?php echo _("Click the button below to permanently remove this item."); ?>
                <p>
		    <?php echo _("Note: This action CANNOT be undone!"); ?>
                </p>
                <p><input class=button type=submit value="<?php echo _("Delete"); ?>">
                </p>
                </td>
            </tr>
        </table>
        </form>
<?php } ?>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php
$con->close();
end_page();

/**
 * $Log: one.php,v $
 * Revision 1.12  2006/01/02 21:30:02  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.11  2005/06/16 23:54:59  vanmer
 * - changed to only allow edit button and output to appear if user_editable_flag is set to 1
 *
 * Revision 1.10  2005/04/15 07:42:43  vanmer
 * - added a new sidebar for managing positions for contacts on activities
 *
 * Revision 1.9  2004/07/25 17:28:19  johnfawcett
 * - reinserted ? in gettext - needed for some languages
 * - standardized delete text and button
 *
 * Revision 1.8  2004/07/25 15:05:00  johnfawcett
 * - unified page title
 * - removed punctuation from gettext string
 *
 * Revision 1.7  2004/07/19 21:30:16  introspectshun
 * - Added space to correct display of page title
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:53  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:06:33  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/03/24 18:12:45  maulani
 * - add phpdoc
 *
 */
?>
