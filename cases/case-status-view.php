<?php
/**
 * View case statuses
 *
 * $Id: case-status-view.php,v 1.1 2005/01/07 01:57:52 braverock Exp $
 */

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from case_statuses where case_status_record_status = 'a' order by case_status_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content>'. htmlspecialchars($rst->fields['case_status_pretty_name']) . '</td><td class=widget_content>'. htmlspecialchars($rst->fields['case_status_long_desc']) . '</td>';
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
                <td class=widget_header colspan=4><?php echo _("Opportunity Statuses"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
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
 * Revision 1.1  2005/01/07 01:57:52  braverock
 * - Initial Revision
 *   new file to show case statuses for consistency w/ workflow in opportunities
 *
 */
?>