<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-graph.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$user_id = $_POST['user_id'];

$sql = "select username from users where user_id = $user_id";
$rst = $con->execute($sql);
$username = $rst->fields['username'];
$rst->close();

$sql1 = "select activity_type_id, activity_type_pretty_plural from activity_types where activity_type_record_status = 'a'";
$rst1 = $con->execute($sql1);
$graph_legend_array = array();

while (!$rst1->EOF) {
    
    for ($i = 0; $i <= 12; $i++) {
	    
    	$start_date = date("Y-m-d", mktime(0,0,0, date('m') - $i, 1,date('Y')));
	    $end_date = date("Y-m-d", mktime(0,0,0, date('m') - $i + 1, 1,date('Y')));
        
        $sql2 = "SELECT count(*) AS activity_count from activities where user_id = $user_id and activity_type_id = " . $rst1->fields['activity_type_id'] . " and entered_at between " . $con->qstr($start_date, get_magic_quotes_gpc()) . " and " . $con->qstr($end_date, get_magic_quotes_gpc());
        $rst2 = $con->execute($sql2);
	    	
       	if ($rst2) {
        	$activity_count = $rst2->fields['activity_count'];
	   	    $rst2->close();
       	}
        
       	if (!$activity_count) {
	       	$activity_count = 0;
       	}
        
	    $array_of_activity_count_values_for_one_user[$i] = $activity_count;
        
    }
    
	$graph_rows .= "g.addRow(" . implode(',', array_reverse($array_of_activity_count_values_for_one_user)) . ");\n";
    array_push($graph_legend_array, "'" . $rst1->fields['activity_type_pretty_plural'] . "'");
    $rst1->movenext();
	
}

$rst1->close();
$con->close();

$page_title = "Activity Summary : $username";
start_page($page_title, true, $msg);

?>

<SCRIPT LANGUAGE="JavaScript1.2" SRC="../../js/graph.js"></SCRIPT>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=75% valign=top>

		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header>Activity Summary</td>
			</tr>
			<tr>
				<td class=widget_label_center>Activity Summary</td>
			</tr>
			<tr>

				<td class=widget_content_graph>
				<SCRIPT LANGUAGE="JavaScript1.2">
				var g = new Graph(<?= $report_graph_width ?>,<?= $report_graph_height ?>);
                <?= $graph_rows ?>
				g.scale = 1;
				g.setXScaleValues(<?= list_of_months() ?>);
				g.stacked = true;
				g.setLegend(<?= implode(',', $graph_legend_array) ?>);
				g.build();
				</SCRIPT>
				</td>
				
			</tr>

		</table>
		
		</td>
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		<!-- right column //-->
		<td class=rcol width=23% valign=top>

		</td>
	</tr>
</table>

<? end_page(); ?>