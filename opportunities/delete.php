<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$opportunity_id = $_GET['opportunity_id'];
$on_what_id=$opportunity_id;

$session_user_id = session_check('', 'Delete');


$con = get_xrms_dbconnection();

$ret = delete_opportunity($con, $opportunity_id);
if ($ret) {
    header("Location: some.php?msg=opportunity_deleted");
} else {
    $msg=urlencode(_("Failed to delete opportunity"));
    header("Location: one.php?opportunity_id=$opportunity_id&msg=$msg");
}

$con->close();


?>