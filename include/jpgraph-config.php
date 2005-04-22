<?php

function GetJPGraphConfig() {

    // graph type
    $graph_info                               = array();
    $graph_info['bar_colors']                 = array('darkblue', 'red', 'purple','orange','darkgreen','pink','green');
    $graph_info['scale']                      = 'textlin';
    $graph_info['antialiasing']               = false;
    
    // image settings
    $graph_info['margins']                    = array(50,30,20,80); // left, right, top, bottom
    
    // graph settings
    $graph_info['graph_margin_color']         = 'white';
    $graph_info['graph_title']                = null;
    $graph_info['graph_title_color']          = 'black';
    $graph_info['graph_title_font']           = FF_ARIAL;
    $graph_info['graph_title_style']          = FS_NORMAL;
    $graph_info['graph_title_size']           = 8;
    $graph_info['graph_show_frame']           = false;
    $graph_info['graph_show_box']             = true;
    
    // x axis
    $graph_info['xaxis_color']                = 'darkgray';
    $graph_info['xaxis_font']                 = FF_ARIAL;
    $graph_info['xaxis_font_style']           = FS_NORMAL;
    $graph_info['xaxis_font_size']            = 8;
    $graph_info['xaxis_label_angle']          = null;
    $graph_info['xaxis_label_interval']       = null;
    $graph_info['xaxis_label_margin']         = 10;
    $graph_info['xaxis_hide_ticks_major']     = true;
    $graph_info['xaxis_hide_ticks_minor']     = false;
    $graph_info['xaxis_tick_interval_step']   = 1;
    $graph_info['xaxis_tick_interval_start']  = 0;
    $graph_info['xaxis_position']     = 'min'; // "min" will position the x-axis at the minimum value of the Y-axis

    // y axis
    $graph_info['yaxis_color']                = 'darkgray';
    $graph_info['yaxis_font']                 = FF_ARIAL;
    $graph_info['yaxis_font_style']           = FS_NORMAL;
    $graph_info['yaxis_font_size']            = 8;

    // y grid
    $graph_info['ygrid_show_major']           = true;
    $graph_info['ygrid_show_minor']           = false;
    $graph_info['ygrid_line_style']           = 'solid';
    $graph_info['ygrid_color']                = 'lightgray';

    // x grid
    $graph_info['xgrid_fill']                 = array(true,'#EEEEEE@0.5','#FFFFFF@0.5');
    $graph_info['xgrid_show_major']           = true;
    $graph_info['xgrid_show_minor']           = false;
    $graph_info['xgrid_line_style']           = 'dashed';
    $graph_info['xgrid_color']                = 'lightgray';
	
	return $graph_info;
}


?>
