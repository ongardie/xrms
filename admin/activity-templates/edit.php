<?php
/**
 * Manage activity templates
 *
 * $Id: edit.php,v 1.13 2010/11/24 22:41:57 gopherit Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$activity_template_id = $_GET['activity_template_id'];
$on_what_table = $_GET['on_what_table'];
$on_what_id = $_GET['on_what_id'];
$return_url = $_GET['return_url'];
getGlobalVar($msg, 'msg');

$con = get_xrms_dbconnection();

// Set the datetime_format and the JavaScript date time format
$datetime_format = set_datetime_format($con, $session_user_id);
if ($datetime_format == 'Y-m-d H:i:s') {
	$java_timeformat = "%Y-%m-%d %H:%M:%S";
	$java_timevalue = '24';
	}
	else {
	$java_timeformat = "%Y-%m-%d %I:%M %p";
	$java_timevalue = '12';
	}

$sql = "select * from activity_templates where activity_template_id = $activity_template_id";

$rst = $con->execute($sql);

if ($rst) {

    $activity_title = $rst->fields['activity_title'];
    $activity_description = $rst->fields['activity_description'];
    $default_text = $rst->fields['default_text'];
    $activity_type_id = $rst->fields['activity_type_id'];
    $start_delay = $rst->fields['start_delay'];
    if ($rst->fields['fixed_date']>'')
        $fixed_date =  date($datetime_format, strtotime($rst->fields['fixed_date']));
    else
        $fixed_date='';
    $duration = $rst->fields['duration'];
    $role_id = $rst->fields['role_id'];
    $sort_order = $rst->fields['sort_order'];
    $workflow_entity = $rst->fields['workflow_entity'];
    $workflow_entity_type = $rst->fields['workflow_entity_type'];

    $rst->close();
}

//lookup which activity type is the Process type, to use for special handling
$sql = "select activity_type_id FROM activity_types WHERE activity_type_short_name='PRO'";
$rst = $con->execute($sql);
if ($rst) $process_activity_type=$rst->fields['activity_type_id'];

//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false, false, 0, 'onchange="changeActivityType(this)"');
$rst->close();
$role_menu = get_role_list(false, true, 'role_id', $role_id, true);


$workflow_entity_array=array( 'opportunities'=>_("Opportunities"), 'cases'=>_("Cases"));
$workflow_entity_menu=create_select_from_array($workflow_entity_array, 'workflow_entity', $workflow_entity, ' onchange="changeWorkflowEntity(this)"');
foreach ($workflow_entity_array as $entity=>$entity_name) {
    $entity_singular=make_singular($entity);
    $sql = "SELECT {$entity_singular}_type_pretty_name, {$entity_singular}_type_id FROM {$entity_singular}_types";
    $rst = $con->execute($sql);
    $workflow_entity_type_menus[$entity]= $rst->getmenu2('workflow_entity_type_'.$entity, $workflow_entity_type, false, false, 0, "onchange=\"changeEntityType(this)\" id=workflow_entity_type_$entity");
    $rst->close();

}
$workflow_entity_type_hidden="<input type=hidden name=workflow_entity_type value=\"$workflow_entity_type\">";

$con->close();

$page_title = _("Activity Template Details") .': ' .ucwords($activity_title);
start_page($page_title, true, $msg);

?>
<div id="Main">
    <form action=edit-2.php method=post name=activity_template_form>
    <input type=hidden name=activity_template_id value="<?php  echo $activity_template_id; ?>">
    <input type=hidden name=on_what_table value="<?php echo $on_what_table; ?>">
    <input type=hidden name=on_what_id value="<?php echo $on_what_id; ?>">
    <input type=hidden name=return_url value="<?php echo $return_url; ?>">
    <table class=widget cellspacing=1>
        <tr>
            <td class=widget_header colspan=4><?php echo _("Edit Activity Template Information"); ?></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Title"); ?></td>
            <td class=widget_content_form_element><input type=text size=40 name="activity_title" value="<?php echo $activity_title; ?>"></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Delay Start By"); ?></td>
            <td class=widget_content_form_element>
                <?php echo render_time_period_controls ($start_delay, 'start_delay', TRUE, 'onchange="validate_reset_fixed();"'); ?>
            </td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Fixed Date"); ?></td>
            <td class=widget_content_form_element>
                <span style="white-space: nowrap;">
                    <input type=text size=16 ID="f_date_activity" name="fixed_date" onchange="validate_reset_delay();" value="<?php echo $fixed_date; ?>" />
                    <img alt="<?php echo _('Fixed Date'); ?>" title="<?php echo _('Select fixed date'); ?>"
                         ID="f_trigger_activity" style="CURSOR: pointer" border=0 src="../../img/cal.gif">
                </span>
            </td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Duration"); ?></td>
            <td class=widget_content_form_element>
                <?php echo render_time_period_controls ($duration, 'duration', TRUE); ?>
            </td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Type"); ?></td>
            <td class=widget_content_form_element><?php echo $activity_type_menu; ?></td>
        </tr>

        <tr id=entity_selection>
            <td class=widget_label_right><?php echo _("Workflow Entity"); ?></td>
            <td class=widget_content_form_element><?php echo $workflow_entity_menu.$workflow_entity_type_hidden; ?></td>
        </tr>

        <?php foreach ($workflow_entity_type_menus as $type_entity => $type_menu) { ?>
            <tr id=entity_type_selection_<?php echo $type_entity; ?>>
                <td class=widget_label_right><?php echo _("Workflow Entity Type"); ?></td>
                <td class=widget_content_form_element><?php echo $type_menu; ?></td>
            </tr>
        <?php } ?>

        <tr>
            <td class=widget_label_right><?php echo _("Role"); ?></td>
            <td class=widget_content_form_element><?php echo $role_menu; ?></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Description"); ?></td>
            <td class=widget_content_form_element><textarea rows=8 cols=100 name="activity_description"><?php echo $activity_description ?></textarea></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Default Text"); ?></td>
            <td class=widget_content_form_element><textarea rows=8 cols=100 name="default_text"><?php echo $default_text; ?></textarea></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Sort Order"); ?></td>
            <td class=widget_content_form_element><input type=text size=2 name="sort_order" value='<?php echo $sort_order; ?>'></td>
        </tr>

        <tr>
            <td class=widget_content colspan=2>
                <input class=button type=submit value="<?php echo _("Save Changes"); ?>">
                  &nbsp;
                  <input class="button" type="button" onclick="location.href='<?php echo $http_site_root . $return_url; ?>';" value="<?php echo _('Cancel'); ?>">
                &nbsp;
                <input class=button type=submit value="<?php echo _("Delete"); ?>" onclick="return confirm_delete();" />
            </td>
        </tr>
    </table>
    </form>
</div>


<script language=javascript>
<!--
function changeActivityType(typeSel) {
    var type=typeSel.options[typeSel.selectedIndex].value;
    if (type==<?php echo $process_activity_type; ?>) {
        showWorkflowEntity();
    } else {
        hideWorkflowEntity(typeSel.form);
        unsetWorkflowEntityTypes();
    }
    hideAllTypes();
}
function changeWorkflowEntity(entitySel) {
    var entity=entitySel.options[entitySel.selectedIndex].value;
    hideAllTypes();
    var obj=document.getElementById('entity_type_selection_'+entity);
    if (!obj) return false;
    obj.style.display='';
    document.getElementById('workflow_entity_type_'+entity).selectedIndex=0;
}

function hideAllTypes() {
<?php foreach ($workflow_entity_type_menus as $js_entity=>$menu) { ?>
    document.getElementById('entity_type_selection_<?php echo $js_entity; ?>').style.display='none';
<?php } ?>
}

function unsetWorkflowEntityTypes() {
<?php foreach ($workflow_entity_type_menus as $js_entity=>$menu) { ?>
    document.getElementById('workflow_entity_type_<?php echo $js_entity; ?>').selectedIndex=0;
<?php } ?>
}

function hideWorkflowEntity(objForm) {
    if (objForm) {
        objForm.workflow_entity.value='';
        objForm.workflow_entity_type.value='';
    }
    document.getElementById('entity_selection').style.display='none';
}

function showWorkflowEntity() {
    document.getElementById('entity_selection').style.display='';
}

function changeEntityType(objType) {
    objType.form.workflow_entity_type.value=objType.options[objType.selectedIndex].value;
}

hideAllTypes();
hideWorkflowEntity();

<?php if ($workflow_entity AND $workflow_entity_type) { ?>
document.getElementById('entity_selection').style.display='';
document.getElementById('entity_type_selection_<?php echo $workflow_entity; ?>').style.display='';
document.getElementById('entity_type_selection_<?php echo $workflow_entity; ?>').selectedIndex=<?php echo $workflow_entity_type; ?>;
<?php } ?>

Calendar.setup({
    inputField     :    "f_date_activity",      // id of the input field
    ifFormat       :    "<?php echo $java_timeformat; ?>",       // format of the input field
    showsTime      :    true,            // will display a time selector
    timeFormat     :    value="<?php echo $java_timevalue; ?>",  //12 or 24
    button         :    "f_trigger_activity",   // trigger for the calendar (button ID)
    singleClick    :    false,           // double-click mode
    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
    align          :    "TL"           // alignment (defaults to \"Bl\")
});

function validate_reset_delay() {
    if ((document.forms[0].fixed_date.value > '') &&
        ((document.forms[0].start_delay_days.value > 0)
        || (document.forms[0].start_delay_hrs.value > 0)
        || (document.forms[0].start_delay_mins.value > 0))) {
        var answer = confirm('<?php echo addslashes(_('You cannot select a Delay Start By and a Fixed Date at the same time.')) .'\n\n'. addslashes(_('Would you like to clear the Delay Start By values?')); ?>');
        if (answer) {
            document.forms[0].start_delay_days.value = 0;
            document.forms[0].start_delay_hrs.value = 0;
            document.forms[0].start_delay_mins.value = 0;
        } else {
            document.forms[0].f_date_activity.value = '';
        }
    }
}

function validate_reset_fixed() {
    if ((document.forms[0].fixed_date.value > '') &&
        ((document.forms[0].start_delay_days.value > 0)
        || (document.forms[0].start_delay_hrs.value > 0)
        || (document.forms[0].start_delay_mins.value > 0))) {
        var answer = confirm('<?php echo addslashes(_('You cannot select a Delay Start By and a Fixed Date at the same time.')) .'\n\n'. addslashes(_('Would you like to clear the Fixed Date value?')); ?>');
        if (answer) {
            document.forms[0].f_date_activity.value = '';
        } else {
            document.forms[0].start_delay_days.value = 0;
            document.forms[0].start_delay_hrs.value = 0;
            document.forms[0].start_delay_mins.value = 0;
        }
    }
}

function confirm_delete() {
     var answer = confirm('<?php echo addslashes(_('Delete Opportunity Status?')) .'\n\n'. addslashes(_('WARNING: This action CANNOT be undone!')); ?>');
     if (answer) {
         document.forms[0].action = 'delete.php';
         document.forms[0].submit();
         return true;
     } else {
         return false;
     }
 }

//-->
</script>



<?php

end_page();

/**
 * $Log: edit.php,v $
 * Revision 1.13  2010/11/24 22:41:57  gopherit
 * Revised the interface for editing Activity Templates attached to an Opportunity:
 * - provided support for the new start_delay field which allows workflow activities to have gaps between them, measured in seconds by start_delay
 * - finished the fixed_date functionality which lay dormant in the code base until now
 * - fixed the datetime format of the fixed_date input
 *
 * Revision 1.12  2006/12/05 11:09:59  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.11  2006/01/02 21:27:56  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.10  2005/09/29 15:00:07  vanmer
 * - added workflow entity and type menus to activity template page
 * - added javascript to only display entity when activity type is process
 * - added javascript to only display proper entity type menu based on which entity is selected
 *
 * Revision 1.9  2005/07/08 17:17:12  braverock
 * - clean up formatting
 *
 * Revision 1.8  2005/07/08 02:33:32  vanmer
 * - added role menu to activity template edit screen
 *
 * Revision 1.7  2005/01/11 22:28:29  vanmer
 * - added option to explicitly set sort order on activity template
 *
 * Revision 1.6  2004/08/19 21:55:09  neildogg
 * - Adds input for default text in templated activity
 *
 * Revision 1.5  2004/07/25 17:24:19  johnfawcett
 * - standardized page title
 * - standardized delete text and button
 *
 * Revision 1.4  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.3  2004/07/15 20:36:18  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.2  2004/06/14 20:50:11  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.1  2004/06/03 16:11:53  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.3  2004/04/16 22:18:24  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>