<?php
/**
 * Pager_Columns class
 *
 * This class works with the $columns parameter of GUP_Pager to allow the user to change the pager layout
 *
 * Click the link below to run the example code (you may need to modify the URL of this link)
 *
 * @link http://localhost/xrms/include/classes/Pager/examples/  http://localhost/xrms/include/classes/Pager/examples/
 *
 * @example Pager_Columns.doc.1.php check out
 *
 * $Id: Pager_Columns.php,v 1.29 2006/07/25 19:53:44 vanmer Exp $
 */
require_once('view_functions.php');

class Pager_Columns {

/**
 * 
**/
    var $pager_name;
    var $pager_columns;
    var $pager_views;

    var $default_columns;
    var $form_id;
    var $visible_column_size;
    var $con;
    var $user_id;
    var $view_name;
    var $debug;
    var $action;
    var $view_criteria;

    /**
    * Constructor for the Pager_Columns class.  
    *
    * Requires at least a pager_name and form_id to operate properly
    * Default columns will be taken as all columns from pager_columns, if not provided explicitly
    *
    * @param string pager name/identifier
    * @param array pager columns to modify
    * @param array column names to use as 'default' view
    * @param string needed for JS to modify form
    * @param integer vertical height of multi-select widget
    * @param adodbconnection optionally providing a database connection to use for saved views
    * @param array optionally providing an extra "criteria" array to associate with a view when it is saved (can be retrieved when view is loaded using GetViewCriteria
    * @param boolean indicating if debug output should be displayed
    */
    function Pager_Columns($pager_name, $pager_columns, $default_columns, $form_id, $visible_column_size=6, $con=false, $view_criteria=false, $debug=false) {

        //set user from global $session_user_id variable
        global $session_user_id;
        $this->user_id=$session_user_id;

        //get dbconnection if not provided
        if (!$con) $this->con = get_xrms_dbconnection();
        else $this->con = $con;

        if ($debug) $this->debug=true;

        $this->SetViewCriteria($view_criteria);

        $this->pager_name = $pager_name;

        getGlobalVar($view_name, $pager_name . '_view_name');
        //ensure view_name and view_key are populated
        if (!$view_name) {
            //if no view is specified, set action to load
            $this->action='load';
            $view_name=$this->GetCurrentViewName();
            $view_name="{$this->view_key}_$view_name";
        } else {
            $this->SetViewName($view_name);
        }

        //set default columns incoming
        $this->default_columns = $default_columns;

        //set pager columns based on incoming array
        $this->SetPagerColumns($pager_columns);

        $this->form_id = $form_id;
	$this->visible_column_size=$visible_column_size;


        //initialize all view-related data
        $this->init_views();
 
        //ensure view name is set in session
        $this->StoreViewName($view_name);

        //process and handle any actions from GET/POST
        $this->handleFormActions();


    }

    /**
    * Sets the internal variable storing the data which will be stored along with a view
    *
    * @param array of data to be stored and retrieved along with the view
    *
    **/
    function SetViewCriteria($view_criteria) {
        $this->view_criteria=$view_criteria;
    }

    /**
    * Function to store the passed criteria directly into the view if the view has been saved
    * This function should be run when setting view criteria after the constructor returns
    * This will allow the new criteria to be saved along with the view, if the view is set to be saved
    *
    * @param array of data to be stored and retrieved along with the view
    *
    **/
    function SetCurrentViewCriteria($view_criteria) {
        $this->SetViewCriteria($view_criteria);
        if ($this->action=='save') {
            //save new criteria to the view if it is not already there
            $this->SaveView();
        }
    }

