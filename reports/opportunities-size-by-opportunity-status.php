<?php
/**
 *
 * Opportunities size by opportunity status report.
 *
 * $Id: opportunities-size-by-opportunity-status.php,v 1.4 2004/06/12 05:35:58 introspectshun Exp $
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

$sql1 = "select opportunity_status_id, opportunity_status_pretty_plural from opportunity_statuses where opportunity_status_record_status = 'a'";
$rst1 = $con->execute($sql1);
$opportunity_status_count = $rst1->recordcount();
$graph_legend_array = array();
$array_of_total_values = array();
$array_of_total_weighted_values = array();
$total_opportunity_count = 0;

while (!$rst1->EOF) {
    
    $sql2 = "select sum(size*probability)/100 as total, sum(size) - sum(size*probability)/100 as weighted_total 
	from opportunities 
	where opportunity_status_id = " . $rst1->fields['opportunity_status_id'] . " 
	and opportunity_record_status = 'a'";
    $rst2 = $con->execute($sql2);
    
    if ($rst2) {
        $total = $rst2->fields['total'];
        $weighted_total = $rst2->fields['weighted_total'];
        $rst2->close();
    }
    
    if (!$total) {
        $total = 0;
    }
    
    if (!$weighted_total) {
        $weighted_total = 0;
    }
    
    
    $total_opportunity_count += $opportunity_count;
    array_push($array_of_total_values, $total);
    array_push($array_of_total_weighted_values, $weighted_total);
    array_push($graph_legend_array, "'" . $rst1->fields['opportunity_status_pretty_plural'] . "'");
    $rst1->movenext();
    
}

$graph_rows .= "g.addRow(" . implode(',', $array_of_total_weighted_values) . ");\n";
$graph_rows .= "g.addRow(" . implode(',', $array_of_total_values) . ");\n";


$rst1->close();
$con->close();

$page_title = "Opportunities (Size) by Status";
start_page($page_title, true, $msg);

?>

<script language="javascript" src="../js/graph.js"></script>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Opportunities (Size) by Status</td>
            </tr>
            <tr>
                <td class=widget_label_center>Opportunities (Size) by Status</td>
            </tr>
            <tr>

                <td class=widget_content_graph>
                <SCRIPT LANGUAGE="JavaScript1.2">
                var g = new Graph(<?php echo $report_graph_width; ?>,<?php echo $report_graph_height; ?>);
                <?php echo $graph_rows; ?>
                g.scale = <?php echo round(array_sum($array_of_total_values) / sizeof($array_of_total_values)) ?>;
                g.stacked = true;
                g.setXScaleValues(<?php  echo implode(',', $graph_legend_array); ?>);
				g.setLegend('Remainder', 'Weighted');
                g.build();
                </SCRIPT>
                </td>

            </tr>

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
 * $Log: opportunities-size-by-opportunity-status.php,v $
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
