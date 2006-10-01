<?php
/**
*    bulkactivity-1.php
*
* $Id: bulkactivity-1.php,v 1.1 2006/10/01 00:15:06 braverock Exp $
*
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-files.php');
require_once($include_directory . 'utils-activities.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$array_of_contacts = $_POST['array_of_contacts'];


$return_url = $_POST['return_url'];
$user_id = $_POST['user_id'];
$activity_type_id = $_POST['activity_type_id'];
$activity_title = $_POST['activity_title'];
$scheduled_at = $_POST['scheduled_at'];
$ends_at = $_POST['ends_at'];
$campaign_id = $_POST['campaign_id'];
$activity_description = $_POST['activity_description'];

$start_hour = '8:00:00';
$end_hour = '19:30:00';
$slice_length_minutes = '30';

if (!$ends_at) {
   $ends_at = date('Y-m-d');
}

$start_time = strtotime($ends_at . ' ' . $start_hour);
$end_time = strtotime($ends_at . ' ' . $end_hour);

/*
echo $array_of_contacts[0];
echo "<br>";
echo $return_url;
echo "<br>";
echo $activity_type_id;
echo "<br>";
echo $activity_title;
echo "<br>";
echo "scheduled_at: "; echo $scheduled_at;
echo "<br>";
echo "ends_at: "; echo $ends_at;
echo "<br>";
echo $user_id;
echo "<br>";
echo $campaign_id;
echo "<br>";
exit;
*/

$con = get_xrms_dbconnection();
//$con->debug = 1;

if (is_array($array_of_contacts)) {
    $imploded_contacts = implode(',', $array_of_contacts);
} elseif (is_numeric($array_of_contacts)) {
    $imploded_contacts= $array_of_contacts;
}else {
    echo _("WARNING: No array of contacts!") . "<br>";
}

// loop through the contacts and send each one a copy of the message
$sql = "select * from contacts where contact_id in (" . $imploded_contacts . ")";
$rst = $con->execute($sql);

if ($rst) {
        $i = 0;
        while (!$rst->EOF)
        {
            $current_time = $start_time + $i*$slice_length_minutes*60;
            if ($current_time == $end_time)  {
               $i = -1;
               $start_time = $start_time + 86400;
               $end_time = $end_time + 86400;
            }
            $scheduled_at = date('Y-m-d H:i:s',$current_time);
            $ends_at = date('Y-m-d H:i:s',$current_time+1);

            // Create "activity" log
            $activity_data['activity_type_id']     = $activity_type_id;
            $activity_data['company_id']           = $rst->fields['company_id']; // which company is this activity related to
            $activity_data['contact_id']           = $rst->fields['contact_id'];
            $activity_data['activity_title']       = $activity_title;
            $activity_data['activity_description'] = $activity_description;
            $activity_data['activity_status']      = 'o';         // Open status
            $activity_data['completed_bol']        = false;           // activity not completed
            $activity_data['scheduled_at']         = $scheduled_at;
            $activity_data['ends_at']              = $ends_at;
            $activity_data['user_id']              = $user_id;

            if ($campaign_id)
            {
               $activity_data['on_what_table']  = 'campaigns';
               $activity_data['on_what_id']  = $campaign_id;
            }

            if ( $activity_id = add_activity($con, $activity_data) )
            {
              //echo "! ";
            }
            $i++;
            $rst->movenext();
        }   // WHILE

        $rst->close();

        $feedback = '<p /><b>' . _("Activity inserted") . '.</b>';
    }
    // Failed to create contact list
    else
    {
        db_error_handler($con, $sql);
    }

    $con->close();

header("Location: " . $http_site_root . $return_url);

/**
 * $Log: bulkactivity-1.php,v $
 * Revision 1.1  2006/10/01 00:15:06  braverock
 * - Initial Revision of Bulk Activity and Bulk Assignment contributed by Danielle Baudone
 *
 *
 */
?>