    /**
    * Function to set the pager columns array, the complete set of pager columns
    * which to restrict based on columns from the view.
    * Should be run when setting the pager columns data, as it sets up the columns indexed by key
    * Also sets the default columns if they are not already set
    *
    * @param array of pager columns data to be restricted and passed back when retrieving the view
    *
    *
    **/
    function SetPagerColumns($pager_columns) {
        if (!$pager_columns) return false;
        $columns = array();

        // internally we always refer to the thing by the value
        foreach($pager_columns as $pager_column) {

            if($pager_column['index_sql']) {
                $columns[$pager_column['index_sql']] = $pager_column;
            } elseif($pager_column['index_calc']) {
                $columns[$pager_column['index_calc']] = $pager_column;
            } elseif($pager_column['index_data']) {
                $columns[$pager_column['index_data']]= $pager_column;
            } elseif($pager_column['index']) {
                $columns[$pager_column['index']]= $pager_column;
            }
        }

        $pager_columns = $columns;

        if(!$this->default_columns) {
            $this->printDebug("No default columns provided.  Settings default columns based on provided pager columns");
            $this->default_columns = array_keys($pager_columns);
        }

        if ($columns) $this->pager_columns=$columns;

    }

    /**
    * Function to set the internal variables for view name and key based on a single string
    *
    * @param string with the view name, in format {view_key}_{view_name} (replacing {view_key} and {view_name} with their respective values
    *
    **/
    function SetViewName($view_name) {
        $view_arr=explode("_",$view_name);
        $this->view_key=array_shift($view_arr);
        $view_name=implode("_",$view_arr);
        $this->view_name=$view_name;
    }

    /**
    * Function to store the current view name in the session, as well as in internal variables
    *
    * @param string with the view name, in format {view_key}_{view_name} (replacing {view_key} and {view_name} with their respective values
    *
    **/
    function StoreViewName($view_name) {
        $this->SetViewName($view_name);
        // store view name in the session
        $_SESSION[$this->pager_name . '_columns_view'] = $view_name;
    }

    /**
    * Function to initialize views, called by the constructor
    *
    **/
    function init_views() {
        //initialize view table
        //commented, probably not required here if user runs the upgrade script
       //initViews($this->con);

        // read the views from the views API
        $this->readViews();

    }

    /**
    * Function to write the view data to the views API
    *
    **/
    function writeViews() {
        //retrieve view based on current view key
        $views=$this->pager_views[$this->view_key];

        //store global views in user_id 0
        if ($this->view_key=="global") $user_id=0;
        else $user_id=$this->user_id;

        //write views for this pager to the views API
        writeViews($this->con, $this->pager_name, $user_id, $views);

    }

    /**
    * Function to read the view data from the views API
    *
    **/
    function readViews() {
        /** read global views **/
        $views=readViews($this->con, $this->pager_name, 0);
        $this->pager_views['global']=$views;

        /** read user views **/
        $views=readViews($this->con, $this->pager_name, $this->user_id);
        $this->pager_views['user'] = $views;


        /** ensure that default values are properly set **/
        $this->checkDefaultViews();
    }

    /**
    * Function wrapper for administrator check in views API, 
    * run to ensure that user is an administrator with permission to alter global views
    *
    **/
    function checkAdmin() {
        //run API admin check for the current user
        return checkViewAdmin($this->con, $this->user_id);
    }

    /**
    * Function to ensure that default values are set in the pager views
    * run after views are loaded so user always has at least a default view
    *
    **/
    function checkDefaultViews() {
        // look for this pager in the preferences array, initialize one (default view) and write if it does not exist.
        if(!isset($this->pager_views) || !is_array($this->pager_views) || !isset($this->pager_views['global']['default'])) {

            $this->pager_views['global']['default'] = $this->default_columns;
        }

        //ensure that the user has a default set to work with
        if(!isset($this->pager_views) || !is_array($this->pager_views) || !isset($this->pager_views['user']['default'])) {
            $this->pager_views['user']['default'] = $this->pager_views['global']['default'];
            $this->writeViews();
        }

        //always add an option for the system default, as passed in
        $this->pager_views['system']['default']=$this->default_columns;
    }

