<?php
/**
 *
 * Cases by case status report.
 *
 * $Id: cases-by-case-status.php,v 1.12 2008/11/13 09:42:35 metamedia Exp $
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
$hide_closed_cases = $_POST['hide_closed_cases'];

$page_title = _("Cases by Status");
start_page($page_title, true, $msg);

$con = get_xrms_dbconnection();
// $con->debug = 1;

if (strlen($hide_closed_cases) > 0) {
	$checked_hide_closed_cases = "checked";
	$hide_closed_cases = true;
}
else $hide_closed_cases = false;

$caseTypesArray = getCaseTypesArray($con);
$map_and_graph = GetCasesByCaseStatusGraph($con, $hide_closed_cases, $caseTypesArray);

?>

<SCRIPT LANGUAGE="JavaScript1.2" SRC="<?php  echo $http_site_root; ?>/js/graph.js"></SCRIPT>

<div id="Main">
    <div id="ContentFullWidth">
    <form method=POST>
        <table class=widget cellspacing=1>
            <tr>
                <th class=widget_header><?php echo _("Cases by Status"); ?></th>
            </tr>
            <tr>
                <td class=widget_content_graph>
					<?php echo $map_and_graph; ?>
                </td>
            </tr>
            <tr>
            <td><?php echo _("Exclude Closed Cases"); ?>:
			    <input type=checkbox name=hide_closed_cases value="true" <?php echo $checked_hide_closed_cases; ?>>
            </td>
            </tr>
            <tr>
            <td>Choose Case Types to Display: 
                <?php foreach ($caseTypesArray as $caseTypeID => $caseType): ?>
                <input type=checkbox name=caseType_<?php echo $caseTypeID?> value="true" <?php echo $caseType['checked']?>>
                <?php echo $caseType['name'] ?>
                <?php endforeach ?>
            </td>
            </tr>
            <tr>
             <td>
                <input class=button name='submit' type='submit' value='<?php echo _("Change Graph"); ?>'>
            </td>
            </tr>
        </table>
    </form>
    </div>

</div>

<?php

end_page();

function getCaseTypesArray($con) {
	
	$sql = <<<SQL
    SELECT case_type_id, case_type_pretty_plural as name, 1 as draw, 'CHECKED' as checked
    FROM case_types
    WHERE case_type_record_status = 'a'
    ORDER BY case_type_id
SQL;

#	echo "<pre>$sql</pre>";
	$caseTypesArray = $con->GetAssoc($sql);
	
	if ($caseTypesArray) {
		foreach($caseTypesArray as $caseTypeID => $caseType) {
			
		  if (isset($_POST["caseType_$caseTypeID"])) {
		      $caseTypesArray[$caseTypeID]['checked'] = 'checked';
		      $caseTypesArray[$caseTypeID]['draw'] = 1;
		  }
		  elseif (isset($_POST['submit'])) {
		      $caseTypesArray[$caseTypeID]['checked'] = '';
		      $caseTypesArray[$caseTypeID]['draw'] = 0;
		  }
		}
	}
	
	return $caseTypesArray;
}

function GetCasesByCaseStatusGraph($con, $hide_closed_cases, $caseTypesArray) {
    global $http_site_root, $tmp_export_directory, $session_user_id;

	$sql1 = <<<SQL
	SELECT case_status_id, case_status_pretty_plural 
	FROM case_statuses 
	WHERE case_status_record_status = 'a'
SQL;
    $draw = array();
	foreach ($caseTypesArray as $caseTypeID => $caseType) {
	   if($caseType['draw'] == 1) {
	       $draw[] = $caseTypeID;  	
		}
	}
	if (count($draw) > 0)
        $sql1 .= "\n AND case_type_id in (".implode($draw,',').")";
	
	if ($hide_closed_cases) $sql1 .= "\n AND status_open_indicator = 'o'";
	
	$sql1 .= "\n ORDER BY case_type_id, sort_order";
	#echo "<pre>$sql1</pre>";
	
	$rst1 = $con->execute($sql1);
	$case_status_count = $rst1->recordcount();
	$graph_legend_array = array();
	$graph_url_array = array();
	$array_of_case_count_values = array();
	$total_case_count = 0;
	
	    $sql2 = <<<SQL
	    SELECT case_status_id, count(*) AS case_count 
	    FROM cases 
	    WHERE case_record_status = 'a'
	    GROUP BY case_status_id
SQL;
#echo "<pre>$sql2</pre>";

	$typeCountArray = $con->GetAssoc($sql2);
	
    while (!$rst1->EOF) {
        $statusID = $rst1->fields['case_status_id'];	
	    $case_count = $typeCountArray[$statusID];
	    if (!$case_count) $case_count = 0;
	    $total_case_count += $case_count;
	    array_push($array_of_case_count_values, $case_count);
	    array_push($graph_legend_array, $rst1->fields['case_status_pretty_plural']);
		array_push($graph_url_array, $http_site_root . '/cases/some.php?cases_case_status_id=' . $statusID);
	
	    $rst1->movenext();
	}
	
	$graph_info = array();
	$graph_info['size_class']   = 'main';
	$graph_info['graph_type']   = 'single_bar';
	$graph_info['data']         = $array_of_case_count_values;
	$graph_info['x_labels']     = $graph_legend_array;
	$graph_info['xaxis_label_angle'] = 30;
	$graph_info['xaxis_font_size'] = 7;
	$graph_info['graph_title']  = $title;
	$graph_info['csim_targets'] = $graph_url_array;
	
	$graph = new BarGraph($graph_info);
	
	$basename = 'cases-by-case-status';
	$filename = "$basename-$session_user_id.jpg";
	
	return $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);
	
}


/**
 * $Log: cases-by-case-status.php,v $
 * Revision 1.12  2008/11/13 09:42:35  metamedia
 * Added "Change Graph" feature. Users can now:
 *
 * 1) Hide or show closed cases, and
 * 2) Choose which case types to graph.
 *
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
