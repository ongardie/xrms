<?php

/**

Please keep this file's interface and behavior synchronized with Array_Pager!




*/


class XRMS_Pager {

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
    var $linkSelectedColor = 'red';
    var $cache = 0;  #secs to cache with CachePageExecute()
    var $how_many_rows;
	// adodb_pager code end

    var $pager_id;
    var $EndRows;





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
    function XRMS_Pager(&$db, $sql, $caption, $form_id, $pager_id='adodb_pager', $column_info)
    {

        global $http_site_root;

        // begin sorted columns stuff
        getGlobalVar($this->sort_column, $pager_id . '_sort_column');
        getGlobalVar($this->current_sort_column, $pager_id . '_current_sort_column');
        getGlobalVar($this->sort_order, $pager_id . '_sort_order');
        getGlobalVar($this->current_sort_order, $pager_id . '_current_sort_order');
        getGlobalVar($this->next_page, $pager_id . '_next_page');
        getGlobalVar($this->resort, $pager_id . '_resort');


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

        $order_by = " order by " . $this->sort_column;

        $order_by .= " " . $this->sort_order;

        // store current page in session
        if (isset($this->next_page)) {
            $HTTP_SESSION_VARS[$pager_id . '_' . $curr_page] = $this->next_page;
        }
        if (empty($HTTP_SESSION_VARS[$pager_id . '_' . $curr_page])) $HTTP_SESSION_VARS[$pager_id . '_' . $curr_page] = 1; ## at first page

        $this->curr_page = $HTTP_SESSION_VARS[$pager_id . '_' . $curr_page];
        // end sorted columns stuff


        $this->data = $data;
        $this->pager_id = $pager_id;
        $this->form_id = $form_id;
        $this->column_info = $column_info;
        $this->caption = $caption;


        // Set up the Subtotal and Total column arrays
        foreach($this->column_info as $k => $column_header) {
            if($column_header['subtotal']) {
                $this->SubtotalColumns[$column_header['index']] = 0;
            }
            if($column_header['total']) {
                $this->TotalColumns[$column_header['index']] = 0;
            }

        }

		$sql .= " $order_by";

		// adodb_pager code begin
      	$this->sql = $sql;
      	$this->id = $table_id;
      	$this->db = $db;
      	$this->showPageLinks = true;
      	$this->selected_column = $this->sort_column-1;
      	$this->selected_column_html = $this->pretty_sort_order;

		// adodb_pager code end

    	//$this->ADODB_Pager($db, $sql, $table_id,  $showPageLinks, $this->current_sort_column-1, $pretty_sort_order);
   }

