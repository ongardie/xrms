<?php
/**
*
* Log an email message
*
* $Id: logger.php,v 1.3 2006/01/02 23:02:14 vanmer Exp $
*/

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once 'Console/Getopt.php';

$args = Console_Getopt::readPHPArgv();

$con = get_xrms_dbconnection();
// $con->debug = 1;

$message = file_get_contents("php://stdin");
$message_sql = $con->qstr($message);
preg_match("/Subject: (.*)\n/i", $message, $subject);
$subject = "Email: " . $subject[1];
$subject_sql = $con->qstr($subject);

switch ( $args[1] ) {
     case '-to':
       $activity_type_id = 3; # "Email To"
       $addr = $args[2];
     break;
     case '-from':
       $activity_type_id = 4; # "Email From"
       $addr = $args[2];
     break;
     case '-from_in_msg':
       $activity_type_id = 4; # "Email From"
       preg_match("/From: (.*)\n/i", $message, $addr);
       $addr = $addr[1];
       preg_match("/<([^>]+)>/", $addr, $addr_from);
       $long_addr = $addr_from[1];
       if ($long_addr != "") $addr = $long_addr;
       $addr_sql = $con->qstr($addr);
     break;
   }

preg_match("/Date: (.*)\n/i", $message, $date);
$date= $date[1];

$addr_sql = $con->qstr($addr);

$sql = "select  contact_id, company_id from contacts
        where email like " . $addr_sql . " limit 1";
$rst = $con->execute($sql);

if ($rst) {
    if (!$rst->EOF) {
        $contact_id = $rst->fields['contact_id'];
        $company_id = $rst->fields['company_id'];
    } else {
        $contact_id = 0; // gotta go somewhere...
        $company_id = 0; // gotta go somewhere...
    }
}

switch ( $args[3] ) {
     case '-to':
       $user = $args[4];
     break;
     case '-from':
       $user = $args[4];
     break;
   }

$user_sql = $con->qstr($user);

$sql = "select user_id from users
        where email like " . $user_sql . " limit 1";
$rst = $con->execute($sql);

if ($rst) {
    $user_id = $rst->fields['user_id'];
} else {
    $user_id = 0; // gotta go somewhere...
}

// for debugging:
// echo "From: $addr\n contact_id: $contact_id\nTo: $user\nuser_id: $user_id\n\n";

# set on_what_table
$on_what_table = "activities";

# set on_what_id
$on_what_id = "";

# set on_what_status
$on_what_status = "";

# set activity_title
$activity_title = $subject_sql;

# set activity description
$activity_description = $message_sql;

# set entered_at time
$entered_at = $con->DBTimeStamp(time());

# set entered_by
$entered_by = $user_id;

# set last_modified_at time
$last_modified_at = $con->DBTimeStamp(time());

# set last_modified_by
$last_modified_by = $user_id;

# set scheduled_at
$scheduled_at =  $con->DBTimeStamp(strtotime($date));

# set ends_at
$ends_at = $scheduled_at;

# set completed_at
$completed_at = $scheduled_at;

# set activity_stauts
$activity_status = "c"; # default to CLOSED status

# set activity_record_status
$activity_record_status = 'a';

# make insert statment
$insert = "INSERT INTO `activities` ( `activity_id` , `activity_type_id` , `user_id` , `company_id` , 
`contact_id` , `on_what_table` , `on_what_id` , `on_what_status` , `activity_title` , `activity_description` , 
`entered_at` , `entered_by` , `last_modified_at` , `last_modified_by` , `scheduled_at` , `ends_at` , 
`completed_at` , `activity_status` , `activity_record_status` ) 
VALUES ( '', '" . $activity_type_id . "', '" . $user_id . "', '" . $company_id . "', '" . $contact_id . "', '
". $on_what_table . "', '" . $on_what_id . "', '" . $on_what_status . "', " . $activity_title . ", 
" . $activity_description . ", " . $entered_at . ", '" . $entered_by . ", " . $last_modified_at . ", '
" . $last_modified_by . "', " . $scheduled_at . ", " . $ends_at . ", " . $completed_at . ", '
" . $activity_status . "', '" . $activity_record_status . "');";

$rst = $con->execute($insert);

// for debugging:
// echo $insert . "\n";

$con->close();

/**
* $Log: logger.php,v $
* Revision 1.3  2006/01/02 23:02:14  vanmer
* - changed to use centralized dbconnection function
*
* Revision 1.2  2005/02/10 14:40:03  maulani
* - Set last modified info when creating activities
*
* Revision 1.1  2004/11/16 00:04:37  gpowers
* - email logging script (in development)
*
*
*/
?>
