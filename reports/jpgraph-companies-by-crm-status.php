<?php
/**
 *
 * Companies by crm status report.
 *
 * $Id: jpgraph-companies-by-crm-status.php,v 1.1 2005/03/09 21:02:48 daturaarutad Exp $
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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;


// calcul le tableau des valeurs verticales
$sql1 = "select crm_status_id, crm_status_pretty_plural from crm_statuses where crm_status_record_status = 'a'";
$rst1 = $con->execute($sql1);
$graph_legend_array = array();
$array_of_company_count_values = array();
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
        $rst2->close();
    }

    if (!$company_count) {
        $company_count = 0;
    }
    $total_company_count += $company_count;
    array_push($array_of_company_count_values, $company_count);
    array_push($graph_legend_array, $rst1->fields['crm_status_pretty_plural']);
    // calcul de la chaine la plus longue
    if (strlen($rst1->fields['crm_status_pretty_plural'])>$size_max_string )
    {
       $size_max_string= strlen($rst1->fields['crm_status_pretty_plural']);
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
//$graph_info['xaxis_label_angle'] = 30;
//$graph_info['xaxis_font_size'] = 7;
$graph_info['graph_title']  = $title;

$graph = new BarGraph($graph_info);

$graph->Display();

?>
