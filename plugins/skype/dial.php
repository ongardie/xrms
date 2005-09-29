<?php
/*
*
* Simple SKype Dialer
* Heavily hacked by Nic Lowe
* Resting on the Shoulders of Giants (Glenn Powers)
*
* CTI / Asterisk Outdial XRMS Plugin v0.2
* uses asterisk from:
* http://www.asterisk.org/
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the Open Software License v. 2.0
*
*/

/*
 *
 */

function lookupCID($thelookupCID) {

        $lookupCID_sip_array = parse_ini_file("/etc/asterisk/sip.conf", true);

        while ($v = current($lookupCID_sip_array)) {
                if (isset($v['crm_username'])){
                        if($v['crm_username'] == $thelookupCID) {
                                $thelookupCID = key($lookupCID_sip_array);
                                return $thelookupCID;
                        }
                }
                next($lookupCID_sip_array);
        }
}

/*
 * End LookupCID
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$session_user_id = session_check(); 
$session_username = $_SESSION['username'];
$msg = $_GET['msg'];

$contact_id = $_GET['contact_id'];
$company_id = $_GET['company_id'];
$phone = $_GET['phone'];
$phone_dial_prefix = "1";

$msg = urlencode(_("Dialing Phone Number: ") . $phone);

// Get contact name 

$sql = "SELECT first_names,last_name from contacts
        WHERE contact_id = " . $contact_id . " LIMIT 1";
$rst = $con->execute($sql);

if ($rst) {
    if (!$rst->EOF) {
        $contact_name = urlencode($rst->fields['first_names'] . " "
              . $rst->fields['last_name']);
    }
}

// Get variables from the custom fields of the user's contact id.

$sql = "SELECT custom1, custom2, custom3 from contacts, users
        WHERE  users.user_id = " . $session_user_id . "
        AND contacts.contact_id = users.user_contact_id
        LIMIT 1";
$rst = $con->execute($sql);

if ($rst) {
    if (!$rst->EOF) {
        $channel = $rst->fields['custom1'];
        $extension_to_dial = $rst->fields['custom2'];
        $CID = $rst->fields['custom3'];
    }
}

// $sipCID = lookupCID($session_username);
//Skype dialer
header("Location: callto://+".$phone);
exit;
//sleep(5); 

//exit;
// Create an Activity on Dial
if ($contact_id!=""){
$header_loc="../../activities/new-2.php?user_id=" . $session_user_id
    . "&activity_status=o&activity_type_id=1&contact_id="
    . $contact_id . "&company_id=" . $company_id . "&activity_title="
    . _("Call%20To%20") . $contact_name
    . "&return_url=/contacts/one.php?contact_id=" . $contact_id;
}else{
$header_loc="../../activities/new-2.php?user_id=" . $session_user_id
    . "&activity_status=o&activity_type_id=1&company_id=" . $company_id . "&activity_title="
    . _("Call%20To%20") . _("Switchboard")
    . "&return_url=/companies/one.php?company_id=" . $company_id;
}
header("Location: ".$header_loc);

//Skype dialer
//header("Location: callto://+".$phone);
// if you don't want to create an activity on dial, use this instead:
// header("Location: $http_site_root/contacts/one.php?contact_id=$contact_id&msg=$msg");
?>
