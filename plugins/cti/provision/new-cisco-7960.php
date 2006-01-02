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

$user_id = $_POST['user_id'];
$email = $_POST['email'];
$extension = $_POST['extension'];
$username = $_POST['username'];
$first_names = $_POST['first_names'];
$last_name = $_POST['last_name'];

$mac = $_POST['mac'];
$vm_password = $_POST['vm_password'];

// DEFAULTS
$proxy_server = "127.0.0.1"; // CHANGE THIS!
$telnet_password = "TEST"; // REPLACE THIS!

if (check_object_permission_bool($_SESSION['session_user_id'], 'cti_sip_admin', 'Create')) {
 
$rec = array();

$rec['full_name'] = $first_names . " " . $last_name;

$rec['proxy1_address'] = $proxy_server;
$rec['line1_name'] = $username . "-1";
$rec['line1_displayname'] = $username . "-1";
$rec['line1_authname'] = $username . "-1";
$rec['line1_password'] = mt_rand(10000000,99999999);

$rec['proxy2_address'] = $proxy_server;
$rec['line2_name'] = $username . "-2";
$rec['line2_displayname'] = $username . "-2";
$rec['line2_authname'] = $username . "-2";
$rec['line2_password'] = mt_rand(10000000,99999999);

$rec['proxy3_address'] = $proxy_server;
$rec['line3_name'] = $username . "-3";
$rec['line3_displayname'] = $username . "-3";
$rec['line3_authname'] = $username . "-3";
$rec['line3_password'] = mt_rand(10000000,99999999);

$rec['proxy4_address'] = $proxy_server;
$rec['line4_name'] = $username . "-4";
$rec['line4_displayname'] = $username . "-4";
$rec['line4_authname'] = $username . "-4";
$rec['line4_password'] = mt_rand(10000000,99999999);

$rec['proxy5_address'] = $proxy_server;
$rec['line5_name'] = $username . "-5";
$rec['line5_displayname'] = $username . "-5";
$rec['line5_authname'] = $username . "-5";
$rec['line5_password'] = mt_rand(10000000,99999999);

$rec['proxy6_address'] = $proxy_server;
$rec['line6_name'] = $username . "-6";
$rec['line6_displayname'] = $username . "-6";
$rec['line6_authname'] = $username . "-6";
$rec['line6_password'] = mt_rand(10000000,99999999);

$rec['proxy_emergency'] = "";
$rec['proxy_emergency_port'] = "";

$rec['proxy_backup'] = "";
$rec['proxy_backup_port'] = "";

$rec['outbound_proxy'] = "";
$rec['outbound_proxy_port'] = "";

$rec['nat_enable'] = "";
$rec['nat_address'] = "";
$rec['voip_control_port'] = "5060";
$rec['start_media_port'] = "7000";
$rec['end_media_port'] = "8000";
$rec['nat_received_processing'] = "";

$rec['phone_label'] = "Telephone";

$rec['time_zone'] = "EST";

$rec['telnet_level'] = "2";

$rec['phone_prompt'] = "cisco";
$rec['phone_password'] = $telnet_password;

$rec['enable_vad'] = "0";

$rec['context'] = "office";
$rec['vm_email'] = $email;
$rec['vm_password'] = $vm_password;
$rec['extension'] = $extension;

$rec['mac'] = $mac;

$tbl = 'cti_cisco_7960_config';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

add_audit_item($con, $session_user_id, 'provisioned', 'cisco-office-phone', $mac, 1);
   
header("Location: " . $http_site_root . "/plugins/cti/provision/new-cisco-7960-2.php?mac=" . urlencode($mac));

} else {
    echo _("You do not have permission to provision phones.");
}

/**
 * $Log: new-cisco-7960.php,v $
 * Revision 1.3  2006/01/02 23:52:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.2  2005/04/03 19:18:45  gpowers
 * - updated to use mt_rand(10000000,99999999) for sip passwords
 *   - this should be good enough for phones on private LANs.
 *
 * Revision 1.1  2005/03/31 22:39:59  gpowers
 * - basic Cisco 7960 CNF file setup
 * - asterisk sip.conf config
 * - asterisk voicemail.conf config
 *
 */
?>
