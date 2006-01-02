<?php
/**
 *
 * Opportunities size by industry report.
 *
 * $Id: opportunities-size-by-industry.php,v 1.11 2006/01/02 23:46:52 vanmer Exp $
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

$con = get_xrms_dbconnection();

$page_title = _("Opportunities (Size) by Industry");
start_page($page_title, true, $msg);

$map_and_image = GetOpportunitiesQuantityByOpportunityStatusGraph($con);


?>

<script language="javascript" src="../js/graph.js"></script>

<div id="Main">
    <div id="ContentFullWidth">

        <table class=widget cellspacing=1>
            <tr>
                <th class=widget_header><?php echo _("Opportunities (Size) by Industry"); ?></th>
            </tr>
            <tr>
                <td class=widget_content_graph>
					<?php echo $map_and_image; ?>
                </td>
            </tr>
        </table>
    </div>
</div>

<?php

end_page();

function GetOpportunitiesQuantityByOpportunityStatusGraph($con) {

    global $http_site_root, $tmp_export_directory, $session_user_id;
	
	$sql1 = "select industry_id, industry_pretty_name from industries where industry_record_status = 'a'";
	$rst1 = $con->execute($sql1);
	$industry_count = $rst1->recordcount();
	$graph_legend_array = array();
	$array_of_total_values = array();
	$array_of_total_weighted_values = array();
    $graph_url_array = array();
	$total_opportunity_count = 0;
	
	while (!$rst1->EOF) {
	
	    $sql2 = "select sum(size*probability)/100 as total, sum(size) - sum(size*probability)/100 as weighted_total 
	    from opportunities, companies 
	    where opportunities.company_id = companies.company_id 
	    and industry_id = " . $rst1->fields['industry_id'] . " 
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
	    array_push($graph_legend_array, $rst1->fields['industry_pretty_name']);
		array_push($graph_url_array, $http_site_root . '/opportunities/some.php?industry_id=' . $rst1->fields['industry_id']);
	    $rst1->movenext();
	
	}
	
	
	$rst1->close();
	$con->close();
	
	$graph_info = array();
	$graph_info['size_class']   = 'main';
	$graph_info['graph_type']   = 'grouped_bar';
	$graph_info['data']         = array($array_of_total_values, $array_of_total_weighted_values);
	$graph_info['legend']       = array('Total', 'Weighted');
	$graph_info['x_labels']     = $graph_legend_array;
	$graph_info['xaxis_label_angle'] = 30;
	$graph_info['xaxis_font_size'] = 7;
	$graph_info['csim_targets'] = array($graph_url_array, $graph_url_array);
	
	
	$graph = new BarGraph($graph_info);
    $basename = 'opportunities-quantitiy-by-industry';
    $filename = "$basename-$session_user_id.jpg";


    return $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);



}

/**
 * $Log: opportunities-size-by-industry.php,v $
 * Revision 1.11  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.10  2005/04/05 18:50:16  daturaarutad
 * added .jpg extension to graph images
 *
 * Revision 1.9  2005/04/01 23:43:01  daturaarutad
 * updated for change of bar_type->graph_type
 *
 * Revision 1.8  2005/03/11 21:51:50  daturaarutad
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
