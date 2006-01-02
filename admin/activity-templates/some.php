<?php
/**
 * Manage Activity Templates
 *
 * $Id: some.php,v 1.5 2006/01/02 21:27:56 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from activity_templates where activity_template_record_status='a' order by on_what_table, on_what_id, sort_order";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {

        //gets the status pretty name
        $on_what_table = substr($rst->fields['on_what_table'], 0, strpos($rst->fields['on_what_table'], "_"));
        $sql_status = "select * from " . $on_what_table . "_statuses where " . $on_what_table . "_status_id=" . $rst->fields['on_what_id'];
        $rst_status = $con->execute($sql_status);
        $table_status = $rst_status->fields[$on_what_table . "_status_pretty_name"];

        //assemble the table
        $table_rows .= '<tr>';
		$table_rows .= '<td class=widget_content><a href=edit.php?activity_template_id=' 
			. $rst->fields['activity_template_id'] . '&on_what_table=' . $on_what_table 
			. '_statuses&on_what_id=' . $rst->fields['on_what_id'] . '>' . $rst->fields['activity_title'] 
			. '</a></td>';
        $table_rows .= '<td class=widget_content>' . $table_status . '</td>';
		$table_rows .= '<td class=widget_content>' . ucwords(str_replace("_", " ", $rst->fields['on_what_table'])) 
			. '</td>';
        $table_rows .= '</tr>';
        $rst->movenext();
	}
    $rst->close();
}

$con->close();

$page_title = _("Manage Activity Templates");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Activity Templates"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
                <td class=widget_label><?php echo _("Linked Table"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

    &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.5  2006/01/02 21:27:56  vanmer
 * - changed to use centralized dbconnection function
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