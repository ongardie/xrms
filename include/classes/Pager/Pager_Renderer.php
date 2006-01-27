<?php
/**
 * Pager_Renderer class
 *
 * This class handles the display of the pager
 *
 * How it works:
 *
 * 
 *
 * @link http://localhost/xrms/include/classes/Pager/examples/  http://localhost/xrms/include/classes/Pager/examples/
 *
 * @example Pager_Renderer.doc.1.php check out
 *
 * $Id: Pager_Renderer.php,v 1.2 2006/01/27 13:47:38 vanmer Exp $
 */


class Pager_Renderer {

    var $render_data;
    var $pager;

    var $first = '<code>|&lt;</code>';
    var $prev = '<code>&lt;&lt;</code>';
    var $next = '<code>>></code>';
    var $last = '<code>>|</code>';
    var $moreLinks = '...';
    var $startLinks = '...';


    function Pager_Renderer($pager) {
        $this->pager =& $pager;
        $this->render_data =& $pager->render_data;
    }

    function ToHTML($buffer_output = false) {

        $render_data =& $this->render_data;
        if($buffer_output) {
             ob_start();
        } 

        $this->RenderBeginWidget();
        $this->RenderTitle();
        $this->RenderNav();
        $this->RenderGrid();
        //$this->RenderTotals();
        $this->RenderEndRows();
        $this->RenderEndWidget();

        if($buffer_output) {
            $s =  ob_get_contents();
            ob_end_clean();
            return $s;
        }
    }

    function RenderBeginWidget() {
        $render_data =& $this->render_data;

        // anchor for screen positioning
        echo "<a name=\"{$render_data['pager_id']}\"></a>\n";

        // javascript and hidden form fields
        echo $render_data['js_and_hiddens'];

        // begin main table
        echo "<!-- Begin Pager -->\n
              <table class=widget cellspacing=1 width=\"100%\" id=\"{$render_data['pager_id']}_contents\">\n";
    }

    function RenderTitle() {
        $render_data =& $this->render_data;

        if($render_data['show_caption_bar']) {
            echo "
                <tr><td colspan={$render_data['colspan']} class=widget_header align=left>
                    <table width=\"100%\" cellspacing=0 cellpadding=0 border=0>
                        <tr><td class=widget_header align=left>{$render_data['caption']}</td>
                            <td class=widget_header align=right>{$render_data['showhide_link']}{$render_data['cache_indicator']}{$render_data['size_buttons']}</td>
                        </tr>
                    </table>
                </td></tr>\n";
        }
	}

    /**
    * Assumes the following defined in $render_data: page_nav, current_page, last_page, colspan and optionally record_count
    */
    function RenderNav() {
        $render_data =& $this->render_data;

        if ($render_data['page_nav']) {

            $page_count_html = _("Page") . ' ' . $render_data['current_page'] . "/" . $render_data['last_page'];

            if($render_data['record_count'] > -1) {
                $page_count_html .= ' (' . $render_data['record_count'] . ' '. _("records found") . ')';
            }

            echo "<tr><td colspan={$render_data['colspan']} class=widget_label>".
                        "<table border=0 cellpadding=0 cellspacing=0 width=\"100%\">".
                        "<tr><td class=widget_label style=\"text-align: left;\">{$page_count_html} </td><td style=\"text-align: right;\" class=widget_label>{$render_data['page_nav']}</td></tr>".
                        "</table>".
                 "</td></tr>\n";
        }
	}


