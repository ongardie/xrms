<?php
/**
 *
 * Companies by industry report.
 *
 * $Id: companies-by-industry.php,v 1.13 2006/01/02 23:46:52 vanmer Exp $
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
$user_id = $_GET['user_id'];
$all_users = $_GET['all_users'];

if (!$user_id)
{ 
	$all_users = true;	
}

$page_title = _("Companies by Industry");
start_page($page_title, true, $msg);


$con = get_xrms_dbconnection();
// $con->debug = 1;


$user_menu = get_user_menu($con, $user_id);

$map_and_image = GetCompaniesByIndustryGraph($con, $user_id, $all_users);


?>

<div id="Main">
    <div id="ContentFullWidth">
        <table class=widget cellspacing=1>
        <tr>
              <th class=widget_header><?php echo  _("Companies by Industry"); ?></th>
        </tr>
        <tr>
            <td class=widget_content_graph>
				<?php echo $map_and_image; ?>
            </td>
        </tr>
        </table>
<?php
    echo "
    <table>
    <form method=get>
    <tr>
        <th>" . _("User") . "</th>
        <th></th>
    </tr>
    <tr>
            <td>" . $user_menu . "</td>
            <td>
                <input class=button type=submit value=";
    echo "\"";
    echo _("Change Graph");
    echo "\"";
    echo ">
            </td>
    </tr>
    <tr>
       <td>
                <input name=all_users type=checkbox ";

    if ($all_users) {
        echo "checked";
    }

    echo ">" . _("All Users") . "
       </td>
            <td>
            </td>
    </tr>
    </form>
    ";

?>

        </table>
    </div>
</div>
<?php
end_page();


function GetCompaniesByIndustryGraph($con, $user_id, $all_users) {

    global $http_site_root, $tmp_export_directory, $session_user_id;
	
	$sql1 = "select industry_id, industry_short_name, industry_pretty_plural from industries where industry_record_status = 'a'";
	$rst1 = $con->execute($sql1);
	$industry_count = $rst1->recordcount();
	$graph_legend_array = array();
	$array_of_company_count_values = array();
	$graph_url_array = array();
	$total_company_count = 0;
	$size_max_string = 0;
	
	// JNH
	if (($user_id) && (!$all_users)) {
	      $userArray = array($user_id);
	}
	
	
	while (!$rst1->EOF) {
	
	    $sql2 = "select count(*) AS company_count from companies where company_record_status = 'a' and industry_id = " . $rst1->fields['industry_id'];
	      if ($user_id)
	      {
	         $sql2 .= " and user_id =" . $user_id;
	      }
	
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
	    array_push($graph_legend_array, $rst1->fields['industry_pretty_plural']);
		array_push($graph_url_array, $http_site_root . '/companies/some.php?industry_id=' . $rst1->fields['industry_id']);

	    // calcul de la chaine la plus longue
	    if (strlen($rst1->fields['industry_pretty_plural'])>$size_max_string )
	    {
	       $size_max_string= strlen($rst1->fields['industry_pretty_plural']);
	    }
	    $rst1->movenext();
	
	}
	$rst1->close();
	
	$title = $total_company_count . " ";
	$title .= _("Companies ");
	$title .= _(" - ");

	if (!$all_users)
	{
	   if($user_id)
	   {
	    $sql = "select last_name,first_names from users where user_id = $user_id";
	    $rst = $con->SelectLimit($sql, 1, 0);
	    if ($rst)
	    {
	         $last_name = $rst->fields['last_name'];
	         $first_name = $rst->fields['first_names'];
	    }
	    $rst->close();
	    $title .= $first_name;
	    $title .= " ";
	    $title .= $last_name;
	
	   }
	}
	else
	{
	   $title .= _("All Users");
	}
	
	$graph_info = array();
	$graph_info['size_class']   = 'main';
	$graph_info['graph_type']   = 'single_bar';
	$graph_info['data']         = $array_of_company_count_values;
	$graph_info['x_labels']     = $graph_legend_array;
	$graph_info['xaxis_label_angle'] = 30;
	$graph_info['xaxis_font_size'] = 7;
	$graph_info['graph_title']  = $title;
    $graph_info['csim_targets'] = $graph_url_array;

	
	$graph = new BarGraph($graph_info);

    $basename = 'companies-by-industry';
    $filename = "$basename-$session_user_id.jpg";


    return $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);

}


/**
 * $Log: companies-by-industry.php,v $
 * Revision 1.13  2006/01/02 23:46:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.12  2005/04/05 18:50:16  daturaarutad
 * added .jpg extension to graph images
 *
 * Revision 1.11  2005/04/01 23:43:01  daturaarutad
 * updated for change of bar_type->graph_type
 *
 * Revision 1.10  2005/03/21 13:40:58  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.9  2005/03/11 19:33:04  daturaarutad
 * updated to support client side image maps
 *
 * Revision 1.8  2005/03/09 21:06:12  daturaarutad
 * updated to use Jean-Noel HAYART changes: user filtering
 * updated to use JPGraph bar chart class
 *
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
