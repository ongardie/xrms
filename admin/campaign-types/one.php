<?php
/**
 * Manage campaign types
 *
 * $Id: one.php,v 1.9 2006/01/02 21:37:29 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$campaign_type_id = $_GET['campaign_type_id'];

$con = get_xrms_dbconnection();

$sql = "select * from campaign_types where campaign_type_id = $campaign_type_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$campaign_type_short_name = $rst->fields['campaign_type_short_name'];
	$campaign_type_pretty_name = $rst->fields['campaign_type_pretty_name'];
	$campaign_type_pretty_plural = $rst->fields['campaign_type_pretty_plural'];
	$campaign_type_display_html = $rst->fields['campaign_type_display_html'];
	
	$rst->close();
}

$con->close();

$page_title = _("Campaign Type Details").': '.$campaign_type_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=edit-2.php method=post>
		<input type=hidden name=campaign_type_id value="<?php  echo $campaign_type_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Edit Campaign Type Information"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Short Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=campaign_type_short_name value="<?php  echo $campaign_type_short_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=campaign_type_pretty_name value="<?php  echo $campaign_type_pretty_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Plural"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=campaign_type_pretty_plural value="<?php  echo $campaign_type_pretty_plural; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Display HTML"); ?></td>
				<td class=widget_content_form_element><input type=text size=30 name=campaign_type_display_html value="<?php  echo $campaign_type_display_html; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
			</tr>
		</table>
		</form>

		<form action=delete.php method=post onsubmit="javascript: return confirm('<?php echo _("Delete Campaign Type?"); ?>');">
		<input type=hidden name=campaign_type_id value="<?php  echo $campaign_type_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Delete Campaign Type"); ?></td>
			</tr>
			<tr>
				<td class=widget_content>
				<?php echo _("Click the button below to permanently remove this item."); ?>
				<p><?php echo _("Note: This action CANNOT be undone!"); ?>
				<p><input class=button type=submit value="<?php echo _("Delete"); ?>">
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

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.9  2006/01/02 21:37:29  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.8  2004/07/25 17:37:27  johnfawcett
 * - reinserted ? in gettext string - needed by some languages
 * - standardized delete text and button
 *
 * Revision 1.7  2004/07/25 15:36:37  johnfawcett
 * - unified page title
 * - removed punctuation from gettext strings
 *
 * Revision 1.6  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:54  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:13:22  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 19:07:47  maulani
 * - Use CSS2 positioning
 * - Fix HTML so it will validate
 * - Fix delete confirmation bug
 *
 * Revision 1.2  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 */
?>

