<?php
/**
 *
 * Cases by case status report.
 *
 * $Id: cases-by-case-status.php,v 1.11 2006/01/02 23:46:52 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-graph.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Graph/BarGraph.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$page_title = _("Cases by Status");
start_page($page_title, true, $msg);

$con = get_xrms_dbconnection();
// $con->debug = 1;




$map_and_graph = GetCasesByCaseStatusGraph($con);

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
					<?php echo $map_and_graph; ?>
                </td>
            </tr>
        </table>

    </div>

</div>

<?php

end_page();


function GetCasesByCaseStatusGraph($con) {
    global $http_site_root, $tmp_export_directory, $session_user_id;


	$sql1 = "select case_status_id, case_status_pretty_plural from case_statuses where case_status_record_status = 'a'";
	$rst1 = $con->execute($sql1);
	$case_status_count = $rst1->recordcount();
	$graph_legend_array = array();
	$graph_url_array = array();
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
	    array_push($graph_legend_array, $rst1->fields['case_status_pretty_plural']);
		array_push($graph_url_array, $http_site_root . '/cases/some.php?cases_case_status_id=' . $rst1->fields['case_status_id']);
	
	    $rst1->movenext();
	}
	
	
	
	$graph_info = array();
	$graph_info['size_class']   = 'main';
	$graph_info['graph_type']   = 'single_bar';
	$graph_info['data']         = $array_of_case_count_values;
	$graph_info['x_labels']     = $graph_legend_array;
	$graph_info['graph_title']  = $title;
	$graph_info['csim_targets'] = $graph_url_array;
	
	$graph = new BarGraph($graph_info);
	
	$basename = 'cases-by-case-status';
	$filename = "$basename-$session_user_id.jpg";
	
	return $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);
	
}


/**
 * $Log: cases-by-case-status.php,v $
 * Revision 1.11  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.10  2005/04/05 18:50:16  daturaarutad
 * added .jpg extension to graph images
 *
 * Revision 1.9  2005/04/01 23:43:01  daturaarutad
 * updated for change of bar_type->graph_type
 *
 * Revision 1.8  2005/03/11 17:21:16  daturaarutad
 * updated to support client side image maps
 *
 * Revision 1.7  2005/03/09 22:00:00  daturaarutad
 * Updated reports to use new graphing class
 *
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
