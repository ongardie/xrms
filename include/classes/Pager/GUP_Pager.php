<?php
/**
 *
 * The Grand Unified Pager
 *
 * This pager was originally an extension of the adodb-pager.inc.php class that comes
 * with ADOdb for PHP.  It has since taken on a few extra features that are described
 * in this file.
 * 
 * The pager can get the data to be displayed from two sources
 * -SQL Query passed in constructor
 * -PHP Array passed in constructor
 * 
 * This pager can be passed an SQL query with the option to have a callback called for each row, 
 * or an array of data may be passed in.
 *
 * The pager by default handles the caching of data and users may flush the cache or ask the pager
 * to watch variables to see if they have changed, triggering a cache flush.
 *
 * Information about the columns to display in the pager is passed in as a structured array, which
 * is described in the constructor docs below.
 * 
 * The pager creates many hidden form variables as well as some javascript code in order to track
 * the sort order and etc.  These variables are prepended with the pager_id to avoid naming collisions.
 *
 * Note: for the examples below you can run the programs (you need ACL role of Administrator) by 
 * navigating to xrms/include/classes/Pager/examples/
 *
 * @example GUP_Pager.doc.1.php Simple example of basic pager usage with SQL
 *  
 * @example GUP_Pager.doc.2.php Another pager example showing Totals and SubTotals columns
 *  
 * @example GUP_Pager.doc.3.php Another pager example showing the use of types (type => currency)
 *  
 * @example GUP_Pager.doc.4.php Another pager example showing Calculated Columns and callback usage
 *  
 * @example GUP_Pager.doc.5.php Simple example of basic pager usage with Data
 *  
 * @example GUP_Pager.doc.6.php Another pager example showing Grouping of SQL and Calculated data
 *  
 * @example GUP_Pager.doc.7.php Another pager example showing Caching 
 *  
 * $Id: GUP_Pager.php,v 1.14 2005/03/15 22:28:50 daturaarutad Exp $
 */


class GUP_Pager {

	var $SubtotalColumns;
	var $TotalColumns;

    var $db;    // ADODB connection object
    var $sql;   // sql used
    var $rs;    // recordset generated
    var $curr_page; // current page number before Render() called, calculated in constructor
    var $rows;      // number of rows per page
    var $linksPerPage=10; // number of links per page in navigation bar

    var $gridAttributes = 'width="100%" border=1 bgcolor=white';

    // Localize text strings here
    var $first = '<code>|&lt;</code>';
    var $prev = '<code>&lt;&lt;</code>';
    var $next = '<code>>></code>';
    var $last = '<code>>|</code>';
    var $moreLinks = '...';
    var $startLinks = '...';
    var $gridHeader = false;
    var $htmlSpecialChars = false;
    var $selected_column = 1;
    var $selected_column_html = '*';
    var $page;
    var $cache = 0;  #secs to cache with CachePageExecute()
	// adodb_pager code end

    var $pager_id;
    var $EndRows;
    var $maximize;


	var $get_only_visible = false; // used internally; whether or not to get the whole sql dataset
	var $use_cached = true; 	// whether or not to use the cache
	var $using_cached = false;	// whether or not we are currently using cached data
	var $group_mode = false;
	var $buffer_output;
	var $rows_displayed = 0;


