<?php
/**
 *
 * Companies by crm status report.
 *
 * $Id: companies-by-crm-status.php,v 1.10 2005/03/15 01:28:56 daturaarutad Exp $
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

$page_title = _("Companies by CRM Status");
start_page($page_title, true, $msg);

// jnh
$userArray = array();
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

// JNH add for change user
$sqljnh = "select username, user_id from users where user_record_status = 'a' order by username";
$rstjnh = $con->execute($sqljnh);
$user_menu = $rstjnh->getmenu2('user_id',$user_id, false);
$rstjnh->close();


$map_and_image = GetCompaniesByCRMStatusGraph($con, $user_id, $all_users);

?>

<div id="Main">
    <div id="ContentFullWidth">
        <table class=widget cellspacing=1>
        <tr>
              <th class=widget_header><?php echo _("Companies by CRM Status"); ?></th>
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
            </td>
    </tr>
    </form>
        </table>
    </div>
</div>

<?php

end_page();




function GetCompaniesByCRMStatusGraph($con, $user_id, $all_users) {

	global $http_site_root, $tmp_export_directory, $session_user_id;

	// calcul le tableau des valeurs verticales
	$sql1 = "select crm_status_id, crm_status_pretty_plural from crm_statuses where crm_status_record_status = 'a'";
	$rst1 = $con->execute($sql1);
	$graph_legend_array = array();
	$array_of_company_count_values = array();
	$graph_url_array = array();
	$total_company_count = 0;
	$size_max_string = 0;

	// JNH
	if (($user_id) && (!$all_users)) {
		  $userArray = array($user_id);
	}

	while (!$rst1->EOF) 
	{
		$sql2 = "SELECT count(*) AS company_count from companies where company_record_status = 'a' and crm_status_id = " . $rst1->fields['crm_status_id'];
		  if ($user_id) 
		  {
			 $sql2 .= " and user_id =" . $user_id;     
		  }

		$rst2 = $con->execute($sql2);

		if ($rst2) {
			$company_count = $rst2->fields['company_count'];
			$crm_status_id = $rst1->fields['crm_status_id'];
		}

		if (!$company_count) {
			$company_count = 0;
		}
		$total_company_count += $company_count;
		array_push($array_of_company_count_values, $company_count);
		array_push($graph_url_array, $http_site_root . '/companies/some.php?companies_crm_status_id=' . $crm_status_id);
		array_push($graph_legend_array, $rst1->fields['crm_status_pretty_plural']);
		// calcul de la chaine la plus longue
		if (strlen($rst1->fields['crm_status_pretty_plural'])>$size_max_string )
		{
		   $size_max_string= strlen($rst1->fields['crm_status_pretty_plural']);
		}
		$rst2->close();

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
	$graph_info['graph_title']  = $title;
	$graph_info['csim_targets'] = $graph_url_array;

	$graph = new BarGraph($graph_info);

	$basename = 'companies-by-crm-status';
	$filename = $basename .'-'. $session_user_id;

	return $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);
}


/**
 * $Log: companies-by-crm-status.php,v $
 * Revision 1.10  2005/03/15 01:28:56  daturaarutad
 * moved graphing code back into original files
 *
 * Revision 1.8  2005/03/09 21:06:11  daturaarutad
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
