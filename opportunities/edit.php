<?php
/**
 * This file allows the editing of opportunities
 *
 * $Id: edit.php,v 1.11 2004/06/14 17:41:36 introspectshun Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$opportunity_id = $_GET['opportunity_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

update_recent_items($con, $session_user_id, "opportunities", $opportunity_id);

$sql = "select o.*, c.company_id, c.company_name
from opportunities o, companies c
where o.company_id = c.company_id
and opportunity_id = $opportunity_id";

$rst = $con->execute($sql);

if ($rst) {
    $company_id = $rst->fields['company_id'];
    $company_name = $rst->fields['company_name'];
    $contact_id = $rst->fields['contact_id'];
    $campaign_id = $rst->fields['campaign_id'];
    $opportunity_status_id = $rst->fields['opportunity_status_id'];
    $user_id = $rst->fields['user_id'];
    $opportunity_title = $rst->fields['opportunity_title'];
    $opportunity_description = $rst->fields['opportunity_description'];
    $size = $rst->fields['size'];
    $probability = $rst->fields['probability'];
    $close_at = $con->userdate($rst->fields['close_at']);
    $rst->close();
}


// associated with
/*

$sql = "select category_id, category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select ccsm.category_id from category_category_scope_map ccsm, category_scopes cs where ccsm.category_scope_id = cs.category_scope_id and cs.on_what_table = 'opportunities')
and category_id in (select category_id from entity_category_map where on_what_table = 'opportunities' and on_what_id = $opportunity_id)
order by category_pretty_name";

*/

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


// not associated with
/*

$sql = "select category_id, category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select ccsm.category_id from category_category_scope_map ccsm, category_scopes cs where ccsm.category_scope_id = cs.category_scope_id and cs.on_what_table = 'opportunities')
and category_id not in (select category_id from entity_category_map where on_what_table = 'opportunities' and on_what_id = $opportunity_id)
order by category_pretty_name";

*/

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm
where cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'opportunities'
and c.category_id not in (" . implode(',', $array_of_categories) . ")
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);

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

//users menu
$sql = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql);
$user_menu = $rst->getmenu2('user_id', $user_id, false);
$rst->close();

//campaign menu
$sql2 = "select campaign_title, campaign_id from campaigns where campaign_record_status = 'a' order by campaign_title";
$rst = $con->execute($sql2);
$campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
$rst->close();

//opportunity status menu
$sql2 = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status = 'a' order by sort_order";
$rst = $con->execute($sql2);
$opportunity_status_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, false);
$rst->close();

$con->close();

$page_title = "Opportunity : $opportunity_title";
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=opportunity_id value=<?php  echo $opportunity_id; ?>>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=on_what_table value=<?php echo "opportunities"; ?>>

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Opportunity Details</td>
            </tr>
            <tr>
                <td class=widget_label_right>Opportunity&nbsp;Title</td>
                <td class=widget_content_form_element><input type=text size=40 name=opportunity_title value="<?php  echo $opportunity_title; ?>"> <?php  echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content_form_element><a href="<?php  echo $http_site_root; ?>/companies/one.php?company_id=<?php  echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Contact</td>
                <td class=widget_content_form_element><?php  echo $contact_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Campaign</td>
                <td class=widget_content_form_element><?php  echo $campaign_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Status</td>
                <td class=widget_content_form_element><?php  echo $opportunity_status_menu; ?>
                &nbsp;<a href='opportunity-view.php' target=new>View Statuses</a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Owner</td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Size&nbsp;(in&nbsp;dollars)</td>
                <td class=widget_content_form_element><input type=text size=10 name=size value="<?php  echo $size; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>Probability&nbsp;(%)</td>
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
                <td class=widget_label_right>Close&nbsp;Date</td>

                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=close_at value="<?php  echo $close_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right_166px>Description</td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=opportunity_description><?php  echo htmlspecialchars($opportunity_description); ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"> <input type=button class=button onclick="javascript: location.href='delete.php?opportunity_id=<?php  echo $opportunity_id; ?>';" value='Delete Opportunity' onclick="javascript: return confirm('Delete Opportunity?');"></td>
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
 * $Log: edit.php,v $
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