    //---------------------------
    // Display link to first page
    function Render_First($anchor=true)
    {
      global $PHP_SELF;
      if ($anchor) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(1);"> ' . $this->first . '</a> &nbsp;';
      } else {
        print "$this->first &nbsp; ";
      }
    }

    //--------------------------
    // Display link to next page
    function render_next($anchor=true)
    {
      global $PHP_SELF;

      if ($anchor) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm('. ($this->rs->AbsolutePage() + 1) . ');">' . $this->next . '</a> &nbsp;';
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
      global $PHP_SELF;

      if (!$this->db->pageExecuteCountRows) return;

      if ($anchor) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(' . $this->rs->LastPageNo() . ');">' . $this->last . '</a> &nbsp;';
      } else {
        print "$this->last &nbsp; ";
      }
    }



    //---------------------------------------------------
    // original code by "Pablo Costa" <pablo@cbsp.com.br>
    function Render_PageLinks()
    {
      global $PHP_SELF;
      $pages        = $this->rs->LastPageNo();
      $linksperpage = $this->linksPerPage ? $this->linksPerPage : $pages;
      for($i=1; $i <= $pages; $i+=$linksperpage)
      {
        if($this->rs->AbsolutePage() >= $i)
        {
          $start = $i;
        }
      }
      $numbers = '';
      $end = $start+$linksperpage-1;
      $link = $this->pager_id . '_' . $this->id . "_next_page";
      if($end > $pages) $end = $pages;


      if ($this->startLinks && $start > 1) {
        $pos = $start - 1;
        $numbers .= "<a href='javascript: ' . $this->pager_id . '_submitForm(" . $pos . ");'>" . $this->startLinks . "</a> ";
      }

      for($i=$start; $i <= $end; $i++) {
        if ($this->rs->AbsolutePage() == $i)
        $numbers .= "<font color=$this->linkSelectedColor><b>$i</b></font>  ";
        else
        $numbers .= "<a href='javascript: ' . $this->pager_id . '_submitForm(" . $i . ");'>" . $i . "</a> ";
        // $numbers .= "<a href=$PHP_SELF?$link=$i>$i</a>  ";

      }
      if ($this->moreLinks && $end < $pages){
        $numbers .= "<a href='javascript: ' . $this->pager_id . '_submitForm(" . $i . ");'>" . $this->moreLinks . "</a>  ";
      }
      print $numbers . ' &nbsp; ';
    }
    // Link to previous page
    function render_prev($anchor=true)
    {
      global $PHP_SELF;
      if ($anchor) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(' . ($this->rs->AbsolutePage() - 1) . ' );"> '.  $this->prev . '</a> &nbsp;';
      } else {
        print "$this->prev &nbsp; ";
      }
    }


	


	function RenderGrid()
	{

        // output headers
        $column_count = count($this->column_info);
        for($i=0; $i<$column_count; $i++) {
            //echo "<td class=widget_label style=\"text-align: center; padding: 0em 0.5em 0em 0.5em;\"><a href='javascript: " . $this->pager_id . "_resort($i);' style=\"color: grey;\"><b>{$this->column_info[$i]['name']}</b></a>";
		    $this->gridHeader[] = $this->column_info[$i]['name'];	
        }

	  	// adodb_pager code begin 
      	global $gSQLBlockRows; // used by rs2html to indicate how many rows to display
      	include_once(ADODB_DIR.'/tohtml.inc.php');
      	ob_start();
      	$gSQLBlockRows = $this->rows;

      	rs2html($this->rs,$this->gridAttributes,$this->gridHeader,$this->htmlSpecialChars,$this->selected_column,$this->selected_column_html, $this->pager_id . '_');

		// Now we output our subtotal rows 
        $this->RenderTotals('Subtotals this page:', $this->SubtotalColumns);
        $this->RenderTotals('Totals:', $this->TotalColumns);

      	$s = ob_get_contents();
      	ob_end_clean();
	  	// adodb_pager code end 

		$rs = $this->rs;
		

/*
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

			for ($i=0; $i < $rs->FieldCount(); $i++) {

				$field = $rs->FetchField($i);

				if(0 == $i) {
					$s .= "<td class=\"widget_content\"><b>SubTotal this page:</b></td>";

				} else {
					if($this->SubtotalColumns[$field->name]) {
						$s .= "<td class=\"widget_content\">" . $this->SubtotalColumns[$field->name] . "</td>";
					} else {
						$s .= "<td class=\"widget_content\">&nbsp;</td>";
					}
				}
			}
			$s .= "</tr>\n";
		}

		if(is_array($this->TotalColumns)) {

			$s .= "<tr>";

			foreach($this->TotalColumns as $fieldname => $tablename) {
				$sql = "SELECT SUM($fieldname) FROM $tablename";
				$rs2 = $this->db->execute($sql);

				if ($rs2) {
					$this->TotalColumns[$fieldname] = $rs2->Fields(0);
    				$rs2->close();
				} else {
    				db_error_handler($this->db, $sql);
				}
			}
			// use the first query rs for field names!
			for ($i=0; $i < $rs->FieldCount(); $i++) {

				$field = $rs->FetchField($i);

				if(0 == $i) {
					$s .= "<td class=\"widget_content\"><b>Total:</b></td>";
				} else {
					if($this->TotalColumns[$field->name]) {
						$s .= "<td class=\"widget_content\">" . $this->TotalColumns[$field->name] . "</td>";
					} else {
						$s .= "<td class=\"widget_content\">&nbsp;</td>";
					}
				}
			}
			$s .= "</tr>\n";
		}
*/
		return $s;
	}



    //-------------------------------------------------------
    // Navigation bar
    //
    // we use output buffering to keep the code easy to read.
    function RenderNav()
    {
      ob_start();
      if (!$this->rs->AtFirstPage()) {
        $this->Render_First();
        $this->Render_Prev();
      } else {
        $this->Render_First(false);
        $this->Render_Prev(false);
      }
      if ($this->showPageLinks){
        $this->Render_PageLinks();
      }
      if (!$this->rs->AtLastPage()) {
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
    //-------------------
    // This is the footer
    function RenderPageCount()
    {
      if (!$this->db->pageExecuteCountRows) return '';
      $lastPage = $this->rs->LastPageNo();
      // *** updated to return an empty string if there's an empty rs
      if ($lastPage == -1) {
        $lastPage = 1;
        return 'aaa';
      } // check for empty rs.
      if ($this->curr_page > $lastPage) $this->curr_page = 1;
        // *** got rid of the font size changes!
        return "$this->page ".$this->curr_page."/".$lastPage;
    }



	

    //------------------------------------------------------
    // overridden to add export and mail merge
    function RenderLayout($header,$grid,$footer,$attributes='class=widget cellspacing=1 cellpadding=0 border=0 width="100%"')
    {
        echo "<table {$attributes} ><tr><td colspan=0 class=widget_header>" . $this->caption . "</td></tr>\n";

        if ($header != '&nbsp;') {
            echo "<tr><td colspan=0>".
            "<table border=0 cellpadding=0 cellspacing=0 width=\"100%\">".
            "<tr><td class=widget_label> $footer </td><td align=right class=widget_label> $header </td></tr>".
            "</table>".
            "</td></tr>\n";
        }

        echo $grid;

		if($this->EndRows) { echo $this->EndRows; }

/*
        if ($this->how_many_rows > 0)
        {
            echo "<tr><td class=widget_content_form_element colspan=50><input type=button class=button onclick=\"javascript: exportIt();\" value='Export'> ";
            echo "<input type=button class=button onclick=\"javascript: bulkEmail();\" value='Mail Merge'></td></tr>";
        }
*/

        echo "</table>";
    }

	function Render($rows=10) {

		$next_page_varname = $this->next_page_varname;

        echo <<<END
            <script language="JavaScript" type="text/javascript">
            <!--

            function {$this->pager_id}_submitForm(nextPage) {
                document.{$this->form_id}.{$this->pager_id}_next_page.value = nextPage;
                document.{$this->form_id}.submit();
            }

            function {$this->pager_id}_resort(sortColumn) {
                document.{$this->form_id}.{$this->pager_id}_sort_column.value = sortColumn + 1;
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
                document.{$this->form_id}.{$this->pager_id}_resort.value = 1;
                document.{$this->form_id}.submit();
            }

            //-->
            </script>

            <input type=hidden name={$this->pager_id}_use_post_vars value=1>
            <input type=hidden name={$this->pager_id}_next_page value="{$this->next_page}">
            <input type=hidden name={$this->pager_id}_resort value="0">
            <input type=hidden name={$this->pager_id}_current_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_current_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_sort_order value="{$this->sort_order}">
END;


		// adodb_pager code begin
        global $ADODB_COUNTRECS;

        $this->rows = $rows;

        $savec = $ADODB_COUNTRECS;
        if ($this->db->pageExecuteCountRows) $ADODB_COUNTRECS = true;
        if ($this->cache)
        $rs = &$this->db->CachePageExecute($this->cache,$this->sql,$rows,$this->curr_page);
        else
        $rs = &$this->db->PageExecute($this->sql,$rows,$this->curr_page);
        $ADODB_COUNTRECS = $savec;

        $this->rs = &$rs;
        if (!$rs) {
          print "<h3>";
          db_error_handler($this->db, $this->sql);
          print "</h3>";
          return;
        }

        if (!$rs->EOF && (!$rs->AtFirstPage() || !$rs->AtLastPage()))
        $header = $this->RenderNav();
        else
        $header = "&nbsp;";

        $grid = $this->RenderGrid();
        $footer = $this->RenderPageCount();
        $this->how_many_rows = $rs->recordcount();
        $rs->Close();
        $this->rs = false;

        $this->RenderLayout($header,$grid,$footer);

		// adodb_pager code end
		//parent::Render($rows);
	}
    function AddEndRows($html) {
        $this->EndRows = $html;
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

}

?>