    /**
    * see @example for details
    *
    * @param object an ADOdb connection object
    * @param string SQL query
    * @param array Numeric or Assoc. Array containing the data
    * @param string Caption to display in pager header
    * @param string ID of the form that contains this pager (for javascript document.form_id.element)
    * @param string ID of the pager, especially useful when there are multiple pagers on a page
    * @param array Structured array for the column info like: 
    *   $columns[] = array('name' => 'Long Value', 'index' => 'long_value', 'type' => 'currency', 'subtotal' => true);
    *
    *   name = the header name of this column
    *   index = where to find this field in a row.  numeric or associative will work.
    *   type = currency will use number_format to display dollar values
    *   subtotal = true will subtotal the columns on the visible page
    *   total = true will total all columns 
	*
    */
    function GUP_Pager(&$db, $sql, $data, $caption, $form_id, $pager_id='gup_pager', $column_info, $use_cached = true, $buffer_output = false)
    {
        global $http_site_root;

		if(empty($sql) && !isset($data)) {
			echo _('Warning: GUP_Pager must be passed either an SQL query or a data array');
			return false;
		}


      	$this->db 			= $db;
      	$this->sql 			= $sql;
        $this->data 		= $data;
        $this->caption 		= $caption;
        $this->form_id 		= $form_id;
        $this->pager_id		= $pager_id;
        $this->column_info 	= $column_info;
		$this->use_cached	= $use_cached;
 		$this->page			= _('Page');
		$this->buffer_output=$buffer_output;

        // get CGI vars
        getGlobalVar($this->sort_column, $pager_id . '_sort_column');
        getGlobalVar($this->current_sort_column, $pager_id . '_current_sort_column');
        getGlobalVar($this->sort_order, $pager_id . '_sort_order');
        getGlobalVar($this->current_sort_order, $pager_id . '_current_sort_order');
        getGlobalVar($this->next_page, $pager_id . '_next_page');
        getGlobalVar($this->resort, $pager_id . '_resort');
        getGlobalVar($this->maximize, $pager_id . '_maximize');
		getGlobalVar($this->group_mode, $this->pager_id . '_group_mode');
		getGlobalVar($this->group_id, $this->pager_id . '_group_id');
		getGlobalVar($refresh, $this->pager_id . '_refresh');

		if($refresh) { $this->use_cached	= false; }
		// don't use the cache if we are in sql-only mode (no data or callbacks)
		if(!isset($data)) { $this->use_cached	= false; }

		if(!is_numeric($this->group_mode)) { unset($this->group_mode); }
		if(isset($this->group_mode)) { $this->maximize = true; }

		// begin sort stuff
        if (!strlen($this->sort_column) > 0) {
			for($i=0; $i<count($this->column_info); $i++) {
				if($this->column_info[$i]['default_sort']) {
            		$this->sort_column = $i+1;
            		$this->sort_order = $this->column_info[$i]['default_sort'];
				}
			}
			if (!strlen($this->sort_column) > 0) {
            	$this->sort_column = 1;
            	$this->sort_order = "asc";
			}
            $this->current_sort_column = $this->sort_column;
        }

        if (!($this->sort_column == $this->current_sort_column)) {
            $this->sort_order = "asc";
        }

        $opposite_sort_order = ($this->sort_order == "asc") ? "desc" : "asc";
        $this->sort_order = (($this->resort) && ($this->current_sort_column == $this->sort_column)) ? $opposite_sort_order : $this->sort_order;

        $ascending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/asc.gif" alt="">';
        $descending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/desc.gif" alt="">';
        $this->pretty_sort_order = ($this->sort_order == "asc") ? $ascending_order_image : $descending_order_image;

		// here we add the ORDER BY clause to the SQL query.
		// this is done seperately for grouping later because we don't know enough yet to contruct it for grouping
		if($this->column_info[$this->sort_column-1]['index_sql']) {
			$this->SetUpSQLOrderByClause();
		}

		//echo $this->sql;
		// end sort stuff

		// this is so that we can refer to all columns by ['index'] later when it doesn't concern us if they are sql/calc/data
		foreach($this->column_info as $k => $column) {
			if(isset($column['index_sql'])) $this->column_info[$k]['index'] = $column['index_sql'];
			if(isset($column['index_calc'])) $this->column_info[$k]['index'] = $column['index_calc'];
			if(isset($column['index_data'])) $this->column_info[$k]['index'] = $column['index_data'];
		}


        // store current page in session
        if (isset($this->next_page)) {
            $_SESSION[$pager_id . '_' . $curr_page] = $this->next_page;
        }
        if (empty($_SESSION[$pager_id . '_' . $curr_page])) $_SESSION[$pager_id . '_' . $curr_page] = 1; ## at first page

        $this->curr_page = $_SESSION[$pager_id . '_' . $curr_page];
        // end sorted columns stuff


        // Init the Subtotal and Total column arrays
        foreach($this->column_info as $k => $column_header) {
            if($column_header['subtotal']) {
                $this->SubtotalColumns[$column_header['index']] = 0;
            }
            if($column_header['total']) {
                $this->TotalColumns[$column_header['index']] = 0;
            }
        }
		
      	$this->selected_column = $this->sort_column-1;
      	$this->selected_column_html = $this->pretty_sort_order;

	} // end constructor


