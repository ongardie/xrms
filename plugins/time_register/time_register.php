#!/usr/bin/php5
<?php

require_once('/var/www/xrms/include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$ntp = array();
exec("/usr/bin/ntpq -p -n",$ntp);

$null =  array_shift($ntp);
$null =  array_shift($ntp);

while ($line = array_shift($ntp)) {

$values = array();
$tally = $line{0};
$values = preg_split('/ +/',  substr($line, 1, 80));

$rec = array();
$rec['time'] = time();
$rec['operator'] = "";
$rec['host'] = "";
$rec['app'] = "ntpq";
$rec['remote'] = $values[0];
$rec['refid'] = $values[1];
$rec['st'] =  $values[2];
$rec['type'] = $values[3];
$rec['atwhen'] = $values[4];
$rec['poll'] = $values[5];
$rec['reach'] = $values[6];
$rec['delay'] = $values[7];
$rec['offset'] = $values[8];
$rec['jitter'] = $values[9];
$rec['entered_at'] = time();
$rec['entered_by'] = '1';
$rec['register_record_status'] = "a";

$tbl = 'time_register';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);
}

$con->close();

?>