<?php

/**
*	Bar Chart class 
*	This class wraps the JPGraph classes providing a simpler interface for
*	creating bar charts.
*
*	@author daturaarutad
*	$Id: BarGraph.php,v 1.8 2005/04/22 20:55:23 daturaarutad Exp $
*/


global $jpgraph_include_directory;

if(PHPVERSION() >= 5.0) {
	$jpgraph_include_directory = $include_directory . 'jpgraph-2.x/';
} else {
	$jpgraph_include_directory = $include_directory . 'jpgraph-1.x/src/';
}
require_once($jpgraph_include_directory . 'jpgraph.php');


// for the label formatting callback 
function yLabelDollars($aLabel) {

  $prefix_arr = array("", "K", "M", "G", "T");
  $value = $aLabel;
  $i=0;
  while (abs($value)>=1000)
  {
     $value /= 1000;
     $i++;
  }
  //$return_str = '$' . round($value, 0).$prefix_arr[$i];
  $return_str = '$' . $value . $prefix_arr[$i];
  return $return_str; 

}



class BarGraph {

var $graph;
var $graph_info;

/**
* @param array contains all of the settings that drive the graph
*/
function BarGraph($graph_info) {
	
	global $include_directory;

	require_once($include_directory . 'jpgraph-config.php');

	// graph type
	$this->graph_info = GetJPGraphConfig();

	// now overwrite them with user specified settings
	$this->graph_info = array_merge($this->graph_info, $graph_info);
}


/**
* private member called by Display() to set up the JPGraph objects and initialize
* them appropriately
*/
function InitGraph() {

	// fragile
	if(is_array($this->data)) {
		$rows = count($this->data[0]);
	} else {
		$rows = count($this->data);
	}

	switch($this->graph_info['size_class']) {
        case 'sidebar': 
            $width = 250;
            $height = 150; // + 30 + 15; 
			$this->graph_info['xaxis_label_angle'] = 90;
			$this->graph_info['legend'] = null;
            //$height = $rows * 35; // + 30 + 15; 
            break;

		case 'main':
			$width = 750;
			$height = 400; // + 30 + 15; 
			//$height = $rows * 35; // + 30 + 15; 
			break;
		default:
			break;
	}

	$this->graph = new Graph($width, $height, 'auto');



	// Set up the graph
	$this->graph->SetScale($this->graph_info['scale']);
	if($this->graph_info['antialiasing']) 
		$this->graph->img->SetAntiAliasing();
	$this->graph->SetMarginColor($this->graph_info['graph_margin_color']);
	if($this->graph_info['graph_title']) 
		$this->graph->title->Set($this->graph_info['graph_title']);
	$this->graph->title->SetFont($this->graph_info['graph_title_font'], $this->graph_info['graph_title_style'], $this->graph_info['graph_title_size']);
	$this->graph->SetFrame($this->graph_info['graph_show_frame']);
	if($this->graph_info['graph_show_box']) 
		$this->graph->SetBox();
	$this->graph->legend->Pos(0.7,0.5); 




	// Set up X-axis
	$this->graph->xaxis->SetColor($this->graph_info['xaxis_color']);
	$this->graph->xaxis->SetFont($this->graph_info['xaxis_font'], $this->graph_info['xaxis_font_style'], $this->graph_info['xaxis_font_size']);
	$this->graph->xaxis->title->Set($this->graph_info['x_title']);

	$this->graph->xaxis->SetTickLabels($this->graph_info['x_labels']);
	if($this->graph_info['xaxis_label_angle'])
		$this->graph->xaxis->SetLabelAngle($this->graph_info['xaxis_label_angle']);
	if($this->graph_info['xaxis_label_interval']) 
		$this->graph->xaxis->SetTextLabelInterval($this->graph_info['xaxis_label_interval']);
	$this->graph->xaxis->SetLabelMargin($this->graph_info['xaxis_label_margin']);
	$this->graph->xaxis->HideTicks($this->graph_info['xaxis_hide_ticks_minor'], $this->graph_info['xaxis_hide_ticks_major']);
	$this->graph->xaxis->SetTextTickInterval($this->graph_info['xaxis_tick_interval_step'], $this->graph_info['xaxis_tick_interval_start']);
	$this->graph->xaxis->SetPos($this->graph_info['xaxis_position']);

	// Note: we use the first item for csim targets!
	if(method_exists($this->graph->xaxis, SetCSIMTargets)) {
		if(is_array($this->graph_info['csim_targets'][0])) {
			$this->graph->xaxis->SetCSIMTargets($this->graph_info['csim_targets'][0], $this->graph_info['csim_alts'][0]); 
		} else {
			$this->graph->xaxis->SetCSIMTargets($this->graph_info['csim_targets'], $this->graph_info['csim_alts']); 
		}
	}

	// Set up Y-axis
	$this->graph->yaxis->SetColor($this->graph_info['yaxis_color']);
	$this->graph->yaxis->SetFont($this->graph_info['yaxis_font'], $this->graph_info['yaxis_font_style'], $this->graph_info['yaxis_font_size']);
	$this->graph->yaxis->title->Set($this->graph_info['y_title']);
	if('dollars' == $this->graph_info['yaxis_label_style']) {
		$this->graph->yaxis->SetLabelFormatCallback('yLabelDollars'); 
	}




	// Set up the X and Y grid
	if($this->graph_info['xgrid_fill']) 
		$this->graph->xgrid->SetFill($this->graph_info['xgrid_fill'][0], $this->graph_info['xgrid_fill'][1], $this->graph_info['xgrid_fill'][2]);

	$this->graph->xgrid->Show($this->graph_info['xgrid_show_major'], $this->graph_info['xgrid_show_minor']);
	$this->graph->xgrid->SetLineStyle($this->graph_info['xgrid_line_style']);
	$this->graph->xgrid->SetColor($this->graph_info['xgrid_color']);

	$this->graph->ygrid->Show($this->graph_info['ygrid_show_major'], $this->graph_info['ygrid_show_minor']);
	$this->graph->ygrid->SetLineStyle($this->graph_info['ygrid_line_style']);
	$this->graph->ygrid->SetColor($this->graph_info['ygrid_color']);

	// Set up the Img margins
	$this->graph->img->SetMargin($this->graph_info['margins'][0], $this->graph_info['margins'][1], $this->graph_info['margins'][2], $this->graph_info['margins'][3]);


}

function InitPlots() {
	global $jpgraph_include_directory;
	// create the plots and add them to the graph
	switch($this->graph_info['graph_type']) {

		case 'single_bar':
		case 'single_line':

			if('single_bar' == $this->graph_info['graph_type']) {
				require_once($jpgraph_include_directory . 'jpgraph_bar.php');
				$plot = new BarPlot($this->graph_info['data']);
			} elseif('single_line' == $this->graph_info['graph_type']) {
				require_once($jpgraph_include_directory . 'jpgraph_line.php');
				$plot = new LinePlot($this->graph_info['data']);
			}

			if(is_array($this->graph_info['legend'])) {
				$plot->SetLegend($this->graph_info['legend']);
			}
			$plot->SetFillColor($this->graph_info['bar_colors'][0]);

			// Set up CSIM stuff
			if($this->graph_info['csim_targets'] || $this->graph_info['csim_alts']) {
				$plot->SetCSIMTargets($this->graph_info['csim_targets'], $this->graph_info['csim_alts']);
			}

			$this->graph->Add($plot);
			break;

		case 'grouped_bar':
		case 'accumulated_bar':
			// Create the bar plots
			$plots = array();

			if(is_array($this->graph_info['data'])) {
				$i=0;
				foreach($this->graph_info['data'] as $plot_data) {

					require_once($jpgraph_include_directory . 'jpgraph_bar.php');
					$plots[$i] = new BarPlot($plot_data);
					$plots[$i]->SetFillColor($this->graph_info['bar_colors'][$i]);

					if(is_array($this->graph_info['legend'])) {
						$plots[$i]->SetLegend($this->graph_info['legend'][$i]);
					}

					// Set up CSIM stuff
					if($this->graph_info['csim_targets'] || $this->graph_info['csim_alts']) {
						$plots[$i]->SetCSIMTargets($this->graph_info['csim_targets'][$i], $this->graph_info['csim_alts'][$i]);

						if(is_array($this->graph_info['legend'])) {
						}
					} else {
					}
					$i++;
				}
				if('grouped_bar' == $this->graph_info['graph_type']) {
					$this->graph->Add(new GroupBarPlot($plots));
				} else {
					$this->graph->Add(new AccBarPlot($plots));
				}
			}
			break;
		case 'multi_line':
			// Create the bar plots
			$plots = array();

			if(is_array($this->graph_info['data'])) {
				$i=0;
				foreach($this->graph_info['data'] as $plot_data) {
					require_once($jpgraph_include_directory . 'jpgraph_line.php');
					$plots[$i] = new LinePlot($plot_data);
					$plots[$i]->SetColor($this->graph_info['bar_colors'][$i]);

					if(is_array($this->graph_info['legend'])) {
						$plots[$i]->SetLegend($this->graph_info['legend'][$i]);
					}

					// Set up CSIM stuff
					if($this->graph_info['csim_targets'] || $this->graph_info['csim_alts']) {
						$plots[$i]->SetCSIMTargets($this->graph_info['csim_targets'][$i], $this->graph_info['csim_alts'][$i]);

						if(is_array($this->graph_info['legend'])) {
						}
					} else {
					}

					$this->graph->Add($plots[$i]);

					$i++;
				}
				
			}
			break;

	}
}

function AddPlotsToGraph() {

}

function Display() {
	$this->InitGraph();
	$this->InitPlots();
	$this->AddPlotsToGraph();

	$this->graph->Stroke();
}

function DisplayCSIM($url, $filename, $map_name, $border = 0) {
	$this->InitGraph();
	$this->InitPlots();
	$this->AddPlotsToGraph();

	$this->graph->Stroke($filename);

	$ret =  $this->graph->GetHTMLImageMap($map_name);
	$ret .= "<img src=\"$url\" usemap=\"$map_name\" border=0>";
	return $ret;
}

}

/**
* $Log: BarGraph.php,v $
* Revision 1.8  2005/04/22 20:55:23  daturaarutad
* moved configuration of graph_info to include/jpgraph-config.php
*
* Revision 1.7  2005/04/02 00:10:13  daturaarutad
* really removed PlotFactory this time and other simplifications
*
* Revision 1.6  2005/04/01 23:00:12  daturaarutad
* removed PlotFactory()
*
* Revision 1.5  2005/03/17 21:02:31  daturaarutad
* check if Axis::SetCSIMTargets exists before calling it
*
* Revision 1.4  2005/03/17 19:59:13  daturaarutad
* added multi-line type and a few assorted tweaks
*
* Revision 1.3  2005/03/11 17:17:36  daturaarutad
* Added Client Side Image Map support
*
* Revision 1.2  2005/03/09 21:09:53  daturaarutad
* updated jpgraph 2.x path
*
* Revision 1.1  2005/03/09 18:28:08  daturaarutad
* new file for creating Bar graphs
*
*/


?>