    /**
	* public method called by user to render the pager
	*
	* @param integer number of rows to display in this pager
	*/
	function Render($rows=10) {

		if($this->buffer_output) {
			ob_start();
		}

		echo "<a name=\"{$this->pager_id}\"></a>\n";

		// output the Javascript functions for sorting and submitting
		$this->Render_JS();

		// adodb_pager code begin
        global $ADODB_COUNTRECS;

        if($this->maximize) {
            $this->rows = 10000; // 10000 per page should be enough for anyone's browser
        } else {
            $this->rows = $rows;
        }


		$this->GetData();

		//print_r($this->data);

		// if group mode or there is data and we're not at the first and last page simultaneously (only one page!)
        if (isset($this->group_mode)) {
			// makes Group: <select> [ungroup button]
        	$page_nav = $this->column_info[$this->group_mode]['name'] . ':' ;
        	$page_nav .= $this->group_select_widget;
        	$page_nav .= "<input type=button class=button onclick=\"javascript:{$this->pager_id}_ungroup({$this->group_mode});\" value=\"" . _('Ungroup') . '">';

		} elseif($this->data && (!$this->AtFirstPage || !$this->AtLastPage)) {
        	$page_nav = $this->RenderNav();
        	$page_count = $this->RenderPageCount();
        } else {
        	$page_nav = "&nbsp;";
        	$page_count = "&nbsp;";
		}

        $grid = $this->RenderGrid();

        $this->RenderLayout($page_nav,$grid,$page_count);

		global $xrms_plugin_hooks;

		// add the hook to include the JS for the tooltips
		$xrms_plugin_hooks['end_page']['pager'] = 'javascript_tooltips_include';

		if($this->buffer_output) {
			$s =  ob_get_contents();
			ob_end_clean();
			return $s;
		}
	}


	/**
	* private function GetData returns the appropriate data slice we are viewing.
	*
	*/
	function GetData() {
		/* Get the data...
			
		Run the query to get the visible results and then call the callback if it exists

		the query may be modified in several ways:

			-order_by clause to sort sql rows
			-page_execute set to get all (like show_all)
			-where could be set to group (also the sort columns should still work within that page)
	
		Caching...if you do sql query, you only get + calc part of the view
		Once you have a cached dataset, you always work from that, even if the sort column is of type sql.
	
		*/
		$cache_name = $this->pager_id . '_data';

		if(isset($this->group_mode)) {
			/* 
				In group mode, we replace the normal sql query with the one passed in group_query_select
				
				We also take this time to create the <select> widget via ADOdb::GetMenu2()

				Finally, if the column is an sql sortable column, we add the appropriate order by clause to the sql query
			*/
				
			if($this->column_info[$this->group_mode]['group_query_list']) {


				$old_fetch_mode = $this->db->fetchMode;
				$this->db->SetFetchMode(ADODB_FETCH_NUM);

				$group_values = $this->db->execute($this->column_info[$this->group_mode]['group_query_list']);

				$this->db->SetFetchMode($old_fetch_mode); 
	
				if(!$group_values) {
					db_error_handler($this->db, $this->column_info[$this->group_mode]['group_query_list']);
				} else {
					// set group id to first item if none is selected
					if(!$this->group_id) {
						$this->group_id = $group_values->fields[1];
					}
					$this->group_select_widget =  $group_values->GetMenu2($this->pager_id . '_group_id', $this->group_id, false, false, 0, "onchange='javascript:{$this->pager_id}_group(" . $this->group_mode . ");'");
				}

				// change the sql query to the group select
				$this->sql = str_replace('XXX-value-XXX', $this->group_id, $this->column_info[$this->group_mode]['group_query_select']);
				if($this->column_info[$this->sort_column-1]['index_sql']) {
					$this->SetUpSQLOrderByClause();
				}
			}
		}

		if($_SESSION[$cache_name] && $this->use_cached) {
			$this->using_cache = true;
			$this->data = $_SESSION[$cache_name];

		} else {

			// clear the cache
			$_SESSION[$cache_name] = null;

			if($this->sql) {
				//echo htmlentities($this->sql) . '<br/>';
	
				if($this->get_only_visible) {
        			$savec = $ADODB_COUNTRECS;
        			if ($this->db->pageExecuteCountRows) $ADODB_COUNTRECS = true;
        			if ($this->cache)
        				$rs = &$this->db->CachePageExecute($this->cache,$this->sql,$this->rows,$this->curr_page);
        			else
        				$rs = &$this->db->PageExecute($this->sql,$this->rows,$this->curr_page);
        			$ADODB_COUNTRECS = $savec;
		
				} else {
					$rs = &$this->db->Execute($this->sql);
				}

      			$this->rs = &$rs;
       			if (!$rs) {
       				print "<h3>";
       				db_error_handler($this->db, $this->sql);
       				print "</h3>";
       				return;
       			}
		
				if(function_exists($this->data)) {
					$user_data_fn = $this->data;
					$this->data = array();
					while (!$rs->EOF) {
            			$row = call_user_func($user_data_fn, $rs->fields, $this->curr_page, $this->rows, $this->column_info[$this->sort_column-1]['index'], $this->sort_order);
						if($row) {
            				$this->data[] = $row;
						}
						$rs->MoveNext();
					} 
				} else {
					while (!$rs->EOF) {
            			$this->data[] = $rs->fields;
						$rs->MoveNext();
					}
				}
			}
			//sto it in the session cache
		    if(!$this->get_only_visible) {	
				$_SESSION[$cache_name] = $this->data;
			}

		}
		
		// if sort column is one of the calculated ones...do this thingy 
		if($this->using_cache || !$this->get_only_visible) {
			// in the same dir as us...
			require_once('Array_Sorter.php');

			if(count($this->data)) {
            	$sorter = new array_sorter($this->data, $this->column_info[$this->sort_column-1]['index'], ($this->sort_order == "asc") ? true : false);
            	$this->data = $sorter->sortit();
			}

			
            // then output rows 34-43 par example
			$this->AbsolutePage = $this->curr_page;
			$this->LastPageNo 	= (int)((count($this->data) + $this->rows - 1) / $this->rows);
			$this->AtFirstPage 	= (1 == $this->curr_page);
			$this->AtLastPage 	= ($this->LastPageNo <= 1);

			//echo "clause A<br>";
            $this->start_data_row = ($this->curr_page -1) * $this->rows;
            $this->end_data_row = min($this->start_data_row + $this->rows, count($this->data));

		} else {
			if($this->rs) { 
				//echo "clause B<br>";
				$this->AbsolutePage = $rs->AbsolutePage();
				$this->LastPageNo = $rs->LastPageNo();
				$this->AtFirstPage = $rs->AtFirstPage();
				$this->AtLastPage = $rs->AtLastPage();

            	$this->start_data_row = 0;
            	$this->end_data_row = count($this->data);
			} else {
				//echo "clause C<br>";
				$this->AbsolutePage = $this->curr_page;
				$this->LastPageNo 	= (int)((count($this->data) + $this->rows - 1) / $this->rows);
				$this->AtFirstPage 	= (1 == $this->curr_page);
				$this->AtLastPage 	= ($this->LastPageNo <= 1);

            	$this->start_data_row = ($this->curr_page -1) * $this->rows;
            	$this->end_data_row = min($this->start_data_row + $this->rows, count($this->data));
			}
		}
		//echo "rows from {$this->start_data_row} to {$this->end_data_row}<br/>";


		// this builds the group select for a calc'd column
		if(isset($this->group_mode) && $this->column_info[$this->group_mode]['group_calc']) {
			// build the select from the real data

			$unique_ids = array();
			$index = $this->column_info[$this->group_mode]['index'];

	 		for($i=$this->start_data_row; $i<$this->end_data_row; $i++) {
				if(!isset($unique_ids[$this->data[$i][$index]])) { $unique_ids[$this->data[$i][$index]] = 0;  }
				$unique_ids[$this->data[$i][$index]]++;
			}

			$this->group_select_widget = '<select name="' . $this->pager_id . "_group_id\" onchange='javascript:{$this->pager_id}_group(" . $this->group_mode . ");'"; 
			foreach($unique_ids as $id => $v) {

            	if(!isset($this->group_id)) {
					//echo "setting manually to $id";
                	$this->group_id = $id;
            	}

				$this->group_select_widget .= "<option value=$id";
				if($this->group_id == $id) { 
					$this->group_select_widget .= " selected"; 
				}
				$this->group_select_widget .= ">$id ($v)</option>";
			}
			$this->group_select_widget .= '</select>';
		}
		// data is now in $this->data
	}

