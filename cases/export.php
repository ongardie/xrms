<?php
/**
 * Export Search Results from cases/some.php
 *
 * $Id: export.php,v 1.1 2005/01/09 03:21:40 braverock Exp $
 */


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
//$con->debug = 1;

$case_title = $_POST['case_title'];
$company_name = $_POST['company_name'];
$user_id = $_POST['user_id'];
$case_status_id = $_POST['case_status_id'];
$case_category_id = $_POST['case_category_id'];

$due_at = $con->SQLDate('Y-M-D', 'due_at');

$sql = "
SELECT
  cases.case_title AS ".$con->qstr(_('Case Title'),get_magic_quotes_gpc()).",
  c.company_name AS ".$con->qstr(_('Company'),get_magic_quotes_gpc()).",
  u.username AS ".$con->qstr(_('Owner'),get_magic_quotes_gpc()).",
  cp.case_priority_pretty_name AS ".$con->qstr(_('Priority'),get_magic_quotes_gpc()).",
  ct.case_type_pretty_name AS ".$con->qstr(_('Type'),get_magic_quotes_gpc()).",
  cs.case_status_pretty_name AS ".$con->qstr(_('Status'),get_magic_quotes_gpc()).",
  $due_at AS ".$con->qstr(_('Due Date'),get_magic_quotes_gpc())."
FROM cases, companies c, case_statuses cs, case_types ct, case_priorities cp, users u
WHERE cases.company_id = c.company_id
  AND cases.user_id = u.user_id
  AND cases.case_status_id = cs.case_status_id
  AND cases.case_type_id = ct.case_type_id
  AND cases.case_priority_id = cp.case_priority_id
  AND cases.case_record_status = 'a'
";



$where = '';

if ($case_category_id > 0) {
    $where .= " and ecm.on_what_table = 'cases'
                and ecm.on_what_id = opp.case_id
                and ecm.category_id = $case_category_id ";
}

if (strlen($case_title) > 0) {
    $where .= " and cases.case_title like " . $con->qstr('%' . $case_title . '%', get_magic_quotes_gpc());
}

if (strlen($company_name) > 0) {
    $where .= " and c.company_name like " . $con->qstr('%' . $company_name . '%', get_magic_quotes_gpc());
}

if (strlen($user_id) > 0) {
    $where .= " and cases.user_id = $user_id";
}

if (strlen($case_status_id) > 0) {
    $where .= " and cases.case_status_id = $case_status_id";
}

$rst = $con->execute($sql.$where);

if (!$rst) {
    db_error_handler ($con, $sql.$where);
}

$filename =  'cases_' . date('Y-m-d_H-i') . '.csv';

if ($rst) {
    $csvdata= rs2csv($rst);
    if ($csvdata) {
      $filesize = strlen($csvdata);
    }  
    $rst->close();
} else {
    echo "<p>" . _("There was a problem with your export") . ":\n";
    if (!$csvdata) {
        echo "<br>" . _("Unable to create file") . ": $xrms_file_root.$tmp_export_directory/$filename \n";
    }
    if (!$rst) {
        db_error_handler($con,$sql);
    }
}

$con->close();

SendDownloadHeaders('text', 'csv', $filename, true, $filesize);
echo $csvdata;

/**
 *$Log: export.php,v $
 *Revision 1.1  2005/01/09 03:21:40  braverock
 *- initial revision of cases export
 *- modeled on opportunities export
 *
 */
?>