<?php
/**
 * Shared activity pager functions
 *
 * $Id: activities-pager-functions.php,v 1.9 2006/04/05 00:53:52 vanmer Exp $
 */

/**
* This is the callback function to be used with GUP_Pager

*/
function GetActivitiesPagerData($row) {
    global $http_site_root;
    global $con;

	// Set the CSS classes for the rows
    if ($row['activity_status'] == 'o') {
        if ($row['is_overdue']) {
        	$row['is_overdue'] = _('Yes');
            $row['activity_status'] = _('Overdue');
            $row['Pager_TD_CSS_All_Rows'] = 'overdue_activity';
        } else {
        	$row['is_overdue'] = '';
            $row['activity_status'] = _('Open');
            $row['Pager_TD_CSS_All_Rows'] = 'open_activity';
        }
    } else {
       	$row['is_overdue'] = '';
        $row['activity_status'] = _('Closed');
        $row['Pager_TD_CSS_All_Rows'] = 'closed_activity';
    }
	if($row['description_brief']) {
		// for some reason, if the first char of description is a newline, the JS breaks...
		$row['description_brief'] = str_replace("\n", "", $row['description_brief']);
		$row['description_brief'] = str_replace("\r", "", $row['description_brief']);
   		$row['title'] = "<a href=\"../activities/one.php?activity_id={$row['activity_id']}&amp;return_url={$row['return_url']}\" onmouseover=\"return escape('" . htmlentities(addslashes($row['description_brief']),ENT_COMPAT,'UTF-8') . "')\" >{$row['activity_title']}</a>";
	} else {
	 	$row['title'] = "<a href=\"../activities/one.php?activity_id={$row['activity_id']}&amp;return_url={$row['return_url']}\">{$row['activity_title']}</a>";
	}

	// Query for the About field
    if ($row['on_what_table'] == 'opportunities') {
        $row['activity_about'] = "<a href='$http_site_root/opportunities/one.php?opportunity_id={$row['on_what_id']}'>";
        $sql2 = "select opportunity_title as attached_to_name
                from opportunities
                where opportunity_id = {$row['on_what_id']}";
    } elseif ($row['on_what_table'] == 'cases') {
        $row['activity_about'] = "<a href='$http_site_root/cases/one.php?case_id={$row['on_what_id']}'>";
        $sql2 = "select case_title as attached_to_name from cases where case_id = {$row['on_what_id']}";
    } elseif (trim($row['on_what_table'])) {
        $row['activity_about'] = "<a href='$http_site_root" . table_one_url($row['on_what_table'], $row['on_what_id']) . "'>";
        $on_what_field=make_singular($row['on_what_table']).'_id';
        $name_field=$con->Concat(implode(", ' ' , ", table_name($row['on_what_table'])));
        $sql2 = "select $name_field as attached_to_name from {$row['on_what_table']} WHERE $on_what_field = {$row['on_what_id']}";
    } else{
        $row['activity_about'] = _('N/A');
        $sql2 = null;
    }
    if($sql2) {
        $rst2 = $con->execute($sql2);

        if ($rst2) {
            $attached_to_name = $rst2->fields['attached_to_name'];
            $row['activity_about'] .= $attached_to_name . "</a>";
            $rst2->close();
        }
    }
    return $row;
}

/**
 * $Log: activities-pager-functions.php,v $
 * Revision 1.9  2006/04/05 00:53:52  vanmer
 * - change values passed into javascript popup to correctly come out as UTF-8 characters
 *
 * Revision 1.8  2005/08/12 16:41:20  ycreddy
 * Added trim when checking whether on_what_table is empty
 *
 * Revision 1.7  2005/07/08 17:51:28  vanmer
 * - added extra case to show ABOUT link for most entities
 *
 * Revision 1.6  2005/06/30 17:07:53  daturaarutad
 * moved creation of title html link to GetActivitiesPagerData and added popup/tooltip containing activity description
 *
 * Revision 1.5  2005/05/20 22:18:55  daturaarutad
 * homogenized is_overdue behavior for all pagers
 *
 * Revision 1.4  2005/05/19 13:20:43  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.3  2005/03/01 15:48:09  daturaarutad
 * changed name from GUP_Pager_TD_Classname to Pager_TD_CSS_All_Rows
 *
 * Revision 1.2  2005/02/24 00:17:39  braverock
 * - add phpdoc
 *
 */
?>
