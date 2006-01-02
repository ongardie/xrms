<?php
/**
 * /admin/relationship-types/some.php
 *
 * List relationship types
 *
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$con = get_xrms_dbconnection();

$sql = "select * from relationship_types where relationship_status = 'a' order by relationship_name, relationship_type_id";
$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$table_rows .= '<tr>';
		$table_rows .= '<td class=widget_content><a href=one.php?relationship_type_id=' . $rst->fields['relationship_type_id'] . '>' . $rst->fields['relationship_name'] . '</a></td>';
                $table_rows .= '<td class=widget_content>' . $rst->fields['from_what_table'] . '<br>' . $rst->fields['to_what_table'] . '</td>';
                $table_rows .= '<td class=widget_content>' . $rst->fields['from_what_text'] . '<br>' . $rst->fields['to_what_text'] . '</td>';
                $table_rows .= '<td class=widget_content>' . htmlspecialchars($rst->fields['pre_formatting']) . '<br>' . htmlspecialchars($rst->fields['post_formatting']) . '</td>';
		$table_rows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

$con->close();

$page_title = _("Manage Relationship Types");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4><?php echo _("Relationship Types"); ?></td>
			</tr>
			<tr>
				<td class=widget_label><?php echo _("Name"); ?></td>
                                <td class=widget_label><?php echo _("Tables"); ?></td>
                                <td class=widget_label><?php echo _("Relationship"); ?></td>
                                <td class=widget_label><?php echo _("Formatting"); ?></td>
			</tr>
			<?php  echo $table_rows; ?>
		</table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		<form action=new-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=2><?php echo _("Add New Role"); ?></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("Relationship Name"); ?></td>
				<td class=widget_content_form_element><input type=text name=relationship_name size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("From What Table"); ?></td>
				<td class=widget_content_form_element><input type=text name=from_what_table size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("To What Table"); ?></td>
				<td class=widget_content_form_element><input type=text name=to_what_table size=20></td>
			</tr>
			<tr>
				<td class=widget_label_right><?php echo _("From Text"); ?></td>
				<td class=widget_content_form_element><input type=text name=from_what_text size=30></td>
			</tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("To Text"); ?></td>
                                <td class=widget_content_form_element><input type=text name=to_what_text size=30></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("Pre-Text Formatting"); ?></td>
                                <td class=widget_content_form_element><input type=text name=pre_formatting size=30></td>
                        </tr>
                        <tr>
                                <td class=widget_label_right><?php echo _("Post-Text Formatting"); ?></td>
                                <td class=widget_content_form_element><input type=text name=post_formatting size=30></td>
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
 * Revision 1.3  2006/01/02 22:03:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2004/07/18 16:03:59  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.1  2004/07/12 18:47:59  neildogg
 * - Added Relationship Type management
 *
 * Revision 1.3  2004/06/14 22:47:04  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 *
 *
 */
?>
