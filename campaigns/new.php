<?php
/**
 * This file allows the creation of campaigns
 *
 * $Id: new.php,v 1.9 2004/07/30 10:30:44 cpsource Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', '', false);
$rst->close();

$sql2 = "select campaign_type_pretty_name, campaign_type_id from campaign_types where campaign_type_record_status = 'a' order by campaign_type_pretty_name";
$rst = $con->execute($sql2);
$campaign_type_menu = $rst->getmenu2('campaign_type_id', '', false);
$rst->close();

$sql2 = "select campaign_status_pretty_name, campaign_status_id from campaign_statuses where campaign_status_record_status = 'a' order by campaign_status_id";
$rst = $con->execute($sql2);
$campaign_status_menu = $rst->getmenu2('campaign_status_id', '', false);
$rst->close();

$con->close();

$page_title = _("New Campaign");
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Campaign Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Campaign Title"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=campaign_title> <?php  echo $required_indicator ?></td>
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
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Ends On"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=ends_at value="<?php  echo date('Y-m-d'); ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
           </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Cost"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=cost></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Description"); ?></td>
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
        msgToDisplay += '\n<?php echo _("You must enter a campaign title."); ?>';
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
