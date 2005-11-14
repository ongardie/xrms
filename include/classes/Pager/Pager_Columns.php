<?php
/**
 * Pager_Columns class
 *
 * This class works with the $columns parameter of XRMS_Pager to allow the user to change the pager layout
 *
 * How it works:
 *
 *  - user loads script including pager
 *  - script creates Pager_Columns object
 *  - Pager_Columns object checks to see if new columns have been selected by checking for a CGI var Portfolio1_New_Columns_View,
 *    adding them to session
 *  - pager_columns checks to see if there is a column list in the session (for Portfolio1)
 *  - if there is, it does the thing to drop teh columns
 *  - if not, it does the same, but with the default list
 *  - page renders
 *
 *  - user clicks 'display selectable columns'
 *  - div hidden toggles and changes text to 'hide selectable columns'
 *  - user clicks around and at some point is happy with their columns list and
 *    clicks 'update column view for this session' button which triggers a javascript function
 *  - javascript packs the column list into a variable along with the pager name, submits form
 *
 * Click the link below to run the example code (you may need to modify the URL of this link)
 *
 * @link http://localhost/xrms/include/classes/Pager/examples/  http://localhost/xrms/include/classes/Pager/examples/
 *
 * @example Pager_Columns.doc.1.php check out
 *
 * $Id: Pager_Columns.php,v 1.15 2005/11/14 20:45:41 daturaarutad Exp $
 */

class Pager_Columns {

    var $pager_name;
    var $pager_columns;
    var $default_columns;
    var $form_id;

    function Pager_Columns($pager_name, $pager_columns, $default_columns, $form_id) {

        getGlobalVar($new_columns_view, $pager_name . '_New_Columns_View');


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

        if($new_columns_view) {

            // getGlobalVar for the user_columns, pack into an array $user_columns
            getGlobalVar($new_columns, $pager_name . '_New_Columns');

            $user_columns = explode(',', $new_columns);
            //$user_columns = array(explode(',' $new_columns));

            // set it in the session
            $_SESSION[$pager_name . '_columns_view'] = $user_columns;
        }


        if(!$default_columns) {
            foreach($pager_columns as $index => $pager_column) {
                $default_columns[] = $index;
            }
        }

        $this->pager_name = $pager_name;
        $this->pager_columns = $pager_columns;
        $this->default_columns = $default_columns;
        $this->form_id = $form_id;
    }

    function GetUserColumnNames() {

        // User Columns will come from the session or default list passed in
        if($_SESSION[$this->pager_name . '_columns_view']) {
            return $_SESSION[$this->pager_name . '_columns_view'];
        } else {
            return $this->default_columns;
        }
    }

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

