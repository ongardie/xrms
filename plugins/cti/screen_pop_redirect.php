<?php

// Some class you've written...
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'confgoto.php');

$session_user_id = session_check();

$con = get_xrms_dbconnection();

$call_id = $_GET['call_id'];
$sql = "SELECT * FROM cti_call_queue WHERE id = '" . $call_id . "' ";
$rst = $con->execute($sql);

if (($rst) && (!$rst->EOF)) {
$callerid = $rst->fields['callerid'];
$msg=urlencode(_("Call From: ") . $callerid);

$caller_phone = preg_replace("/[^\d]/", '', $callerid);

if($rst->_numOfRows > 0){
    $sql = "SELECT * from contacts
            WHERE work_phone like '" . $caller_phone . "' 
            OR home_phone like '" . $caller_phone . "' 
            OR cell_phone like '" . $caller_phone . "' ";
    $rst = $con->execute($sql);

    if($rst->_numOfRows > 0){
        header("Location: ../../contacts/one.php?msg="
            . $msg . "&contact_id=" . $rst->fields['contact_id']);
    } else {
        $sql = "SELECT * from companies
                WHERE phone like '" . $caller_phone . "' 
                OR phone2 like '" . $caller_phone . "' ";
        $rst = $con->execute($sql);

        if($rst->_numOfRows > 0){
            header("Location: ../../companies/one.php?msg="
                . $msg . "&company_id=" . $rst->fields['company_id']);
        } else {
            $msg .= " (" . _("Not in Database") . ")";
            header("Location: ../../companies/new.php?msg=" . $msg);
        }
    }
} else {
    $msg .= " (" . _("Not in Database") . ")";
    header("Location: ../../companies/new.php?msg=" . $msg);
}
}

$con->close;
?>
