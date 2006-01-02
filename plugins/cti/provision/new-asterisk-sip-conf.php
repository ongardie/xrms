<?php
/*
*
* CTI / Create Cisco 7960 CNF Files XRMS Plugin v0.2
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the GNU GPL v. 2.0
*
*/

// include the common files
require_once('../../../include-locations.inc');

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
$mac = $_GET['mac'];

if (check_object_permission_bool($_SESSION['session_user_id'], 'cti_sip_admin', 'Create')) {

$sql = "SELECT * from cti_cisco_7960_config";
$rst = $con->execute($sql);

if ($rst) {
    if (!$rst->EOF) {
        while (!$rst->EOF) {

$callerid = " \"" . $rst->fields['full_name'] ."\" <888-555-" . $rst->fields['extension'] . ">";
            
$dial_file_contents .= "
[" . $rst->fields['line1_name'] . "]
username=" . $rst->fields['line1_authname'] . "
secret=" . $rst->fields['line1_password'] . "
context=" .  $rst->fields['context'] . "
mailbox=" . $rst->fields['extension'] . "
callerid=" . $callerid . "
type=friend
host=dynamic
dtmfmode=rfc2833
canreinvite=yes

[" . $rst->fields['line2_name'] . "]
username=" . $rst->fields['line2_authname'] . "
secret=" . $rst->fields['line2_password'] . "
context=" .  $rst->fields['context'] . "
mailbox=" . $rst->fields['extension'] . "
callerid=" . $callerid . "
type=friend
host=dynamic
dtmfmode=rfc2833
canreinvite=yes

[" . $rst->fields['line3_name'] . "]
username=" . $rst->fields['line3_authname'] . "
secret=" . $rst->fields['line3_password'] . "
context=" .  $rst->fields['context'] . "
mailbox=" . $rst->fields['extension'] . "
callerid=" . $callerid . "
type=friend
host=dynamic
dtmfmode=rfc2833
canreinvite=yes

[" . $rst->fields['line4_name'] . "]
username=" . $rst->fields['line4_authname'] . "
secret=" . $rst->fields['line4_password'] . "
context=" .  $rst->fields['context'] . "
callerid=" . $callerid . "
type=friend
host=dynamic
dtmfmode=rfc2833
canreinvite=yes

[" . $rst->fields['line5_name'] . "]
username=" . $rst->fields['line5_authname'] . "
secret=" . $rst->fields['line5_password'] . "
context=" .  $rst->fields['context'] . "
mailbox=" . $rst->fields['extension'] . "
callerid=" . $callerid . "
type=friend
host=dynamic
dtmfmode=rfc2833
canreinvite=yes

[" . $rst->fields['line6_name'] . "]
username=" . $rst->fields['line6_authname'] . "
secret=" . $rst->fields['line6_password'] . "
context=" .  $rst->fields['context'] . "
mailbox=" . $rst->fields['extension'] . "
callerid=" . $callerid . "
type=friend
host=dynamic
dtmfmode=rfc2833
canreinvite=yes
";


   
               $rst->movenext();
            }
        }
    }
    
    $filename = $xrms_file_root . "/tmp/sip.conf";

   if (!$handle = fopen($filename, 'w')) {
         echo "Cannot open file ($filename)";
         exit;
   }

   if (fwrite($handle, $dial_file_contents) === FALSE) {
       echo "Cannot write to file ($filename)";
       exit;
   }

   fclose($handle);
    
    
}
        
header("Location: " . $http_site_root . "/admin/routing.php?msg=" . urlencode(_("New sip.conf Created")));
   
/**
 * $Log: new-asterisk-sip-conf.php,v $
 * Revision 1.2  2006/01/02 23:52:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.1  2005/03/31 22:39:59  gpowers
 * - basic Cisco 7960 CNF file setup
 * - asterisk sip.conf config
 * - asterisk voicemail.conf config
 *
 */
?>
