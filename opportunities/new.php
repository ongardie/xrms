<?php
/**
 * This file allows the creation of opportunities
 *
 * $Id: new.php,v 1.28 2011/01/20 18:03:29 gopherit Exp $
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
$campaign_id = (array_key_exists('campaign_id',$_GET) ? $_GET['campaign_id'] : $_POST['campaign_id']);
$opportunity_type_id = (array_key_exists('opportunity_type_id',$_GET) ? $_GET['opportunity_type_id'] : $_POST['opportunity_type_id']);
$opportunity_title = (array_key_exists('opportunity_title',$_GET) ? $_GET['opportunity_title'] : $_POST['opportunity_title']);

$con = get_xrms_dbconnection();

$company_name = fetch_company_name($con, $company_id);

// Generate a contact menu
$sql = "SELECT " . $con->Concat("first_names", "' '", "last_name") .
      " AS contact_name, contact_id
        FROM contacts
        WHERE company_id = $company_id ";
if ($company_id == 1) { // add special filter for unknown company
    $sql .= "AND contact_id = $contact_id ";
}
$sql .= "AND contact_record_status = 'a'";
$rst = $con->execute($sql);
$contact_menu = $rst->getmenu2('contact_id', $contact_id, false, false, 1, 'id=contact_id');
$rst->close();

$user_menu = get_user_menu($con, $session_user_id);

//division menu
$sql2 = "select division_name, division_id from company_division where company_id=$company_id order by division_name";
$rst = $con->execute($sql2);
if($rst) {
    $division_menu = $rst->getmenu2('division_id', $division_id, true, false, 1, 'id=division_id');
    $rst->close();
} else {
    db_error_handler($con, $sql2);
}

// Get the campaign menu
$sql2 = "SELECT campaign_title, campaign_id
         FROM campaigns, campaign_statuses
         WHERE campaign_record_status = 'a'
         AND campaign_statuses.campaign_status_id = campaigns.campaign_status_id
         AND campaign_statuses.status_open_indicator = 'o'
         ORDER BY campaign_title";
$rst = $con->execute($sql2);
if($rst) {
    $campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true, false, 1, 'id=campaign_id');
    $rst->close();
} else {
    db_error_handler ($con, $sql2);
}

// Get opportunity type menu
$sql2 = "SELECT opportunity_type_pretty_name, opportunity_type_id
         FROM opportunity_types
         WHERE opportunity_type_record_status = 'a'
         ORDER BY opportunity_type_id";
$rst = $con->execute($sql2);

// defining opportunity_type_id before the call to getmenu2 means that this
// option will be selected when the menu is generated.
if (!$opportunity_type_id) {
    if ( $rst && !$rst->EOF ) {
        $opportunity_type_id = $rst->fields['opportunity_type_id'];
    } else {
        $opportunity_type_id = 0;
    }
}

$opportunity_type_menu = $rst->getmenu2('opportunity_type_id', $opportunity_type_id, false, false, 1, "id=opportunity_type_id onchange=javascript:restrictByOpportunityType();");
$rst->close();

// Get the opportunity status menu
$sql2 = "SELECT opportunity_status_pretty_name, opportunity_status_id
         FROM opportunity_statuses
         WHERE opportunity_status_record_status = 'a'
         AND opportunity_type_id = $opportunity_type_id
         ORDER BY sort_order";
$rst = $con->execute($sql2);
//if you dont have a case status set, you wont be able to enter a record.
if($rst->RecordCount() == 0) {
    echo 'There are no opportunity statuses set for this opportunity type - please set opportunity status first
        <a href="../admin/opportunity-statuses/some.php?opportunity_type_id=', $opportunity_type_id, '">here</a>.';
    exit;
}


// defining opportunity_status_id before the call to getmenu2 means that this
// option will be selected when the menu is generated.
if ( $rst && !$rst->EOF ) {
  $opportunity_status_id = $rst->fields['opportunity_status_id'];
  $rst->close();
} else {
  $opportunity_status_id = 0;
}

$opportunity_status_menu = $rst->getmenu2('opportunity_status_id', $opportunity_status_id, false);

$datetime_format = set_datetime_format($con, $session_user_id);

// custom fields PlugIns
$customs_fields_rows = do_hook_function('opportunity_inline_edit', $customs_fields_rows);
if ($customs_fields_rows <> "") {

$customs_fields_rows_display = "<tr><td colspan=2 class=widget_header>" . _("Opportunity custom information") ."</td>";
$customs_fields_rows_display .= "</tr><tr>";
$customs_fields_rows_display .= "<!-- customfields plugin -->" . $customs_fields_rows;
$customs_fields_rows_display .= "</tr>";
}

$con->close();

$page_title = _("New Opportunity");
start_page($page_title, true, $msg);

?>

<?php jscalendar_includes(); ?>

<script type="text/javascript" language="JavaScript">
    <!--
        function restrictByOpportunityType() {
            opportunity_title=document.getElementById('opportunity_title');
            division=document.getElementById('division_id');
            contact=document.getElementById('contact_id');
            campaign=document.getElementById('campaign_id');
            select=document.getElementById('opportunity_type_id');
            location.href = 'new.php?company_id=<?php echo $company_id; ?>&opportunity_title='+ encodeURIComponent(opportunity_title.value) +'&division_id='+ division.value +'&contact_id='+ contact.value +'&campaign_id='+ campaign.value +'&opportunity_type_id='+ select.value;
        }
     //-->
</script>

<div id="Main">
    <div id="Content">

        <form action=new-2.php onsubmit="javascript: return validate();" method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Opportunity Details"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Opportunity Title"); ?></td>
                <td class=widget_content_form_element><input type="text" size="40" name="opportunity_title" id="opportunity_title" value="<?php echo $opportunity_title; ?>"> <?php echo $required_indicator; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division"); ?></td>
                <td class=widget_content_form_element><?php  echo $division_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Contact"); ?></td>
                <td class=widget_content_form_element><?php echo $contact_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Campaign"); ?></td>
                <td class=widget_content_form_element><?php echo $campaign_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $opportunity_type_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Status"); ?></td>
                <td class=widget_content_form_element><?php echo $opportunity_status_menu; ?>
                <a href="opportunity-view.php?opportunity_type_id=<?php echo $opportunity_type_id ?>" target="_blank"><?php echo _("Status Definitions"); ?></a>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Size (in dollars)"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=size value = '0'></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Probability"); ?></td>
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
                <td class=widget_label_right><?php echo _("Close Date"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_c" name=close_at value="<?php  echo date('Y-m-d'); ?>">
                    <img ID="f_trigger_c" style="CURSOR: pointer;" border=0 title="<?php echo _('Closing Date'); ?>" alt="<?php echo _('Closing Date'); ?>" src="../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=100 name=opportunity_description></textarea></td>
            </tr>
            <!-- accounting plugin -->
            <?php echo $customs_fields_rows_display; ?>
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
    document.forms[0].opportunity_title.focus();
}

function validate() {

    var numberOfErrors = 0;
    var msgToDisplay = '';

    if (document.forms[0].opportunity_title.value == '') {
        numberOfErrors ++;
        msgToDisplay += '\n<?php echo addslashes(_("You must enter an opportunity title.")); ?>';
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
 * Revision 1.28  2011/01/20 18:03:29  gopherit
 * Added encodeURIComponent() in the restrictByCampaignType() function to prevent strings with special characters from breaking the URI.
 * Added campaign_id to the list of parameters passed to the restrictByCampaignType() function.
 *
 * Revision 1.27  2011/01/18 20:02:43  gopherit
 * FIXED: Bug Artifact #3161033 	/opportunities/new.php now only displays the statuses of the selected opportunity type.
 *
 * Revision 1.26  2010/12/07 22:21:43  gopherit
 * Replaced aopportunity_type_id with opportunity_type_id
 *
 * Revision 1.25  2010/10/12 22:06:52  gopherit
 * Minor HTML fixes.
 *
 * Revision 1.24  2007/01/09 02:39:17  ongardie
 * - If there's no opportunity status available for this opportunity type, point user to administration.
 * - Improved similar functionality for cases also by passing acase_type_id, aopportunity_type_id in GET requests.
 *
 * Revision 1.23  2006/12/07 13:54:52  jnhayart
 * Limit campaign choice to "only" open campaign
 *
 * Revision 1.22  2006/12/05 11:33:10  jnhayart
 * Add correct localisation of java string
 *
 * Revision 1.21  2006/11/14 20:12:03  braverock
 * - special handling for unknown company
 * � based on patches by fcrossen
 *
 * Revision 1.20  2006/11/13 16:05:35  braverock
 * - change the way the opportunity type and opportunity status dropdowns are selected
 *   roughly based on patch provided by fcrossen for
 *   https://sourceforge.net/tracker/?func=detail&atid=588128&aid=1595654&group_id=88850
 *
 * Revision 1.19  2006/07/30 09:26:02  jnhayart
 * new hooks for add capability using custom fileds INLINE
 * use of 3 new hook :
 * opportunity_inline_display,opportunity_inline_edit,opportunity_inline_edit_2
 * (for documentation)
 * i test a new style of integration with separation between Core data and Customs
 * THINK to update Custom Fileds plugin database (look in SQL Scripte)
 *
 * Revision 1.18  2006/04/09 00:14:19  braverock
 * - default campaign list to empty selection
 * - add db_error_handler calls
 * - patch suggested by Jean-Noel Hayart
 *
 * Revision 1.17  2006/01/02 23:29:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.16  2005/07/06 22:50:32  braverock
 * - add opportunity types
 *
 * Revision 1.15  2005/05/04 14:37:24  braverock
 * - removed obsolete CSS widget_label_right_166px, replaced with widget_label_right
 *
 * Revision 1.14  2005/03/21 13:40:56  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.13  2005/01/13 19:08:56  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.12  2005/01/11 23:13:35  braverock
 * - removed bad javascript window.open hack, now set empty anchor on current page
 *
 * Revision 1.11  2005/01/06 20:50:06  vanmer
 * - added retrieve/display of division_id to edit and new pages
 *
 * Revision 1.10  2004/07/30 11:11:12  cpsource
 * - Improved msg handling
 *   Got campaign_id and opportunity_status_id from database so
 *     getmenu2 would work properly.
 *
 * Revision 1.9  2004/07/20 19:38:31  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.8  2004/06/14 17:41:36  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, Concat and Date functions.
 *
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
