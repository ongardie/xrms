<?php
/**
 *
 * Opportunities quanity by opportunity status report.
 *
 * $Id: opportunities-quantity-by-opportunity-status.php,v 1.14 2006/05/28 17:14:53 jnhayart Exp $
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
$hide_closed_opps = $_GET['hide_closed_opps'];

if ($all_users=="on")
{
   unset ($user_id);   
}

if(!$user_id)
{
	$all_users = true;
}

if (strlen($hide_closed_opps) > 0) {
	$checked_hide_closed_opps = "checked";
	$hide_closed_opps = true;
}
else $hide_closed_opps = false;
 
$con = get_xrms_dbconnection();

$user_menu = get_user_menu($con, $user_id);

$page_title = _("Opportunities by Status");
start_page($page_title, true, $msg);

$map_and_image = GetOpportunitiesQuantityByOpportunityStatusGraph($con, $user_id, $all_users, $hide_closed_opps);


?>

<div id="Main">
    <div id="ContentFullWidth">
        <table class=widget cellspacing=1>
        <tr>
              <th class=widget_header><?php echo _("Opportunities by Status"); ?></th>
        </tr>
        <tr>
            <td class=widget_content_graph>
				<?php echo $map_and_image; ?>
            </td>
        </tr>
        </table>
    <table>
    <form method=get>
    <tr>
        <th><?php echo _("User"); ?></th>
        <th></th>
    </tr>
    <tr>
            <td><?php echo $user_menu; ?></td>
            <td>
                <input class=button type=submit value="<?php echo _("Change Graph"); ?>">
            </td>
    </tr>
    <tr>
       <td>
                <input name=all_users type=checkbox 
<?php
    if ($all_users) {
        echo "checked";
    }

    echo ">" . _("All Users");
?>
       </td>
            <td>
		<input type=checkbox name=hide_closed_opps value="true" <?php echo $checked_hide_closed_opps; ?>>
		<?php echo _("Exclude Closed Opportunities"); ?>
            
            </td>
    </tr>
    </form>
        </table>
    </div>
</div>

<?php

end_page();

function GetOpportunitiesQuantityByOpportunityStatusGraph($con, $user_id, $all_users, $hide_closed_opps) {

    global $http_site_root, $tmp_export_directory, $session_user_id;

	$sql1 = "select opportunity_status_id, opportunity_status_pretty_plural from opportunity_statuses where opportunity_status_record_status = 'a'";
	
	if ($hide_closed_opps)
	   $sql1 .= " and status_open_indicator = 'o'";
	
	$sql1 .= " order by sort_order";
	
	$rst1 = $con->execute($sql1);
	$opportunity_status_count = $rst1->recordcount();
	$graph_legend_array = array();
	$graph_url_array = array();
	$array_of_opportunity_count_values = array();
	$total_opportunity_count = 0;
	$size_max_string = 0;
	
	// JNH
	if (($user_id) && (!$all_users)) {
	      $userArray = array($user_id);
	}
	
	while (!$rst1->EOF) {
	
	    $sql2 = "SELECT count(*) AS opportunity_count
	    from opportunities
	    where opportunity_status_id = " . $rst1->fields['opportunity_status_id'] . "
	    and opportunity_record_status = 'a'";
	
	    if ($user_id)
	    {
	       $sql2 .= " and user_id =" . $user_id;
	    }
	
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
		array_push($graph_url_array, $http_site_root . '/opportunities/some.php?opportunities_opportunity_status_id=' . $rst1->fields['opportunity_status_id']);
	    array_push($graph_legend_array,$rst1->fields['opportunity_status_pretty_plural']);
	    // calcul de la chaine la plus longue
	    if (strlen($rst1->fields['opportunity_status_pretty_plural'])>$size_max_string )
	    {
	       $size_max_string= strlen($rst1->fields['opportunity_status_pretty_plural']);
	    }
	    $rst1->movenext();
	}
	$rst1->close();
	
	
	$title = $total_opportunity_count . " ";
	$title .= _("Opportunities");
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
	
	$con->close();
	
	$graph_info = array();
	$graph_info['size_class']           = 'main';
	$graph_info['graph_type']           = 'single_bar';
	$graph_info['data']                 = $array_of_opportunity_count_values;
	$graph_info['x_labels']             = $graph_legend_array;
	$graph_info['xaxis_label_angle']    = 30;
	$graph_info['xaxis_font_size']      = 7;
	$graph_info['graph_title']          = $title;
    $graph_info['csim_targets'] 		= $graph_url_array;
	
	$graph = new BarGraph($graph_info);

    $basename = 'opportunities-quantitiy-by-opportunity-status';
    $filename = "$basename-$session_user_id.jpg";


    return $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);

}

/**
 * $Log: opportunities-quantity-by-opportunity-status.php,v $
 * Revision 1.14  2006/05/28 17:14:53  jnhayart
 * correct display when on user is selected and all users also
 *
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
 * Revision 1.9  2005/03/11 20:58:56  daturaarutad
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
