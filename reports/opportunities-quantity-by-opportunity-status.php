<?php
/**
 *
 * Opportunities quanity by opportunity status report.
 *
 * $Id: opportunities-quantity-by-opportunity-status.php,v 1.5 2004/07/04 09:10:56 metamedia Exp $
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

$hide_closed_opps = $_GET['hide_closed_opps'];
if (strlen($hide_closed_opps) > 0) {
	$checked_hide_closed_opps = "checked";
	$hide_closed_opps = true;
}
else $hide_closed_opps = false;
 
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql1 = "select opportunity_status_id, opportunity_status_pretty_plural
from opportunity_statuses
where opportunity_status_record_status = 'a'";

if ($hide_closed_opps) $sql1 .= " and status_open_indicator = 'o'";

$rst1 = $con->execute($sql1);
$opportunity_status_count = $rst1->recordcount();
$graph_legend_array = array();
$array_of_opportunity_count_values = array();
$total_opportunity_count = 0;

while (!$rst1->EOF) {

    $sql2 = "SELECT count(*) AS opportunity_count
	from opportunities
	where opportunity_status_id = " . $rst1->fields['opportunity_status_id'] . "
	and opportunity_record_status = 'a'";
    $rst2 = $con->execute($sql2);

    if ($rst2) {
        $opportunity_count = $rst2->fields['opportunity_count'];
        $rst2->close();
    }

    if (!$opportunity_count) {
        $opportunity_count = 0;
    }
    $total_opportunity_count += $opportunity_count;
    array_push($array_of_opportunity_count_values, $opportunity_count);
    array_push($graph_legend_array, "'" . $rst1->fields['opportunity_status_pretty_plural'] . "'");
    $rst1->movenext();

}

$graph_rows .= "g.addRow(" . implode(',', $array_of_opportunity_count_values) . ");\n";

$rst1->close();
$con->close();

$page_title = "Opportunities by Status";
start_page($page_title, true, $msg);

?>

<SCRIPT LANGUAGE="JavaScript1.2" SRC="<?php  echo $http_site_root; ?>/js/graph.js"></SCRIPT>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header>Opportunities by Status</td>
            </tr>
            <tr>
                <td class=widget_label_center>Opportunities by Status</td>
            </tr>
            <tr>

                <td class=widget_content_graph>
                <SCRIPT LANGUAGE="JavaScript1.2">
                var g = new Graph(<?php  echo ($opportunity_status_count * 80); ?>,<?php  echo $report_graph_height; ?>);
                <?php  echo $graph_rows; ?>
                g.scale = <?php  echo round($total_opportunity_count / 10); ?>;
                g.stacked = false;
                g.setXScaleValues(<?php  echo implode(',', $graph_legend_array); ?>);
                g.build();
                </SCRIPT>
                </td>

            </tr>
		<tr>
                <td class=widget_content_form_element>
		<form method=get>
		<input type=checkbox name=hide_closed_opps value="true" <?php echo $checked_hide_closed_opps; ?>>
		Exclude Closed Opportunities</input>
		<input type=submit class=button value="Change Graph">
		</form>
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
 * $Log: opportunities-quantity-by-opportunity-status.php,v $
 * Revision 1.5  2004/07/04 09:10:56  metamedia
 * Added option to exclude closed opportunities from the graph.
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
