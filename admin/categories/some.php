<?php
/**
 * Manage Categories
 *
 * $Id: some.php,v 1.9 2006/01/02 21:43:28 vanmer Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from categories where category_record_status = 'a' order by category_pretty_name";
$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$table_rows .= '<tr>';
		$table_rows .= '<td class=widget_content><a href=one.php?category_id=' . $rst->fields['category_id'] . '>' . $rst->fields['category_pretty_name'] . '</a></td>';
		$table_rows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

$con->close();

$page_title = _("Manage Categories");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Categories"); ?></td>
			</tr>
			<tr>
				<td class=widget_label><?php echo _("Name"); ?></td>
			</tr>
			<?php  echo $table_rows; ?>
		</table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		<form action=add-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2><?php echo _("Add New Category"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Short Name"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_short_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Name"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_pretty_name size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_pretty_plural size=30></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Display HTML"); ?></td>
				<td class=widget_content_form_element><input type=text name=category_display_html size=30></td>
			</tr>
			<tr>
				<td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
			</tr>
		</table>
		</form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.9  2006/01/02 21:43:28  vanmer
 * - changed to use centralized database connection function
 *
 * Revision 1.8  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.7  2004/07/16 13:51:56  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.6  2004/06/16 20:55:58  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.5  2004/06/14 21:52:23  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/04/23 15:30:11  gpowers
 * added session_check
 *
 * Revision 1.3  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