	/**
	* private function Render_JS outputs the Javascript functions and hidden form variables
	*/
	function Render_JS() {
        echo <<<END
            <script language="JavaScript" type="text/javascript">
            <!--
	        function {$this->pager_id}_refresh() {
                document.{$this->form_id}.{$this->pager_id}_refresh.value = 'true';
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
			}

            function {$this->pager_id}_submitForm(nextPage) {
                document.{$this->form_id}.{$this->pager_id}_next_page.value = nextPage;
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
            }

            function {$this->pager_id}_resort(sortColumn) {
                document.{$this->form_id}.{$this->pager_id}_sort_column.value = sortColumn + 1;
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
                document.{$this->form_id}.{$this->pager_id}_resort.value = 1;
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
            }
            function {$this->pager_id}_maximize() {
                document.{$this->form_id}.{$this->pager_id}_maximize.value = 'true';
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
			}
	        function {$this->pager_id}_unmaximize() {
                document.{$this->form_id}.{$this->pager_id}_maximize.value = null;
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
			}
	        function {$this->pager_id}_group(groupColumn) {
                document.{$this->form_id}.{$this->pager_id}_group_mode.value = groupColumn;
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
			}
	        function {$this->pager_id}_ungroup(groupColumn) {
                document.{$this->form_id}.{$this->pager_id}_group_mode.value = 'ungroup';
                document.{$this->form_id}.{$this->pager_id}_maximize.value = '';
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
			}

            //-->
            </script>

            <input type=hidden name={$this->pager_id}_use_post_vars value=1>
            <input type=hidden name={$this->pager_id}_next_page value="{$this->next_page}">
            <input type=hidden name={$this->pager_id}_resort value="0">
            <input type=hidden name={$this->pager_id}_group_mode value="{$this->group_mode}">
            <input type=hidden name={$this->pager_id}_current_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_current_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_maximize value="{$this->maximize}">
            <input type=hidden name={$this->pager_id}_refresh value="">
END;
	}
    /**
	* private method to render the data portion of the table
	*/
    function RenderGrid()
    {
        ob_start();

        $color_counter = 0;
		$col_classnames = array();

        $column_count = count($this->column_info);
    	$hdr = '';

        // do the headers
        for($i=0; $i<$column_count; $i++) {

        	$group_html = '';

        	if($this->column_info[$i]['group_query_list'] || $this->column_info[$i]['group_calc']) {
            	$group_html = "<a href='javascript: " . $this->pager_id . "_group($i);'><b>(" . _('G') . ")</b></a>";
        	}

			$selected_column_header_html = '';
            if ($i == ($this->sort_column-1)) {
                //echo $this->pretty_sort_order;
				 $selected_column_header_html = $this->pretty_sort_order;
            }

        	if($group_html || $i == ($this->sort_column-1)) {
            	$hdr .= "<td class=widget_label_center><table cellpadding=0 cellspacing=0><tr><td class=widget_label><a href='javascript: " . $this->pager_id . "_resort($i);' ><b>{$this->column_info[$i]['name']}</b></a></td>";
            	$hdr .= "<td class=widget_label>$selected_column_header_html</td><td class=widget_label> $group_html</td></tr></table></td>";
        	} else {
            	$hdr .= "<td class=widget_label_center><a href='javascript: " . $this->pager_id . "_resort($i);' ><b>{$this->column_info[$i]['name']}</b></a>";
        	}

			// set the column css
			if($this->column_info[$i]['css_classname']) {
				$col_classnames[$i] = $this->column_info[$i]['css_classname'];
			} else {
				$col_classnames[$i] = '';
			}

        }

        echo "<tr>$hdr</tr>";

		/*  Grouping:
			Get the index of the column that is being grouped
			Get the value of the thing
			during the loop, check to see if the column matches
			also, this loop could be sped up if you cache $this->column_info[$j]['index'] as $data_index[$j]

		*/
		if($this->column_info[$this->group_mode]['group_calc']) {
			$skip_value = $this->group_id;
		} else {
			$skip_value = null;
		}


        // main data loop
        for($i=$this->start_data_row; $i<$this->end_data_row; $i++) {

			// in group mode, skip this value if it's not one we're interested in.
			if($skip_value && $skip_value != $this->data[$i][$this->column_info[$this->group_mode]['index']]) {
				continue;
			}

           	$row_classnames = (($color_counter % 2) == 1) ? "widget_content" : "widget_content_alt";

			if($this->data[$i]['Pager_TD_CSS_All_Rows']) {
				$row_classnames .= ' ' . $this->data[$i]['Pager_TD_CSS_All_Rows'];
			} 

            $color_counter++;

            echo  "<tr valign=top>\n";

            for($j=0; $j<$column_count; $j++) {

                if($this->column_info[$j]['type']) {
                    if('currency' == $this->column_info[$j]['type']) {
                        echo "<td class='$row_classnames {$col_classnames[$j]}'>$" . number_format($this->data[$i][$this->column_info[$j]['index']], 2, '.', ',') . "</td>\n";
                    } elseif('date' == $this->column_info[$j]['type']) {
                        echo "<td class='$row_classnames {$col_classnames[$j]}'>" . format_date($this->data[$i][$this->column_info[$j]['index']]) . "</td>\n";
                    } elseif('int' == $this->column_info[$j]['type']) {
                        echo "<td class='$row_classnames {$col_classnames[$j]}'>" . number_format($this->data[$i][$this->column_info[$j]['index']], 0, '.',',') . "</td>\n";
                    } else {
                        echo "<td class='$row_classnames {$col_classnames[$j]}'>" . $this->data[$i][$this->column_info[$j]['index']] . "</td>\n";
                    }
                } else {
                    echo "<td class='$row_classnames {$col_classnames[$j]}'>" . $this->data[$i][$this->column_info[$j]['index']] . "</td>\n";
                }
            }
            echo  "</tr>\n";

            // come mister tally man tally me subtotal columns
            if(is_array($this->SubtotalColumns)) {
                foreach($this->SubtotalColumns as $index => $k) {
                    $this->SubtotalColumns[$index] += $this->data[$i][$index];
                }
            }
			$this->rows_displayed++;
        }

		if($this->rows_displayed > 0) {
        	// tally me totals
        	if(is_array($this->TotalColumns)) {
            	for($i=0; $i< count($this->data); $i++) {
                	foreach($this->TotalColumns as $index => $k) {
                    	$this->TotalColumns[$index] += $this->data[$i][$index];
                	}
            	}
        	}
        	// only do the first one if we're not on the first page
        	$this->RenderTotals(_('Subtotals this page:'), $this->SubtotalColumns, $row_classnames, $col_classnames);
        	$this->RenderTotals(_('Totals:'), $this->TotalColumns, $row_classnames, $col_classnames);
		} else {
			echo '<tr><td colspan="' . $column_count . '" class="widget_content">' . _('No matches') . '</td></tr>';
		}

        $s = ob_get_contents();
        ob_end_clean();

        return $s;
    }



