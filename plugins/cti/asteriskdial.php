<?php
/*
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
 * If you are using the sip.conf based lookupCID,
 * Be sure to add crm_username(s) to your sip.conf file. See below.
 *
 * IF Asterisk is running on the same server as XRMS,
 * MAKE SURE /var/spool/asterisk/outgoing is writable
 * by your web server.
 *
 * IF asterisk is running on another server, use sftp
 * to copy the file over.
 *
 */

/*
 * LookupCID :: ismaeljcarlo
 * simple function to lookup extension number from sip.conf
 *
 * ismaeljcarlo@users.sourceforge.net created this function which looks up
 * the value of crm_username from sip.conf and returns the proper extension.
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

$con = get_xrms_dbconnection();
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

// This is the file that will be passed to Asterisk

$dial_file_contents = "Channel:$channel$extension_to_dial
MaxRetries: 1
RetryTime: 60
WaitTime: 30
Callerid: $CID
Context: xrms
Extension: $phone_dial_prefix$phone
Priority: 1
";

$filename = $xrms_file_root . "/tmp/outdial-$phone";

   if (!$handle = fopen($filename, 'w')) {
         echo "Cannot open file ($filename)";
         exit;
   }

   if (fwrite($handle, $dial_file_contents) === FALSE) {
       echo "Cannot write to file ($filename)";
       exit;
   }
   system("mv $filename /var/spool/asterisk/outgoing");

   fclose($handle);

// Create an Activity on Dial
header("Location: ../../activities/new-2.php?user_id=" . $session_user_id
    . "&activity_status=o&activity_type_id=1&contact_id="
    . $contact_id . "&company_id=" . $company_id . "&activity_title="
    . _("Call%20To%20") . $contact_name
    . "&return_url=/contacts/one.php?contact_id=" . $contact_id);


// if you don't want to create an activity on dial, use this instead:
// header("Location: $http_site_root/contacts/one.php?contact_id=$contact_id&msg=$msg");

/**
 * $Log: asteriskdial.php,v $
 * Revision 1.4  2006/01/02 23:52:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.3  2004/11/16 01:38:58  gpowers
 * - added code to use contact.custom1..3 fields for Channel, Extension and CalledID for each XRMS user.
 *
 * Revision 1.2  2004/10/22 08:18:44  gpowers
 * - added patch by ismaeljcarlo @ SF
 *   - lookup crm_username from /etc/asterisk/sip.conf
 *
 * Revision 1.1  2004/08/02 13:49:11  gpowers
 * - CTI / Asterisk Out Dial XRMS Plugin v0.1
 *   - uses the Asterisk Open Source Soft PBX from http://www.asterisk.org/
 * - this plugin has not been tested with asterisk yet.
 * Revision 1.4 2004/10/21 22:05:00 ismaeljcarlo
 * added lookup to sip.conf
 *
 *
 * Revision 1.3  2004/07/22 13:43:50  gpowers
 * - i18n'ed "Back" link
 *
 * Revision 1.2  2004/07/22 13:32:41  gpowers
 * - put server vars and comment at head of file
 * - i18n'ed page title
 * - added phpdoc log
 *
 */
?>
