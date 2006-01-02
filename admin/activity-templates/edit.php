<?php
/**
 * Manage activity templates
 *
 * $Id: edit.php,v 1.11 2006/01/02 21:27:56 vanmer Exp $
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

$sql = "select * from activity_templates where activity_template_id = $activity_template_id";

$rst = $con->execute($sql);

if ($rst) {

    $activity_title = $rst->fields['activity_title'];
    $activity_description = $rst->fields['activity_description'];
        $default_text = $rst->fields['default_text'];
    $activity_type_id = $rst->fields['activity_type_id'];
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
    <div id="Content">

        <form action=edit-2.php method=post name=activity_template_form>
        <input type=hidden name=activity_template_id value="<?php  echo $activity_template_id; ?>">
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
                <td class=widget_label_right><?php echo _("Duration"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name="duration" value="<?php echo $duration; ?>"></td>
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
                <td class=widget_label_right_166px><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=8 cols=100 name="activity_description"><?php echo $activity_description ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Default Text"); ?></td>
                <td class=widget_content_form_element><textarea rows=8 cols=100 name="default_text"><?php echo $default_text; ?></textarea></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Sort Order"); ?></td>
                <td class=widget_content_form_element><input type=text size=2 name="sort_order" value='<?php echo $sort_order; ?>'></td>
            </tr>
            <tr>
                <td class=widget_content colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

    <form action=delete.php method=post>
        <input type=hidden name=activity_template_id value="<?php  echo $activity_template_id; ?>" onsubmit="javascript: return confirm('<?php echo _("Delete Activity Template?"); ?>');">
    <input type=hidden name=on_what_table value="<?php echo $on_what_table; ?>">
    <input type=hidden name=on_what_id value="<?php echo $on_what_id; ?>">
    <input type=hidden name=return_url value="<?php echo $return_url; ?>">
        <table class=widget cellspacing=1>
             <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Activity Template"); ?></td>
             </tr>
             <tr>
                   <td class=widget_content>
                   <?php echo _("Click the button below to permanently remove this item.")
                            . '<p>'
                            . _("Note: This action CANNOT be undone!")
                            . '</p>';
                   ?>
                    <p><input class=button type=submit value="<?php echo _("Delete"); ?>">
                   </td>
             </tr>
        </table>
        </form>

    </div>
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

//-->
</script>



<?php

end_page();

/**
 * $Log: edit.php,v $
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
