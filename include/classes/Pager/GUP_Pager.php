<?php

/**

 The Grand Unified Pager

 Pager for GUP
 based on ADOdb's pager
 (still uses ADOdb's tohtml.php include file)

 see opportunities/some.php for an example of usage


	caching: make a hash of the sql query?  respond to a message to flush the cache!


	// incorrectly storing in cache when it's a non-complete query...







*/

// specific code for tohtml
GLOBAL $gSQLMaxRows,$gSQLBlockRows;

$gSQLMaxRows   = 1000; // max no of rows to download
$gSQLBlockRows = 20; // max no of rows per table block



class GUP_Pager {

	var $SubtotalColumns;
	var $TotalColumns;

	// adodb_pager code begin
    var $id;    // unique id for pager (defaults to 'adodb')
    var $db;    // ADODB connection object
    var $sql;   // sql used
    var $rs;    // recordset generated
    var $curr_page; // current page number before Render() called, calculated in constructor
    var $rows;      // number of rows per page
    var $linksPerPage=10; // number of links per page in navigation bar
    var $showPageLinks;

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
    var $page = 'Page';
    var $cache = 0;  #secs to cache with CachePageExecute()
    var $how_many_rows;
	// adodb_pager code end

    var $pager_id;
    var $EndRows;
    var $maximize;


	var $sql_sort = false;
	var $use_cached = true; 	// whether or not to use the cache
	var $using_cached = false;	// whether or not we are currently using cached data


    /**
    * constructor
    *
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
    */

    function GUP_Pager(&$db, $sql, $data, $caption, $form_id, $pager_id='adodb_pager', $column_info, $use_cached = true)
    {
        global $http_site_root;

      	$this->db 			= $db;
      	$this->sql 			= $sql;
        $this->data 		= $data;
        $this->caption 		= $caption;
        $this->form_id 		= $form_id;
        $this->pager_id		= $pager_id;
        $this->column_info 	= $column_info;
		$this->use_cached	= $use_cached;

        // begin sorted columns stuff
        getGlobalVar($this->sort_column, $pager_id . '_sort_column');
        getGlobalVar($this->current_sort_column, $pager_id . '_current_sort_column');
        getGlobalVar($this->sort_order, $pager_id . '_sort_order');
        getGlobalVar($this->current_sort_order, $pager_id . '_current_sort_order');
        getGlobalVar($this->next_page, $pager_id . '_next_page');
        getGlobalVar($this->resort, $pager_id . '_resort');
        getGlobalVar($this->group, $pager_id . '_group');
        getGlobalVar($this->maximize, $pager_id . '_maximize');

        if (!strlen($this->sort_column) > 0) {
            $this->sort_column = 1;
            $this->current_sort_column = $this->sort_column;
            $this->sort_order = "asc";
        }

        if (!($this->sort_column == $this->current_sort_column)) {
            $this->sort_order = "asc";
        }

        $opposite_sort_order = ($this->sort_order == "asc") ? "desc" : "asc";
        $this->sort_order = (($this->resort) && ($this->current_sort_column == $this->sort_column)) ? $opposite_sort_order : $this->sort_order;

        $ascending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/asc.gif" alt="">';
        $descending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/desc.gif" alt="">';
        $this->pretty_sort_order = ($this->sort_order == "asc") ? $ascending_order_image : $descending_order_image;

		// let the user tell us which column to sort on for this virtual column
		if($this->column_info[$this->sort_column-1]['sql_sort_column']) {
        	$order_by = " order by " . $this->column_info[$this->sort_column-1]['sql_sort_column'];
        	$order_by .= " " . $this->sort_order;
			$this->sql .= " $order_by";
			$this->sql_sort = true;

		} elseif($this->column_info[$this->sort_column-1]['index_sql']) {
        	$order_by = " order by " . $this->sort_column;
        	$order_by .= " " . $this->sort_order;
			$this->sql .= " $order_by";
			$this->sql_sort = true;
		} 
			

		// this is so that we can refer to all columns by ['index'] later when it doesn't concern us
		// if they are sql/calc/data
		foreach($this->column_info as $k => $column) {
			if($column['index_sql']) $this->column_info[$k]['index'] = $column['index_sql'];
			if($column['index_calc']) $this->column_info[$k]['index'] = $column['index_calc'];
			if($column['index_data']) $this->column_info[$k]['index'] = $column['index_data'];
		}

		//echo "sort column:{$this->sort_column}-{$this->column_info[$this->sort_column-1]['index']}<br/>";

        // store current page in session
        if (isset($this->next_page)) {
            $HTTP_SESSION_VARS[$pager_id . '_' . $curr_page] = $this->next_page;
        }
        if (empty($HTTP_SESSION_VARS[$pager_id . '_' . $curr_page])) $HTTP_SESSION_VARS[$pager_id . '_' . $curr_page] = 1; ## at first page

        $this->curr_page = $HTTP_SESSION_VARS[$pager_id . '_' . $curr_page];
        // end sorted columns stuff


        // Set up the Subtotal and Total column arrays
        foreach($this->column_info as $k => $column_header) {
            if($column_header['subtotal']) {
                $this->SubtotalColumns[$column_header['index']] = 0;
            }
            if($column_header['total']) {
                $this->TotalColumns[$column_header['index']] = 0;
            }
        }

		// If we are grouping, get the list of IDs to group on
		

      	$this->showPageLinks = true;
      	$this->selected_column = $this->sort_column-1;
      	$this->selected_column_html = $this->pretty_sort_order;
	} // end GUP_Pager

