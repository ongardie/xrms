<?php
/**
 * This file allows the creation of campaigns
 *
 * $Id: new.php,v 1.18 2011/01/18 23:14:54 gopherit Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$on_what_table='campaigns';
$session_user_id = session_check('','Create');
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$campaign_title = (array_key_exists('campaign_title',$_GET) ? $_GET['campaign_title'] : $_POST['campaign_title']);
$campaign_type_id = (array_key_exists('campaign_type_id',$_GET) ? $_GET['campaign_type_id'] : $_POST['campaign_type_id']);

$con = get_xrms_dbconnection();

$user_menu = get_user_menu($con, $session_user_id);

$sql = 'SELECT campaign_type_pretty_name, campaign_type_id
        FROM campaign_types
        WHERE campaign_type_record_status = \'a\'
        ORDER BY campaign_type_pretty_name';
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

$page_title = _("New Campaign");
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<script type="text/javascript" language="JavaScript">
    <!--
        function restrictBycampaignType() {
            campaign_title=document.getElementById('campaign_title');
            select=document.getElementById('campaign_type_id');
            location.href = 'new.php?campaign_title='+ campaign_title.value + '&campaign_type_id=' + select.value;
        }
     //-->
    </script>

<div id="Main">
    <div id="Content">

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
<?php
// company_id is not generated in this script, nor passed in, nor
// is it used by new-2.php, so, it's hereby deleted.
//echo '<input type=hidden name=company_id value="'.$company_id.'">';
?>
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
                <td class=widget_content_form_element><?php  echo $campaign_type_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Status"); ?></td>
                <td class=widget_content_form_element><?php  echo $campaign_status_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Starts On"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=starts_at value="<?php  echo date('Y-m-d'); ?>">
                    <img ID="f_trigger_c" style="CURSOR: pointer" border=0 title="<?php echo _('Starts On'); ?>" alt="<?php echo _('Starts On'); ?>" src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Ends On"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=ends_at value="<?php  echo date('Y-m-d'); ?>">
                    <img ID="f_trigger_d" style="CURSOR: pointer" border=0 title="<?php echo _('Ends On'); ?>" alt="<?php echo _('Ends On'); ?>" src="../img/cal.gif">
                </td>
           </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Cost"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=cost></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=campaign_description></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
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
 * $Log: new.php,v $
 * Revision 1.18  2011/01/18 23:14:54  gopherit
 * Added the restrictByCampaignType javascript so that only campaign statuses of the selected campaign type are displayed.
 *
 * Revision 1.17  2010/12/06 15:54:24  gopherit
 * Minor HTML fixes.
 *
 * Revision 1.16  2007/02/20 16:45:18  jnhayart
 * prevent broken javascript variable after localisation
 *
 * Revision 1.15  2007/01/15 13:06:43  fcrossen
 *  - change to pass correct user_id to get_user_menu function
 *
 * Revision 1.14  2006/01/02 22:41:51  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.13  2005/05/04 14:35:24  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.12  2005/03/21 13:40:53  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.11  2005/01/13 18:09:01  vanmer
 * - Basic ACL changes to allow create functionality to be restricted
 *
 * Revision 1.10  2004/07/30 10:52:47  cpsource
 * - Remove unused company_id from processing.
 *   Cleanup repetative operations by adding a subroutine.
 *
 * Revision 1.9  2004/07/30 10:30:44  cpsource
 * - Make sure msg can be optionally used.
 *
 * Revision 1.8  2004/07/16 05:28:14  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.7  2004/06/12 03:27:32  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.6  2004/06/04 17:45:54  gpowers
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
