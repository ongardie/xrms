<?php
/**
 * Manage activity templates
 *
 * $Id: edit.php,v 1.7 2005/01/11 22:28:29 vanmer Exp $
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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from activity_templates where activity_template_id = $activity_template_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$activity_title = $rst->fields['activity_title'];
	$activity_description = $rst->fields['activity_description'];
        $default_text = $rst->fields['default_text'];
	$activity_type_id = $rst->fields['activity_type_id'];
	$duration = $rst->fields['duration'];	
	$sort_order = $rst->fields['sort_order'];	
	
	$rst->close();
}

//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false);
$rst->close();
$con->close();

$page_title = _("Activity Template Details") .': ' .ucwords($activity_title);
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=edit-2.php method=post>
		<input type=hidden name=activity_template_id value="<?php  echo $activity_template_id; ?>">
		<input type=hidden name=return_url value="<?php echo $return_url; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Edit Activity Template Information"); ?></td>
			</tr>
            <tr>
                <td class=widget_label_right><?php echo _("Title"); ?></td>
				<td class=widget_content_form_element><input type=text size=40 name="activity_title" value='<?php echo $activity_title; ?>'></td>
			</tr>
			<tr>
                <td class=widget_label_right><?php echo _("Duration"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name="duration" value='<?php echo $duration; ?>'></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Type"); ?></td>
				<td class=widget_content_form_element><?php echo $activity_type_menu; ?></td>
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

<?php

end_page();

/**
 * $Log: edit.php,v $
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
