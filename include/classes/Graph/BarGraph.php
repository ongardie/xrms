<?php

/**
*	Bar Chart class 
*	This class wraps the JPGraph classes providing a simpler interface for
*	creating bar charts.
*
*	@author daturaarutad
*	$Id: BarGraph.php,v 1.1 2005/03/09 18:28:08 daturaarutad Exp $
*/

if(PHPVERSION() >= 5.0) {
	require_once($include_directory . 'jpgraph-2.x/src/jpgraph.php');
	require_once($include_directory . 'jpgraph-2.x/src/jpgraph_bar.php');
} else {
	require_once($include_directory . 'jpgraph-1.x/src/jpgraph.php');
	require_once($include_directory . 'jpgraph-1.x/src/jpgraph_bar.php');
}

class BarGraph {

var $graph;
var $graph_info;

/**
* @param array contains all of the settings that drive the graph
*/
function BarGraph($graph_info) {

	// graph type
	$this->graph_info 								= array();
	$this->graph_info['bar_colors'] 				= array('darkblue', 'red', 'blue');
	$this->graph_info['scale'] 						= 'textlin';
	$this->graph_info['antialiasing']				= true;
	
	// image settings
	$this->graph_info['margins']					= array(40,30,20,80); // left, right, top, bottom

	// graph settings
	$this->graph_info['graph_margin_color']			= 'white';
	$this->graph_info['graph_title']				= null;
	$this->graph_info['graph_title_color']			= 'black';
	$this->graph_info['graph_title_font']			= FF_ARIAL;
	$this->graph_info['graph_title_style']			= FS_NORMAL;
	$this->graph_info['graph_title_size']			= 8;
	$this->graph_info['graph_show_frame']			= false;
	$this->graph_info['graph_show_box']				= true;

	// x axis
	$this->graph_info['xaxis_color']				= 'darkgray';
	$this->graph_info['xaxis_font']					= FF_ARIAL;
	$this->graph_info['xaxis_font_style']			= FS_NORMAL;
	$this->graph_info['xaxis_font_size']			= 8;
	$this->graph_info['xaxis_label_angle']			= null;
	$this->graph_info['xaxis_label_interval']		= null;
	$this->graph_info['xaxis_label_margin']			= 10;
	$this->graph_info['xaxis_hide_ticks_major']		= true;
	$this->graph_info['xaxis_hide_ticks_minor']		= false;
	$this->graph_info['xaxis_tick_interval_step']	= 1;
	$this->graph_info['xaxis_tick_interval_start']	= 0;
	$this->graph_info['xaxis_position']		= 'min'; // "min" will position the x-axis at the minimum value of the Y-axis

	// y axis
	$this->graph_info['yaxis_color']				= 'darkgray';
	$this->graph_info['yaxis_font']					= FF_ARIAL;
	$this->graph_info['yaxis_font_style']			= FS_NORMAL;
	$this->graph_info['yaxis_font_size']			= 8;

	// y grid
	$this->graph_info['ygrid_show_major']			= true;
	$this->graph_info['ygrid_show_minor']			= false;
	$this->graph_info['ygrid_line_style']			= 'solid';
	$this->graph_info['ygrid_color']				= 'lightgray';

	// x grid
	$this->graph_info['xgrid_fill']					= array(true,'#EEEEEE@0.5','#FFFFFF@0.5');
	$this->graph_info['xgrid_show_major']			= true;
	$this->graph_info['xgrid_show_minor']			= false;
	$this->graph_info['xgrid_line_style']			= 'dashed';
	$this->graph_info['xgrid_color']				= 'lightgray';


	// now overwrite them with user specified settings
	$this->graph_info = array_merge($this->graph_info, $graph_info);
/*
	echo '<pre>';
	print_r($this->graph_info);
	echo '</pre>';
	*/

}


/**
* private member called by Display() to set up the JPGraph objects and initialize
* them appropriately
*/
function CreateGraph() {

	// fragile
	if(is_array($this->data)) {
		$rows = count($this->data[0]);
	} else {
		$rows = count($this->data);
	}

	switch($this->graph_info['size_class']) {
		case 'main':
			$width = 750;
			$height = 400; // + 30 + 15; 
			//$height = $rows * 35; // + 30 + 15; 
			break;
		default:
			break;
	}

	$this->graph = new Graph($width, $height, 'auto');

	// create the plots and add them to the graph
	switch($this->graph_info['bar_type']) {
		case 'single':

			$plot = new BarPlot($this->graph_info['data']);
			$plot->SetFillColor($this->graph_info['bar_colors'][0]);

			$this->graph->Add($plot);
			break;

		case 'grouped':
			// Create the bar plots
			$plots = array();
			if(is_array($this->graph_info['data'])) {
				$plots = array();
				$i=0;
				foreach($this->graph_info['data'] as $plot_data) {
					$plot = new BarPlot($plot_data);
					$plot->SetFillColor($this->graph_info['bar_colors'][$i]);
					if(is_array($this->graph_info['legend'])) {
						$plot->SetLegend($this->graph_info['legend'][$i]);
					}

					$plots[] = $plot;
					$i++;
				}
				$this->graph->Add(new GroupBarPlot($plots));
			}
			break;

		case 'accumulated':
			// Create the bar plots
			$plots = array();
			if(is_array($this->graph_info['data'])) {
				$plots = array();
				$i=0;
				foreach($this->graph_info['data'] as $plot_data) {
					$plot = new BarPlot($plot_data);
					$plot->SetFillColor($this->graph_info['bar_colors'][$i]);
					if(is_array($this->graph_info['legend'])) {
						$plot->SetLegend($this->graph_info['legend'][$i]);
					}

					$plots[] = $plot;
					$i++;
				}
				$this->graph->Add(new AccBarPlot($plots));
			}
			break;
	}

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

	// Set up Y-axis
	$this->graph->yaxis->SetColor($this->graph_info['yaxis_color']);
	$this->graph->yaxis->SetFont($this->graph_info['yaxis_font'], $this->graph_info['yaxis_font_style'], $this->graph_info['yaxis_font_size']);
	$this->graph->yaxis->title->Set($this->graph_info['y_title']);

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

// seems like a good cantidate for inheritance
function Display() {
	$this->CreateGraph();
	$this->graph->Stroke();
}

}

/**
* $Log: BarGraph.php,v $
* Revision 1.1  2005/03/09 18:28:08  daturaarutad
* new file for creating Bar graphs
*
*/


?>
