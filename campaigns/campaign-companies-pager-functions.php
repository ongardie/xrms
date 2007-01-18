<?php
/*
 * Company Campaign pager functions
 *
 * This is the callback function to be used with GUP_Pager
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

?>
