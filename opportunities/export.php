<?php

require_once('../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'adodb/toexport.inc.php');

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$opportunity_title = $_POST['opportunity_title'];
$company_name = $_POST['company_name'];
$user_id = $_POST['user_id'];
$opportunity_status_id = $_POST['opportunity_status_id'];
$opportunity_category_id = $_POST['opportunity_category_id'];

$close_at = $con->SQLDate('Y-M-D', 'close_at');

$sql = "
SELECT
  opp.opportunity_title AS 'Opportunity Title',
  c.company_name AS 'Company',
  u.username AS 'Owner',
  CASE
    WHEN (opp.size > 0) THEN opp.size
    ELSE 0
  END AS 'Opportunity Size',
  (opp.probability / 100) AS 'Probability',
  CASE
    WHEN (opp.size > 0) THEN ((opp.size * opp.probability) / 100)
    ELSE 0
  END AS 'Weighted Size',
  os.opportunity_status_pretty_name AS 'Status',
  $close_at AS 'Close Date'
FROM opportunities opp, companies c, opportunity_statuses os, users u
WHERE opp.company_id = c.company_id
  AND opp.user_id = u.user_id
  AND opp.opportunity_status_id = os.opportunity_status_id
  AND opp.opportunity_record_status = 'a'
";

$where = '';

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
    echo "<p>" . _("There was a problem with your export") . ":\n";
    if (!$fp) {
        echo "<br>" . _("Unable to open file") . ": $tmp_export_directory . $filename \n";
    }
    if (!$rst) {
        echo "<br>" . _("No results returned from database by query") . ": \n";
        echo "<br> $sql \n";
    }
}

$con->close();

header("Location: {$http_site_root}/export/{$filename}");

?>
