<?php
/**
 * This file allows the editing of cases
 *
 * $Id: edit.php,v 1.22 2006/01/02 22:47:25 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$case_id = $_GET['case_id'];
$case_type_id=$_GET['case_type_id'];
$on_what_id=$case_id;

$session_user_id = session_check('','Update');
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';


$con = get_xrms_dbconnection();
// $con->debug = 1;

update_recent_items($con, $session_user_id, "cases", $case_id);

$sql = "select * from cases where case_id = $case_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $division_id = $rst->fields['division_id'];
    $contact_id = $rst->fields['contact_id'];
    $case_status_id = $rst->fields['case_status_id'];
    $case_priority_id = $rst->fields['case_priority_id'];
    
    if (!$case_type_id)  $case_type_id = $rst->fields['case_type_id'];
    
    $user_id = $rst->fields['user_id'];
    $case_title = $rst->fields['case_title'];
    $case_description = $rst->fields['case_description'];
    $due_at = $con->userdate($rst->fields['due_at']);
    $rst->close();
}

$company_name = fetch_company_name($con, $company_id);

// associated with

/*

$sql = "select category_id, category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select ccsm.category_id from category_category_scope_map ccsm, category_scopes cs where ccsm.category_scope_id = cs.category_scope_id and cs.on_what_table = 'cases')
and category_id in (select category_id from entity_category_map where on_what_table = 'cases' and on_what_id = $case_id)
order by category_pretty_name";

*/

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'cases'
and ecm.on_what_id = $case_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'cases'
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);
$array_of_categories = array();
array_push($array_of_categories, 0);

if ($rst) {
    while (!$rst->EOF) {
        $associated_with .= "<a href='remove-category.php?case_id=$case_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        array_push($array_of_categories, $rst->fields['category_id']);
        $rst->movenext();
    }
    $rst->close();
}

// not associated with

/*

$sql = "select category_id, category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select ccsm.category_id from category_category_scope_map ccsm, category_scopes cs where ccsm.category_scope_id = cs.category_scope_id and cs.on_what_table = 'cases')
and category_id not in (select category_id from entity_category_map where on_what_table = 'cases' and on_what_id = $case_id)
order by category_pretty_name";

*/

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm
where cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'cases'
and c.category_id not in (" . implode(',', $array_of_categories) . ")
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);

$not_associated_with = '';

if ($rst) {
    while (!$rst->EOF) {
        $not_associated_with .= "<a href='add-category.php?case_id=$case_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        $rst->movenext();
    }
    $rst->close();
}

$sql = "
SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name,
  contact_id
FROM contacts
WHERE company_id = $company_id
  AND contact_record_status = 'a'
";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false);
$rst->close();

$user_menu = get_user_menu($con, $user_id);

//division menu
$sql2 = "select division_name, division_id from company_division where company_id=$company_id order by division_name";
$rst = $con->execute($sql2);
$division_menu = $rst->getmenu2('division_id', $division_id, true);
$rst->close();

//case priority list
$sql2 = "select case_priority_pretty_name, case_priority_id from case_priorities where case_priority_record_status = 'a' order by case_priority_id";
$rst = $con->execute($sql2);
$case_priority_menu = $rst->getmenu2('case_priority_id', $case_priority_id, false);
$rst->close();

//case type list
$sql2 = "select case_type_pretty_name, case_type_id from case_types where case_type_record_status = 'a' order by case_type_id";
$rst = $con->execute($sql2);
$case_type_menu = $rst->getmenu2('case_type_id', $case_type_id, false, false, 1, "id=case_type_id onchange=javascript:restrictByCaseType();");
$rst->close();

//case status list
$sql2 = "select case_status_pretty_name, case_status_id from case_statuses WHERE case_type_id=$case_type_id AND case_status_record_status = 'a' order by sort_order";
$rst = $con->execute($sql2);
if (!$rst) db_error_handler($con, $sql2);
$case_status_menu = $rst->getmenu2('case_status_id', $case_status_id, false);
$rst->close();

$con->close();

$page_title = _("Edit Case #") . $case_id . ": " . $case_title;
start_page($page_title, true, $msg);

// enable confirm
confGoTo_includes();

?>

<?php jscalendar_includes(); ?>
    
    <script language=JavaScript>
    <!--
        function restrictByCaseType() {
            select=document.getElementById('case_type_id');
            location.href = 'edit.php?case_id=<?php echo $case_id; ?>&case_type_id=' + select.value;
        }
     //-->
    </script>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=case_id value=<?php  echo $case_id; ?>>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=on_what_table value=<?php echo "cases"; ?>>


        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Case Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Case Title"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=case_title value="<?php  echo $case_title; ?>"> <?php  echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content_form_element><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division"); ?></td>
                <td class=widget_content_form_element><?php  echo $division_menu; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Contact"); ?></td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $case_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Status"); ?></td>
                <td class=widget_content_form_element>
                    <?php  echo $case_status_menu; ?>
                    <a href="#" onclick="javascript:window.open('case-status-view.php?case_type_id=<?php  echo $case_type_id; ?>');"><?php echo _("Status Definitions"); ?></a>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Priority"); ?></td>
                <td class=widget_content_form_element><?php  echo $case_priority_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Due By"); ?></td>
                <td class=widget_content_form_element>
            <tr>
                <td class=widget_label_right><?php echo _("Due At"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=due_at value="<?php  echo $due_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=case_description><?php  echo $case_description; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>">
<?php
                $quest  = _('Delete Case?');
                $button = _('Delete');
                $to_url = "delete.php?case_id=$case_id";
                acl_confGoTo( $quest, $button, $to_url, 'cases', $case_id, 'Delete' );
?>
                </td>
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
        msgToDisplay += '\n<?php echo _("You must enter a case title."); ?>';
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
        ifFormat       :    "%Y-%m-%d %H:%M:%S",       // format of the input field
        showsTime      :    true,            // will display a time selector
        button         :    "f_trigger_c",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });

</script>

<?php

end_page();

/**
 * $Log: edit.php,v $
 * Revision 1.22  2006/01/02 22:47:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.21  2005/06/29 17:18:16  maulani
 * - Correctly display case status definitions
 *
 * Revision 1.20  2005/06/01 16:03:06  vanmer
 * - changed delete button for case to be controlled by the ACL
 *
 * Revision 1.19  2005/05/04 14:34:55  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.18  2005/03/21 13:40:54  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.17  2005/01/11 23:10:33  braverock
 * - removed bad javascript window.open hack, now set empty anchor on current page
 *
 * Revision 1.16  2005/01/10 21:51:08  vanmer
 * - moved case_id to above session_check for ACL
 * - added parameter to session_check for ACL
 * - added reload by case type, to restrict statuses
 *
 * Revision 1.15  2005/01/07 01:59:31  braverock
 * - add link to case status pop-up
 *
 * Revision 1.14  2005/01/06 20:53:38  vanmer
 * - added retrieve/display of division_id to edit and new pages
 *
 * Revision 1.13  2004/08/02 12:04:46  cpsource
 * - Per bug 997663, add confirm for delete of cases.
 *
 * Revision 1.12  2004/07/30 11:02:14  cpsource
 * - Optionally define msg
 *   set default no_update flag to false in edit-2.php
 *
 * Revision 1.11  2004/07/25 19:25:45  johnfawcett
 * - standardized delete button
 *
 * Revision 1.10  2004/07/25 15:41:01  johnfawcett
 * - corrected page title
 *
 * Revision 1.9  2004/07/16 07:11:17  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.8  2004/06/12 04:08:06  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.7  2004/06/04 17:38:23  gpowers
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
