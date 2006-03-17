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
 * $Id: Pager_Columns.php,v 1.21 2006/03/17 00:11:40 vanmer Exp $
 */

class Pager_Columns {

    var $pager_name;
    var $pager_columns;
    var $prefs_columns;
    var $default_columns;
    var $form_id;
    var $visible_column_size;

    /**
    * @param string pager name/identifier
    * @param array pager columns to modify
    * @param array column names to use as 'default' view
    * @param string needed for JS to modify form
    * @param integer vertical height of multi-select widget
    */
    function Pager_Columns($pager_name, $pager_columns, $default_columns, $form_id, $visible_column_size=6) {

        global $session_user_id;
        $con = get_xrms_dbconnection();

        getGlobalVar($pager_columns_action, $pager_name . '_pager_columns_action');
        getGlobalVar($view_name, $pager_name . '_view_name');
        getGlobalVar($view_name_new, $pager_name . '_view_name_new');

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

        if(!$default_columns) {
            $default_columns = array_keys($pager_columns);
        }


        // this function checks for existance first and creates if it does not exist, setting skip system edit to true
        add_user_preference_type($con, 'pager_columns', _("Pager Columns Display Settings"),false,false,false,false,false,true);


        // read this user's pager_columns preference 
        $this->prefs_columns = unserialize(get_user_preference($con, $session_user_id, 'pager_columns', false, false, false));

        // look for this pager in the preferences array, initialize one (default view) and write if it does not exist.
        if(!isset($this->prefs_columns[$pager_name]) || !is_array($this->prefs_columns[$pager_name])) {

            $this->prefs_columns[$pager_name]['default'] = $default_columns;

            set_user_preference($con, $session_user_id, 'pager_columns', serialize($this->prefs_columns), false, true);
        }
 

        // if we are saving user's prefs..
        if('save' == $pager_columns_action) {

            // use new name first or selected name
            $view_name = $view_name_new ? $view_name_new : $view_name;

            // getGlobalVar for the user_columns, as array
            getGlobalVar($user_columns, $pager_name . '_pager_columns');
            // set this view-name's columns list
            $this->prefs_columns[$pager_name][$view_name] = $user_columns;
            // write
            set_user_preference($con, $session_user_id, 'pager_columns', serialize($this->prefs_columns), false, true);

        } elseif ('delete' == $pager_columns_action) {
            // remove from array 
            unset($this->prefs_columns[$pager_name][$view_name]);
            // write
            set_user_preference($con, $session_user_id, 'pager_columns', serialize($this->prefs_columns), false, true);
            // default always exists (see above)
            $view_name = 'default';
        }

        // store view name in the session
        $_SESSION[$pager_name . '_columns_view'] = $view_name;

        // create <option> tags for views list
        foreach(array_keys($this->prefs_columns[$pager_name]) as $option_view_name ) {
            $this->view_options .= "<option value=\"$option_view_name\"" . ($view_name == $option_view_name ? ' selected="selected"' : '') . ">$option_view_name</option>\n";
        }


        $this->pager_name = $pager_name;
        $this->pager_columns = $pager_columns;
        $this->default_columns = $default_columns;
        $this->form_id = $form_id;
	    $this->visible_column_size=$visible_column_size;
    }

    /**
    * Returns an array of column names for this session's view (uses 'default' if no view set)
    *
    * @return array Column Names
    */
    function GetUserColumnNames() {

        // User Columns will come from the session or default list passed in
        $view_name = isset($_SESSION[$this->pager_name . '_columns_view']) ? $_SESSION[$this->pager_name . '_columns_view'] : 'default';
            
        $columns =  $this->prefs_columns[$this->pager_name][$view_name];

        return $columns;
    }


    /**
    * Returns the sub-array of the initial pager_columns array to be used with the pager
    *
    * @return array pager_columns 
    */
    function GetUserColumns() {

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

        $widget_caption = _('Select Columns for Display');
        $displayed_text = _('Displayed Columns');
        $avail_text = _('Available Columns');
        $text_save_columns = _("Save");
        $text_load_columns = _("Load");
        $text_cancel_columns = _("Hide");
        $text_delete_columns = _("Delete");

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
                        "._('View Name')."<br/>
                        <input type=text size=10 name={$this->pager_name}_view_name_new><br/>
                        <select name={$this->pager_name}_view_name>
                            {$this->view_options}
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

}
/**
 * $Log: Pager_Columns.php,v $
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