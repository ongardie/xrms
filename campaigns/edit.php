<?php
/**
 * This file allows the editing of campaigns
 *
 * $Id: edit.php,v 1.20 2011/01/20 22:21:05 gopherit Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$campaign_id = (int)$_GET['campaign_id'];
$session_user_id = session_check('','Update');
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$campaign_title = (array_key_exists('campaign_title',$_GET) ? $_GET['campaign_title'] : $_POST['campaign_title']);
$campaign_type_id = (array_key_exists('campaign_type_id',$_GET) ? $_GET['campaign_type_id'] : $_POST['campaign_type_id']);

$con = get_xrms_dbconnection();

update_recent_items($con, $session_user_id, "campaigns", $campaign_id);

$sql = "SELECT *
        FROM campaigns
        WHERE campaign_id = $campaign_id";
$rst = $con->execute($sql);

if ($rst) {
    $campaign_status_id = $rst->fields['campaign_status_id'];
    if (!$campaign_type_id) $campaign_type_id = $rst->fields['campaign_type_id'];
    $user_id = $rst->fields['user_id'];
    if (!$campaign_title) $campaign_title = $rst->fields['campaign_title'];
    $campaign_description = $rst->fields['campaign_description'];
    $starts_at = $con->userdate($rst->fields['starts_at']);
    $ends_at = $con->userdate($rst->fields['ends_at']);
    $cost = $rst->fields['cost'];
    $rst->close();
}

$user_menu = get_user_menu($con, $user_id);

$sql = "SELECT campaign_type_pretty_name, campaign_type_id
        FROM campaign_types
        WHERE campaign_type_record_status = 'a'
        ORDER BY campaign_type_pretty_name";
$rst = $con->execute($sql);
if($rst) {
    // defining campaign_type_id before the call to getmenu2 means that this
    // option will be selected when the menu is generated.
    if (!$campaign_type_id) {
        if (!$rst->EOF ) {
            $campaign_type_id = $rst->fields['campaign_type_id'];
            $campaign_type_pretty_name = $rst->fields['campaign_type_pretty_name'];
        } else {
            echo 'There have been no campaign types defined - please define them first
                <a href="../admin/campaign-types/some.php">here</a>.';
            exit;
        }
    }

    $campaign_type_menu = $rst->getmenu2('campaign_type_id', $campaign_type_id, false, false, 1, 'id="campaign_type_id" onchange="javascript:restrictBycampaignType();"');
    $rst->close();
} else {
    db_error_handler($con, $sql);
}

// Get the campaign status menu
$sql2 = "SELECT campaign_status_pretty_name, campaign_status_id
         FROM campaign_statuses
         WHERE campaign_type_id=$campaign_type_id
         AND campaign_status_record_status = 'a'
         ORDER BY sort_order, campaign_status_id";
$rst = $con->execute($sql2);
//if you dont have a campaign status set, you wont be able to enter a record.
if ( $rst AND $rst->RecordCount() == 0 ) {
	echo _('There are no campaign statuses defined for the campaign type') .' "'. $campaign_type_pretty_name .'".  '.
             _('Please define those first') .' <a href="../admin/campaign-statuses/some.php?campaign_type_id='. $campaign_type_id .'">'. _('here') .'</a>.';
	exit;
} elseif ( !$rst->EOF ) {
    $campaign_status_id = $rst->fields['campaign_status_id'];
} else {
    $campaign_status_id = 0;
}

$campaign_status_menu = $rst->getmenu2('campaign_status_id', $campaign_status_id, false);

$con->close();

$page_title = _("Edit Campaign") .': '. $campaign_title;
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>
<?php confGoTo_includes(); ?>

<script type="text/javascript" language="JavaScript">
    <!--
        function restrictBycampaignType() {
            campaign_title=document.getElementById('campaign_title');
            campaign_id=document.getElementById('campaign_id');
            select=document.getElementById('campaign_type_id');
            location.href = 'edit.php?campaign_id=<?php echo $campaign_id; ?>&campaign_title='+ encodeURIComponent(campaign_title.value) + '&campaign_type_id=' + select.value;
        }
     //-->
</script>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=campaign_id value=<?php  echo $campaign_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Campaign Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Campaign Title"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name="campaign_title" id="campaign_title" value="<?php  echo $campaign_title ?>"> <?php  echo $required_indicator ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $campaign_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Status"); ?></td>
                <td class=widget_content_form_element><?php  echo $campaign_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Starts On"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=starts_at value="<?php  echo $starts_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: pointer" border=0 title="<?php echo _('Starts On'); ?>" alt="<?php echo _('Starts On'); ?>" src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Ends On"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=ends_at value="<?php  echo $ends_at; ?>">
                    <img ID="f_trigger_d" style="CURSOR: pointer" border=0 title="<?php echo _('Ends On'); ?>" alt="<?php echo _('Ends On'); ?>" src="../img/cal.gif">
                </td>
           </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Cost"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=cost value="<?php  echo $cost; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=campaign_description><?php  echo $campaign_description; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>">
<?php
		$quest = _("Delete Campaign?");
		$button = _("Delete");
                $to_url = 'delete.php?campaign_id='.$campaign_id;
                acl_confGoTo($quest,$button,$to_url,'campaigns',$campaign_id,'Delete');
?>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<script language="JavaScript" type="text/javascript">

function initialize() {
    document.forms[0].campaign_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].campaign_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo addslashes(_("You must enter a campaign title.")); ?>';
    }

    if (numberOfErrors > 0) {
        alert(msgToDisplay);
        return false;
    } else {
        return true;
    }

}

initialize();

    Calendar.setup({
        inputField     :    "f_date_c",      // id of the input field
        ifFormat       :    "%Y-%m-%d",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "f_trigger_c",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });

    Calendar.setup({
        inputField     :    "f_date_d",      // id of the input field
        ifFormat       :    "%Y-%m-%d",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "f_trigger_d",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });

</script>

<?php

end_page();

/**
 * $Log: edit.php,v $
 * Revision 1.20  2011/01/20 22:21:05  gopherit
 * Modified so the opportunity SQL query does not overwrite passed-in parameters.
 * The javascript restrictByCampaignType() was incorrectly calling new.php instead of edit.php and was missing the campaign_id parameter.
 *
 * Revision 1.19  2011/01/20 16:46:53  gopherit
 * Added the restrictByCampaignType javascript so that only campaign statuses of the selected campaign type are displayed.
 *
 * Revision 1.18  2007/02/20 16:40:30  jnhayart
 * prevent broken javascript variable after localisation
 *
 * Revision 1.17  2006/01/02 22:41:51  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.16  2005/06/01 16:03:46  vanmer
 * - changed delete campaign button to use confGoTo again, using ACL control
 *
 * Revision 1.15  2005/05/04 14:35:25  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.14  2005/03/21 13:40:53  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.13  2005/03/15 22:17:24  vanmer
 * - changed to use render_delete_button infrastructure for showing/hiding buttons based on ACL
 *
 * Revision 1.12  2005/01/13 18:00:10  vanmer
 * - Basic ACL changes to allow edit functionality to be restricted
 *
 * Revision 1.11  2004/07/30 09:55:34  cpsource
 * - Add confGoTo sub-system
 *   Make msg defined to '' if not passed in
 *
 * Revision 1.10  2004/07/25 19:19:38  johnfawcett
 * - reinserted ? in gettext string - needed for some languages
 * - standardized delete button
 *
 * Revision 1.9  2004/07/25 15:23:36  johnfawcett
 * - corrected page title
 * - removed punctuation from gettext string
 *
 * Revision 1.8  2004/07/16 05:28:14  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.7  2004/06/12 03:27:32  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.6  2004/06/04 17:44:05  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.5  2004/04/17 16:02:40  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.4  2004/04/16 22:20:55  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/08 16:58:23  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