    /**
    * Function to process any form actions, and run the appropriate method
    * run in the constructor so that every page load can have form actions processed
    *
    **/
    function handleFormActions() {

        //retrieve GET/POST action variable
        getGlobalVar($pager_columns_action, $this->pager_name . '_pager_columns_action');


        if ($pager_columns_action) {
            //set the action internally
            $this->action=$pager_columns_action;
        }

        // if we are saving the view
        if('save' == $this->action) {
            $this->SaveView();

        // if we are deleting the view
        } elseif ('delete' == $this->action) {
            $this->DeleteView();

        // if we are loading the view
        } elseif ('load' == $this->action) {
            $this->LoadView();
        }


    }

    /**
    * Function to run when loading a view (currently does nothing)
    *
    **/
    function LoadView() {
        //stub function to run when form action is load
    }


    /**
    * Function to retrieve any parameters for the associated pager from GET/POST
    * Used to save these parameters when storing the view
    *
    * @return array of variables to be set internally in the GUP_Pager
    *
    **/
    function GetPagerParams() {
        $options=array();
        /**
          * collection of pager parameters to store
          * These parameters are stored keyed by associated parameter.  The value is used to retrieve 
          * the form variable in question, and this value is stored in the pager parameters, keyed for 
          * both the key and the value
        **/
        $param_names=array("sort_order"=>"current_sort_order","sort_column"=>"current_sort_column","group_mode"=>"last_group_mode","group_id"=>"group_id");

        //loop on available parameters
        foreach ($param_names as $pkey=>$param) {
            getGlobalVar($pval,"{$this->pager_name}_$param");
            if ($pval OR $pval===0 OR $pval==='0') { 
                //set value for both key and value of available parameters
                $options[$pkey]=$pval;
                $options[$param]=$pval;
                $this->PrintDebug("SAVING PAGER {$this->pager_name} PARAM $pkey:$param {$options[$pkey]}");
            }
        }

        //if any parameters were available, then ensure that resort is set to false and next page is set to 1
        if (count($options)>0) {
            $options['resort']='false';
            $options['next_page']=1;
            return $options;
        }

        //by default return false
        return false;
    }



    /**
    * Function to create the view data structure that will be saved using the saved views API
    *
    * @return array of view data with keys 'pager_options', 'columns' and optionally 'view_criteria'
    **/
    function PrepareViewData() {
        $view=array();
        // getGlobalVar for the user_columns, as array
        getGlobalVar($user_columns, $this->pager_name . '_pager_columns');

        //retrieve pager options from the form variables
        $view['pager_options']=$this->GetPagerParams();

        //set columns based on selected user columns
        $view['columns']=$user_columns;

        //if view criteria has been provided, add it to the view data being saved
        if ($this->view_criteria) $view['view_criteria']=$this->view_criteria;

        return $view;
    }

    /**
    * Function to run in order to save the current view
    *
    **/
    function SaveView() {
        //retrieve form variables for new view name and global flag
        getGlobalVar($view_name_new, $this->pager_name . '_view_name_new');
        getGlobalVar($view_global, $this->pager_name . '_view_new_global');

        // use new name first or selected name
        $view_name = $view_name_new ? $view_name_new : $this->view_name;

        //make view data to store
        $view_data=$this->PrepareViewData();

        //if the global flag is set, assume view key is global
        if ($view_global) $this->view_key="global";

        // ensure that user can only write to a global view if admin check is run
        if ($this->view_key=="global" AND !$this->checkAdmin()) {
            $this->view_key="user";
        }

        //ensure that the system view is never saved
        if ($this->view_key=='system') $this->view_key="user";

        $this->PrintDebug("Saving {$this->view_key} view $view_name");

        //set the view into the internal collection of views for the current pager
        $this->pager_views[$this->view_key][$view_name] = $view_data;

        // write the views for this view key
        $this->writeViews();

        //set view to saved key/view
        $this->StoreViewName("{$this->view_key}_$view_name");
    }

