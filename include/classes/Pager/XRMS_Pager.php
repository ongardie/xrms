<?php

/**

 Pager for XRMS
 based on ADOdb's pager
 (still uses ADOdb's tohtml.php include file)

 see opportunities/some.php for an example of usage


*/

// specific code for tohtml
GLOBAL $gSQLMaxRows,$gSQLBlockRows;

$gSQLMaxRows   = 1000; // max no of rows to download
$gSQLBlockRows = 20; // max no of rows per table block



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
    var $maximize;




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
      $link = $this->pager_id . '_' . "_next_page";
      if($end > $pages) $end = $pages;


      if ($this->startLinks && $start > 1) {
        $pos = $start - 1;
        $numbers .= "<a href='javascript:{$this->pager_id}_submitForm(" . $pos . ");'>" . $this->startLinks . "</a> ";
      }

      for($i=$start; $i <= $end; $i++) {
        if ($this->rs->AbsolutePage() == $i)
        $numbers .= "<font color=$this->linkSelectedColor><b>$i</b></font>  ";
        else
        $numbers .= "<a href='javascript:{$this->pager_id}_submitForm(" . $i . ");'>" . $i . "</a> ";
        // $numbers .= "<a href=$PHP_SELF?$link=$i>$i</a>  ";

      }
      if ($this->moreLinks && $end < $pages){
        $numbers .= "<a href='javascript:{$this->pager_id}_submitForm(" . $i . ");'>" . $this->moreLinks . "</a>  ";
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
		    //$this->gridHeader[] = "<td class=widget_label style=\"text-align: center; padding: 0em 0.5em 0em 0.5em;\"><a href='javascript: " . $this->pager_id . "_resort($i);' style=\"color: grey;\"><b>{$this->column_info[$i]['name']}</b></a>";
		    //$this->gridHeader[] = "<td class=widget_label><a href='javascript: " . $this->pager_id . "_resort($i);' ><b>{$this->column_info[$i]['name']}</b></a>";
			$this->gridHeader[] = $this->column_info[$i]['name'];

        }

	  	// adodb_pager code begin 
      	global $gSQLBlockRows; // used by rs2html to indicate how many rows to display
      	//include_once(ADODB_DIR.'/tohtml.inc.php');
      	ob_start();
      	$gSQLBlockRows = $this->rows;

      	$this->rs2html($this->rs,$this->gridAttributes,$this->gridHeader,$this->htmlSpecialChars,$this->selected_column,$this->selected_column_html, $this->pager_id . '_');

		// Now we output our subtotal rows 
		$this->CalculateTotals();
        $this->RenderTotals('Subtotals this page:', $this->SubtotalColumns);
        //$this->RenderTotals('Totals:', $this->TotalColumns);

      	$s = ob_get_contents();
      	ob_end_clean();
	  	// adodb_pager code end 

		$rs = $this->rs;
		return $s;
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
/* disabled for now
        if(is_array($this->TotalColumns)) {

            //$s .= "<tr>";

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
        return "$this->page ".$this->curr_page."/" . $lastPage;
    }



	

    //------------------------------------------------------
    // overridden to add export and mail merge
    function RenderLayout($header,$grid,$footer,$attributes='class=widget cellspacing=1 cellpadding=0 border=0 width="100%"')
    {
		$colspan = count($this->column_info);
		if($this->maximize) {
        	echo "<table {$attributes} ><tr><td colspan=$colspan class=widget_header>" . $this->caption . "<a href=javascript:{$this->pager_id}_unmaximize();>(show paged)</a></td></tr>\n";
		} else {
        	echo "<table {$attributes} ><tr><td colspan=$colspan class=widget_header>" . $this->caption . "<a href=javascript:{$this->pager_id}_maximize();>(show all)</a></td></tr>\n";
		}
        if ($header != '&nbsp;') {
            echo "<tr><td colspan=$colspan>".
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

		echo "<a name=\"{$this->pager_id}\"></a>\n";

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
		

            //-->
            </script>

            <input type=hidden name={$this->pager_id}_use_post_vars value=1>
            <input type=hidden name={$this->pager_id}_next_page value="{$this->next_page}">
            <input type=hidden name={$this->pager_id}_resort value="0">
            <input type=hidden name={$this->pager_id}_current_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_current_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_maximize value="{$this->maximize}">
END;


		// adodb_pager code begin
        global $ADODB_COUNTRECS;

        if($this->maximize) {
            $this->rows = 10000; // sanity
        } else {
            $this->rows = $rows;
        }

        $savec = $ADODB_COUNTRECS;
        if ($this->db->pageExecuteCountRows) $ADODB_COUNTRECS = true;
        if ($this->cache)
        $rs = &$this->db->CachePageExecute($this->cache,$this->sql,$this->rows,$this->curr_page);
        else
        $rs = &$this->db->PageExecute($this->sql,$this->rows,$this->curr_page);
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


// RecordSet to HTML Table
//------------------------------------------------------------
// Convert a recordset to a html table. Multiple tables are generated
// if the number of rows is > $gSQLBlockRows. This is because
// web browsers normally require the whole table to be downloaded
// before it can be rendered, so we break the output into several
// smaller faster rendering tables.
//
// $rs: the recordset
// $ztabhtml: the table tag attributes (optional)
// $zheaderarray: contains the replacement strings for the headers (optional)
//
//  USAGE:
//  include('adodb.inc.php');
//  $db = ADONewConnection('mysql');
//  $db->Connect('mysql','userid','password','database');
//  $rs = $db->Execute('select col1,col2,col3 from table');
//  rs2html($rs, 'BORDER=2', array('Title1', 'Title2', 'Title3'));
//  $rs->Close();
//
// RETURNS: number of rows displayed
// *** added parameters selected_column and selected_column_html to indicate which column is sorted and how

function rs2html(&$rs,$ztabhtml=false,$zheaderarray=false,$htmlspecialchars=true,$selected_column=1,$selected_column_html='****', $pager_id = null) {
    $s ='';
    $rows=0;
    $docnt = false;
    GLOBAL $gSQLMaxRows,$gSQLBlockRows;

    if (!$rs) {
        printf(ADODB_BAD_RS,'rs2html');
        return false;
    }

    // *** got rid of ztabhtml attributes here:
    if (! $ztabhtml) $ztabhtml = "";
    //else $docnt = true;
    $typearr = array();

    $ncols = $rs->FieldCount();

	$ncols = count($this->column_info);

    // *** commented out the line below b/c we don't want *another* freakin' table -- we just want rows
    // $hdr = "<TABLE COLS=$ncols $ztabhtml>\n\n";
    // made it 'ncols - 1' so that you can't sort by the delete button
    $hdr = '';
    for ($i=0; $i < $ncols; $i++) {
        $field = $rs->FetchField($i);
        if ($zheaderarray) $fname = $zheaderarray[$i];
        else $fname = htmlspecialchars($field->name);
        $typearr[$i] = $rs->MetaType($field->type,$field->max_length);
        //print " $field->name $field->type $typearr[$i] ";
        // no &nbsp; here... we don't want the link visible
        if (strlen($fname)==0) $fname = '';
        // *** and here below we just want stylized <td> elements, not <th>'s
        // *** also we need to make these headers re-sort the results if asked
        $hdr .= "<td class=widget_label ><a href='javascript: " . $pager_id . "resort($i);' ><b>$fname</b></a>";

        if ($i == $selected_column) {
            $hdr .= $selected_column_html;
        }

        $hdr .= "</td>";
    }

    // *** added <tr> and </tr> tags around $hdr
    print "<tr>" . $hdr . "</tr>\n\n";
    // smart algorithm - handles ADODB_FETCH_MODE's correctly!
    $numoffset = isset($rs->fields[0]);
    // added this for colors
    $color_counter = 0;

    while (!$rs->EOF) {

        $color_counter++;
        $classname = (($color_counter % 2) == 1) ? "widget_content" : "widget_content_alt";

        $s .= "<tr valign=top>\n";

		for($i=0; $i<$ncols; $i++) {

        //for ($i=0, $v=($numoffset) ? $rs->fields[0] : reset($rs->fields);
            //$i < $ncols;
            //$i++, $v = ($numoffset) ? @$rs->fields[$i] : next($rs->fields)) {

            $type = $typearr[$i];

            //$s .= "<td class=$classname>" . $v . "</td>\n";
            $s .= "<td class=$classname>" . $rs->fields[$this->column_info[$i]['index']] . "</td>\n";


            // *** for each of these types we want stylized <td> elements
            /*
            switch($type) {
            case 'T':
                $s .= " <td class=$classname>" . $rs->UserTimeStamp($v,"Y-M-d") . "&nbsp;</td>\n";
            break;
            case 'D':
                $s .= " <td class=$classname>" . $rs->UserDate($v,"D d, M Y") . "&nbsp;</td>\n";
            break;
            case 'I':
                $s .= " <td class=$classname>" . stripslashes((trim($v))) . "&nbsp;</TD>\n";
                break;
            case 'N':
                if ($i == 8) {
                    $s .= " <td class=$classname>$" . number_format(stripslashes((trim($v))), 2) . "&nbsp;</TD>\n";
                } else {
                    $s .= " <td class=$classname>" . stripslashes((trim($v))) . "&nbsp;</TD>\n";
                }
            break;
            default:
                if ($htmlspecialchars) $v = htmlspecialchars($v);
                // *** good one $s .= " <td class=$classname>". str_replace("\n",'<br>',stripslashes((trim($v)))) ."&nbsp;</TD>\n";
                $s .= " <td class=$classname>". stripslashes((trim($v))) ."&nbsp;</TD>\n";
                break;
            } // switch
            */

            // print "<li>$v - $i - $type - $numoffset - $color_counter</li>";

        } // for
        $s .= "</tr>\n\n";
        $rows += 1;
        if ($rows >= $gSQLMaxRows) {
            $rows = "<p>Truncated at $gSQLMaxRows</p>";
            break;
        } // switch

        $rs->MoveNext();

        // additional EOF check to prevent a widow header
        if (!$rs->EOF && $rows % $gSQLBlockRows == 0) {
            //if (connection_aborted()) break;// not needed as PHP aborts script, unlike ASP
            echo $s . "\n\n";
            $s = $hdr;
        }
    } // end while

    if (strlen($s) == 0) {
        $s = "<tr><td colspan=$ncols class=widget_content>"._("No matches")."</td></tr>";
    }
    // if ($docnt) print "<H2>".$rows." Rows</H2>";

    echo $s."\n\n";
    return $rows;
 }



}

?>
