<?php

require_once('../../../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');

require_once('../BarGraph.php');


$data1 = array();
$data2 = array();

$session_user_id = session_check();

    $graph_info = array();
    $graph_info['size_class']           = 'main';
    $graph_info['graph_type']           = 'single_bar';
    $graph_info['data']                 = array(23,42,5,37);
    $graph_info['x_labels']             = array('one','two','three');
    $graph_info['xaxis_label_angle']    = 30;
    $graph_info['xaxis_font_size']      = 7;
    $graph_info['graph_title']          = 'Test graph';
    $graph_info['csim_targets']         = array('http://www.explodingdog.com', 'http://www.google.com','http://www.slashdot.org','http://www.explodingdog.com');

    $graph = new BarGraph($graph_info);

    $basename = 'opportunities-quantitiy-by-opportunity-status';
    $filename = $basename .'-'. $session_user_id;

    $graph =  $graph->DisplayCSIM($http_site_root . '/export/' . $filename, $tmp_export_directory . $filename , $basename);


?>
<html><body>
CSIM Graph Test
<?php echo $graph; ?>
</body></html>
