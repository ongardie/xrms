<?php
/**
 * /files/pager.php
 *
 * An include file to override ADODB_Pager to implement files specific functions
 *
 * $Id: pager.php,v 1.1 2004/08/19 13:14:05 maulani Exp $
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
// Create files_pager as a child of adodb_pager
class Files_Pager extends ADODB_Pager{

    //------------------------------------------------------
    // Constructor calls the parent constructor
    function Files_Pager(&$db, $sql, $selected_column=1, $selected_column_html='*')
    {
         $this->ADODB_Pager($db, $sql, 'files', false, $selected_column, $selected_column_html);
    }

}

/**
 * $Log: pager.php,v $
 * Revision 1.1  2004/08/19 13:14:05  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * 
 */
?>