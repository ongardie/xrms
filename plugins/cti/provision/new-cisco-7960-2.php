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

$sql = "SELECT * from cti_cisco_7960_config WHERE mac = '" . $mac . "'";
$rst = $con->execute($sql);

if ($rst) {
    if (!$rst->EOF) {
        while (!$rst->EOF) {

$dial_file_contents = "proxy1_address: \"" . $rst->fields['proxy1_address'] . "\" 
line1_name: \"" . $rst->fields['line1_name'] . "\"
line1_displayname: \"" . $rst->fields['line1_displayname'] . "\"
line1_authname: \"" . $rst->fields['line1_authname'] . "\"
line1_password: \"" . $rst->fields['line1_password'] . "\"

proxy2_address: \"" . $rst->fields['proxy2_address'] . "\"
line2_name: \"" . $rst->fields['line2_name'] . "\"
line2_displayname: \"" . $rst->fields['line2_displayname'] . "\"
line2_authname: \"" . $rst->fields['line2_authname'] . "\"
line2_password: \"" . $rst->fields['line2_password'] . "\"

proxy3_address: \"" . $rst->fields['proxy3_address'] . "\"
line3_name: \"" . $rst->fields['line3_name'] . "\"
line3_displayname: \"" . $rst->fields['line3_displayname'] . "\"
line3_authname: \"" . $rst->fields['line3_authname'] . "\"
line3_password: \"" . $rst->fields['line3_password'] . "\"

proxy4_address: \"" . $rst->fields['proxy4_address'] . "\"
line4_name: \"" . $rst->fields['line4_name'] . "\"
line4_displayname: \"" . $rst->fields['line4_displayname'] . "\"
line4_authname: \"" . $rst->fields['line4_authname'] . "\"
line4_password: \"" . $rst->fields['line4_password'] . "\"

proxy5_address: \"" . $rst->fields['proxy5_address'] . "\"
line5_name: \"" . $rst->fields['line5_name'] . "\"
line5_displayname: \"" . $rst->fields['line5_displayname'] . "\"
line5_authname: \"" . $rst->fields['line5_authname'] . "\"
line5_password: \"" . $rst->fields['line5_password'] . "\"

proxy6_address: \"" . $rst->fields['proxy6_address'] . "\"
line6_name: \"" . $rst->fields['line6_name'] . "\"
line6_displayname: \"" . $rst->fields['line6_displayname'] . "\"
line6_authname: \"" . $rst->fields['line6_authname'] . "\"
line6_password: \"" . $rst->fields['line6_password'] . "\"

proxy_emergency: \"" . $rst->fields['proxy_emergency'] . "\"
proxy_emergency_port: \"" . $rst->fields['proxy_emergency_port'] . "\"

proxy_backup: \"" . $rst->fields['proxy_backup'] . "\"
proxy_backup_port: \"" . $rst->fields['proxy_backup_port'] . "\"

outbound_proxy: \"" . $rst->fields['outbound_proxy'] . "\"
outbound_proxy_port: \"" . $rst->fields['outbound_proxy_port'] . "\"

nat_enable: \"" . $rst->fields['nat_enable'] . "\"
nat_address: \"" . $rst->fields['nat_address'] . "\"
voip_control_port: \"" . $rst->fields['voip_control_port'] . "\"
start_media_port: \"" . $rst->fields['start_media_port'] . "\"
end_media_port:  \"" . $rst->fields['end_media_port'] . "\"
nat_received_processing: \"" . $rst->fields['nat_received_processing'] . "\"

phone_label: \"" . $rst->fields['phone_label'] . "\"

time_zone: \"" . $rst->fields['time_zone'] . "\"

telnet_level: \"" . $rst->fields['telnet_level'] . "\"

phone_prompt: \"" . $rst->fields['phone_prompt'] . "\"
phone_password: \"" . $rst->fields['phone_password'] . "\"

enable_vad: \"" . $rst->fields['enable_vad'] . "\"
";

$filename = $xrms_file_root . "/tmp/SIP" . $mac . ".cnf";

   if (!$handle = fopen($filename, 'w')) {
         echo "Cannot open file ($filename)";
         exit;
   }

   if (fwrite($handle, $dial_file_contents) === FALSE) {
       echo "Cannot write to file ($filename)";
       exit;
   }

   fclose($handle);
   
               $rst->movenext();
            }
        }
    }
}
        
header("Location: " . $http_site_root . "/admin/routing.php?msg=" . urlencode(_("Phone Provisioned")));
   
/**
 * $Log: new-cisco-7960-2.php,v $
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
