<?php
/**
 * Manage crm statuses
 *
 * $Id: one.php,v 1.11 2006/01/02 21:48:01 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$crm_status_id = $_GET['crm_status_id'];

$con = get_xrms_dbconnection();

$sql = "select * from crm_statuses where crm_status_id = $crm_status_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$crm_status_short_name = $rst->fields['crm_status_short_name'];
	$crm_status_pretty_name = $rst->fields['crm_status_pretty_name'];
	$crm_status_pretty_plural = $rst->fields['crm_status_pretty_plural'];
	$crm_status_display_html = $rst->fields['crm_status_display_html'];
	
	$rst->close();
}

$con->close();

$page_title = _("CRM Status Details").': '.$crm_status_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=edit-2.php method=post>
		<input type=hidden name=crm_status_id value="<?php  echo $crm_status_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Edit Rating Information"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Short Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=10 name=crm_status_short_name value="<?php  echo $crm_status_short_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Name"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=crm_status_pretty_name value="<?php  echo $crm_status_pretty_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Plural"); ?></td>
				<td class=widget_content_form_element><input type=text size=20 name=crm_status_pretty_plural value="<?php  echo $crm_status_pretty_plural; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Display HTML"); ?></td>
				<td class=widget_content_form_element><input type=text size=30 name=crm_status_display_html value="<?php  echo $crm_status_display_html; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
			</tr>
		</table>
		</form>

		<form action="delete.php" method=post onsubmit="javascript: return confirm('<?php echo _("Delete CRM Status?"); ?>');">
		<input type=hidden name="crm_status_id" value="<?php  echo $crm_status_id; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Delete CRM Status"); ?></td>
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

        <!-- right column //-->
    <div id="Sidebar">

		&nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.11  2006/01/02 21:48:01  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.10  2004/07/25 18:35:19  johnfawcett
 * - corrected string erroneously pasted
 *
 * Revision 1.9  2004/07/25 18:16:20  johnfawcett
 * - reinserted ? in gettext string - needed by some languages
 * - standardized delete text and button
 *
 * Revision 1.8  2004/07/25 15:18:21  johnfawcett
 * - added punctuation which I removed instead of moving
 *
 * Revision 1.7  2004/07/25 15:08:45  johnfawcett
 * - unified page title
 * - removed punctuation from gettext call
 *
 * Revision 1.6  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:57  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 22:14:42  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:48  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

