<?php

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/toexport.inc.php');

$sql = "select u.username as 'Account Owner', 
c.company_name as 'Company', 
cont.first_names as 'Contact First Names', 
cont.last_name as 'Contact Last Name', 
cont.title as 'Contact Title', 
cont.description as 'Contact Description', 
cont.summary as 'Contact Summary', 
cont.email as 'Contact E-Mail Address', 
cont.work_phone as 'Contact Work Phone', 
cont.home_phone as 'Contact Home Phone', 
cont.cell_phone as 'Contact Cell Phone', 
cont.aol_name as 'AOL Name', 
cont.yahoo_name as 'Yahoo Name', 
cont.msn_name as 'MSN Name' 
from companies c, contacts cont, users u 
where company_record_status = 'a' 
and contact_record_status = 'a' 
and c.company_id = cont.company_id 
and c.user_id = u.user_id";

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$rst = $con->execute($sql);

$fp = fopen($xrms_file_root . '/tmp/contacts.csv', 'w');

// if (($fp) && ($rst)) {
	rs2csvfile($rst, $fp);
	$rst->close();
	fclose($fp);
// }

$con->close();

header("Location: {$http_site_root}/tmp/contacts.csv");

?>