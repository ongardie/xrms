<?php
/**
 * Show and edit the details for a single opportunity status
 *
 * Called from admin/opportunity-status/some.php
 *
 * $Id: one.php,v 1.20 2007/10/17 15:14:20 randym56 Exp $
 */

//include required common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

//check to see if we are logged in
$session_user_id = session_check( 'Admin' );

$aopportunity_type_id = $_GET['aopportunity_type_id'];
$opportunity_status_id = $_GET['opportunity_status_id'];

$con = get_xrms_dbconnection();

$sql = "select * from opportunity_statuses where opportunity_status_id = $opportunity_status_id";

//$con->debug=1;

$rst = $con->execute($sql);

if ($rst) {

    $opportunity_status_id = $rst->fields['opportunity_status_id'];
    $sort_order = $rst->fields['sort_order'];
    $status_open_indicator = $rst->fields['status_open_indicator'];
    $opportunity_status_short_name = $rst->fields['opportunity_status_short_name'];
    $opportunity_status_pretty_name = $rst->fields['opportunity_status_pretty_name'];
    $opportunity_status_pretty_plural = $rst->fields['opportunity_status_pretty_plural'];
    $opportunity_status_display_html = $rst->fields['opportunity_status_display_html'];
    $opportunity_status_long_desc = $rst->fields['opportunity_status_long_desc'];
	$status_workflow_type = $rst->fields['status_workflow_type'];
	$workflow_goto = $rst->fields['workflow_goto'];
    $rst->close();
}

$table_name = "opportunity_statuses";

// list of all activity templates connected to this opportunity
$sql_activity_templates="select activity_title,
                                duration,
								fixed_date,
                                activity_template_id,
                                activity_type_pretty_name, activity_templates.sort_order, role_name
                         from activity_types,
                              activity_templates LEFT OUTER JOIN Role on Role.role_id = activity_templates.role_id
                         where on_what_id=$opportunity_status_id
                                and on_what_table='$table_name'
                                and activity_templates.activity_type_id=activity_types.activity_type_id
                                and activity_template_record_status='a'
                                order by activity_templates.sort_order";

$classname = 'open_activity';