    /**
    * Function to run when deleting the current view
    *
    **/
    function DeleteView() {
       //ensure user is admin before allowing removal of global 
       if ($this->view_key=="global" AND !$this->checkAdmin()) return false;

        // remove from array 
        unset($this->pager_views[$this->view_key][$this->view_name]);

        // write out views
        $this->writeViews();

        //ensure that if default was removed that it gets replaced
        $this->checkDefaultViews();

        // default always exists (see above)
        $this->StoreViewName('user_default');
    }

    /**
    * Function to turn available views into an HTML string for use in a select dropdown
    *
    * @return string with <option> tags
    **/
    function RenderViewOptions() {
        $view_options='';
        foreach ($this->pager_views as $vkey=>$views) {

            //add strings for opt groups, to be internationalized
            $s=_("GLOBAL");
            $s=_("USER");
            $s=_("SYSTEM");
            //add an optgroup around each type of view key
            $group_label=_(strtoupper($vkey));
            $view_options.="<optgroup LABEL=\"$group_label\">";

            foreach(array_keys($views) as $option_view_name ) {
                //ensure that "default" named option is internationalized
                if ($option_view_name=='default') $option_view_name=_("default");

                //set string for view key/view name
                $value="{$vkey}_$option_view_name";
                $current_view="{$this->view_key}_{$this->view_name}";
                // create <option> tag for view
               $view_options .= "<option value=\"$value\"" . (($value==$current_view) ? ' selected="selected"' : '') . ">$option_view_name</option>\n";
            }
            //end optgroup for this view key
            $view_options.="</optgroup>";
        }
        return $view_options;
    }

    /**
    * Returns an array of column names for this session's view (uses 'default' if no view set)
    *
    * @return array Column Names
    */
    function GetUserColumnNames() {

        // User Columns will come from the session or default list passed in
        $view_name = $this->GetCurrentViewName();

        $this->PrintDebug("GRABBING USER COLUMNS FOR {$this->view_key} VIEW $view_name");
        $columns =  $this->pager_views[$this->view_key][$view_name];
        if ($columns['columns']) $columns=$columns['columns'];

        return $columns;
    }

    /**
    * Returns the current view name from the session (if stored there)
    * Also sets the internal variables for view_name and view_key
    *
    * @return string with current view name (not including key)
    */
    function GetCurrentViewName() {
        $view_name=isset($_SESSION[$this->pager_name . '_columns_view']) ? $_SESSION[$this->pager_name . '_columns_view'] : 'user_default';
        $this->SetViewName($view_name);
        $this->PrintDebug("CURRENT PAGER {$this->pager_name} {$this->view_key} VIEW NAME IS {$this->view_name}");
        return $this->view_name;
    }

    /**
    * Gets view data for a view (or the current view if not provided)
    * Used as the base data when passing back the view to the user
    * Only sets 'options' and 'criteria' when view is being loaded
    *
    * @return array with view data, including possible keys 'pager_options' 'columns' 'view_criteria'
    */
    function GetViewData($view_name=false) {
        if (!$view_name) $view_name = $this->GetCurrentViewName();

        $view_data=$this->pager_views[$this->view_key][$view_name];


        if ($this->action=='load') {
            if ($view_data['pager_options']) {
                $this->PrintDebug("Loading Pager Options For Pager {$this->pager_name}");
                //print_r($view_data['pager_options']);
                $view_data['options']=$view_data['pager_options'];
            }
            if ($view_data['view_criteria']) {
                $this->PrintDebug("Loading View Criteria For Pager {$this->pager_name}");
                $view_data['criteria']=$view_data['view_criteria'];
            }
        }
//        print_r($view_data);

        return $view_data;
    }

    /**
    * Function to call when retrieving a view for use with a GUP_Pager
    * Retrieves the current view data, and sets columns as needed by the GUP_Pager
    *
    * @return array with view data, including possible keys 'columns' 'options' 'criteria'
    */
    function GetUserView() {
        $view_data=$this->GetViewData();
        $view_data['columns']=$this->GetUserPagerColumns();
        return $view_data;
    }

