<?php
/**
 * This file allows the creation of opportunities
 *
 * $Id: new.php,v 1.3 2004/04/16 22:22:41 maulani Exp $
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

$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false);
$rst->close();

$sql2 = "select campaign_title, campaign_id from campaigns where campaign_record_status = 'a' order by campaign_title";
$rst = $con->execute($sql2);
$campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
$rst->close();

$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

$sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status = 'a' order by opportunity_status_id";
$rst = $con->execute($sql2);
$opportunity_status_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, false);
$rst->close();

$con->close();

$page_title = "New Opportunity";
start_page($page_title, true, $msg);

?>

<script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=55% valign=top>

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
                    <option value="100">100%
                    <option value="90">90%
                    <option value="80">80%
                    <option value="70">70%
                    <option value="60">60%
                    <option value="50">50%
                    <option value="40">40%
                    <option value="30">30%
                    <option value="20">20%
                    <option value="10">10%
                    <option value="0">0%
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>Close Date</td>
                <td class=widget_content_form_element><input type=text size=12 name=close_at value="<?php  echo date('Y-m-d'); ?>">&nbsp;<a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></td>
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

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=43% valign=top>

        </td>
    </tr>
</table>

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

<!--

// create calendar object(s) just after form tag closed
// specify form element as the only parameter (document.forms['formname'].elements['inputname']);
// note: you can have as many calendar objects as you need for your application

    var cal1 = new calendar1(document.forms[0].elements['close_at']);
    cal1.year_scroll = true;
    cal1.time_comp = false;

//-->

</script>

<?php

end_page();

/**
 * $Log: new.php,v $
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
