<?php
/**
 * This file allows the creation of cases
 *
 * $Id: new.php,v 1.20 2006/01/02 22:47:25 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check('','Create');

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$company_id = (array_key_exists('company_id',$_GET) ? $_GET['company_id'] : $_POST['company_id']);
$division_id = (array_key_exists('division_id',$_GET) ? $_GET['division_id'] : $_POST['division_id']);
$contact_id = (array_key_exists('contact_id',$_GET) ? $_GET['contact_id'] : $_POST['contact_id']);
$case_type_id = (array_key_exists('case_type_id',$_GET) ? $_GET['case_type_id'] : $_POST['case_type_id']);
$case_title = (array_key_exists('case_title',$_GET) ? $_GET['case_title'] : $_POST['case_title']);

$con = get_xrms_dbconnection();

$company_name = fetch_company_name($con, $company_id);

//get menu for contacts
$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, contact_id FROM contacts WHERE company_id = $company_id AND contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false, false, 1, 'id=contact_id');
$rst->close();

$user_menu = get_user_menu($con, $session_user_id);

//division menu
$sql2 = "select division_name, division_id from company_division where company_id=$company_id order by division_name";
$rst = $con->execute($sql2);
$division_menu = $rst->getmenu2('division_id', $division_id, true, false, 1, 'id=division_id');
$rst->close();

//get case priority menu
$sql2 = "select case_priority_pretty_name, case_priority_id from case_priorities where case_priority_record_status = 'a' order by case_priority_id";
$rst = $con->execute($sql2);

// defining case_priority_id before the call to getmenu2 means that this
// option will be selected when the menu is generated.
if ( $rst && !$rst->EOF ) {
  $case_priority_id = $rst->fields['case_priority_id'];
} else {
  $case_priority_id = 0;
}

$case_priority_menu = $rst->getmenu2('case_priority_id', $case_priority_id, false);
$rst->close();

//get case name menu
$sql2 = "select case_type_pretty_name, case_type_id from case_types where case_type_record_status = 'a' order by case_type_id";
$rst = $con->execute($sql2);

// defining case_type_id before the call to getmenu2 means that this
// option will be selected when the menu is generated.
if (!$case_type_id) {
    if ( $rst && !$rst->EOF ) {
    $case_type_id = $rst->fields['case_type_id'];
    } else {
    $case_type_id = 0;
    }
}

$case_type_menu = $rst->getmenu2('case_type_id', $case_type_id, false, false, 1, "id=case_type_id onchange=javascript:restrictByCaseType();");
$rst->close();

//get case status menu
$sql2 = "select case_status_pretty_name, case_status_id from case_statuses where case_type_id=$case_type_id AND case_status_record_status = 'a' order by sort_order, case_status_id";
$rst = $con->execute($sql2);
//added because if you dont have a case status set you wont be able to enter a record.
if ($rst->RecordCount()==0){echo "There are no case statuses set for this case type - please set case status first <a href='../admin/case-statuses/some.php>here</a>.";exit;}


// defining case_status_id before the call to getmenu2 means that this
// option will be selected when the menu is generated.
if ( $rst && !$rst->EOF ) {
  $case_status_id = $rst->fields['case_status_id'];
} else {
  $case_status_id = 0;
}

$case_status_menu = $rst->getmenu2('case_status_id', $case_status_id, false);

$rst->close();
$con->close();

$page_title = _("New Case");
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>
    
    <script language=JavaScript>
    <!--
        function restrictByCaseType() {
            case_title=document.getElementById('case_title');
            division=document.getElementById('division_id');
            contact=document.getElementById('contact_id');
            select=document.getElementById('case_type_id');
            location.href = 'new.php?company_id=<?php echo $company_id; ?>&case_title='+ case_title.value +'&division_id='+division.value + '&contact_id=' + contact.value + '&case_type_id=' + select.value;
        }
     //-->
    </script>


<div id="Main">
    <div id="Content">

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Case Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Case Title"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=case_title id=case_title value="<?php echo $case_title ?>"> <?php  echo $required_indicator ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division"); ?></td>
                <td class=widget_content_form_element><?php  echo $division_menu; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Contact"); ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $case_type_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Status"); ?></td>
                <td class=widget_content_form_element>
                    <?php  echo $case_status_menu ?>
                    <a href="#" onclick="javascript:window.open('case-status-view.php');"><?php echo _("Status Definitions"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Priority"); ?></td>
                <td class=widget_content_form_element><?php  echo $case_priority_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Due By"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=due_at value="<?php  echo date('Y-m-d H:i:s'); ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=case_description></textarea></td>
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
    document.forms[0].case_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].case_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter a case title."); ?>"); ?>';
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
        inputField     :    "f_date_d",      // id of the input field
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
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
 * Revision 1.20  2006/01/02 22:47:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.19  2005/05/04 14:34:55  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.18  2005/03/21 13:40:54  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.17  2005/03/01 18:51:39  niclowe
 * Added error trap where there are no case statuses set for a particular case type
 *
 * Revision 1.16  2005/01/11 23:10:32  braverock
 * - removed bad javascript window.open hack, now set empty anchor on current page
 *
 * Revision 1.15  2005/01/10 21:53:19  vanmer
 * - added redirect on case_type change, will re-fill fields above the type
 * - added javascript id to getmenu2 calls, to allow for above feature to operate properly
 *
 * Revision 1.14  2005/01/07 02:00:01  braverock
 * - add link to case status pop-up
 *
 * Revision 1.13  2005/01/06 20:53:38  vanmer
 * - added retrieve/display of division_id to edit and new pages
 *
 * Revision 1.12  2004/08/13 13:35:54  maulani
 * - Fix bug 1008689
 *  - Correct errant sql sort order.
 *  - Correct errant variable set.  case_priority_id, case_type_id, and case_status_id
 *    are all integers.  They were erroneously set to blank strings.
 *
 * Revision 1.11  2004/07/30 10:18:00  cpsource
 * - Fix (yet again) three (more) bugs that were masked by undefined
 *   variables:
 *     case_priority_id
 *     case_type_id
 *     case_status_id
 *   whereby getmenu2 was not selecting them as the default properly.
 *
 * Revision 1.10  2004/07/25 14:35:47  johnfawcett
 * - corrected gettext call
 *
 * Revision 1.9  2004/07/16 07:11:17  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.8  2004/06/12 04:08:06  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.7  2004/06/04 17:37:35  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
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
