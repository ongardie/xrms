<?php
/**
 *
 * Opportunities quanity by opportunity status report.
 *
 * $Id: jpgraph-opportunities-quantity-by-opportunity-status.php,v 1.1 2005/03/09 21:02:48 daturaarutad Exp $
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

if (strlen($hide_closed_opps) > 0) {
	$hide_closed_opps = true;
}
else $hide_closed_opps = false;
 
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql1 = "select opportunity_status_id, opportunity_status_pretty_plural from opportunity_statuses where opportunity_status_record_status = 'a'";

if ($hide_closed_opps) 
   $sql1 .= " and status_open_indicator = 'o'";

$sql1 .= " order by sort_order";

$rst1 = $con->execute($sql1);
$opportunity_status_count = $rst1->recordcount();
$graph_legend_array = array();
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
$graph_info['size_class']   		= 'main';
$graph_info['bar_type']     		= 'single';
$graph_info['data']        			= $array_of_opportunity_count_values;
$graph_info['x_labels'] 	    	= $graph_legend_array;
$graph_info['xaxis_label_angle']	= 30;
$graph_info['xaxis_font_size']  	= 7;
$graph_info['graph_title']     		= $title;

$graph = new BarGraph($graph_info);

$graph->Display();




?>
