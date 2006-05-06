<?php
/**
 * This file allows the editing of opportunities
 *
 * $Id: edit.php,v 1.27 2006/05/06 09:34:27 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$opportunity_id = isset($_GET['opportunity_id']) ? $_GET['opportunity_id'] : '';
$on_what_id=$opportunity_id;
$session_user_id = session_check('','Update');

$msg            = isset($_GET['msg'])  ? $_GET['msg'] : '';

$division_id = (array_key_exists('division_id',$_GET) ? $_GET['division_id'] : '' );
$contact_id = (array_key_exists('contact_id',$_GET) ? $_GET['contact_id'] : '' );
$opportunity_type_id = (array_key_exists('opportunity_type_id',$_GET) ? $_GET['opportunity_type_id'] : '' );
$opportunity_title = (array_key_exists('opportunity_title',$_GET) ? $_GET['opportunity_title'] : '' );

getGlobalVar($return_url, 'return_url');
if (!$return_url) { $return_url="/opportunities/one.php?opportunity_id=$opportunity_id"; }
$con = get_xrms_dbconnection();
// $con->debug = 1;

update_recent_items($con, $session_user_id, "opportunities", $opportunity_id);

$sql = "select o.*, c.company_id, c.company_name
from opportunities o, companies c
where o.company_id = c.company_id
and opportunity_id = $opportunity_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $division_id = $rst->fields['division_id'];
    $company_name = $rst->fields['company_name'];
    $contact_id = $rst->fields['contact_id'];
    $campaign_id = $rst->fields['campaign_id'];
    $opportunity_status_id = $rst->fields['opportunity_status_id'];
    if (!$opportunity_type_id) $opportunity_type_id = $rst->fields['opportunity_type_id'];
    $user_id = $rst->fields['user_id'];
    $opportunity_title = $rst->fields['opportunity_title'];
    $opportunity_description = $rst->fields['opportunity_description'];
    $size = $rst->fields['size'];
    $probability = $rst->fields['probability'];
    $close_at = $con->userdate($rst->fields['close_at']);
    $rst->close();
}

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'opportunities'
and ecm.on_what_id = $opportunity_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'opportunities'
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);
$array_of_categories = array();
array_push($array_of_categories, 0);

if ($rst) {
    while (!$rst->EOF) {
        $associated_with .= "<a href='remove-category.php?opportunity_id=$opportunity_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        array_push($array_of_categories, $rst->fields['category_id']);
        $rst->movenext();
    }
    $rst->close();
}


$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm
where cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'opportunities'
and c.category_id not in (" . implode(',', $array_of_categories) . ")
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);

$not_associated_with = '';

if ($rst) {
    while (!$rst->EOF) {
        $not_associated_with .= "<a href='add-category.php?opportunity_id=$opportunity_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        $rst->movenext();
    }
    $rst->close();
}

//contact menu
$sql = "
SELECT " . $con->Concat("first_names", "' '", "last_name") . " AS contact_name, contact_id
FROM contacts
WHERE company_id = '" . $company_id ."'
  AND contact_record_status = 'a'
";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false);
$rst->close();

$user_menu = get_user_menu($con, $user_id);

//campaign menu
$sql2 = "select campaign_title, campaign_id from campaigns where campaign_record_status = 'a' order by campaign_title";
$rst = $con->execute($sql2);
$campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
$rst->close();

//division menu
$sql2 = "select division_name, division_id from company_division where company_id=$company_id order by division_name";
$rst = $con->execute($sql2);
$division_menu = $rst->getmenu2('division_id', $division_id, true);
$rst->close();

//opportunity type list
$sql2 = "select opportunity_type_pretty_name, opportunity_type_id from opportunity_types where opportunity_type_record_status = 'a' order by opportunity_type_id";
$rst = $con->execute($sql2);
$opportunity_type_menu = $rst->getmenu2('opportunity_type_id', $opportunity_type_id, false, false, 1, "id=opportunity_type_id onchange=javascript:restrictByOpportunityType();");
$rst->close();

//opportunity status menu
$sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_type_id=$opportunity_type_id AND opportunity_status_record_status = 'a' order by sort_order";
$rst = $con->execute($sql2);
$opportunity_status_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, false);
$rst->close();

$con->close();

$page_title = _("Opportunity") . " : " . $opportunity_title;
start_page($page_title, true, $msg);

// include confgoto.js
confGoTo_includes();

?>

<?php jscalendar_includes(); ?>

    <script language=JavaScript>
    <!--
        function restrictByOpportunityType() {
            opportunity_title=document.getElementById('opportunity_title');
            division=document.getElementById('division_id');
            contact=document.getElementById('contact_id');
            select=document.getElementById('opportunity_type_id');
            location.href = 'new.php?company_id=<?php echo $company_id; ?>&opportunity_title='+ opportunity_title.value +'&division_id='+division.value + '&contact_id=' + contact.value + '&opportunity_type_id=' + select.value;
        }
     //-->
    </script>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=opportunity_id value=<?php  echo $opportunity_id; ?>>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=on_what_table value=<?php echo "opportunities"; ?>>
        <input type=hidden name=return_url value="<?php echo $return_url; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Opportunity Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Opportunity Title"); ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=opportunity_title value="<?php  echo $opportunity_title; ?>"> <?php  echo $required_indicator; ?></td>
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
                <td class=widget_label_right><?php echo _("Campaign"); ?></td>
                <td class=widget_content_form_element><?php  echo $campaign_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_type_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Status"); ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_status_menu; ?>
                &nbsp;
                <a href="#" onclick="javascript:window.open('opportunity-view.php?opportunity_type_id=<?php echo $opportunity_type_id; ?>');"><?php echo _("Status Definitions"); ?></a>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Size (in dollars)"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=size value="<?php  echo $size; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Probability (%)"); ?></td>
                <td class=widget_content_form_element>
                <select name=probability>
                    <option value="0"<?php if ($probability == '0') {echo ' selected';}; ?>>0%
                    <option value="10"<?php if ($probability == '10') {echo ' selected';}; ?>>10%
                    <option value="20"<?php if ($probability == '20') {echo ' selected';}; ?>>20%
                    <option value="30"<?php if ($probability == '30') {echo ' selected';}; ?>>30%
                    <option value="40"<?php if ($probability == '40') {echo ' selected';}; ?>>40%
                    <option value="50"<?php if ($probability == '50') {echo ' selected';}; ?>>50%
                    <option value="60"<?php if ($probability == '60') {echo ' selected';}; ?>>60%
                    <option value="70"<?php if ($probability == '70') {echo ' selected';}; ?>>70%
                    <option value="80"<?php if ($probability == '80') {echo ' selected';}; ?>>80%
                    <option value="90"<?php if ($probability == '90') {echo ' selected';}; ?>>90%
                    <option value="100"<?php if ($probability == '100') {echo ' selected';}; ?>>100%
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Close Date"); ?></td>

                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=close_at value="<?php  echo $close_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=opportunity_description><?php  echo htmlspecialchars($opportunity_description); ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                <input class=button type=submit value="<?php echo _("Save Changes"); ?>">
<?php
        acl_confGoTo (
              _('Delete Opportunity?'),                      // question to ask operator
              _('Delete'),                                   // display this on button
              'delete.php?opportunity_id='.$opportunity_id,  // do this if operator approves
              'opportunities',               // what table will be affected (for ACL)
              $opportunity_id,               // which entity (for ACL)
              'Delete'                   // what action will be taken (for ACL)
              );
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
    document.forms[0].opportunity_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].opportunity_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo _("You must enter an opportunity title."); ?>';
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
 * $Log: edit.php,v $
 * Revision 1.27  2006/05/06 09:34:27  vanmer
 * - added return_url to opportunities edit page
 *
 * Revision 1.26  2006/04/29 01:49:20  vanmer
 * - restrict opportunities statuses to only statuses associated with current opportunity type
 * - added closed_by and closed_at fields and output to opportunities/one page
 *
 * Revision 1.25  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.24  2005/07/06 22:50:32  braverock
 * - add opportunity types
 *
 * Revision 1.23  2005/06/01 16:20:31  vanmer
 * - altered delete button to be controlled by ACL
 *
 * Revision 1.22  2005/05/04 14:37:24  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.21  2005/03/21 13:40:56  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.20  2005/01/13 19:08:56  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.19  2005/01/11 23:15:20  braverock
 * - removed bad javascript window.open hack - now set empty anchor on current page
 *
 * Revision 1.18  2005/01/06 20:50:06  vanmer
 * - added retrieve/display of division_id to edit and new pages
 *
 * Revision 1.17  2004/12/30 21:57:08  braverock
 * - localize strings
 * - remove obsolete lines
 *
 * Revision 1.16  2004/07/30 09:45:29  cpsource
 * - Place confGoTo setup later in startup sequence.
 *
 * Revision 1.15  2004/07/29 09:39:48  cpsource
 * - Seperate .js from .php for confGoTo for PHP V4 problems.
 *
 * Revision 1.14  2004/07/28 19:39:57  cpsource
 * - Add confGoTo for Delete confirm question
 *   Fix some undefined variable usages
 *
 * Revision 1.13  2004/07/25 20:28:05  johnfawcett
 * - standardized delete button
 *
 * Revision 1.12  2004/07/20 19:38:31  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.11  2004/06/14 17:41:36  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, Concat and Date functions.
 *
 * Revision 1.10  2004/06/05 16:24:13  braverock
 * - reverse probability sort order to 0->100
 *
 * Revision 1.9  2004/06/04 17:39:44  gpowers
 * Applied Patch [ 965012 ] Calendar replacement By: miguel Gonçves - mig77
 * w/minor changes: changed includes to function, used complete php tags
 *
 * Revision 1.8  2004/06/03 16:16:18  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.7  2004/04/17 15:59:58  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.6  2004/04/16 22:22:41  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.5  2004/04/08 17:13:06  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