    /**
    * Deprecated function wrapper for GetUserView
    * Was previous used to retrieve and return columns for use in a GUP_Pager
    *
    * @return array with view data, including possible keys 'columns' 'options' 'criteria'
    */
    function GetUserColumns() {
        return $this->GetUserView();
    }

    /**
    * Function to call when retrieving the criteria set on a view
    * Retrieves the current view data criteria if no view name is provided
    * Only returns the criteria if it is provided in the view data
    * This means criteria is only returned when the view is being loaded (load button clicked)
    *
    * @return array with view criteria, as passed in/saved with the view
    */
    function GetViewCriteria($view_name=false) {
        $view_data=$this->GetViewData($view_name);
        if (array_key_exists('criteria',$view_data)) {
            return $view_data['criteria'];
        }

        return false;
    }

    /**
    * Returns the sub-array of the initial pager_columns array to be used with the pager
    *
    * @return array pager_columns 
    */
    function GetUserPagerColumns() {

        $return_pager_columns = array();
        $user_columns = $this->GetUserColumnNames();

        foreach($user_columns as $user_column) {
            if(isset($this->pager_columns[$user_column]['name'])) {
                $return_pager_columns[] = $this->pager_columns[$user_column];
            }
        }
        return $return_pager_columns;
    }
    /**
    * Returns the button to un-hide the widget
    *
    * @return string HTML for Select Column Layouts button
    */
    function GetSelectableColumnsButton() {

        return '<input type="button" class="button" onclick="document.getElementById(\'' . $this->pager_name . '_widget\').style.display=\'block\'; location.href=\'#' . $this->pager_name . '_select_columns\';" value="' . _("Select Column Layouts") . '">';
    }