    function RenderGrid() {
        $render_data =& $this->render_data;

        // first the column headers
        $column_header_html = '';

        global $http_site_root;


        $selected_column_arrow_html = ($render_data['sort_order'] == "asc") ? 
                                ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/asc.gif" alt="">' : 
                                ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/desc.gif" alt="">';





            for($i=0; $i<$render_data['column_count']; $i++) {

                if($render_data['header'][$i]['selected']) {
                    $selected_column_arrow = $selected_column_arrow_html;
                } else {
                    $selected_column_arrow = '';
                }

                $render_data['header'][$i]['col_classname'] = $this->column_info[$i]['css_classname'];

                $column_header_html .= "<td class=widget_label_center>
                                            <table cellpadding=0 cellspacing=0>
                                                <tr><td class=widget_content>{$render_data['header'][$i]['header_text']}</td>
                                                    <td class=widget_content>$selected_column_arrow</td><td class=widget_content> {$render_data['header'][$i]['group_widget']}</td>
                                                </tr>
                                            </table>
                                        </td>";

            }
	
            echo "<tr>$column_header_html</tr>";

            $col_classnames = $render_data['header']['col_classnames'][$i]; 


	        if(count($render_data['rows'])) {	
		        // then the columns themselves
                $color_counter = 0;

		        foreach($render_data['rows'] as $i => $row) {
		            echo  "<tr valign=top>\n";
		
		            // set up row_classnames (alternate colors and also add Pager_TD_CSS_All_Rows if it exists)
                    $row_classnames = (($color_counter++ % 2) == 1) ? "widget_content" : "widget_content_alt";
    
                    if($row['Pager_TD_CSS_All_Rows']) {
                        $row_classnames .= ' ' . $row['Pager_TD_CSS_All_Rows'];
                    } 

		
		            // This is the actual data 
		            for($j=0; $j<$render_data['column_count']; $j++) {
		
		                if($render_data[$j]['data_type']) {
		                    if('currency' == $render_data[$j]['data_type'] AND is_numeric($render_data['rows'][$i]['columns'][$j])) {
		                        echo "<td class='$row_classnames {$col_classnames[$j]}'>$" . number_format($render_data['rows'][$i]['columns'][$j], 2, '.', ',') . "</td>\n";
		                    } elseif('currency_six_places' == $render_data[$j]['data_type'] AND is_numeric($render_data['rows'][$i]['columns'][$j])) {
		                        echo "<td class='$row_classnames {$col_classnames[$j]}'>$" . number_format($render_data['rows'][$i]['columns'][$j], 6, '.', ',') . "</td>\n";
		                    } elseif('date' == $render_data[$j]['data_type']) {
		                        echo "<td class='$row_classnames {$col_classnames[$j]}'>" .  date ( 'Y-m-d' , strtotime($render_data['rows'][$i]['columns'][$j])) . "</td>\n";
		                    } elseif('int' == $render_data[$j]['data_type']) {
		                        echo "<td class='$row_classnames {$col_classnames[$j]}'>" . number_format($render_data['rows'][$i]['columns'][$j], 0, '.',',') . "</td>\n";
		                    } elseif('filesize' == $render_data[$j]['data_type']) {
		                        echo "<td class='$row_classnames {$col_classnames[$j]}'>" . pretty_filesize($render_data['rows'][$i]['columns'][$j], 0, '.',',') . "</td>\n";
		                    } else {
		                        echo "<td class='$row_classnames {$col_classnames[$j]}'>" . $render_data['rows'][$i]['columns'][$j] . "</td>\n";
		                    }
		                } else {
		                    echo "<td class='$row_classnames {$col_classnames[$j]}'>" . $render_data['rows'][$i]['columns'][$j] . "</td>\n";
		                }
		            }
		        }
		        echo  "</tr>\n";
	        } else {
                echo '<tr><td colspan="' . $render_data['column_count'] . '" class="widget_content">' . _('No matches') . '</td></tr>';
            }

            // only do the first one if we're not on the first page
            $this->RenderTotals(_('Subtotals this page:'), $this->pager->SubtotalColumns, $row_classnames, $col_classnames);
            $this->RenderTotals(_('Totals:'), $this->pager->TotalColumns, $row_classnames, $col_classnames);

	}


    /**
    * private method to render the totals and subtotals columns
    */
    function RenderTotals($caption, $values, $row_classnames, $col_classnames) {

        if(0 != count($values)) {

            echo "<tr>";
            echo  "<td class=\"widget_label\"><b>$caption</b></td>";

            // starting with 1 because column 1 is used for the header, so sorry!
            for ($i=1; $i < count($this->pager->column_info); $i++) {
                if(isset($values[$this->pager->column_info[$i]['index']])) {
                    if('currency' == $this->pager->column_info[$i]['data_type']) {
                        echo "<td class=\"$row_classnames {$col_classnames[$i]}\"><b>$" . number_format($values[$this->pager->column_info[$i]['index']], 2, '.', ',') . "</b></td>\n";
                    } else {
                        echo "<td class=\"$row_classnames {$col_classnames[$i]}\"><b>" . $values[$this->pager->column_info[$i]['index']] . "</b></td>";
                    }
                } else {
                    echo "<td class=\"widget_content_alt\">&nbsp;</td>";
                }
            }
            echo "</tr>";
        }
    }



    function RenderEndRows() {
        $render_data =& $this->render_data;

        if($render_data['end_rows']) { echo $render_data['end_rows']; }

	}
    function RenderEndWidget() {
        $render_data =& $this->render_data;
        echo "</table>\n<!-- End Pager -->";

	}



}
/**
 * $Log: Pager_Renderer.php,v $
 * Revision 1.2  2006/01/27 13:47:38  vanmer
 * - added check to ensure that field is numeric before forcing it through numeric format on datatype of currencies
 *
 * Revision 1.1  2006/01/18 21:57:58  daturaarutad
 * move HTML to Pager_Renderer class
 *
 *
 */

?>