<?php
/**
 * /cases/pager.php
 *
 * An include file to override ADODB_Pager to implement cases specific functions
 *
 * $Id: pager.php,v 1.1 2004/08/19 13:12:16 maulani Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

// include adodb pager class
//show_test_values('sdklfl');
require_once('../include-locations.inc');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
// Create cases_pager as a child of adodb_pager
class Cases_Pager extends ADODB_Pager{

    //------------------------------------------------------
    // Constructor calls the parent constructor
    function Cases_Pager(&$db, $sql, $selected_column=1, $selected_column_html='*')
    {
         $this->ADODB_Pager($db, $sql, 'cases', false, $selected_column, $selected_column_html);
    }

    //------------------------------------------------------
    // overridden to add export and mail merge
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
            echo "<tr><td class=widget_content_form_element colspan=10><input type=button class=button onclick=\"javascript: exportIt();\" value='Export - Not Coded Yet'> ";
            echo "<input type=button class=button onclick=\"javascript: bulkEmail();\" value='Mail Merge'></td></tr>";
        }

        echo "</table>";
    }
}


/**
 * $Log: pager.php,v $
 * Revision 1.1  2004/08/19 13:12:16  maulani
 * - Add specific pager to override formatting
 *
 * 
 */
?>