    function GetSelectableColumnsButton() {

        return '<input type="button" class="button" onclick="document.getElementById(\'' . $this->pager_name . '_widget\').style.display=\'block\'; location.href=\'#' . $this->pager_name . '_select_columns\';" value="' . _("Select Column Layouts") . '">';
    }
    function GetSelectableColumnsWidget() {

        global $http_site_root;


        $user_columns = $this->GetUserColumnNames();
        $avail_columns_options = '';
        $display_columns_options = '';
        $reset_columns_rows = '';

        $text_update_columns = _("Update View");
        $text_reset_columns = _("Reset View");
        $text_cancel_columns = _("Cancel Changes");

        $widget_name = $this->pager_name . '_widget';

        // create localized strings for move buttons
        $moveone = htmlspecialchars(_("one"));
        $moveall = htmlspecialchars(_("move all"));

        // add currently displayed columns
        foreach($user_columns as $user_column) {
            if(isset($user_column)) {
                $display_columns_options .= "<option value=\"$user_column\">{$this->pager_columns[$user_column]['name']}</option>\n";
            }
        }

        // add available columns
        foreach($this->pager_columns as $pager_column_index => $pager_column) {


            if(!in_array($pager_column_index, $user_columns)) {
                $avail_columns_options .= "<option value=\"$pager_column_index\">{$pager_column['name']}</option>\n";
            }

            // if in the default columns list, add to displayed, otherwise add to avail when reset button pressed
            if(true === in_array($pager_column_index, $this->default_columns)) {
                $reset_columns_rows .= "\t\taddOption('{$this->pager_name}_displayColumns', '{$pager_column['name']}', '$pager_column_index');\n";
            } else {
                $reset_columns_rows .= "\t\taddOption('{$this->pager_name}_availColumns', '{$pager_column['name']}', '$pager_column_index');\n";
            }
        }

        $s = <<<END
        <script language="JavaScript" src="{$http_site_root}/js/jsSelect.js"></script>

        <input type="hidden" name="{$this->pager_name}_New_Columns_View">
        <input type="hidden" name="{$this->pager_name}_New_Columns">

        <script language="JavaScript" type="text/javascript">

            function {$this->pager_name}_columns_change(obj) {

                document.{$this->form_id}.{$this->pager_name}_New_Columns_View.value = true;

                var objList = document.getElementById('{$this->pager_name}_displayColumns');
                var strCurItemValue = '';

                for (var i=0; i<objList.options.length; i++ )
                {
                    strCurItemValue += objList.options[i].value + ",";
                }
                document.{$this->form_id}.{$this->pager_name}_New_Columns.value = strCurItemValue;

                document.{$this->form_id}.submit();
            }

            function {$this->pager_name}_reset_default(obj) {
                deleteAllOption(document.getElementById('{$this->pager_name}_displayColumns'));
                deleteAllOption(document.getElementById('{$this->pager_name}_availColumns'));

                $reset_columns_rows;

                {$this->pager_name}_columns_change(obj);
            }
        </script>


        <!-- the hidden div -->
        <div class="PagerSelectableColumns" id="{$this->pager_name}_widget">
        <a name="{$this->pager_name}_select_columns"></a>
        <table class=widget cellspacing=1>
            <tr>
                <td class="widget_header" colspan=5>
                    Select Columns
                </td>
            </tr>
            <tr>
                <td class="widget_content" colspan=2 align="center">
                    <b>Displayed Columns</b>
                </td>
                <td class="widget_content" colspan=2 align="center">
                    <b>Available Columns</b>
                </td>
                <td class="widget_content" align="center">
                    <b>Layouts</b>
                </td>
            </tr>
            <tr align="center">

                <td class="widget_content">
                    <input type="button" class="button" onClick="shiftListItem('up', '{$this->pager_name}_displayColumns')" value="/\"><br/><input type="button" class="button" onClick="shiftListItem('dn', '{$this->pager_name}_displayColumns')" value="\/">
                </td>
                <td class="widget_content">
                    <select name="{$this->pager_name}_displayColumns"
                            id="{$this->pager_name}_displayColumns"
                            size="4"
                            class="myList"
                            onFocus="objFocusList = this"
                            onChange="setDisplay(this)">
            $display_columns_options
            </select>
<input type="button" class="button" onClick="moveListItemAll( '{$this->pager_name}_displayColumns', '{$this->pager_name}_availColumns' )" value="$moveall ->">

        </td>

        <td class="widget_content">
            <input type="button" class="button" onClick="moveListItem( '{$this->pager_name}_availColumns', '{$this->pager_name}_displayColumns' )" value="<- $moveone"><br/><input type="button" class="button" onClick="moveListItem( '{$this->pager_name}_displayColumns', '{$this->pager_name}_availColumns' )" value="$moveone ->"><br/>        </td>

        <td class="widget_content">
            <select name="{$this->pager_name}_availColumns"
                    id="{$this->pager_name}_availColumns"
                    size="4"
                    class="myList"
                    onFocus="objFocusList = this"
                    onChange="setDisplay(this)">
            $avail_columns_options
            </select>
<input type="button" class="button" onClick="moveListItemAll( '{$this->pager_name}_availColumns', '{$this->pager_name}_displayColumns' )" value="<- $moveall">
        </td>

        <td class="widget_content_center">

            <input type="button" class="button" name="button" onclick="{$this->pager_name}_columns_change(this)" value="$text_update_columns"><br/>
            <input type="button" class="button" name="button" onclick="{$this->pager_name}_reset_default(this)" value="$text_reset_columns"><br/>
            <input type="button" class="button" name="button" onclick="document.getElementById('{$widget_name}').style.display = 'none'" value="$text_cancel_columns"><br/>
        </td>
    </tr>
    </table>
    </div>


<script language="JavaScript" type="text/javascript">
    document.getElementById('{$widget_name}').style.display = 'none';
</script>
END;
        return $s;
    }
}
/**
 * $Log: Pager_Columns.php,v $
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