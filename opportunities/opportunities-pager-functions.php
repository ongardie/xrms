<?php
/*
 * Created on 27 déc. 2005
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
/**
 * Shared opportunity pager functions
 *
 * $Id: opportunities-pager-functions.php,v 1.1 2006/04/11 01:57:46 vanmer Exp $
 */

/**
* This is the callback function to be used with GUP_Pager

*/
function GetOpportunityPagerData($row) {
    global $http_site_root;
    global $con;

	// Set the CSS classes for the rows
    if ($row['is_overdue']) {
    	$row['is_overdue'] = _('Yes');
        $row['Pager_TD_CSS_All_Rows'] = 'overdue_activity';
    } else {
    	$row['is_overdue'] = '';
        $row['Pager_TD_CSS_All_Rows'] = 'open_activity';
    }
    return $row;
}

/**
 * $Log: opportunities-pager-functions.php,v $
 * Revision 1.1  2006/04/11 01:57:46  vanmer
 * - added marking of overdue opportunites in red, like activities
 * - added extra columns and groupability on the opportunites pager
 * - added ability to hide closed opportunities
 * - Thanks to Jean-Noël HAYART for providing this patch
 *
 *
 */
?>
