<?php
/**
 *
 * Companies by company source report.
 *
 * $Id: companies-by-company-source.php,v 1.9 2005/03/11 20:28:31 daturaarutad Exp $
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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;


// JNH add for change user
$sqljnh = "select username, user_id from users where user_record_status = 'a' order by username";
$rstjnh = $con->execute($sqljnh);
$user_menu = $rstjnh->getmenu2('user_id',$user_id, false);
$rstjnh->close();

$page_title = _("Companies by Source");
start_page($page_title, true, $msg);

$map_and_image = GetCompaniesByCompanySourceGraph($con, $user_id, $all_users);

$con->close();
?>
<div id="Main">
    <div id="ContentFullWidth">
        <table class=widget cellspacing=1>
        <tr>
              <th class=widget_header><?php echo _("Companies by Source"); ?></th>
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

function GetCompaniesByCompanySourceGraph($con, $user_id, $all_users) {

    global $http_site_root, $tmp_export_directory, $session_user_id;
	
	$sql1 = "select company_source_id, company_source_pretty_name from company_sources where company_source_record_status = 'a'";
	$rst1 = $con->execute($sql1);

	$company_source_count = $rst1->recordcount();
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
	
	    $sql2 = "SELECT count(*) AS company_count 
	    from companies 
	    where company_source_id = " . $rst1->fields['company_source_id'];
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
	    array_push($graph_legend_array,$rst1->fields['company_source_pretty_name']);
		array_push($graph_url_array, $http_site_root . '/companies/some.php?company_source_id=' . $rst1->fields['company_source_id']);
	
	    // calcul de la chaine la plus longue
	    if (strlen($rst1->fields['company_source_pretty_name'])>$size_max_string )
	    {
	       $size_max_string= strlen($rst1->fields['company_source_pretty_name']);
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
	$con->close();
	
	$graph_info = array();
	$graph_info['size_class']   = 'main';
	$graph_info['bar_type']     = 'single';
	$graph_info['data']         = $array_of_company_count_values;
	$graph_info['x_labels']     = $graph_legend_array;
	$graph_info['xaxis_label_angle'] = 30;
	$graph_info['xaxis_font_size'] = 7;
	$graph_info['graph_title']  = $title;
	$graph_info['csim_targets'] = $graph_url_array;
	
	
	$graph = new BarGraph($graph_info);
	
	$basename = 'companies-by-crm-status';
	$filename = $basename .'-'. $session_user_id;
	
	return $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);

}



/**
 * $Log: companies-by-company-source.php,v $
 * Revision 1.9  2005/03/11 20:28:31  daturaarutad
 * updated to support client side image maps
 *
 * Revision 1.8  2005/03/09 21:06:08  daturaarutad
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
