<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

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

$page_title = "New Campaign";
start_page($page_title, true, $msg);

?>

<script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=55% valign=top>

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id ?>>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=2>Campaign Details</td>
            </tr>
            <tr>
                <td class=widget_label_right>Campaign Title</td>
                <td class=widget_content_form_element><input type=text size=40 name=campaign_title> <?php  echo $required_indicator ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Type</td>
                <td class=widget_content_form_element><?php  echo $campaign_type_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Status</td>
                <td class=widget_content_form_element><?php  echo $campaign_status_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Owner</td>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Starts On</td>
                <td class=widget_content_form_element><input type=text size=12 name=starts_at value="<?php echo date('Y-m-d') ?>">&nbsp;<a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Ends On</td>
                <td class=widget_content_form_element><input type=text size=12 name=ends_at value="<?php echo date('Y-m-d') ?>">&nbsp;<a href="javascript:cal2.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Cost</td>
                <td class=widget_content_form_element><input type=text size=10 name=cost></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=campaign_description></textarea></td>
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

<script language=javascript>

function initialize() {
    document.forms[0].campaign_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].campaign_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\nYou must enter a campaign title.';
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

    var cal1 = new calendar1(document.forms[0].elements['starts_at']);
    cal1.year_scroll = true;
    cal1.time_comp = false;

    var cal2 = new calendar1(document.forms[0].elements['ends_at']);
    cal2.year_scroll = true;
    cal2.time_comp = false;

//-->
</script>

<?php end_page(); ?>
