<?php
/**
 * This file allows the creation of cases
 *
 * $Id: new.php,v 1.6 2004/06/03 16:15:33 braverock Exp $
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

//get menu for contacts
$sql = "select concat(first_names, ' ', last_name) as contact_name, contact_id from contacts where company_id = $company_id and contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false);
$rst->close();

//get username menu
$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $session_user_id, false);
$rst->close();

//get case priority menu
$sql2 = "select case_priority_pretty_name, case_priority_id from case_priorities where case_priority_record_status = 'a' order by case_priority_id";
$rst = $con->execute($sql2);
$case_priority_menu = $rst->getmenu2('case_priority_id', $case_priority_id, false);
$rst->close();

//get case name menu
$sql2 = "select case_type_pretty_name, case_type_id from case_types where case_type_record_status = 'a' order by case_type_id";
$rst = $con->execute($sql2);
$case_type_menu = $rst->getmenu2('case_type_id', $case_type_id, false);
$rst->close();

//get case status menu
$sql2 = "select case_status_pretty_name, case_status_id from case_statuses where case_status_record_status = 'a' order by sort_order";
$rst = $con->execute($sql2);
$case_status_menu = $rst->getmenu2('case_status_id', $case_status_id, false);
$rst->close();

$con->close();

$page_title = "New Case";
start_page($page_title, true, $msg);

?>

<script language="javascript" src="<?php  echo $http_site_root; ?>/js/calendar1.js"></script>

<div id="Main">
    <div id="Content">

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Case Details</td>
            </tr>
            <tr>
                <td class=widget_label_right>Case Title</td>
                <td class=widget_content_form_element><input type=text size=40 name=case_title> <?php  echo $required_indicator ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Contact</td>
                <td class=widget_content_form_element><?php  echo $contact_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Type</td>
                <td class=widget_content_form_element><?php  echo $case_type_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Status</td>
                <td class=widget_content_form_element><?php  echo $case_status_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Priority</td>
                <td class=widget_content_form_element><?php  echo $case_priority_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Owner</td>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Due By</td>
                <td class=widget_content_form_element><input type=text size=12 name=due_at value="<?php echo date('Y-m-d') ?>">&nbsp;<a href="javascript:cal1.popup();"><img class=date_picker border=0 src="../img/cal.gif"></a></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=case_description></textarea></td>
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
    document.forms[0].case_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].case_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\nYou must enter a case title.';
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

    var cal1 = new calendar1(document.forms[0].elements['due_at']);
    cal1.year_scroll = true;
    cal1.time_comp = false;

//-->
</script>

<?php

end_page();

/**
 * $Log: new.php,v $
 * Revision 1.6  2004/06/03 16:15:33  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.5  2004/04/17 16:02:41  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.4  2004/04/16 22:21:59  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.3  2004/04/08 16:59:15  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