    /**
	* private method to render the top navigation bar
	*/
    function RenderNav()
    {
      ob_start();

      	$this->Render_First();
      	$this->Render_Prev();
	
	  	$this->Render_PageLinks();
	
      	$this->Render_Next();
      	$this->Render_Last();

      $s = ob_get_contents();
      ob_end_clean();

      return $s;
    }

    /**
	* private method to render this part:  |< < 1 2 3 ... > >|
	*/
    function Render_PageLinks()
    {

   		// original code by "Pablo Costa" <pablo@cbsp.com.br>
   		$pages        = $this->LastPageNo;
  		$linksperpage = $this->linksPerPage ? $this->linksPerPage : $pages;
   		for($i=1; $i <= $pages; $i+=$linksperpage)
   		{
      		if($this->AbsolutePage >= $i)
      		{
        		$start = $i;
       		}
   		}
   		$numbers = '';
   		$end = $start+$linksperpage-1;
   		$link = $this->pager_id . '_' . "_next_page";
   		if($end > $pages) $end = $pages;
		
		
   		if ($this->startLinks && $start > 1) {
     		$pos = $start - 1;
       		$numbers .= "<a href='javascript:{$this->pager_id}_submitForm(" . $pos . ");'>" . $this->startLinks . "</a> ";
   		}
		
   		for($i=$start; $i <= $end; $i++) {
       		if ($this->AbsolutePage == $i)
       		$numbers .= "<b>$i</b>  ";
       		else
       		$numbers .= "<a href='javascript:{$this->pager_id}_submitForm(" . $i . ");'>" . $i . "</a> ";
   		}
   		if ($this->moreLinks && $end < $pages){
       		$numbers .= "<a href='javascript:{$this->pager_id}_submitForm(" . $i . ");'>" . $this->moreLinks . "</a>  ";
   		}
   		print $numbers . ' &nbsp; ';
	}