	function Render($rows=10) {

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


		$data = $this->GetData();

        if ($this->data && (!$this->AtFirstPage || !$this->AtLastPage))
        	$page_nav = $this->RenderNav();
        else
        	$page_nav = "&nbsp;";

        $grid = $this->RenderGrid();
        $page_count = $this->RenderPageCount();
        $this->how_many_rows = count($this->data);
        //$rs->Close();
        //$this->rs = false;

        $this->RenderLayout($page_nav,$grid,$page_count);
	}






	// GetData returns the appropriate data slice we are viewing.
	function GetData() {
		/* Get the data...
			
		Run the query to get the visible results and then call the callback if it exists

		the query may be modified in several ways:

			-order_by clause to sort sql rows
			-page_execute set to get all (like show_all)
			-where could be set to group (also the sort columns should still work within that page)

	
		Caching...if you do sql query, you only get + calc part of the view
		Once you have a cached dataset, you always work from that, even if the sort column is sql.
	
		*/
		$cache_name = $this->pager_id . '_data';

		if($_SESSION[$cache_name] && $this->use_cached) {
			$this->using_cache = true;
			$this->data = $_SESSION[$cache_name];

		} else {

			// clear the cache
			$_SESSION[$cache_name] = null;

			if($this->sql) {
				//echo "{$this->sql}<br/>";
	
				if($this->sql_sort) {
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
				}
			}
			//sto it in the session cache
		    if(!$this->sql_sort) {	
				//echo "storing in the cache as " . $this->pager_id . '_data because sqlsort is ' . $this->sql_sort . "<br>";
				$_SESSION[$cache_name] = $this->data;
			}

		}
		
		// if sort column is one of the calculated ones...do this thingy 
		if($this->using_cache || !$this->sql_sort) {
			// in the same dir as us...
			require_once('Array_Sorter.php');

            $sorter = new array_sorter($this->data, $this->column_info[$this->sort_column-1]['index'], ($this->sort_order == "asc") ? true : false);
            $this->data = $sorter->sortit();

			
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
	}

	function Render_JS() {
        echo <<<END
            <script language="JavaScript" type="text/javascript">
            <!--

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
                document.{$this->form_id}.{$this->pager_id}_group.value = groupColumn;
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
				document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
			}
		
            //-->
            </script>

            <input type=hidden name={$this->pager_id}_use_post_vars value=1>
            <input type=hidden name={$this->pager_id}_next_page value="{$this->next_page}">
            <input type=hidden name={$this->pager_id}_resort value="0">
            <input type=hidden name={$this->pager_id}_group value="">
            <input type=hidden name={$this->pager_id}_current_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_current_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_maximize value="{$this->maximize}">
END;
	}

