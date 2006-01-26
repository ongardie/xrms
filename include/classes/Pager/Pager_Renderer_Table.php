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
 * $Id: Pager_Renderer_Table.php,v 1.1 2006/01/26 17:29:44 daturaarutad Exp $
 */




class Pager_Renderer_Table extends Pager_Renderer {
    var $row_template = "";
	var $main_table_begin = "<!-- Begin Pager -->\n
	              			<table class=widget cellspacing=1 width=\"100%\" id=\"%%pager_id%%_contents\">\n";
	var $main_table_end = "\n</table>\n<!-- End Pager -->";

    function Pager_Renderer_Table(&$pager) {
        $pager->numeric_index = false;
        
        $this->Pager_Renderer(&$pager);
    }

	function SetTemplates($templates) {
		if(isset($templates['row_template'])) $this->row_template = $templates['row_template'];
		if(isset($templates['main_table_begin'])) $this->main_table_begin = $templates['main_table_begin'];
		if(isset($templates['main_table_end'])) $this->main_table_end = $templates['main_table_end'];
	}

	function ReplaceTags($string) {
        $render_data =& $this->render_data;

		$pager_replace = array('pager_id' => $render_data['pager_id'],
							);
		$search = array();
		$replace = array();

		foreach($pager_replace as $k =>$v) {
			$search[] = "/%%$k%%/";
			$replace[] = $v;
		}
		//echo htmlentities("replacing in $string yields " . preg_replace($search, $replace, $string));
		return preg_replace($search, $replace, $string);
	}

	



    function RenderBeginWidget() {
        $render_data =& $this->render_data;

        // anchor for screen positioning
        echo "<a name=\"{$render_data['pager_id']}\"></a>\n";

        // javascript and hidden form fields
        echo $render_data['js_and_hiddens'];

        // begin main table
		echo $this->ReplaceTags($this->main_table_begin);
        //echo "<!-- Begin Pager -->\n
              //<table class=widget cellspacing=1 width=\"100%\" id=\"{$render_data['pager_id']}_contents\">\n";
    }

    function RenderTitle() { }

    /**
    *
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
                        "<tr><td class=widget_label style=\"text-align: left;\"> </td><td style=\"text-align: right;\" class=widget_label>{$render_data['page_nav']}</td></tr>".
                        "</table>".
                 "</td></tr>\n";
        }
	}


    function RenderGrid() {

        $render_data =& $this->render_data;

        // first the column headers
        $column_header_html = '';

        global $http_site_root;

        if(count($render_data['rows'])) {	
	        // then the columns themselves
                $color_counter = 0;

	        foreach($render_data['rows'] as $i => $row) {
	            echo  "<tr valign=top><td class=widget_content>\n";
	
	            	// This is the actual data 
                    if($this->row_template) {
						// escape the " marks
                        $row_template = preg_replace('/"/', '\"', $this->row_template);

                        $cmd = '$row_html = "' . $row_template . '";';

                        $pattern = '/\%\%(\w+)\%\%/i';
                        $replacement = '{$row[\'columns\'][\'$1\']}';
                        $cmd =  preg_replace($pattern, $replacement, $cmd);

                        //echo htmlentities($cmd) . '<br>';

                        eval($cmd);
                        echo $row_html;
                    } else {
	                echo "<td class=''>No template defined in Pager_Renderer_Table</td>\n";
                    }
				echo "</td></tr>";
	        }
        } else {
            echo '<tr><td colspan="' . $render_data['column_count'] . '" class="widget_content">' . _('No news items found.') . '</td></tr>';
        }
	}



    function RenderEndRows() {
        $render_data =& $this->render_data;

        if($render_data['end_rows']) { echo $render_data['end_rows']; }

        $this->RenderNav();

	}
    function RenderEndWidget() {
        $render_data =& $this->render_data;
		echo $this->ReplaceTags($this->main_table_end);
	}



}
/**
 * $Log: Pager_Renderer_Table.php,v $
 * Revision 1.1  2006/01/26 17:29:44  daturaarutad
 * new class extends Pager_Renderer to allow easy templated table display
 *
 *
 */

?>