    /**
	* private method to Display link to first page
	*/
    function Render_First()
    {
      if(!$this->AtFirstPage) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(1);"> ' . $this->first . '</a> &nbsp;';
      } else {
        print "$this->first &nbsp; ";
      }
    }
    /**
	* private method to display Link to previous page
	*/
    function Render_Prev($anchor=true)
    {
      if(!$this->AtFirstPage) {
        echo "<a href=\"javascript:{$this->pager_id}_submitForm(" . ($this->AbsolutePage - 1) . " );\">{$this->prev}</a> &nbsp;";
      } else {
        print "$this->prev &nbsp; ";
      }
    }

    /**
	* private method to display Link to next page
	*/
    function Render_Next()
    {
      if (!$this->AtLastPage) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm('. ($this->AbsolutePage + 1) . ');">' . $this->next . '</a> &nbsp;';
      } else {
        print "$this->next &nbsp; ";
      }
    }
    /**
	* private method to display Link to last page
    * for better performance with large recordsets, you can set
    * $this->db->pageExecuteCountRows = false, which disables
    * last page counting.
	*/
    function Render_Last($anchor=true)
    {
      if (!$this->db->pageExecuteCountRows) return;

      if (!$this->AtLastPage) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(' . $this->LastPageNo . ');">' . $this->last . '</a> &nbsp;';
      } else {
        print "$this->last &nbsp; ";
      }
    }

    /**
    * This is the page_count
	*/
    function RenderPageCount()
    {
      if (!$this->db->pageExecuteCountRows) return '';
      $lastPage = $this->LastPageNo;
      // *** updated to return an empty string if there's an empty rs
      if ($lastPage == -1) {
        $lastPage = 1;
        return 'aaa';
      } // check for empty rs.
      if ($this->curr_page > $lastPage) $this->curr_page = 1;
        return "$this->page ".$this->curr_page."/" . $lastPage;
    }
	

	/**
	* this function assembles the html elements for final composition of the pager
	*/
    function RenderLayout($page_nav,$grid,$page_count)
    {
		global $http_site_root;

		$colspan = count($this->column_info);

		if($this->using_cache) {
			$cache_indicator = '<input type="button" class="button" value="(cached)" onclick=javascript:' . $this->pager_id . '_refresh();>';
			$cache_indicator = "<a onmouseover=\"this.T_OFFSETX=-360; this.T_OFFSETY=10; return escape('" . _('Refresh Data') . "')\" href=javascript:{$this->pager_id}_refresh();><img alt=\"" . _('Refresh Pager') . "\" border=0 src=\"$http_site_root/img/refresh.gif\"></a> ";
		} else {
			$cache_indicator = "";
		}

		if(isset($this->group_mode)) {
			$size_buttons = '';
		} else {
			if($this->maximize) {
				$size_buttons =  "<a onmouseover=\"this.T_OFFSETX=-360; this.T_OFFSETY=10; return escape('" . _('Show paged data') . "')\" href=javascript:{$this->pager_id}_unmaximize();><img  alt=\"" . _('Restore Pager') . "\" border=0 src=\"$http_site_root/img/restore.gif\"></a>";
			} else {
				if(!($this->AtFirstPage && $this->AtLastPage)) {
					$size_buttons =  "<a onmouseover=\"this.T_OFFSETX=-360; this.T_OFFSETY=10; return escape('" . _('Show all data in a single page.') . "')\" href=javascript:{$this->pager_id}_maximize();><img alt=\"" . _('Maximize Pager') . "\" border=0 src=\"$http_site_root/img/maximize.gif\"></a>";
				}
			}
		}

       	echo "<!-- Begin Pager -->\n
				<table class=widget cellspacing=1 width=\"100%\">
				<tr><td colspan=$colspan class=widget_header align=left>
					<table width=\"100%\" cellspacing=0 cellpadding=0 border=0>
						<tr><td class=widget_header align=left>{$this->caption}</td>
							<td class=widget_header align=right>{$cache_indicator}{$size_buttons}</td>
						</tr>
					</table>
				</td></tr>\n";

        if ($page_nav != '&nbsp;') {
            echo "<tr><td colspan=$colspan>".
            			"<table border=0 cellpadding=0 cellspacing=0 width=\"100%\">".
            			"<tr><td class=widget_label align=left>$page_count </td><td align=right class=widget_label>$page_nav </td></tr>".
            			"</table>".
            	 "</td></tr>\n";
        }

        echo $grid;

		if($this->EndRows) { echo $this->EndRows; }

        echo "</table>\n<!-- End Pager -->";
    }


	/**
	* public method allows users to add HTML to the bottom of the pager
	* @param string html to append
	*/
    function AddEndRows($html) {
        $this->EndRows = $html;
    }

	/**
	* private method to calculate the totals and subtotals columns
	*/
	function CalculateTotals() {
        $rs = $this->rs;

        // Now we output our subtotal rows 
        if(is_array($this->SubtotalColumns)) {
            // reset the record set pointer
            $rs->Move(0);
            $s .= "<tr>";

            while(!$rs->EOF) {
                foreach($this->SubtotalColumns as $fieldname => $v) {
                    $this->SubtotalColumns[$fieldname] += $rs->Fields($fieldname);
                }
                $rs->MoveNext();
            }
        }
        return $s;
	}

	/**
	* private method to render the totals and subtotals columns
	*/
    function RenderTotals($caption, $values, $row_classnames, $col_classnames) {
        if(0 != count($values)) {

            echo "<tr>";
            echo  "<td class=\"widget_label\"><b>$caption</b></td>";

            // starting with 1 because column 1 is used for the header, so sorry!
            for ($i=1; $i < count($this->column_info); $i++) {

                if(isset($values[$this->column_info[$i]['index']])) {
                    if('currency' == $this->column_info[$i]['type']) {
                        echo "<td class=\"$row_classnames {$col_classnames[$i]}\"><b>$" . number_format($values[$this->column_info[$i]['index']], 2, '.', ',') . "</b></td>\n";
                    } else {
                        echo "<td class=\"$row_classnames {$col_classnames[$i]}\"><b>" . $values[$this->column_info[$i]['index']] . "</b></td>";
                    }
                } else {
                    echo "<td class=\"widget_content_alt\">&nbsp;</td>";
                }
            }
            echo "</tr>";
        }
    }
	/**
	* public method to cause the data to be recalculated
	*/
	function FlushCache() {
		$this->use_cached = false;
	}
	/**
	* public method to force the entire dataset to be retrieved
	*/
	function ForceGetAll() {
		$this->get_only_visible = false;
	}
	/**
	* public method to render the totals and subtotals columns
	* This function takes a CGI variable name and handles the 'watching' by comparing with
	* the values in $_SESSION
	* 
	* @param array plain array of CGI variable names
	*/
	function CheckCacheWatchedCGIVars($vars) {
		$prepend = $this->pager_id . '_pager_session_cache_';
		
		foreach($vars as $var) {
			$v = null;
			getGlobalVar($v, $var);

			if(array_key_exists($prepend . $var, $_SESSION) && $_SESSION[$prepend . $var] != $v) {
				//echo "$var has changed from {$_SESSION[$prepend . $var]} to $v, flushing the cache<br/>";
				$this->use_cached = false;
			}
			$_SESSION[$prepend . $var] = $v;
		}
	}
	/**
	* public method to render the totals and subtotals columns
	* This function takes a variable name and a value and handles the 'watching' by comparing with
	* the values in $_SESSION
	* 
	* @param array assoc array of variable names and their values
	*/
	function CheckCacheWatchedLocalVar($varname, $value) {
		$prepend = $this->pager_id . '_pager_session_cache_';
		
		if(!array_key_exists($prepend . $varname, $_SESSION)) {
			//echo "no entry in cache for $varname<br>";
			$this->use_cached = false;
		}
		if(array_key_exists($prepend . $varname, $_SESSION) && $_SESSION[$prepend . $varname] != $value) {
			//echo "$varname has changed from {$_SESSION[$prepend . $varname]} to $value, flushing the cache<br/>";
			$this->use_cached = false;
		}
		$_SESSION[$prepend . $varname] = $value;
	}
	/**
	*  Private function to append the order by clause to the SQL query
	*/
	function SetUpSQLOrderByClause() {
	
		if(isset($this->column_info[$this->sort_column-1]['sql_sort_column'])) {

			$columns = array();

			// set up the order by 8 desc, 9 desc, etc.
			foreach(explode(',', $this->column_info[$this->sort_column-1]['sql_sort_column']) as $column) {
				$columns[] = $column . " " . $this->sort_order;
			}
			
			$order_by = " order by " . implode(', ', $columns);

		} else {

			$order_by = " order by " . $this->column_info[$this->sort_column-1]['index_sql'] . " " . $this->sort_order;
		} 

		$this->sql .= " $order_by";
		$this->get_only_visible = true;
	}
}

