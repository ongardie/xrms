<?php
/**
 * /activities/pager.php
 *
 * An include file to override ADODB_Pager to implement activities specific functions
 *
 * $Id: pager.php,v 1.1 2004/08/16 14:39:23 maulani Exp $
 */

// include adodb pager class
//show_test_values('sdklfl');
require_once('../include-locations.inc');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
// Create activities_pager as a child of adodb_pager
class Activities_Pager extends ADODB_Pager{

    function Activities_Pager(&$db, $sql, $selected_column=1, $selected_column_html='*')
    {
         $this->ADODB_Pager($db, $sql, 'activities', false, $selected_column, $selected_column_html);
    }

    function RenderLayout($header,$grid,$footer,$attributes='class=widget cellspacing=1 cellpadding=0 border=0 width="100%"')
    {
        echo "<table " . $attributes . ">",
        "<tr><td colspan=13 class=widget_header>Search Results</td></tr>\n";

        if ($header != '&nbsp;') {
            echo "<tr><td colspan=13>",
            "<table border=0 cellpadding=0 cellspacing=0 width=\"100%\">",
            "<tr><td class=widget_label>" . $footer . "</td>" . "<td align=right class=widget_label>" . $header . "</td></tr>",
            "</table>",
            "</td></tr>\n";
        }

        echo $grid;

        if ($this->how_many_rows > 0)           
        {
            echo "<tr><td class=widget_content_form_element colspan=10><input type=button class=button onclick=\"javascript: exportIt();\" value='Export'> ";
            echo "<input type=button class=button onclick=\"javascript: bulkEmail();\" value='Mail Merge'></td></tr>";
        }

        echo "</table>";
    }
}


/**
 * $Log: pager.php,v $
 * Revision 1.1  2004/08/16 14:39:23  maulani
 * - Override ADODB_Pager class for activities to allow customization
 * - Customization still todo
 *
 * 
 */
?>