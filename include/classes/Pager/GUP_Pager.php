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
 * $Id: GUP_Pager.php,v 1.49 2006/07/12 00:33:41 vanmer Exp $
 */



require_once('Pager_Renderer.php');


class GUP_Pager {

    var $SubtotalColumns;
    var $TotalColumns;

    var $db;    // ADODB connection object
    var $sql;   // sql used
    var $rs;    // recordset generated
    var $curr_page; // current page number before Render() called, calculated in constructor
    var $rows;      // number of rows per page
    var $linksPerPage=10; // number of links per page in navigation bar

    // Localize text strings here
    var $first = '<code>|&lt;</code>';
    var $prev = '<code>&lt;&lt;</code>';
    var $next = '<code>>></code>';
    var $last = '<code>>|</code>';
    var $moreLinks = '...';
    var $startLinks = '...';
    var $gridHeader = false;
    var $htmlSpecialChars = false;
    //var $selected_column = 1;
    //var $selected_column_html = '*';
    var $page;
    var $cache = 0;  #secs to cache with CachePageExecute()
    // adodb_pager code end

    var $pager_id;
    var $EndRows;
    var $maximize;
    var $show_hide;
    var $showCaption;
    var $hideCaption;


    var $get_only_visible         = false; // used internally; whether or not to get the whole sql dataset
    var $use_cached             = true;     // whether or not to use the cache
    var $using_cached             = false;    // whether or not we are currently using cached data
    var $group_mode             = false;
    var $group_mode_paging        = false;
    var $last_group_mode         = false;
    var $buffer_output;
    var $rows_displayed         = 0;

    var $show_cached_indicator     = false;
    var $cached_indicator_url     = null;

    var $show_caption_bar        = true;
    // show the export button
    var $show_export             = false;
    // do the actual export
    var $do_export                 = false;

    var $modify_data_functions = array();
    var $debug                    = false;
    var $count_sql                = '';
    var $order_by                = '';

    var $render_data            = array();

    var $numeric_index          = true; // this can be modified by the Renderer object

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
    function GUP_Pager(&$db, $sql, $data, $caption, $form_id, $pager_id='gup_pager', $view_info, $use_cached = true, $buffer_output = false, $group_mode_paging = false, $debug=false)
    {
        global $http_site_root;

        if ($debug) $this->debug=true;
        if(empty($sql) && !isset($data)) {
            echo _("Warning: GUP_Pager must be passed either an SQL query or a data array");
            return false;
        }

        //if columns element is not defined, then assume entire view is column data
        if (!$view_info['columns']) { 
            $column_info=$view_info;
            $incoming_options=false;
        } else {
            $column_info = $view_info['columns'];
            $incoming_options = $view_info['options'];
        }


          $this->db             = $db;
          $this->sql             = $sql;
        $this->data         = $data;
        $this->caption         = $caption;
        $this->showCaption    = (($showCaption) ? $showCaption : _("Show"));
        $this->hideCaption    = (($hideCaption) ? $hideCaption : _("Hide"));
        $this->form_id         = $form_id;
        $this->pager_id        = $pager_id;
        $this->column_info     = $column_info;
        $this->use_cached    = $use_cached;
         $this->page            = _('Page');
        $this->buffer_output=$buffer_output;
        $this->group_mode_paging=$group_mode_paging;


        // get CGI vars
        getGlobalVar($this->sort_column, $pager_id . '_sort_column');
        getGlobalVar($this->current_sort_column, $pager_id . '_current_sort_column');
        getGlobalVar($this->sort_order, $pager_id . '_sort_order');
        getGlobalVar($this->current_sort_order, $pager_id . '_current_sort_order');
        getGlobalVar($this->next_page, $pager_id . '_next_page');
        getGlobalVar($this->resort, $pager_id . '_resort');
        getGlobalVar($this->maximize, $pager_id . '_maximize');
        getGlobalVar($this->show_hide, $pager_id . '_show_hide');
        if (!$this->show_hide) $this->show_hide='show';

        getGlobalVar($this->group_mode, $this->pager_id . '_group_mode');
        getGlobalVar($this->last_group_mode, $this->pager_id . '_last_group_mode');
        getGlobalVar($this->group_id, $this->pager_id . '_group_id');
        getGlobalVar($refresh, $this->pager_id . '_refresh');
        getGlobalVar($this->do_export, $this->pager_id . '_export');

        //process incoming options, if available and set
        if ($incoming_options) {
            foreach ($incoming_options as $okey=>$oval) {
                $this->PrintDebug("SETTING INTERNAL OPTION $okey TO $oval FROM VIEW");
                $eval_str="\$this->{$okey} = $oval;";
                $this->PrintDebug("EVALING: $eval_str");
                eval($eval_str);
            }
        }


        if($refresh) { 
            $this->PrintDebug("CGI refresh, not using cache");
            $this->use_cached    = false; 
        }
        // don't use the cache if we are in sql-only mode (no data or callbacks)
        if(!isset($data)) { 
            $this->PrintDebug("SQL-only mode (no data or callbacks), not using cache");
            $this->use_cached    = false; 
        }
        // don't use the cache if there is no sql and the data is not
        if(isset($data) && !function_exists($data)) { 
            $this->PrintDebug("Data-only mode (no SQL or callbacks), not using cache");
            $this->use_cached    = false; 
        }

        if($this->do_export) { unset($this->group_mode); }  // group mode doesn't make sense for export
        if(!is_numeric($this->group_mode)) { unset($this->group_mode); }
        if(isset($this->group_mode) && !$this->group_mode_paging) { $this->maximize = true; }


        // this is so that we can refer to all columns by ['index'] later when it doesn't concern us if they are sql/calc/data
        foreach($this->column_info as $k => $column) {
            if(isset($column['index_sql'])) $this->column_info[$k]['index'] = $column['index_sql'];
            if(isset($column['index_calc'])) $this->column_info[$k]['index'] = $column['index_calc'];
            if(isset($column['index_data'])) $this->column_info[$k]['index'] = $column['index_data'];
        }

        // Init the Subtotal and Total column arrays
        foreach($this->column_info as $k => $column_header) {
            if($column_header['subtotal']) {
                $this->SubtotalColumns[$column_header['index']] = 0;
            }
            if($column_header['total']) {
                $this->TotalColumns[$column_header['index']] = 0;
            }
        }
        
        $this->render_data['caption'] = $caption;

    } // end constructor


