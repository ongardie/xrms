<?php

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/toexport.inc.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$title = $_POST['title'];
$contact = $_POST['contact'];
$company = $_POST['company'];
$owner = $_POST['owner'];
$date = $_POST['date'];
$before_after = $_POST['before_after'];
$activity_type_id = $_POST['activity_type_id'];
$completed = $_POST['completed'];
$user_id = $_POST['user_id'];

$sql = "select          
 a.*,                             
 if(activity_status = 'o' and ends_at < now(), 1, 0) as is_overdue,
 concat(cont.first_names, ' ', cont.last_name) as 'Contact',
 c.company_name as 'Company',
 activity_type_pretty_name as 'Type'
from companies c, users u, activity_types at, activities a left outer join contacts cont on cont.contact_id = a.contact_id 
where a.company_id = c.company_id 
and at.activity_type_id = a.activity_type_id 
and a.user_id = u.user_id";
                
$criteria_count = 0; 

if (strlen($title) > 0) { 
    $criteria_count++;
    $sql .= " and a.activity_title like " . $con->qstr('%' . $title . '%', get_magic_quotes_gpc());
}   

if (strlen($contact) > 0) { 
    $criteria_count++;
    $sql .= " and cont.last_name like " . $con->qstr('%' . $contact . '%', get_magic_quotes_gpc());
}   

if (strlen($company) > 0) { 
    $criteria_count++;
    $sql .= " and c.company_name like " . $con->qstr('%' . $company . '%', get_magic_quotes_gpc());
}   

if (strlen($user_id) > 0) { 
    $criteria_count++;
    $sql .= " and a.entered_by like " . $con->qstr('%' . $user_id . '%', get_magic_quotes_gpc());
}   

if (strlen($activity_type_id) > 0) { 
    $criteria_count++;
    $sql .= " and a.activity_type_id like " . $con->qstr('%' . $activity_type_id . '%', get_magic_quotes_gpc());
}   

if (strlen($completed) > 0) { 
    $criteria_count++;
    $sql .= " and a.activity_status = " . $con->qstr($completed, get_magic_quotes_gpc());
}   

if (strlen($date) > 0) { 
    $criteria_count++;
    if (!$before_after) { 
        $sql .= " and a.scheduled_at <= " . $con->qstr($date . " 23:59:59", get_magic_quotes_gpc());
    } else {
        $sql .= " and a.scheduled_at >= " . $con->qstr($date . " 00:00:00", get_magic_quotes_gpc());
    }   
}   

$sql .= " order by is_overdue desc, a.scheduled_at, a.entered_at desc";

$rst = $con->execute($sql);

$filename =  'activities_' . time() . '.csv';

$fp = fopen($tmp_export_directory . $filename, 'w');

if (($fp) && ($rst)) {
    rs2csvfile($rst, $fp);
    $rst->close();
    fclose($fp);
} else {
    echo "<p>There was a problem with your export:\n";
    if (!$fp) {
        echo "<br>Unable to open file: $tmp_export_directory . $filename \n";
    }
    if (!$rst) {
        echo "<br> No results returned from database by query: \n";
        echo "<br> $sql \n";
    }
}

$con->close();

header("Location: {$http_site_root}/export/{$filename}");

?>