    //---------------------------------------------------
    // original code by "Pablo Costa" <pablo@cbsp.com.br>
    function Render_PageLinks()
    {
      global $PHP_SELF;
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
        // $numbers .= "<a href=$PHP_SELF?$link=$i>$i</a>  ";

      }
      if ($this->moreLinks && $end < $pages){
        $numbers .= "<a href='javascript:{$this->pager_id}_submitForm(" . $i . ");'>" . $this->moreLinks . "</a>  ";
      }
      print $numbers . ' &nbsp; ';
    }


    function RenderGrid()
    {
        ob_start();

        $color_counter = 0;

        // output headers
        echo "<tr>";
        $column_count = count($this->column_info);
        for($i=0; $i<$column_count; $i++) {
            echo "<td class=widget_label ><a href='javascript: " . $this->pager_id . "_resort($i);'><b>{$this->column_info[$i]['name']}</b></a>";

            if ($i == ($this->sort_column-1)) {
                echo $this->pretty_sort_order;
            }
        }
        echo "</tr>";

        for($i=$this->start_data_row; $i<$this->end_data_row; $i++) {
            $classname = (($color_counter % 2) == 1) ? "widget_content" : "widget_content_alt";
            $color_counter++;

            echo  "<tr valign=top>\n";

            for($j=0; $j<$column_count; $j++) {

                if($this->column_info[$j]['type']) {
                    if('currency' == $this->column_info[$j]['type']) {
                        echo "<td align=right class=$classname>$" . number_format($this->data[$i][$this->column_info[$j]['index']], 2, '.', ',') . "</td>\n";
                    } elseif('date' == $this->column_info[$j]['type']) {
                        echo "<td align=right class=$classname>" . format_date($this->data[$i][$this->column_info[$j]['index']]) . "</td>\n";
                    } elseif('int' == $this->column_info[$j]['type']) {
                        echo "<td align=right class=$classname>" . number_format($this->data[$i][$this->column_info[$j]['index']], 0, '.',',') . "</td>\n";
                    } else {
                        echo "<td align=right class=$classname>" . $this->data[$i][$this->column_info[$j]['index']] . "</td>\n";
                    }
                } else {
                    echo "<td align=right class=$classname>" . $this->data[$i][$this->column_info[$j]['index']] . "</td>\n";

                }
            }
            echo  "</tr>\n";

            // come mister tally man tally me subtotal columns
            if(is_array($this->SubtotalColumns)) {
                foreach($this->SubtotalColumns as $index => $k) {
                    $this->SubtotalColumns[$index] += $this->data[$i][$index];
                }
            }
        }

        // tally me totals
        if(is_array($this->TotalColumns)) {
            for($i=0; $i< count($this->data); $i++) {
                foreach($this->TotalColumns as $index => $k) {
                    $this->TotalColumns[$index] += $this->data[$i][$index];
                }
            }
        }
        // only do the first one if we're not on the first page
        $this->RenderTotals('Subtotals this page:', $this->SubtotalColumns);
        $this->RenderTotals('Totals:', $this->TotalColumns);

        $s = ob_get_contents();
        ob_end_clean();

        return $s;
    }



    //-------------------------------------------------------
    // Navigation bar
    //
    // we use output buffering to keep the code easy to read.
    function RenderNav()
    {
      ob_start();
      if (!$this->AtFirstPage) {
        $this->Render_First();
        $this->Render_Prev();
      } else {
        $this->Render_First(false);
        $this->Render_Prev(false);
      }
	  if($this->group) {
	  	// maybe move this to the render page links function
	  	echo "<select name=bob><option>hi</option><option>mom</option></select>";

	  } else {
      	if ($this->showPageLinks){
        	$this->Render_PageLinks();
      	}
	  }
      if (!$this->AtLastPage) {
        $this->Render_Next();
        $this->Render_Last();
      } else {
        $this->Render_Next(false);
        $this->Render_Last(false);
      }
      $s = ob_get_contents();
      ob_end_clean();
      return $s;
    }
    // Display link to first page
    function Render_First($anchor=true)
    {
      if ($anchor) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(1);"> ' . $this->first . '</a> &nbsp;';
      } else {
        print "$this->first &nbsp; ";
      }
    }
    // Display link to next page
    function Render_Next($anchor=true)
    {
      if ($anchor) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm('. ($this->AbsolutePage + 1) . ');">' . $this->next . '</a> &nbsp;';
      } else {
        print "$this->next &nbsp; ";
      }
    }
    //------------------
    // Link to last page
    //
    // for better performance with large recordsets, you can set
    // $this->db->pageExecuteCountRows = false, which disables
    // last page counting.
    function render_last($anchor=true)
    {
      if (!$this->db->pageExecuteCountRows) return;

      if ($anchor) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(' . $this->LastPageNo . ');">' . $this->last . '</a> &nbsp;';
      } else {
        print "$this->last &nbsp; ";
      }
    }
    // Link to previous page
    function Render_Prev($anchor=true)
    {
      if ($anchor) {
        echo "<a href=\"javascript:{$this->pager_id}_submitForm(" . ($this->AbsolutePage - 1) . " );\">{$this->prev}</a> &nbsp;";
      } else {
        print "$this->prev &nbsp; ";
      }
    }

    //-------------------
    // This is the page_count
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
	

    //------------------------------------------------------
    function RenderLayout($page_nav,$grid,$page_count,$attributes='class=widget cellspacing=1 cellpadding=0 border=0 width="100%"')
    {
		$colspan = count($this->column_info);

		if($this->using_cache) {
			$cache_indicator = "(cached)";
		} else {
			$cache_indicator = "";
		}
		if($this->maximize) {
			$size_buttons =  "<a href=javascript:{$this->pager_id}_unmaximize();>(show paged)</a>";
		} else {
			$size_buttons =  "<a href=javascript:{$this->pager_id}_maximize();>(show all)</a>";
		}

       	echo "<table class=widget cellspacing=1 cellpadding=0 border=0 width=\"100%\">
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

        echo "</table>";
    }



    function AddEndRows($html) {
        $this->EndRows = $html;
    }

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

    function RenderTotals($caption, $values) {
        if(0 != count($values)) {

            echo "<tr>";
            echo  "<td align=right class=\"widget_label\"><b>$caption</b></td>";

            // starting with 1 because column 1 is used for the header, so sorry!
            for ($i=1; $i < count($this->column_info); $i++) {

                if(isset($values[$this->column_info[$i]['index']])) {
                    if('currency' == $this->column_info[$i]['type']) {
                        echo "<td align=right class=\"widget_content_alt\"><b>$" . number_format($values[$this->column_info[$i]['index']], 2, '.', ',') . "</b></td>\n";
                    } else {
                        echo "<td align=right class=\"widget_content_alt\"><b>" . $values[$this->column_info[$i]['index']] . "</b></td>";
                    }
                } else {
                    echo "<td class=\"widget_content_alt\">&nbsp;</td>";
                }
            }
            echo "</tr>";
        }
    }
	function FlushCache() {
		$this->use_cached = false;
	}
	// if any of these variables change from page to page, we flush the cache.
	function CheckCacheWatchedCGIVars($vars) {
		$prepend = $this->pager_id . '_pager_session_cache_';
		
		foreach($vars as $var) {
			$v = null;
			getGlobalVar($v, $var);
			//echo "$var is $v<br>\n";

			if(array_key_exists($prepend . $var, $_SESSION) && $_SESSION[$prepend . $var] != $v) {
				//echo "$var has changed from {$_SESSION[$prepend . $var]} to $v, flushing the cache<br/>";
				$this->use_cached = false;
			}
			$_SESSION[$prepend . $var] = $v;
		}
	}
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
}

?>
