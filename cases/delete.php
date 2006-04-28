<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-cases.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

getGlobalVar($case_id, 'case_id');
$on_what_id=$case_id;

if ( !$case_id ) {
  header("Location: some.php?msg=no_case");
  exit;
}

$session_user_id = session_check('','Delete');

$con = get_xrms_dbconnection();


$ret=delete_case($con, $case_id);

if ($ret) {
    header("Location: some.php?msg=case_deleted");
} else {
    $msg=urlencode(_("Failed to delete case."));
    header("Location: one.php?case_id=$case_id&msg=$msg");
}
?>