<?php
/**
 * This file allows the creation of opportunities
 *
 * $Id: new.php,v 1.7 2004/06/05 16:24:13 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$company_id = $_POST['company_id'];
$contact_id = $_POST['contact_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$company_name = fetch_company_name($con, $company_id);


//generate a contact menu
$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false);
$rst->close();

//get a campaign menu
$sql2 = "select campaign_title, campaign_id from campaigns where campaign_record_status = 'a' order by campaign_title";
$rst = $con->execute($sql2);
$campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
$rst->close();

//get a username menu
$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

//get the opportunity status menu
$sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status = 'a' order by sort_order";
$rst = $con->execute($sql2);
$opportunity_status_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, false);
$rst->close();

$con->close();

$page_title = "New Opportunity";
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Opportunity Details</td>
            </tr>
            <tr>
                <td class=widget_label_right>Opportunity Title</td>
                <td class=widget_content_form_element><input type=text size=40 name=opportunity_title> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Contact</td>
                <td class=widget_content_form_element><?php echo $contact_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Campaign</td>
                <td class=widget_content_form_element><?php echo $campaign_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Status</td>
                <td class=widget_content_form_element><?php echo $opportunity_status_menu; ?>
                <a href="javascript:window.open('opportunity-view.php');">Status Definitions</a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Owner</td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Size (in dollars)</td>
                <td class=widget_content_form_element><input type=text size=10 name=size value = '0'></td>
            </tr>
            <tr>
                <td class=widget_label_right>Probability</td>
                <td class=widget_content_form_element>
                <select name=probability>
                    <option value="0">0%
                    <option value="10">10%
                    <option value="20">20%
                    <option value="30">30%
                    <option value="40">40%
                    <option value="50">50%
                    <option value="60">60%
                    <option value="70">70%
                    <option value="80">80%
                    <option value="90">90%
                    <option value="100">100%
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>Close Date</td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=close_at value="<?php  echo date('Y-m-d'); ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=opportunity_description></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
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
    document.forms[0].opportunity_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].opportunity_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\nYou must enter an opportunity title.';
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

</script>

<?php

end_page();

/**
 * $Log: new.php,v $
 * Revision 1.7  2004/06/05 16:24:13  braverock
 * - reverse probability sort order to 0->100
 *
 * Revision 1.6  2004/06/04 17:41:36  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.5  2004/06/03 16:16:18  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.4  2004/04/17 15:59:59  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/16 22:22:41  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.2  2004/04/08 17:13:06  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
