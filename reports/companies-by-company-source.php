<?php
/**
 *
 * Companies by company source report.
 *
 * $Id: companies-by-company-source.php,v 1.7 2005/01/03 06:37:19 ebullient Exp $
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
// $con->debug = 1;

$sql1 = "select company_source_id, company_source_short_name from company_sources where company_source_record_status = 'a'";
$rst1 = $con->execute($sql1);
$company_source_count = $rst1->recordcount();
$graph_legend_array = array();
$array_of_company_count_values = array();
$total_company_count = 0;

while (!$rst1->EOF) {

    $sql2 = "SELECT count(*) AS company_count 
	from companies 
	where company_source_id = " . $rst1->fields['company_source_id'];
    $rst2 = $con->execute($sql2);

    if ($rst2) {
        $company_count = $rst2->fields['company_count'];
        $rst2->close();
    }

    if (!$company_count) {
        $company_count = 0;
    }
    $total_company_count += $company_count;
    array_push($array_of_company_count_values, $company_count);
    array_push($graph_legend_array, "'" . $rst1->fields['company_source_short_name'] . "'");
    $rst1->movenext();

}

$graph_rows .= "g.addRow(" . implode(',', $array_of_company_count_values) . ");\n";

$rst1->close();
$con->close();

$page_title = _("Companies by Source");
start_page($page_title, true, $msg);

?>

<SCRIPT LANGUAGE="JavaScript1.2" SRC="<?php  echo $http_site_root; ?>/js/graph.js"></SCRIPT>

<div id="Main">
    <div id="ContentFullWidth">

        <table class=widget cellspacing=1>
            <tr>
                <th class=widget_header><?php echo _("Companies by Source"); ?></th>
            </tr>
            <tr>

                <td class=widget_content_graph>
                <SCRIPT LANGUAGE="JavaScript1.2">
                var g = new Graph(<?php  echo ($company_source_count * 50); ?>,<?php  echo $report_graph_height; ?>);
                <?php  echo $graph_rows; ?>
                g.scale = <?php  echo round($total_company_count / 10); ?>;
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
 * $Log: companies-by-company-source.php,v $
 * Revision 1.7  2005/01/03 06:37:19  ebullient
 * update reports - graphs centered on page, reports surrounded by divs
 *
 * Revision 1.6  2004/07/20 18:36:58  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.5  2004/06/12 05:35:58  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.4  2004/04/17 15:57:03  maulani
 * - Add CSS2 positioning
 * - Add phpdoc
 *
 *
 */
?>
