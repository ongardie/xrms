<?php
/**
 * import-activities.php - File importer for XRMS
 *
 * The three import-activities files in XRMS allow administrators
 * to import new activities into XRMS
 *
 * @author Jean Noel HAYART, from original import-companies
 *
 * $Id: import-activities.php,v 1.2 2006/07/30 11:13:17 jnhayart Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check( 'Admin' );

$page_title = _("Import Activities");
if (!isset($msg)) { $msg=''; };

$con = get_xrms_dbconnection();

$user_menu = get_user_menu($con, $session_user_id);

//get activity type menu
$sql = "SELECT activity_type_pretty_name, activity_type_id
        FROM activity_types
        WHERE activity_type_record_status = 'a'
        ORDER BY sort_order, activity_type_pretty_name";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false);
$rst->close();

//get campaign titles
$sql2 = "SELECT campaign_title, campaign_id
         FROM campaigns c, campaign_statuses cs
         WHERE c.campaign_status_id = cs.campaign_status_id
           AND c.campaign_record_status = 'a'
           AND campaign_status_record_status = 'a'
           AND status_open_indicator = 'o'
           ";

$rst = $con->execute($sql2);
if (!$rst) {
    db_error_handler($con, $sql2);
} elseif ($rst->rowcount()) {
    $campaign_menu = $rst->getmenu2('campaign_id', $campaign_id, true);
    $rst->close();
}

$con->close();
start_page($page_title, true, $msg);
// load confGoTo.js
confGoTo_includes();

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=50% valign=top>

        <form action="import-activities-2.php" method=post enctype="multipart/form-data">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Import Activities"); ?></td>
            </tr>
            <tr>
	            <td class=widget_content colspan=2>
	            	<?php echo _("Import activities, use text file for generate activty in XRMS Tables, actualy read only 3 columns (Company_Name-Mandatory, First-Names and Last-Name-Option."); ?>
	            	<br>
	            	<?php echo _("you can also link imported activities to campaign."); ?>
	            </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("File"); ?></td>
                <td class=widget_content_form_element><input type=file name=file1></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Field Delimiter"); ?></td>
                <td class=widget_content_form_element>
                    <input type=radio name=delimiter value=comma ><?php echo _("comma"); ?>
                    <input type=radio name=delimiter value=tab><?php echo _("tab"); ?>
                    <input type=radio name=delimiter value=pipe><?php echo _("pipe"); ?>
                    <input type=radio name=delimiter value='semi-colon' checked><?php echo _("semi-colon"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("File Format"); ?></td>
                <td class=widget_content_form_element><select name="file_format">
<?php
if ($handle = opendir('.')) {
   $opts = array();
   $mask = '/^(import-template-)([^\.]+)(.php)$/i';
   while (false !== ($filename = readdir($handle))) {
      if (preg_match($mask, $filename)) {
         preg_match($mask,$filename,$format_name);
         $opts[] = '<option value="' . $format_name[2] . '">' . $format_name[2] . '</option>';
      }
   }
   if (!empty($opts)) {
       natsort($opts);
       foreach ($opts as $opt) {
           echo $opt;
       }
   }
};
?>
                </select>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Account Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Activity") . "&nbsp;" . _("Type"); ?></td>
                <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Summary"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text size=50 name=activity_title >
                </td>
            </tr>
                        <tr>
                <td class=widget_label_right><?php echo _("Activity Notes"); ?></td>
                <td class=widget_content_form_element>
                    <textarea rows=10 cols=70 name=opportunity_description><?php  echo htmlspecialchars($opportunity_description); ?></textarea><br>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Scheduled Start"); ?></td>
                <td class=widget_content_form_element>
                    <?php jscalendar_includes(); ?>
                    <input type=text ID="f_date_c" name=scheduled_at value="<?php  echo $scheduled_at; ?>">
                    <img ID="f_trigger_c" style="CURSOR: hand" border=0 src="../../img/cal.gif">
                </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Scheduled End"); ?></td>
                <td class=widget_content_form_element>
                    <input type=text ID="f_date_d" name=ends_at value="<?php  echo $ends_at; ?>">
                    <img ID="f_trigger_d" style="CURSOR: hand" border=0 src="../../img/cal.gif">
                </td>
           </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Campaigns") . "&nbsp;" . _("name"); ?></td>
                <td class=widget_content_form_element><?php  echo $campaign_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Completed?"); ?></td>
                <td class=widget_content_form_element><input type=checkbox name=activity_status value='on' <?php if ($activity_status == 'c') {print "checked";}; ?>>
                    <?php if ($completed_by) echo " by $completed_by_user"; if ($completed_at AND ($completed_at!='0000-00-00 00:00:00')) echo " at $completed_at"; ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Import"); ?>"></td>
            </tr>

        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=2%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=63% valign=top>

        </td>
    </tr>
</table>
<script type="text/javascript">
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
<script type="text/javascript">
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


<?php end_page();
/**
 *
 */
?>
