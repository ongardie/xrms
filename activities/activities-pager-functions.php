<?php
/**
 * Shared activity pager functions
 *
 * $Id: activities-pager-functions.php,v 1.2 2005/02/24 00:17:39 braverock Exp $
 */

function GetActivitiesPagerData($row) {
    global $http_site_root;
    global $con;

    if ($row['activity_status'] == 'o') {
        if ($row['is_overdue']) {
            $row['activity_status'] = _('Overdue');
            $row['GUP_Pager_TD_Classname'] = 'overdue_activity';
        } else {
            $row['activity_status'] = _('Open');
            $row['GUP_Pager_TD_Classname'] = 'open_activity';
        }
    } else {
        $row['activity_status'] = _('Closed');
        $row['GUP_Pager_TD_Classname'] = 'closed_activity';
    }

    if ($row['on_what_table'] == 'opportunities') {
        $row['activity_about'] = "<a href='$http_site_root/opportunities/one.php?opportunity_id={$row['on_what_id']}'>";
        $sql2 = "select opportunity_title as attached_to_name
                from opportunities
                where opportunity_id = {$row['on_what_id']}";
    } elseif ($row['on_what_table'] == 'cases') {
        $row['activity_about'] = "<a href='$http_site_root/cases/one.php?case_id={$row['on_what_id']}'>";
        $sql2 = "select case_title as attached_to_name from cases where case_id = {$row['on_what_id']}";
    } else {
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
 * Revision 1.2  2005/02/24 00:17:39  braverock
 * - add phpdoc
 *
 */
?>
