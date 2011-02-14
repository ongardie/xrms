<?php

/*
	V4.68 25 Nov 2005  (c) 2000-2005 John Lim (jlim@natsoft.com.my). All rights reserved.
	  Released under both BSD license and Lesser GPL library license. 
	  Whenever there is any discrepancy between the two licenses, 
	  the BSD license will take precedence. 
	  Set tabs to 4 for best viewing.

  	This class provides recordset pagination with 
	First/Prev/Next/Last links. 
	
	Feel free to modify this class for your own use as
	it is very basic. To learn how to use it, see the 
	example in adodb/tests/testpaging.php.
	
	"Pablo Costa" <pablo@cbsp.com.br> implemented Render_PageLinks().
	
	Please note, this class is entirely unsupported, 
	and no free support requests except for bug reports
	will be entertained by the author.

*/
class ADODB_Pager {
	var $id; 	// unique id for pager (defaults to 'adodb')
	var $db; 	// ADODB connection object
	var $sql; 	// sql used
	var $rs;	// recordset generated
	var $curr_page;	// current page number before Render() called, calculated in constructor
	var $rows;		// number of rows per page
    var $linksPerPage=10; // number of links per page in navigation bar
    var $showPageLinks; 

	var $gridAttributes = 'width=100% border=1 bgcolor=white';
	
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
	
	//----------------------------------------------
	// constructor
	//
	// $db	adodb connection object
	// $sql	sql statement
	// $id	optional id to identify which pager, 
	//		if you have multiple on 1 page. 
	//		$id should be only be [a-z0-9]*
	//

    // *** updated to look in HTTP_POST_VARS instead of HTTP_GET_VARS
    function ADODB_Pager(&$db, $sql, $id='adodb', $showPageLinks=true, $selected_column=1, $selected_column_html='*')
	{
      global $HTTP_SERVER_VARS,$PHP_SELF,$HTTP_SESSION_VARS,$HTTP_POST_VARS;
	
		$curr_page = $id.'_curr_page';
		if (empty($PHP_SELF)) $PHP_SELF = $_SERVER['PHP_SELF'];
		
		$this->sql = $sql;
		$this->id = $id;
		$this->db = $db;
		$this->showPageLinks = $showPageLinks;
      $this->selected_column = $selected_column;
      $this->selected_column_html = $selected_column_html;
		
		$next_page = $id.'_next_page';	
		
      if (isset($HTTP_POST_VARS[$next_page])) {
        $HTTP_SESSION_VARS[$curr_page] = $HTTP_POST_VARS[$next_page];
		}
      if (empty($HTTP_SESSION_VARS[$curr_page])) $HTTP_SESSION_VARS[$curr_page] = 1; ## at first page
		
      $this->curr_page = $HTTP_SESSION_VARS[$curr_page];
		
	}
	
	//---------------------------
	// Display link to first page
	function Render_First($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
        echo '<a href="javascript: submitForm(1);"> ' . $this->first . '</a> &nbsp;';
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
        echo '<a href="javascript: submitForm('. ($this->rs->AbsolutePage() + 1) . ');">' . $this->next . '</a> &nbsp;';
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
        echo '<a href="javascript: submitForm(' . $this->rs->LastPageNo() . ');">' . $this->last . '</a> &nbsp;';
		} else {
			print "$this->last &nbsp; ";
		}
	}
	
	//---------------------------------------------------
	// original code by "Pablo Costa" <pablo@cbsp.com.br> 
        function render_pagelinks()
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
			$link = $this->id . "_next_page";
            if($end > $pages) $end = $pages;
			
			
			if ($this->startLinks && $start > 1) {
				$pos = $start - 1;
        $numbers .= "<a href='javascript: submitForm(" . $pos . ");'>" . $this->startLinks . "</a> ";
            } 
			
			for($i=$start; $i <= $end; $i++) {
                if ($this->rs->AbsolutePage() == $i)
                    $numbers .= "<font color=$this->linkSelectedColor><b>$i</b></font>  ";
                else 
        $numbers .= "<a href='javascript: submitForm(" . $i . ");'>" . $i . "</a> ";
        // $numbers .= "<a href=$PHP_SELF?$link=$i>$i</a>  ";
            
            }
      if ($this->moreLinks && $end < $pages){
        $numbers .= "<a href='javascript: submitForm(" . $i . ");'>" . $this->moreLinks . "</a>  ";
      }
            print $numbers . ' &nbsp; ';
        }
	// Link to previous page
	function render_prev($anchor=true)
	{
	global $PHP_SELF;
		if ($anchor) {
        echo '<a href="javascript: submitForm(' . ($this->rs->AbsolutePage() - 1) . ' );"> '.  $this->prev . '</a> &nbsp;';
		} else {
			print "$this->prev &nbsp; ";
		}
	}
	
	//--------------------------------------------------------
	// Simply rendering of grid. You should override this for
	// better control over the format of the grid
	//
	// We use output buffering to keep code clean and readable.
	function RenderGrid()
	{
	global $gSQLBlockRows; // used by rs2html to indicate how many rows to display
		include_once(ADODB_DIR.'/tohtml.inc.php');
		ob_start();
		$gSQLBlockRows = $this->rows;
      rs2html($this->rs,$this->gridAttributes,$this->gridHeader,$this->htmlSpecialChars,$this->selected_column,$this->selected_column_html);
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
	
	//-----------------------------------
	// Call this class to draw everything.
	function Render($rows=10)
	{
	global $ADODB_COUNTRECS;
	
		$this->rows = $rows;
		
		if ($this->db->dataProvider == 'informix') $this->db->cursorType = IFX_SCROLL;
		
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
	}
	
	//------------------------------------------------------
	// override this to control overall layout and formating
      function RenderLayout($header,$grid,$footer,$attributes='class=widget cellspacing=1 cellpadding=0 border=0 width="100%"')
	{
        echo "<table " . $attributes . ">"
        . "<tr><td colspan=23 class=widget_header>" . _("Search Results") . "</td></tr>\n";

        if ($header != '&nbsp;') {
          echo "<tr><td colspan=23>",
          "<table border=0 cellpadding=0 cellspacing=0 width=\"100%\">",
          "<tr><td class=widget_label>" . $footer . "</td>" . "<td align=right class=widget_label>" . $header . "</td></tr>",
          "</table>",
          "</td></tr>\n";
        }

        echo $grid;

        echo "</table>";
	}
}


?>