    /**
    * Returns the Selectable Columns Widget
    *
    * It is assumed that this widget will be placed within a <form> and the form_id is passed in the constructor.
    *
    * @return string HTML for Select Column Layouts widget
    */
    function GetSelectableColumnsWidget() {
        global $http_site_root;
        // Get the column names
        $user_columns = $this->GetUserColumnNames();

        $widget_name = $this->pager_name . '_widget';

        //store element names for later use ("__ContactPager_pager_columns[]")
        $select_name = $this->pager_name . '_pager_columns';
        $select_name_displayed = '_' . $select_name;
        $select_name_avail = '__' . $select_name;

        // compile available columns (name-displayname)
        foreach($this->pager_columns as $pager_column_index => $pager_column) {
            $avail_columns[$pager_column_index] = $pager_column['name'];
        }

        // Include PEAR advmultiselect files
		global $include_directory;
		set_include_path(get_include_path().PATH_SEPARATOR.$include_directory."classes");
        require_once 'HTML/QuickForm.php';
        require_once 'HTML/QuickForm/advmultiselect.php';
        require_once 'HTML/QuickForm/Renderer/Default.php';

        // amsBasic1 and name are ignored since we use our own FormTemplate
		$form = new HTML_QuickForm('amsBasic1');
		$form->removeAttribute('name');        // XHTML compliance

	
		$ams =& $form->addElement('advmultiselect', $select_name, null, $avail_columns, array('size' => $this->visible_column_size));

        // This determines which items appear in the "Displayed Columns" side
        $form->setConstants(array($select_name => $user_columns));

        $widget_caption = _("Select Columns for Display");
        $displayed_text = _("Displayed Columns");
        $avail_text = _("Available Columns");
        $text_save_columns = _("Save");
        $text_load_columns = _("Load");
        $text_cancel_columns = _("Hide");
        $text_delete_columns = _("Delete");
        $view_options=$this->RenderViewOptions();
        if ($this->checkAdmin()) $global_option_str="<input type=checkbox name={$this->pager_name}_view_new_global>Global<br/>";
        else $global_option_str='';

        $ams->setButtonAttributes('add'     , 'class=button');
        $ams->setButtonAttributes('remove'  , 'class=button');
        $ams->setButtonAttributes('moveup'  , 'class=button');
        $ams->setButtonAttributes('movedown', 'class=button');
        
        // template for a dual multi-select element shape
        $template = "
        <table class=widget>
        <tr><td class=widget_header colspan=4>$widget_caption <div class=\"right\"><a href='' onclick=\"document.getElementById('{$widget_name}').style.display = 'none'\" >$text_cancel_columns</a></div></td></tr>
        <tr>
        <td class=\"widget_content_center\">
            <table>
                <tr><td>
                        "._("New View")."<br/>
                        <input type=text size=10 name={$this->pager_name}_view_name_new><br/>
                        $global_option_str
                        "._("View Name")."<br/>
                        <select name={$this->pager_name}_view_name>
                            {$view_options}
                        </select>
                    </td>
                </tr>


                <tr><td> 
            <input type=\"button\" class=\"button\" name=\"button\" onclick=\"{$this->pager_name}_save_view(this)\" value=\"$text_save_columns\"><br/>
            <input type=\"button\" class=\"button\" name=\"button\" onclick=\"{$this->pager_name}_load_view(this)\" value=\"$text_load_columns\"><br/>
            <input type=\"button\" class=\"button\" name=\"button\" onclick=\"{$this->pager_name}_delete_view(this)\" value=\"$text_delete_columns\"><br/>
                </td></tr>
            </table>
        </td>
        <td>$avail_text<br/>{unselected}</td>
        <td align=\"center\">
            {add}<br />{remove}<br /><br />{moveup}<br />{movedown}<br />
        </td>
        <td>$displayed_text<br/>{selected}</td>

        </tr>
        </table>
        ";

        $ams->setElementTemplate($template);

		
        $s = $ams->getElementJs(false);

        $renderer =& new HTML_QuickForm_Renderer_Default();

        $form_template = <<<END
        <script language="JavaScript" src="{$http_site_root}/js/jsSelect.js"></script>

        <input type="hidden" name="{$this->pager_name}_pager_columns_action">

        <script language="JavaScript" type="text/javascript">
            function {$this->pager_name}_save_view(obj) {
                document.{$this->form_id}.{$this->pager_name}_pager_columns_action.value = 'save';
                document.{$this->form_id}.submit();
            }
            function {$this->pager_name}_load_view(obj) {
                document.{$this->form_id}.{$this->pager_name}_pager_columns_action.value = 'load';
                document.{$this->form_id}.submit();
            }
            function {$this->pager_name}_delete_view(obj) {
                document.{$this->form_id}.{$this->pager_name}_pager_columns_action.value = 'delete';
                document.{$this->form_id}.submit();
            }
        </script>


        <!-- the hidden div -->
        <div class="PagerSelectableColumns" id="{$this->pager_name}_widget">
        <a name="{$this->pager_name}_select_columns"></a>
{hidden}
<table border=\"0\">\n{content}\n</table>\n
        </div> <!-- PagerSelectableColumns -->


<script language="JavaScript" type="text/javascript">
    document.getElementById('{$widget_name}').style.display = 'none';
</script>
END;

        $renderer->setFormTemplate($form_template);
        //$renderer->setFormTemplate("\n\n<div>\n{hidden}<table border=\"0\">\n{content}\n</table>\n</div>\n");

        $form->accept($renderer);

        $s .= $renderer->toHtml();

        return $s;
    }


    /**
    * Function to trap debug output
    * Outputs only when debug is set internally
    *
    */
    function PrintDebug($string) {
        if ($this->debug) echo "$string<br/>\n";
    }

