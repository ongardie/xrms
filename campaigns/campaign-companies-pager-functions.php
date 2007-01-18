<?php
/*
 * Company Campaign pager functions
 *
 * This is the callback function to be used with GUP_Pager
 *
 *
*/

function GetCampaignCompaniesPagerData($row) {
    global $http_site_root;
    global $con;

    // extract the date added from the database timestamp
    
    if ($row['date_added']) {
      $tdate = explode(' ',$row['date_added']);
      $row['date_added'] = $tdate[0];
    }
    return $row;
}

/**
 * $Log: campaign-companies-pager-functions.php,v $
 * Revision 1.2  2007/01/18 13:18:48  fcrossen
 *  - initial revision
 *  - alter value in date_added to show date instead of timestamp
 *
 *
 */
 
?>