/**
 * $Log: GUP_Pager.php,v $
 * Revision 1.14  2005/03/15 22:28:50  daturaarutad
 * default sql_sort_column to index_sql value if its not set already
 *
 * Revision 1.13  2005/03/07 16:31:02  daturaarutad
 * fixed code for comma delimited sql_sort_column parameter
 *
 * Revision 1.12  2005/03/04 17:55:47  daturaarutad
 * added code to handle default_sort in column_info
 *
 * Revision 1.11  2005/03/01 21:58:36  daturaarutad
 * added functionality to specify CSS classes for rows/columns in pager
 *
 * Revision 1.10  2005/03/01 15:47:53  daturaarutad
 * expanded the ability to specify CSS styles for rows and columns
 *
 * Revision 1.9  2005/02/28 00:00:17  daturaarutad
 * comments on all functions
 *
 * Revision 1.8  2005/02/25 03:42:33  daturaarutad
 * don't use the cache if we are in sql-only mode (no data or callbacks)
 *
 * Revision 1.7  2005/02/24 23:53:26  daturaarutad
 * allow empty $data to be passed in, showing No Matches
 *
 * Revision 1.6  2005/02/24 21:30:40  daturaarutad
 * Fixed up some of the CSS styles
 * Moved the SQL sort order code into a new function
 * Added ability to set custom TD CSS classname for activities pager
 * No longer calling sorter class when there is no data to sort
 *
 * Revision 1.5  2005/02/17 07:59:22  daturaarutad
 * added output buffering to capture echos
 *
 * Revision 1.4  2005/02/15 23:52:17  daturaarutad
 * fixed a small bug with grouping for calculated data
 * added a warning if $sql and $data are both empty
 * added code to show number of rows in the grouping <select> for calc'd data
 * fixed a ADODB_FETCH_MODE bug
 *
 * Revision 1.3  2005/02/11 01:49:03  daturaarutad
 * updated to allow for grouping of calculated (non-sql) columns
 *
 */
?>