    /**
    * Function to set the internal debug variable
    * Defaults to turning debug on
    *
    */
    function SetDebug($debug=true) {
        $this->debug=$debug;
    }


}
/**
 * $Log: Pager_Columns.php,v $
 * Revision 1.29  2006/07/25 19:53:44  vanmer
 * - added informative debug output
 *
 * Revision 1.28  2006/07/19 01:46:55  vanmer
 * - changed to ensure that programmatic default is set if not provided at constructor level
 * - added translation of optgroup names and "default" entries
 *
 * Revision 1.27  2006/07/19 01:38:04  vanmer
 * - added code to always load last view when returning to a page
 * - added code to set view to be loaded if no view is specified when visiting the page
 *
 * Revision 1.26  2006/07/14 03:45:36  vanmer
 * - patch to ensure that user columns inherit from global defaults
 *
 * Revision 1.25  2006/07/14 03:17:19  vanmer
 * - commented initViews functions, now run when XRMS is upgraded
 * - added phpdoc to new Pager_Columns functionality
 *
 * Revision 1.24  2006/07/13 00:13:39  vanmer
 * - Added functionality for global, user and system options for pagers
 * - Changed to save/retrieve pager views using new external view functions
 * - Added checks to ensure only administrative users can save global views
 *
 * Revision 1.23  2006/07/12 03:48:36  vanmer
 * -Initial revision of saving views including pager parameters
 * -Expanded view functions to fetch and set pager options
 *
 * Revision 1.22  2006/07/12 01:01:03  vanmer
 * - cleaned up constructor, moved relevant code to new functions
 * - changed render of view options to happen when actually rendering the rest of the columns widget
 * - intial steps towards altering how pager columns outputs views
 *
 * Revision 1.21  2006/03/17 00:11:40  vanmer
 * - added extra parameters when creating user preference type, to hide option from system preferences menu
 *
 * Revision 1.20  2006/03/12 09:29:27  vanmer
 * - added missing site root global, needed for path to javascript include file
 *
 * Revision 1.19  2006/02/01 23:30:32  daturaarutad
 * rearrange buttons in template
 *
 * Revision 1.18  2006/02/01 22:37:03  daturaarutad
 * call set_include_path() before including PEAR HTML classes, add & to address advmultiselect by reference
 *
 * Revision 1.17  2006/02/01 21:41:00  daturaarutad
 * big update to use HTML_QuickForm_advmultiselect class for widget and allow saveable views
 *
 * Revision 1.16  2006/01/28 13:07:16  vanmer
 * - added parameter to control the number of rows in each selectable column box
 *
 * Revision 1.15  2005/11/14 20:45:41  daturaarutad
 * fix accidental commit
 *
 * Revision 1.13  2005/08/28 18:06:04  braverock
 * - romove colspan from Layouts tag
 *
 * Revision 1.12  2005/08/28 15:15:36  braverock
 * - add htmlspecialchars to localized buttons
 *
 * Revision 1.11  2005/08/28 15:12:14  braverock
 * - localized selectable columns widget header and move buttons
 *
 * Revision 1.10  2005/08/25 22:38:43  braverock
 * - fix HTML compliance for IE parsing
 *   - patch provided by "Holger G. Hahn" <hghahn [at] daybyday [dot] de>
 *
 * Revision 1.9  2005/08/16 00:34:57  vanmer
 * - added class for div of selectable columns widget
 *
 * Revision 1.8  2005/06/28 18:43:34  daturaarutad
 * fixed reset button issue
 *
 * Revision 1.7  2005/03/25 20:05:26  daturaarutad
 * enhancement to the Update/Reset/Cancel columns buttons and their behavior
 *
 * Revision 1.6  2005/03/15 22:28:05  daturaarutad
 * removed setting of sql_sort_column, its now it GUP_Pager::SetUpSQLOrderByClause
 *
 * Revision 1.5  2005/03/10 20:38:09  daturaarutad
 * no longer require default_columns
 *
 * Revision 1.4  2005/02/25 03:45:59  daturaarutad
 * code to set up sql_sort_column if it was not set by the user
 *
 * Revision 1.3  2005/02/07 19:12:34  daturaarutad
 * updated to work with the GU_Pager
 *
 * Revision 1.2  2005/01/25 04:00:55  daturaarutad
 * added anchor to jump up to selectable columns div when button is pressed to unhide it
 *
 */

?>