$rst = $con->execute($sql_activity_templates);
//make activity_templates table in HTML
if ($rst) {
    //get first record count and last record count
    $cnt = 1;
    $maxcnt = $rst->rowcount();
    while (!$rst->EOF) {
        $sort_order = $rst->fields['sort_order'];
		if ($rst->fields['fixed_date']>'') $fixed_date = date("Y-m-d", strtotime($rst->fields['fixed_date'])); else $fixed_date='';
        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'>"
            . "<a href='$http_site_root/admin/activity-templates/edit.php?activity_template_id="
            . $rst->fields['activity_template_id'] . "&on_what_table=opportunity_statuses&on_what_id="
            . $opportunity_status_id . "&return_url=/admin/opportunity-statuses/one.php?opportunity_status_id="
            . $opportunity_status_id . "'>"
            . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['duration'] . '</td>';
		$activity_rows .= '<td class=' . $classname . '>' . $fixed_date . '</td>';
        $activity_rows .= '<td class=' . $classname . '>' . $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'. $rst->fields['role_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'
                . '<table width=100% cellpadding=0 border=0 cellspacing=0>'
                . '<tr><td>' . $sort_order . '</td>'
                . '<td align=right>';
        if ($sort_order != $cnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?allowMultiple=1&direction=up&sort_order='
            . $sort_order . '&table_name=opportunity_status&on_what_id=' . $opportunity_status_id
            . '&return_url=/admin/opportunity-statuses/one.php?opportunity_status_id='
            . $opportunity_status_id . '&activity_template=1">up</a> &nbsp; ';
        }
        if ($sort_order != $maxcnt) {
            $activity_rows .= '<a href="' . $http_site_root
            . '/admin/sort.php?allowMultiple=1&direction=down&sort_order='
            . $sort_order . '&table_name=opportunity_status&on_what_id=' . $opportunity_status_id
            . '&return_url=/admin/opportunity-statuses/one.php?opportunity_status_id='
            . $opportunity_status_id . '&activity_template=1">down</a>';
        }
        $activity_rows .= '</td></tr></table></td></tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    db_error_handler($con,$sql_activity_templates);
}


//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

//get Opportunity Statuses menu for drop down
$sql = "select opportunity_status_pretty_name, opportunity_status_id from opportunity_statuses where opportunity_status_record_status = 'a'";
$rst = $con->execute($sql);
$opportunity_status_menu = $rst->getmenu2('workflow_goto', $workflow_goto, true);
$rst->close();

//get workflow_goto list menu
//$sql = "select opportunity_status_pretty_name, opportunity_status_short_name from opportunity_statuses where opportunity_status_record_status = 'a'";
//$rst = $con->execute($sql);
//$workflow_goto_menu = $rst->getmenu2('opportunity_status_short_name', $workflow_goto, true);
//$rst->close();

$con->close();

//get role menu
$role_menu = get_role_list(false, true, 'role_id', $role_id, true);

$page_title = _("Opportunity Status (Hopper Track) Details").': '.$opportunity_status_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=opportunity_status_id value="<?php  echo $opportunity_status_id; ?>">
        <input type=hidden name=aopportunity_type_id value="<?php  echo $aopportunity_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Opportunity Status (Hopper Track) Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=opportunity_status_short_name value="<?php  echo $opportunity_status_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=opportunity_status_pretty_name value="<?php  echo $opportunity_status_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text size=20 name=opportunity_status_pretty_plural value="<?php  echo $opportunity_status_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text size=60 name=opportunity_status_display_html value="<?php  echo $opportunity_status_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Long Description"); ?></td>
                <td class=widget_content_form_element><input type=text size=80 name=opportunity_status_long_desc value="<?php  echo $opportunity_status_long_desc; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Open Status"); ?></td>
                <td class=widget_content_form_element>
                <select name="status_open_indicator">
                    <option value="o" <?php if (($status_open_indicator == "o") or ($status_open_indicator == '')) {print " selected ";} ?>><?php echo _("Open"); ?>
                    <option value="w" <?php if ($status_open_indicator == "w") {print " selected ";} ?>><?php echo _("Closed/Won"); ?>
                    <option value="l" <?php if ($status_open_indicator == "l") {print " selected ";} ?>><?php echo _("Closed/Lost"); ?>
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Workflow Resolution Type"); ?></td>
                <td class=widget_content_form_element>
                <select name="status_workflow_type">
                    <option value="0" <?php if (($status_workflow_type == 0) or ($status_workflow_type == null)) {print " selected ";} ?>><?php echo _("End"); ?>
                    <option value="1" <?php if ($status_workflow_type == 1) {print " selected ";} ?>><?php echo _("Repeat"); ?>
                    <option value="2" <?php if ($status_workflow_type == 2) {print " selected ";} ?>><?php echo _("GoTo"); ?>
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Workflow GoTo"); ?></td>
					<td class=widget_content_form_element><?php  echo $opportunity_status_menu; ?> (Used only if "Go To" selected in Workflow Resolution Type field.)</td>
            </tr>

            <tr>
              <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"> 
                  <br>
                NOTE: The opportunity defaults as a &quot;delta-day&quot; event unless you enter a date in any of the Activity Workflow items below. You must leave the date field blank in ALL records to maintain a &quot;delta-day&quot; opportunity.&nbsp; It is not possible to combine &quot;delta-day&quot; and &quot;calendar&quot; driven hoppers. To set this opportunity as a calendar hopper, you simply put 0 in the Duration column first. </td>
            </tr>

        </table>
        </form>

        <!-- link activities to opportunities //-->
        <form action="../activity-templates/new.php" method=post>
        <input type=hidden name=on_what_id value="<?php echo $opportunity_status_id; ?>">
        <input type=hidden name=on_what_table value="<?php echo $table_name; ?>">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6><?php echo _("Link Workflow Activity To Opportunity Status (Hopper Track)"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("Duration"); ?><br> <?php echo _("(defaults to days)"); ?></td>
				<td class=widget_label><?php echo _("Fixed Date"); ?><br> <?php echo _("(Duration MUST = 0)"); ?></td>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Role"); ?></td>
                <td class=widget_label colspan=2 width="20%"><?php echo _("Sort Order"); ?></td>
            <tr>
                <td class=widget_content_form_element><input type=text size=30 name="title"></td>
                <td class=widget_content_form_element><input type=text name="duration"></td>
				<td class=widget_contect_form_element><input type=text size=16 ID="f_date_activity" name="fixed_date">
                        <img ID="f_trigger_activity" style="CURSOR: hand" border=0 src="../../img/cal.gif"></td>
                <td class=widget_content_form_element>
                    <?php
                        echo $activity_type_menu;
                    ?>
                </td>
                <td class=widget_content_form_element><?php echo $role_menu; ?></td>
                <td class=widget_content_form_element colspan=2>
                    <input type=text size=2 name="sort_order">
                    &nbsp;
                    <input class=button type=submit value="<?php echo _("Add"); ?>">
                </td>
            </tr>
            <?php
                if (!is_null($activity_rows)) {
                    echo $activity_rows;
                } else {
                    echo "<tr>\n";
                    echo "\t\t".'<td class=widget_content_form_element colspan=7>'._("No linked activities")."</td>\n";
                    echo "\t</tr>\n";
                }
            ?>

        </table>
        </form>
            <script language="JavaScript" type="text/javascript">
                    Calendar.setup({
                    inputField     :    "f_date_activity",      // id of the input field
                    ifFormat       :    "%Y-%m-%d",       // format of the input field
                    showsTime      :    true,            // will display a time selector
                    button         :    "f_trigger_activity",   // trigger for the calendar (button ID)
                    singleClick    :    false,           // double-click mode
                    step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
                    align          :    "TL"           // alignment (defaults to \"Bl\")
                });
			</script>


    </div>


    <!-- right column //-->
    <div id="Sidebar">

        <form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo addslashes(_("Delete Opportunity Status (Hopper Track)?")); ?>');">
        <input type=hidden name=opportunity_status_id value="<?php  echo $opportunity_status_id; ?>">
        <input type=hidden name=aopportunity_type_id value="<?php  echo $aopportunity_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Opportunity Status (Hopper Track)"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                <?php echo _("Click the button below to permanently remove this item."); ?>
                <p><?php echo _("Note: This action CANNOT be undone!"); ?></p>
                <p><input class=button type=submit value="<?php echo _("Delete"); ?>"></p>
                </td>
            </tr>
        </table>
        </form>

    </div>


</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.20  2007/10/17 15:14:20  randym56
 * Show ID field to make ACL mods for group members easier and match new docs
 *
 * 2007/03/18 randym
 * - Added two input fields for two new fields in table:
 * - table: opportunity_statuses
 * - fields added:
 *    status_workflow_type (integer: used for resolution types �> End=0, Repeat=1, or Goto=2)
 *    workflow_goto (used to enter the name of another opportunity status � I plan to make this
 *         a dropdown lookup from the opportunity_statuses table, but for now it�s free-form).
 *
 * Revision 1.18  2006/12/29 06:48:56  ongardie
 * - Don't allow blank on activities type drop-down.
 *
 * Revision 1.17  2006/12/05 11:10:01  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.16  2006/01/02 21:59:08  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.15  2005/07/08 17:29:29  braverock
 * - add role_id and sort_order to display/new
 * - properly localize strings
 * - add db_error_handler
 * - move variables inside if rst conditional
 *
 * Revision 1.14  2005/05/10 13:31:52  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.13  2005/01/11 22:23:32  vanmer
 * - altered to allow multiple activities to exist at the same sort_order, for workflow additions
 * - altered to properly show up/down links by retrieving record count from correct recordset
 *
 * Revision 1.12  2004/07/25 18:38:17  johnfawcett
 * - corrected erroneously pasted string
 *
 * Revision 1.11  2004/07/25 18:26:22  johnfawcett
 * - standardized page title
 * - standardized delete text and button
 * - added delete confirm (call to javascript)
 *
 * Revision 1.10  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.9  2004/07/16 13:51:59  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.8  2004/06/24 20:06:19  braverock
 * - add sort order to activity templates
 *   - patch provided by Neil Roberts
 *
 * Revision 1.7  2004/06/14 22:36:43  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.6  2004/06/03 16:13:22  braverock
 * - add functionality to support workflow and activity templates
 * - add functionality to support changing sort order
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.5  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.4  2004/03/15 16:49:56  braverock
 * - add sort_order and open status indicator to opportunity statuses
 *
 * Revision 1.3  2004/01/25 18:39:41  braverock
 * - fixed insert bugs so long_desc will be disoplayed and inserted properly
 * - added phpdoc
 *
 */
?>
