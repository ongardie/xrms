<?php

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/toexport.inc.php');

//set target and see if we are logged in
$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$opportunity_title = $_POST['opportunity_title'];
$company_name = $_POST['company_name'];
$user_id = $_POST['user_id'];
$opportunity_status_id = $_POST['opportunity_status_id'];
$opportunity_category_id = $_POST['opportunity_category_id'];

$close_at = $con->SQLDate('Y-M-D', 'close_at');

$sql = "select opp.opportunity_title as 'Opportunity Title',
company_name as 'Company',
u.username as 'Owner',
if (size > 0, size, 0) as 'Opportunity Size',
probability/100 as 'Probability',
if (size > 0, size*probability/100, 0) as 'Weighted Size',
os.opportunity_status_pretty_name as 'Status',
$close_at as 'Close Date'
from opportunities opp, companies c, opportunity_statuses os, users u, entity_category_map ecm
where opp.company_id = c.company_id
and opp.user_id = u.user_id
and opp.opportunity_status_id = os.opportunity_status_id
and opportunity_record_status = 'a' ";

if ($opportunity_category_id > 0) {
    $where .= " and ecm.on_what_table = 'opportunities'
                and ecm.on_what_id = opp.opportunity_id
                and ecm.category_id = $opportunity_category_id ";
}

if (strlen($opportunity_title) > 0) {
    $where .= " and opp.opportunity_title like " . $con->qstr('%' . $opportunity_title . '%', get_magic_quotes_gpc());
}

if (strlen($company_name) > 0) {
    $where .= " and c.company_name like " . $con->qstr('%' . $company_name . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $where .= " and opp.user_id = $user_id";
}

if (strlen($opportunity_status_id) > 0) {
    $where .= " and opp.opportunity_status_id = $opportunity_status_id";
}

$rst = $con->execute($sql.$where);

$filename =  'opportunities_' . time() . '.csv';

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