<?php
/**
 *
 * Cases by case status report.
 *
 * $Id: cases-by-case-status.php,v 1.6 2005/01/03 06:37:19 ebullient Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-graph.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql1 = "select case_status_id, case_status_pretty_plural from case_statuses where case_status_record_status = 'a'";
$rst1 = $con->execute($sql1);
$case_status_count = $rst1->recordcount();
$graph_legend_array = array();
$array_of_case_count_values = array();
$total_case_count = 0;

while (!$rst1->EOF) {

    $sql2 = "SELECT count(*) AS case_count 
	from cases 
	where case_status_id = " . $rst1->fields['case_status_id'] . " 
	and case_record_status = 'a'";
    $rst2 = $con->execute($sql2);

    if ($rst2) {
        $case_count = $rst2->fields['case_count'];
        $rst2->close();
    }

    if (!$case_count) {
        $case_count = 0;
    }
    $total_case_count += $case_count;
    array_push($array_of_case_count_values, $case_count);
    array_push($graph_legend_array, "'" . $rst1->fields['case_status_pretty_plural'] . "'");
    $rst1->movenext();

}

$graph_rows .= "g.addRow(" . implode(',', $array_of_case_count_values) . ");\n";

$rst1->close();
$con->close();

$page_title = _("Cases by Status");
start_page($page_title, true, $msg);

?>

<SCRIPT LANGUAGE="JavaScript1.2" SRC="<?php  echo $http_site_root; ?>/js/graph.js"></SCRIPT>

<div id="Main">
    <div id="ContentFullWidth">

        <table class=widget cellspacing=1>
            <tr>
                <th class=widget_header><?php echo _("Cases by Status"); ?></th>
            </tr>
            <tr>
                <td class=widget_content_graph>
                <SCRIPT LANGUAGE="JavaScript1.2">
                var g = new Graph(<?php echo $report_graph_width; ?>,<?php  echo $report_graph_height; ?>);
                <?php  echo $graph_rows; ?>
                g.scale = <?php  echo round($total_case_count / 10) + 1; ?>;
                g.stacked = false;
                g.setXScaleValues(<?php  echo implode(',', $graph_legend_array); ?>);
                g.build();
                </SCRIPT>
                </td>
            </tr>
        </table>

    </div>

</div>

<?php

end_page();

/**
 * $Log: cases-by-case-status.php,v $
 * Revision 1.6  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.5  2004/07/20 18:36:58  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.4  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.3  2004/04/17 15:57:03  maulani
 * - Add CSS2 positioning
 * - Add phpdoc
 *
 *
 */
?>