    /**
    * private method to determine the proper sort column and set up the SQL ORDER BY clause
    *
    * This allows 
    *
    * @param boolean Whether or not this is the first call
    */
    function PrepareSort() {
        global $http_site_root;
    
            // set up sort_column and sort_order
            if (!(strlen($this->sort_column) > 0)) {
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

            $this->render_data['sort_order'] = $this->sort_order;
    
            $ascending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/asc.gif" alt="">';
            $descending_order_image = ' <img border=0 height=10 width=10 src="' . $http_site_root . '/img/desc.gif" alt="">';
            $this->pretty_sort_order = ($this->sort_order == "asc") ? $ascending_order_image : $descending_order_image;
    
            // here we add the ORDER BY clause to the SQL query.
            // this is done seperately for grouping later because we don't know enough yet to construct the query for grouping
            if($this->column_info[$this->sort_column-1]['index_sql']) {
                $this->SetUpSQLOrderByClause();
            }
    
            $this->PrintDebug("Current SQL: {$this->sql}");
    
            // store current page in session
            if (isset($this->next_page)) {
                $_SESSION[$this->pager_id . '_curr_page'] = $this->next_page;
            }
            if (empty($_SESSION[$this->pager_id . '_curr_page'])) $_SESSION[$this->pager_id . '_curr_page'] = 1; ## at first page
    
            $this->curr_page = $_SESSION[$this->pager_id . '_curr_page'];
            // end sorted columns stuff
    }

    /**
    * Override the default_sort field passed in via $columns
    *
    * @param array Key is the name of the field you wish to set as default_sort, Value is either asc or desc
    */
    function SetDefaultSortColumn($sort_info) {

        $key = key($sort_info);

        foreach($this->column_info as $k => $column) {
            unset($this->column_info[$k]['default_sort']);

            if($this->column_info[$k]['index'] == $key) {
                $this->column_info[$k]['default_sort'] = $sort_info[$key];
            }
        }
    }


    /**
    * public method called by user to render the pager
    *
    * @param integer number of rows to display in this pager
    */
    function Render($rows=0, $renderer = null) {

        if(!$rows) {
            $rows = 10;
        }

        if(!$this->db->pageExecuteCountRows) $this->render_data['no_page_links'];

        // sort must come before Render_JS_and_Hiddens()
        $this->PrepareSort();


        $this->render_data['pager_id'] = $this->pager_id;

        // output the Javascript functions for sorting and submitting
        $js =  $this->Render_JS_and_Hiddens();

        // move to Pager_Renderer
        $this->render_data['js_and_hiddens'] = $js;

        // adodb_pager code begin
        global $ADODB_COUNTRECS;

        if($this->maximize) {
            $this->rows = 10000; // 10000 per page should be enough for anyone's browser
        } else {
            $this->rows = $rows;
        }


        $this->GetData();
        //print_r($this->data);

        $page_nav = '';

        // if group mode or there is data and we're not at the first and last page simultaneously (only one page!)
        if (isset($this->group_mode)) {
            // makes Group: <select> [ungroup button]
            $page_nav .= $this->column_info[$this->group_mode]['name'] . ':' ;
            $page_nav .= $this->group_select_widget;
            $page_nav .= "<input type=button class=button onclick=\"javascript:{$this->pager_id}_ungroup({$this->group_mode});\" value=\"" . _('Ungroup') . '">';
        } 
        if($this->data && (!$this->AtFirstPage || !$this->AtLastPage)) {
            $page_count = $this->PreparePageCount();
            $page_nav .= $this->PrepareNav();
        } else {
            $page_count = $this->PreparePageCount();
        }
        $grid = $this->PrepareGrid();






        $this->render_data['page_count'] = $page_count;
        $this->render_data['page_nav'] = $page_nav;
        $this->render_data['grid'] = $grid;

        $this->PrepareTitleButtons();

        global $xrms_plugin_hooks;

        // add the hook to include the JS for the tooltips
        $xrms_plugin_hooks['end_page']['pager'] = 'javascript_tooltips_include';

        if(!$renderer) {
            $renderer = new Pager_Renderer($this);
        }

        return $renderer->ToHTML($this->buffer_output);
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
        $render_data =& $this->render_data;

        // finalize the order_by (it could be changed by PrepareSort prior to this moment)
        $this->sql .= $this->order_by;


        // If there is a modify function (operates on the whole dataset), get all records in the SQL query below.
        if($this->modify_data_functions) {
            $this->get_only_visible =false;
        }



        $cache_name = $this->pager_id . '_data';

        if(isset($this->group_mode)) {
            /* 
                In group mode, we replace the normal sql query with the one passed in group_query_select
                
                We also take this time to create the <select> widget via ADOdb::GetMenu2()

                Finally, if the column is an sql sortable column, we add the appropriate order by clause to the sql query
            */
                
            if($this->column_info[$this->group_mode]['group_query_list']) {

                // can't use $count_sql in group mode because it is for the whole dataset, not our grouped copy.
                if($this->column_info[$this->group_mode]['group_query_count']) {
                    //$this->count_sql = $this->column_info[$this->group_mode]['group_query_count'];
                    $this->count_sql = str_replace('XXX-value-XXX', $this->group_id, $this->column_info[$this->group_mode]['group_query_count']); 
                    $this->PrintDebug("setting custom count_sql for group mode:<br>{$this->count_sql}");
                } else {
                    $this->count_sql = '';
                    $this->PrintDebug("disabling count_sql for group mode");
                }

                $old_fetch_mode = $this->db->fetchMode;
                $this->db->SetFetchMode(ADODB_FETCH_NUM);

                $this->PrintDebug("executing group query: {$this->column_info[$this->group_mode]['group_query_list']}");
                $group_values = $this->db->execute($this->column_info[$this->group_mode]['group_query_list']);

                $this->db->SetFetchMode($old_fetch_mode); 

                
    
                if(!$group_values) {
                    db_error_handler($this->db, $this->column_info[$this->group_mode]['group_query_list']);
                } else {
                    // set group id to first item if none is selected or group column has changed
                    if(!isset($this->group_id) || $this->group_mode != $this->last_group_mode) {
                        $this->group_id = $group_values->fields[1];
                    }
                    $this->group_select_widget =  $group_values->GetMenu2($this->pager_id . '_group_id', $this->group_id, false, false, 0, "onchange='javascript:{$this->pager_id}_group(" . $this->group_mode . ");'");
                }

                // change the sql query to the group select, if group id has a value
                if ($this->group_id!='') {
                    $this->sql = str_replace('XXX-value-XXX', $this->group_id, $this->column_info[$this->group_mode]['group_query_select']); 
                    if($this->column_info[$this->sort_column-1]['index_sql']) {
                        $this->SetUpSQLOrderByClause();
                    }
                }
            }
        }

        if($_SESSION[$cache_name] && $this->use_cached) {
            $this->PrintDebug("Getting Data From Cache");

            $this->data = $_SESSION[$cache_name];
            $this->using_cache = true;

        } else {
            $this->PrintDebug("Not Using Cache $cache_name");

            // clear the cache
            $_SESSION[$cache_name] = null;

            if($this->sql) {
                $this->PrintDebug("Current SQL: ". htmlentities($this->sql));
    
                if($this->get_only_visible) {
                    $this->PrintDebug("Running SQL query for a page of the data");

                    global $ADODB_COUNTRECS;

                    $savec = $ADODB_COUNTRECS;
                    if ($this->db->pageExecuteCountRows) {
                        $ADODB_COUNTRECS = true;
                    } else {
                        $ADODB_COUNTRECS = false;
                    }

                    if ($this->cache)
                        $rs = &$this->db->CachePageExecute($this->cache,$this->sql,$this->rows,$this->curr_page,false,$this->count_sql);
                    else
                        $rs = &$this->db->PageExecute($this->sql,$this->rows,$this->curr_page,false,0,$this->count_sql);
                    $ADODB_COUNTRECS = $savec;

    
                } else {
                    $this->PrintDebug("Running SQL query for all data");
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
                    $this->PrintDebug("Running callback for calculated data");

                    $user_data_fn = $this->data;
                    $this->data = array();
                    while (!$rs->EOF) {
                        $row = call_user_func($user_data_fn, $rs->fields, $this->curr_page, $this->rows, $this->column_info[$this->sort_column-1]['index'], $this->sort_order, $this);
                        if($row) {
                            $this->data[] = $row;
                        }
                        $rs->MoveNext();
                    } 
                } else {
                    $this->data = array();
                    while (!$rs->EOF) {
                        $this->data[] = $rs->fields;
                        $rs->MoveNext();
                    }
                }
            }
            //store it in the session cache
            if(!$this->get_only_visible) {    
                $_SESSION[$cache_name] = $this->data;
            }

        }
        // store the column_info array for pager-export.php always, becuase they may have changed columns but not needed to re-calculate data
        $_SESSION[$this->pager_id . '_columns'] = $this->column_info;


        // data is now in $this->data
        if($this->modify_data_functions) {
            foreach($this->modify_data_functions as $fn) {
                if(function_exists($fn)) 
                    $this->data = call_user_func($fn, $this->data, $this);

                    // experimental!  this makes the page count be based on $this->data instead of $this->rs
                    // which allows the modify callback to drop rows, yet get a full page each time
                    $this->rs = false;
            }
        }


        // if sort column is one of the calculated ones, use the Array_Sorter to sort
        if($this->using_cache || !$this->get_only_visible) {
            // in the same dir as us...
            require_once('Array_Sorter.php');

            if(count($this->data)) {
                $sorter = new array_sorter($this->data, $this->column_info[$this->sort_column-1]['index'], ($this->sort_order == "asc") ? true : false);
                $this->data = $sorter->sortit();
            }

            
            // then output rows 34-43 par example
            $this->AbsolutePage = $this->curr_page;
            $this->LastPageNo     = (int)((count($this->data) + $this->rows - 1) / $this->rows);
            $this->AtFirstPage     = (1 == $this->curr_page);
            $this->AtLastPage     = ($this->LastPageNo <= 1);

            $this->PrintDebug("clause A");
            $this->start_data_row = ($this->curr_page -1) * $this->rows;
            $this->end_data_row = min($this->start_data_row + $this->rows, count($this->data));
			//echo $this->AbsolutePage . ':' . $this->LastPageNo .':'. $this->start_data_row .':'. $this->end_data_row;

        } else {
            if($this->rs) { 
                $this->PrintDebug("clause B");
                $this->AbsolutePage = $rs->AbsolutePage();
                $this->LastPageNo = $rs->LastPageNo();
                $this->AtFirstPage = $rs->AtFirstPage();
                $this->AtLastPage = $rs->AtLastPage();

                $this->start_data_row = 0;
                $this->end_data_row = count($this->data);
            } else {
                $this->PrintDebug("clause C");
                $this->AbsolutePage = $this->curr_page;
                $this->LastPageNo     = (int)((count($this->data) + $this->rows - 1) / $this->rows);
                $this->AtFirstPage     = (1 == $this->curr_page);
                $this->AtLastPage     = ($this->LastPageNo <= 1);

                $this->start_data_row = ($this->curr_page -1) * $this->rows;
                $this->end_data_row = min($this->start_data_row + $this->rows, count($this->data));
            }
        }

        $render_data['absolute_page'] = $this->AbsolutePage;
        $render_data['last_page_num'] = $this->LastPageNo;
        $render_data['at_first_page'] = $this->AtFirstPage;
        $render_data['at_last_page'] = $this->AtLastPage;

        
        $this->PrintDebug("rows from {$this->start_data_row} to {$this->end_data_row}");


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
                    $this->PrintDebug("setting manually to $id");
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
    }
    function AddModifyDataCallback($fn) {
        $this->modify_data_functions[] = $fn;
    }

    /**
    * private function 
    * Render_JS_and_Hiddens()
    * outputs the Javascript functions and hidden form variables
    */
    function Render_JS_and_Hiddens() {
        global $http_site_root;

        $ret = '';

        // output JS code to submit the page to the export handler script.
        if($this->do_export) {

            // exporting works in two passes...on this pass we output this part which will cause a reload to pager-export.php
            $cache_name = $this->pager_id . '_data';
            $pager_id = $this->pager_id;
            $ret .= <<<END

                <!-- used by pager-export.php -->
                <input type=hidden name=pager_id value="">

                <script language="JavaScript" type="text/javascript">
                    // don't clobber any existing onload functions
                    var oldEvt = window.onload; 
                    window.onload = function() { 
                        if (oldEvt) oldEvt(); 


                        var oldAction = document.{$this->form_id}.action;

                        document.{$this->form_id}.action = '$http_site_root/export/export.php';
                        document.{$this->form_id}.pager_id.value = '$pager_id';

                        document.{$this->form_id}.submit();
                        document.{$this->form_id}.action = oldAction;

                        document.{$this->form_id}.{$this->pager_id}_export.value = '';
                        
                    }
                </script>
END;
        }
        if ($this->show_hide=='hide') {
            $js_show_hide="
\nvar oldEvt = window.onload;\nwindow.onload = function() {\nif (oldEvt) oldEvt();\n{$this->pager_id}_Hide();\n}\n";
        } else {
            $js_show_hide='';
        }

        $ret .= <<<END
            <script language="JavaScript" type="text/javascript">
            <!-- // Begin Pager Javascript
            $js_show_hide

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
            function {$this->pager_id}_Hide() {
                pager=document.getElementById('{$this->pager_id}_contents');
                hlink=document.getElementById('{$this->pager_id}_showhideLink');
                document.{$this->form_id}.{$this->pager_id}_show_hide.value='hide';
                hlink.innerHTML='{$this->showCaption}';
                hlink.onclick={$this->pager_id}_Show;
                if (pager) {
                    for (var r=1; r< pager.rows.length; r++)
                        pager.rows[r].style.display='none';
                }
            }
            function {$this->pager_id}_Show() {
                pager=document.getElementById('{$this->pager_id}_contents');
                hlink=document.getElementById('{$this->pager_id}_showhideLink');
                hlink.innerHTML='{$this->hideCaption}';
                document.{$this->form_id}.{$this->pager_id}_show_hide.value='show';
                hlink.onclick={$this->pager_id}_Hide;
                if (pager) {
                    for (var r=1; r< pager.rows.length; r++)
                        pager.rows[r].style.display='';
                }
            }
            function {$this->pager_id}_unmaximize() {
                document.{$this->form_id}.{$this->pager_id}_maximize.value = null;
                document.{$this->form_id}.{$this->pager_id}_next_page.value = '';
                document.{$this->form_id}.action = document.{$this->form_id}.action + "#" + "{$this->pager_id}";
                document.{$this->form_id}.submit();
            }
            function {$this->pager_id}_group(groupColumn) {
                document.{$this->form_id}.{$this->pager_id}_last_group_mode.value = document.{$this->form_id}.{$this->pager_id}_group_mode.value;
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
            
            // End Pager Javascript -->
            </script>
            <!-- Begin Pager Hidden Form Vars -->
            <input type=hidden name={$this->pager_id}_use_post_vars value=1>
            <input type=hidden name={$this->pager_id}_next_page value="{$this->next_page}">
            <input type=hidden name={$this->pager_id}_resort value="0">
            <input type=hidden name={$this->pager_id}_group_mode value="{$this->group_mode}">
            <input type=hidden name={$this->pager_id}_last_group_mode value="{$this->group_mode}">
            <input type=hidden name={$this->pager_id}_current_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_sort_column value="{$this->sort_column}">
            <input type=hidden name={$this->pager_id}_current_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_sort_order value="{$this->sort_order}">
            <input type=hidden name={$this->pager_id}_maximize value="{$this->maximize}">
            <input type=hidden name={$this->pager_id}_show_hide value="{$this->show_hide}">
            <input type=hidden name={$this->pager_id}_refresh value="">
            <input type=hidden name={$this->pager_id}_export value="">
            <!-- End Pager Hidden Form Vars -->

END;
        return $ret;
    }
    /**
    * private method to render the data portion of the table
    */
    function PrepareGrid()
    {
        ob_start();

        $render_data =& $this->render_data;

        $color_counter = 0;
        $col_classnames = array();

        $column_count = count($this->column_info);

        $render_data['column_count'] = $column_count;


        // do the headers
        for($i=0; $i<$column_count; $i++) {

            $group_html = '';

            if($this->column_info[$i]['group_query_list'] || $this->column_info[$i]['group_calc']) {
                $group_html = "<a href='javascript: " . $this->pager_id . "_group($i);'><b>(" . _('G') . ")</b></a>";
            }

            $selected_column_header_html = '';
            if ($i == ($this->sort_column-1)) {
                //echo $this->pretty_sort_order;
                //$selected_column_header_html = $this->pretty_sort_order;
                $render_data['header'][$i]['selected'] = true;
            }
            if($this->column_info[$i]['not_sortable']) {
                $header_text = '<b>' . $this->column_info[$i]['name'] . '</b>';
            } else {
                $header_text = "<a href='javascript: " . $this->pager_id . "_resort($i);' ><b>{$this->column_info[$i]['name']}</b></a>";
            }

            if($group_html || $i == ($this->sort_column-1)) {
                //$render_data['header'][$i]['selected'] = true;
                $render_data['header'][$i]['group_widget'] = $group_html;
                $render_data['header'][$i]['header_text'] = $header_text;
            } else {
                $render_data['header'][$i]['header_text'] = $header_text;
            }

            // set the column css
            if($this->column_info[$i]['css_classname']) {
                //$col_classnames[$i] = $this->column_info[$i]['css_classname'];
                $render_data['header']['col_classnames'][$i] = $this->column_info[$i]['css_classname'];
            } else {
                //$col_classnames[$i] = '';
                $render_data['header']['col_classnames'][$i] = '';
            }
            $render_data[$i]['data_type'] = $this->column_info[$i]['type'];
        }


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

        $render_data_row_count = 0;

        // main data loop
        for($i=$this->start_data_row; $i<$this->end_data_row; $i++) {

            // in group mode, skip this value if it's not one we're interested in.
            if($skip_value && $skip_value != $this->data[$i][$this->column_info[$this->group_mode]['index']]) {
                continue;
            }

            if($this->data[$i]['Pager_TD_CSS_All_Rows']) {
                $render_data['rows'][$i]['Pager_TD_CSS_All_Rows'] .= ' ' . $this->data[$i]['Pager_TD_CSS_All_Rows'];
            } 
            
            if($this->numeric_index) {
                for($j=0; $j<$column_count; $j++) {
                    $render_data['rows'][$render_data_row_count]['columns'][$j] = $this->data[$i][$this->column_info[$j]['index']];
                }
            } else {
                for($j=0; $j<$column_count; $j++) {
                    $render_data['rows'][$render_data_row_count]['columns'][$this->column_info[$j]['index']] = $this->data[$i][$this->column_info[$j]['index']];
                }
            }
            // come mister tally man tally me subtotal columns
            if($this->SubtotalColumns) {
                foreach($this->SubtotalColumns as $index => $k) {
                    $this->SubtotalColumns[$index] += $this->data[$i][$index];
                }
            }
            $this->rows_displayed++;
            $render_data_row_count++;
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
            //$this->RenderTotals(_('Subtotals this page:'), $this->SubtotalColumns, $row_classnames, $col_classnames);
            //$this->RenderTotals(_('Totals:'), $this->TotalColumns, $row_classnames, $col_classnames);
        } 

    }



    /**
    * private method to render the top navigation bar
    */
    function PrepareNav()
    {
      ob_start();

          $this->Prepare_First();
          $this->Prepare_Prev();
    
          $this->Prepare_PageLinks();
    
          $this->Prepare_Next();
          $this->Prepare_Last();

      $s = ob_get_contents();
      ob_end_clean();

      return $s;
    }


    /**
    * private method to Display link to first page
    */
    function Prepare_First()
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
    function Prepare_Prev($anchor=true)
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
    function Prepare_Next()
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
    function Prepare_Last($anchor=true)
    {
      if ($this->sql && !$this->db->pageExecuteCountRows) return;
      if (!$this->AtLastPage) {
        echo '<a href="javascript: ' . $this->pager_id . '_submitForm(' . $this->LastPageNo . ');">' . $this->last . '</a> &nbsp;';
      } else {
        print "$this->last &nbsp; ";
      }
    }


    /**
    * private method to render this part:  |< < 1 2 3 ... > >|
    */
    function Prepare_PageLinks()
    {

        $render_data =& $this->render_data;

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
        $render_data['page_links'] = $numbers;

        echo $numbers . ' &nbsp;';

    }


    /**
    * This is the page_count
    */
    function PreparePageCount()
    {
        $render_data =& $this->render_data;


        if (!$this->db->pageExecuteCountRows) return '';
        $lastPage = $this->LastPageNo;
        // *** updated to return an empty string if there's an empty rs
        if ($lastPage < 1) {
            $lastPage = 1;
            return '';
        } // check for empty rs.
        if ($this->curr_page > $lastPage) $this->curr_page = 1; {


            $return  = "$this->page ".$this->curr_page."/" . $lastPage;

            // note: -1 is also returned from MaxRecordCount in DBs that don't support the record counting.
            $count = -1;
    
            if($this->rs) {
                $count = $this->rs->MaxRecordCount();
            } elseif($this->data) {
                $count = count($this->data);
            }
    
            if($count > -1) {
                $return .= ' (' . $count . ' '. _("records found") . ')';
            }
    
            $render_data['current_page'] = $this->curr_page;
            $render_data['last_page'] = $lastPage;
            $render_data['record_count'] = $count;

            return $return;
        }
    }
    

    /**
    * this function assembles the html elements for final composition of the pager
    */
    function PrepareTitleButtons()
    {
        global $http_site_root;
        
        $render_data =& $this->render_data;

        
        //  same as usual except the refresh button isn't clickable 
        // (this is for instances where the data is entirely calculated and out of our control.)
        if($this->show_cached_indicator) {

            if($this->cached_indicator_url) {
                $cache_indicator = "<a onmouseover=\"this.T_OFFSETX=-360; this.T_OFFSETY=10; return escape('" . _('Refresh Data') . "')\" href=\"{$this->cached_indicator_url}\"><img alt=\"" . _('Refresh Pager') . "\" border=0 src=\"$http_site_root/img/refresh.gif\"></a> ";
            } else {
                $cache_indicator = "<img border=0 src=\"$http_site_root/img/refresh.gif\"> ";
            }

        } elseif($this->using_cache) {
            $cache_indicator = "<a onmouseover=\"this.T_OFFSETX=-360; this.T_OFFSETY=10; return escape('" . _('Refresh Data') . "')\" href=javascript:{$this->pager_id}_refresh();><img alt=\"" . _('Refresh Pager') . "\" border=0 src=\"$http_site_root/img/refresh.gif\"></a> ";
        } else {
            $cache_indicator = "";
        }


        if($this->group_mode && !$this->group_mode_paging) {
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

        $showhide_link="<a href=\"#\" id=\"{$this->pager_id}_showhideLink\" onclick=\"javascript:{$this->pager_id}_Hide();\">{$this->hideCaption}</a>";

        $render_data['colspan'] =  count($this->column_info);
        $render_data['showhide_link']       = $showhide_link;
        $render_data['cache_indicator']     = $cache_indicator;
        $render_data['size_buttons']        = $size_buttons;
        $render_data['show_caption_bar']    = $this->show_caption_bar;

    }


    /**
    * public method allows users to add HTML to the bottom of the pager
    * @param string html to append
    */
    function AddEndRows($html) {
        $this->EndRows = $html;
        $this->render_data['end_rows'] = $html;
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
    function RenderTotals($caption, $values, $row_classnames, $col_classnames) {
        if(0 != count($values)) {

            echo "<tr>";
            echo  "<td class=\"widget_label\"><b>$caption</b></td>";

            // starting with 1 because column 1 is used for the header
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
    */



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
    * public method to show the cache indicator
    */
    function SetCachedIndicator($url = null) {
        if($url) $this->cached_indicator_url = $url;
        $this->show_cached_indicator = true;
    }

    /**
    * public method to hide the caption bar (note that this will also hide the refresh button and others!)
    */
    function HideCaptionBar() {
        $this->show_caption_bar = false;
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
            
            $this->order_by = " order by " . implode(', ', $columns);

        } else {

            $this->order_by = " order by " . $this->column_info[$this->sort_column-1]['index_sql'] . " " . $this->sort_order;
        } 

        if(!$this->do_export) {
            $this->get_only_visible = true;
        }
    }
    /**
    *  Public function to enable the export button..also triggers the Javascript code to support the export
    */
    function GetAndUseExportButton($_on_what_table=false,$_on_what_id=false) {
        $this->show_export = true;
        $button_value=_("Export");
        $onclick="document.{$this->form_id}.{$this->pager_id}_export.value='set'; document.{$this->form_id}.submit();";
        return render_export_button($button_value, 'button', $onclick, false, false, $_on_what_table, $_on_what_id);
    }
    /**
    *  Public function to enable debugging output for this pager
    */
    function SetDebug($debug = true) { $this->debug = $debug; }

    /**
    *  Public function to enable debugging output for this pager
    */
    function SetCountSQL($count_sql) { $this->count_sql = $count_sql; }

    /**
    *  Public function to get column info array (sometimes useful for callback)
    */
    function GetColumns() { 
        return $this->column_info; 
    }
    function PrintDebug($string) {
        if ($this->debug) echo $string.'<br>';
    }


}

/**
 * $Log: GUP_Pager.php,v $
 * Revision 1.49  2006/07/12 00:33:41  vanmer
 * - added debug parameter to constructor, to allow debug output during object init
 * - wrapped all debug output in PrintDebug function
 * - added code to allow column info to be passed in as part of a view, in element 'columns'
 * - added code to process incoming object options from view, in element 'options'
 *
 * Revision 1.48  2006/06/02 23:40:34  ongardie
 * - If the callback isn't callable, $this->data needs to be an array anyway.
 *
 * Revision 1.47  2006/01/27 13:46:57  vanmer
 * - changed is_array call to basic if for performance
 * - added parameter to callback for data for pager object
 *
 * Revision 1.46  2006/01/26 17:30:31  daturaarutad
 * check for sql mode before conditional on $this->db->pageExecuteCountRows
 *
 * Revision 1.45  2006/01/26 17:01:13  daturaarutad
 * fix current page session var name
 *
 * Revision 1.44  2006/01/23 23:46:59  daturaarutad
 * add pager object to pager callback params passed; add GetColumns() method
 *
 * Revision 1.43  2006/01/18 21:57:58  daturaarutad
 * move HTML to Pager_Renderer class
 *
 * Revision 1.41  2005/12/12 17:54:44  daturaarutad
 * use export/export.php wrapper for export (sourceforge bug # 1211679)
 *
 * Revision 1.40  2005/12/12 17:10:31  daturaarutad
 * tidy up the appearance a bit
 *
 * Revision 1.39  2005/12/06 18:04:20  daturaarutad
 * add filesize as available column type for rendering
 *
 * Revision 1.38  2005/11/09 23:05:25  daturaarutad
 * do not show page count if no records returned ever; allow modify_data_functions callbacks to change number of records; always get all data if a modify_data_function callback is set (note, this is not the row-by-row callback)
 *
 * Revision 1.37  2005/09/09 22:38:16  daturaarutad
 * Add SetDefaultSortColumn(), which allows override of default_sort field.  Moved sort code into PrepareSort, which is called at beginning of Render().
 *
 * Revision 1.36  2005/08/28 14:58:09  braverock
 * - fixed quoting of "records found" string for i18n
 *
 * Revision 1.35  2005/08/23 18:01:34  daturaarutad
 * fix but with size_buttons not showing (check for group_mode first)
 *
 * Revision 1.34  2005/08/15 00:46:37  daturaarutad
 * added group_mode_paging parameter to constructor to allow paging in group mode; see examples/ for an example
 *
 * Revision 1.33  2005/08/12 20:10:34  daturaarutad
 * add last_group_mode to traack if group column has changed and therefor we need to reset group_id
 *
 * Revision 1.32  2005/07/28 15:41:49  vanmer
 * - added Hide and Show javascript functions to collapse view of pager down to only caption
 * - pass hide/show value in form variables
 * - added event to onload of window, if initial condition of pager should be hidden
 *
 * Revision 1.31  2005/07/26 23:17:34  vanmer
 * - changed to only assign default Group value when group_id not set
 * - changed to only use grouping SQL if group_id value is not blank (allows 0 as a value now)
 *
 * Revision 1.30  2005/07/26 22:46:22  vanmer
 * - changed to only use group sql if group_id contains a value, otherwise use original sql
 *
 * Revision 1.29  2005/07/07 03:25:11  daturaarutad
 * patched to allow $count_sql to be passed in to _adodb_getcount()
 *
 * Revision 1.28  2005/07/06 20:36:59  daturaarutad
 * set $ADODB_COUNTRECS = false before using for CountRecs()
 *
 * Revision 1.27  2005/06/30 16:46:21  vanmer
 * - added parameters to allow on_what_table and on_what_id to be passed into ACL to determine which permissions to
 * use for export button
 *
 * Revision 1.26  2005/06/30 05:03:14  vanmer
 * - altered to use render_export_button ACL function instead of simply displaying the export button
 *
 * Revision 1.25  2005/06/29 22:36:34  daturaarutad
 * show record count in pager
 *
 * Revision 1.24  2005/05/04 19:17:14  daturaarutad
 * added HideCaptionBar() so that caption can be hidden
 *
 * Revision 1.23  2005/05/03 22:09:47  daturaarutad
 * added currency_six_places
 *
 * Revision 1.22  2005/04/21 16:37:43  daturaarutad
 * added SetDebug function for showing some basic debug output
 *
 * Revision 1.21  2005/04/18 21:42:49  daturaarutad
 * added ability to set a URL for the externally-controlled cache_indicator
 *
 * Revision 1.20  2005/04/15 17:25:41  daturaarutad
 * now storing column_info in session always for pager-export.php
 *
 * Revision 1.19  2005/04/13 16:30:58  daturaarutad
 * removed debug msg
 *
 * Revision 1.18  2005/04/13 06:30:36  daturaarutad
 * added export functionality
 *
 * Revision 1.17  2005/04/04 15:19:29  daturaarutad
 * added not_sortable flag for columns
 *
 * Revision 1.16  2005/03/25 23:49:38  daturaarutad
 * Added ModifyData callback
 * fixed up some of the caching
 * fixed cache indicator
 *
 * Revision 1.15  2005/03/17 19:43:12  daturaarutad
 * don't use the cache if there is no sql and the data is not a function
 *
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
