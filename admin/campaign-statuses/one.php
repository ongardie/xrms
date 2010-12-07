<?php
/**
 * Show and edit the details for a single campaign status
 * /admin/campaign-statuses/one.php
 *
 * Called from admin/campaign-statuses/some.php
 *
 * $Id: one.php,v 1.13 2010/12/07 22:41:03 gopherit Exp $
 */

// Include required common files
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

// Check to see if we are logged in
$session_user_id = session_check( 'Admin' );

$campaign_status_id = (int)$_GET['campaign_status_id'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM campaign_statuses WHERE campaign_status_id = $campaign_status_id";

$rst = $con->execute($sql);

if ($rst) {
    $campaign_type_id = $rst->fields['campaign_type_id'];
    $status_open_indicator = $rst->fields['status_open_indicator'];
    $campaign_status_short_name = $rst->fields['campaign_status_short_name'];
    $campaign_status_pretty_name = $rst->fields['campaign_status_pretty_name'];
    $campaign_status_pretty_plural = $rst->fields['campaign_status_pretty_plural'];
    $campaign_status_display_html = $rst->fields['campaign_status_display_html'];
    $campaign_status_long_desc = $rst->fields['campaign_status_long_desc'];
    $sort_order = $rst->fields['sort_order'];
    $rst->close();
} else {
    db_error_handler ($con,$sql);
}

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

$table_name = "campaign_statuses";

// list of all activity templates connected to this campaign
$sql_activity_templates = "SELECT activity_title, start_delay, duration, fixed_date, activity_template_id, activity_type_pretty_name, activity_templates.sort_order, role_name
                            FROM activity_types, activity_templates
                            LEFT OUTER JOIN Role on Role.role_id = activity_templates.role_id
                            WHERE on_what_id=$campaign_status_id
                            AND on_what_table='$table_name'
                            AND activity_templates.activity_type_id=activity_types.activity_type_id
                            AND activity_template_record_status='a'
                            ORDER BY activity_templates.sort_order";

$classname = 'open_activity';


$rst = $con->execute($sql_activity_templates);
//make activity_templates table in HTML
if ($rst) {
    // get the numbers of rows
    $maxcnt = $rst->rowcount();
    $i = 1;
    while (!$rst->EOF) {
        if ($rst->fields['fixed_date']>'')
            $fixed_date =  date($datetime_format, strtotime($rst->fields['fixed_date']));
        else
            $fixed_date='';
        $activity_rows .= '<tr>';
        $activity_rows .= "<td class='$classname'>"
            . "<a href='$http_site_root/admin/activity-templates/edit.php?activity_template_id="
            . $rst->fields['activity_template_id'] . "&on_what_table=campaign_statuses&on_what_id="
            . $campaign_status_id . "&return_url=/admin/campaign-statuses/one.php?campaign_status_id="
            . $campaign_status_id . "'>"
            . $rst->fields['activity_title'] . '</a></td>';
        $activity_rows .= '<td class=' . $classname . '>'. render_time_period_controls($rst->fields['start_delay']) . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'. $fixed_date . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'. render_time_period_controls($rst->fields['duration']) .'</td>';
        $activity_rows .= '<td class=' . $classname . '>'. $rst->fields['activity_type_pretty_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'. $rst->fields['role_name'] . '</td>';
        $activity_rows .= '<td class=' . $classname . '>'
                . '<table width=100% cellpadding=0 border=0 cellspacing=0>'
                . '<tr><td>' . $rst->fields['sort_order'] . '</td>'
                . '<td align=right>';
        if ($i > 1) {
            $activity_rows .= '<a href="'. $http_site_root
                . '/admin/sort.php?table_name=campaign_status&sort_order='. $rst->fields['sort_order'] .'&direction=up'
                . '&on_what_id=' . $campaign_status_id .'&resort_id='. $rst->fields['activity_template_id'] .'&activity_template=1'
                . '&return_url=/admin/campaign-statuses/one.php?campaign_status_id='. $campaign_status_id .'">'. _('up') .'</a> &nbsp; ';
        }
        if ($i < $maxcnt) {
            $activity_rows .= '<a href="'. $http_site_root
                . '/admin/sort.php?table_name=campaign_status&sort_order='. $rst->fields['sort_order'] .'&direction=down'
                . '&on_what_id='. $campaign_status_id .'&resort_id='. $rst->fields['activity_template_id'] .'&activity_template=1'
                . '&return_url=/admin/campaign-statuses/one.php?campaign_status_id='. $campaign_status_id .'">'. _('down') .'</a> &nbsp; ';
        }
        $activity_rows .= '</td></tr></table></td></tr>';
        $rst->movenext();
        $i++;
    }
    $rst->close();
} else {
    db_error_handler($con, $sql_activity_templates);
}


//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', '', false);
$rst->close();

$con->close();

//get role menu
$role_menu = get_role_list(false, true, 'role_id', $role_id, true);

$page_title = _("Campaign Status Details").': '.$campaign_status_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <form action=edit-2.php method=post>
    <input type=hidden name=campaign_status_id value="<?php  echo $campaign_status_id; ?>">
    <input type=hidden name=campaign_type_id value="<?php  echo $campaign_type_id; ?>">
    <table class=widget cellspacing=1>
        <tr>
            <td class=widget_header colspan=4><?php echo _("Edit Campaign Status Information"); ?></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Short Name"); ?></td>
            <td class=widget_content_form_element><input type=text size=10 name=campaign_status_short_name value="<?php echo $campaign_status_short_name; ?>"></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Full Name"); ?></td>
            <td class=widget_content_form_element><input type=text size=20 name=campaign_status_pretty_name value="<?php echo $campaign_status_pretty_name; ?>"></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
            <td class=widget_content_form_element><input type=text size=20 name=campaign_status_pretty_plural value="<?php echo $campaign_status_pretty_plural; ?>"></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
            <td class=widget_content_form_element><input type=text size=60 name=campaign_status_display_html value="<?php echo htmlspecialchars($campaign_status_display_html); ?>"></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Long Description"); ?></td>
            <td class=widget_content_form_element><input type=text size=80 name=campaign_status_long_desc value="<?php echo $campaign_status_long_desc; ?>"></td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Open Status"); ?></td>
            <td class=widget_content_form_element>
            <select name="status_open_indicator">
                <option value="o"  <?php if ($status_open_indicator == 'o') {echo "selected"; } ?> ><?php echo _("Open"); ?>
                <option value="c"  <?php if ($status_open_indicator != 'o') {echo "selected"; } ?> ><?php echo _("Closed"); ?>
            </select>
            </td>
        </tr>

        <tr>
            <td class=widget_label_right><?php echo _("Sort Order"); ?></td>
            <td class=widget_content_form_element><input type=text name=sort_order size=5 value="<?php echo $sort_order; ?>"></td>
        </tr>

        <tr>
            <td class=widget_content_form_element colspan=2>
                <input class=button type=submit value="<?php echo _("Save Changes"); ?>">
                &nbsp;
                <input class="button" type="button" onclick="location.href='some.php?campaign_type_id=<?php echo $campaign_type_id; ?>';" value="<?php echo _('Cancel'); ?>">
                &nbsp;
                <input class=button type=submit value="<?php echo _("Delete"); ?>" onclick="return confirm_delete();" />
            </td>
        </tr>
    </table>
    </form>

    <!-- link activities to campaigns //-->
    <form action="../activity-templates/new.php" method=post>
    <input type=hidden name=on_what_id value="<?php echo $campaign_status_id; ?>">
    <input type=hidden name=on_what_table value="<?php echo $table_name; ?>">

    <table class=widget cellspacing=1>
        <tr>
            <td class=widget_header colspan=7><?php echo _("Workflow Activity Templates"); ?></td>
        </tr>

        <tr>
            <td class=widget_label><?php echo _("Title"); ?></td>
            <td class=widget_label><?php echo _("Delay Start By"); ?></td>
            <td class=widget_label><?php echo _("Fixed Date"); ?></td>
            <td class=widget_label><?php echo _("Duration"); ?></td>
            <td class=widget_label><?php echo _("Type"); ?></td>
            <td class=widget_label><?php echo _("Role"); ?></td>
            <td class=widget_label width="20%"><?php echo _("Sort Order"); ?></td>
        </tr>

        <tr>
            <td class=widget_content_form_element><input type=text size=30 name="title"></td>
            <td class=widget_content_form_element>
                <?php echo render_time_period_controls (0, 'start_delay', TRUE, 'onchange="validate_reset_fixed();"'); ?>
            </td>
            <td class=widget_content_form_element>
                <span style="white-space: nowrap;">
                    <input type=text size=16 ID="f_date_activity" name="fixed_date" onchange="validate_reset_delay();">
                    <img alt="<?php echo _('Fixed Date'); ?>" title="<?php echo _('Select fixed date'); ?>"
                         ID="f_trigger_activity" style="CURSOR: pointer" border=0 src="../../img/cal.gif">
                </span>
            </td>
            <td class=widget_content_form_element>
                <?php
                    // Should switch this to default_activity_duration
                    echo render_time_period_controls (900, 'duration', TRUE);
                ?>
            </td>
            <td class=widget_content_form_element>
                <?php
                    echo $activity_type_menu;
                ?>
            </td>
            <td class=widget_content_form_element><?php echo $role_menu; ?></td>
            <td class=widget_content_form_element>
                <input type=text size=2 name="sort_order">
                <input class=button type=submit value="<?php echo _("Add New"); ?>">
            </td>
        </tr>

        <tr>
            <td class="widget_header" colspan="7"><?php echo _("Templates Currently Linked to This Campaign Status"); ?></td>
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
</div>

<script language="JavaScript" type="text/javascript">
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
        if ((document.forms[1].fixed_date.value > '') &&
            ((document.forms[1].start_delay_days.value > 0)
            || (document.forms[1].start_delay_hrs.value > 0)
            || (document.forms[1].start_delay_mins.value > 0))) {
            var answer = confirm('<?php echo addslashes(_('You cannot select a Delay Start By and a Fixed Date at the same time.')) .'\n\n'. addslashes(_('Would you like to clear the Delay Start By values?')); ?>');
            if (answer) {
                document.forms[1].start_delay_days.value = 0;
                document.forms[1].start_delay_hrs.value = 0;
                document.forms[1].start_delay_mins.value = 0;
            } else {
                document.forms[1].f_date_activity.value = '';
            }
        }
    }

    function validate_reset_fixed() {
        if ((document.forms[1].fixed_date.value > '') &&
            ((document.forms[1].start_delay_days.value > 0)
            || (document.forms[1].start_delay_hrs.value > 0)
            || (document.forms[1].start_delay_mins.value > 0))) {
            var answer = confirm('<?php echo addslashes(_('You cannot select a Delay Start By and a Fixed Date at the same time.')) .'\n\n'. addslashes(_('Would you like to clear the Fixed Date value?')); ?>');
            if (answer) {
                document.forms[1].f_date_activity.value = '';
            } else {
                document.forms[1].start_delay_days.value = 0;
                document.forms[1].start_delay_hrs.value = 0;
                document.forms[1].start_delay_mins.value = 0;
            }
        }
    }

    function confirm_delete() {
         var answer = confirm('<?php echo addslashes(_("Notice: Deleting this Campaign Status will also delete ALL Activity Templates attached to it."))
                                            .'\n\n'. addslashes(_('WARNING: This action CANNOT be undone!'))  .'\n\n'. addslashes(_('Delete Campaign Status?')); ?>');
         if (answer) {
             document.forms[0].action = 'delete.php';
             document.forms[0].submit();
             return true;
         } else {
             return false;
         }
     }
</script>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.13  2010/12/07 22:41:03  gopherit
 * Revised the Campaign Status functionality to mirror the functionality of Opportunity and Case Statuses.
 *
 * Revision 1.12  2007/09/17 21:31:53  myelocyte
 * - Fixed bugs: 984168, 984170 and similar bugs not reported
 *    I have added htmlspecialchars function in one.php before all display_html variable is
 *    displayed. This same error affected most of the display_html fields in Admin section.
 *    I tried to fix them all.
 *
 * Revision 1.11  2006/12/05 11:09:59  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.10  2006/01/02 21:37:28  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.9  2004/07/25 17:34:45  johnfawcett
 * - reinserted ? in gettext string - needed by some languages
 * - standardized delete text and button
 * - corrected bug: did not ask for confirmation before deleting
 *
 * Revision 1.8  2004/07/25 15:33:09  johnfawcett
 * - modified reference "account status" to "campaign status"
 *
 * Revision 1.7  2004/07/25 15:32:02  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 * - modified references to opportunities to campagins
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:53  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:09:56  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/23 16:49:27  gpowers
 * changed "echo $account_status_id;" to "echo $campaign_status_id;"
 *     this appears to have been a bug
 * added support for status_open_indicator,
 *     which is needed for reports/open-items.php and
 *     reports/completed-items.php
 * currently, there are two open statuses: open & closed
 * to add additional status, edit the HTML in this file AND some.php
 * 'o' means open, anything else means closed
 *
 * Revision 1.2  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 *
 */
?>