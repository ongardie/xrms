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

$close_at = $con->SQLDate('Y-M-D', 'close_at');

$sql = "select opp.opportunity_title as 'Opportunity Title', 
company_name as 'Company', 
u.username as 'Owner', 
if (size > 0, size, 0) as 'Opportunity Size', 
probability/100 as 'Probability', 
if (size > 0, size*probability/100, 0) as 'Weighted Size', 
os.opportunity_status_pretty_name as 'Status', 
$close_at as 'Close Date' 
from opportunities opp, companies c, opportunity_statuses os, users u 
where opp.company_id = c.company_id 
and opp.user_id = u.user_id 
and opp.opportunity_status_id = os.opportunity_status_id 
and opportunity_record_status = 'a'";

$rst = $con->execute($sql);

$filename =  'opportunities_' . time() . '.csv';

$fp = fopen($tmp_upload_directory . $filename, 'w');

if (($fp) && ($rst)) {
	rs2csvfile($rst, $fp);
	$rst->close();
	fclose($fp);
}

$con->close();

header("Location: {$http_site_root}/tmp/{$filename}");

?>