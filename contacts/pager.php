<?php
/**
 * /contacts/pager.php
 *
 * An include file to override ADODB_Pager to implement contacts specific functions
 *
 * $Id: pager.php,v 1.2 2004/08/20 03:12:13 maulani Exp $
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
// Create contacts_pager as a child of adodb_pager
class Contacts_Pager extends ADODB_Pager{

    //------------------------------------------------------
    // Constructor calls the parent constructor
    function Contacts_Pager(&$db, $sql, $selected_column=1, $selected_column_html='*')
    {
         $this->ADODB_Pager($db, $sql, 'contacts', false, $selected_column, $selected_column_html);
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
            echo "<tr><td class=widget_content_form_element colspan=10><input type=button class=button onclick=\"javascript: exportIt();\" value='Export'> ";
            echo "<input type=button class=button onclick=\"javascript: bulkEmail();\" value='Mail Merge'></td></tr>";
        }

        echo "</table>";
    }
}


/**
 * $Log: pager.php,v $
 * Revision 1.2  2004/08/20 03:12:13  maulani
 * - Restore mail merge functionality reported in bug 941839
 *
 * Revision 1.1  2004/08/19 13:12:15  maulani
 * - Add specific pager to override formatting
 *
 * 
 */
?>