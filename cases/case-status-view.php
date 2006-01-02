<?php
/**
 * View case statuses
 *
 * $Id: case-status-view.php,v 1.3 2006/01/02 22:47:25 vanmer Exp $
 */

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$case_type_id = $_GET['case_type_id'];
$session_user_id = session_check();

$con = get_xrms_dbconnection();

$sql = "select s.*, t.case_type_pretty_name from case_statuses s, case_types t ";
$sql .= "where s.case_type_id=t.case_type_id ";
$sql .= "and case_status_record_status = 'a' and s.case_type_id=$case_type_id ";
$sql .= "order by sort_order";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content>'. htmlspecialchars($rst->fields['case_type_pretty_name']) . '</td>';
        $table_rows .= '<td class=widget_content>'. htmlspecialchars($rst->fields['case_status_pretty_name']) . '</td>';
        $table_rows .= '<td class=widget_content>'. htmlspecialchars($rst->fields['case_status_long_desc']) . '</td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("View Case Statuses");
start_page($page_title, $show_navbar = false);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=3><?php echo _('Case Statuses'); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Type"); ?></td>
                <td class=widget_label><?php echo _("Status"); ?></td>
                <td class=widget_label><?php echo _("Description"); ?></td>
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
 * $Log: case-status-view.php,v $
 * Revision 1.3  2006/01/02 22:47:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2005/06/29 17:18:13  maulani
 * - Correctly display case status definitions
 *
 * Revision 1.1  2005/01/07 01:57:52  braverock
 * - Initial Revision
 *   new file to show case statuses for consistency w/ workflow in opportunities
 *
 */
?>