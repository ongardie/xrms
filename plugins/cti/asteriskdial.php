<?php
/*
*
* CTI / Asterisk Outdial XRMS Plugin v0.1
* uses asterisk from:
* http://www.asterisk.org/
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the Open Software License v. 2.0
*
*/

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];
$contact_id = $_GET['contact_id'];
$phone = $_GET['phone'];

$msg = _("Dialing Phone Number:") . $phone;

# assume all phone numbers can be dialed by adding a 1
# all outgoing calls will be connected to ext. 800 in "callme" context

$dial_file_contents = "Channel: Zap/1/1$phone
 MaxRetries: 2
 RetryTime: 60
 WaitTime: 30
 Context: callme
 Extension: 800
 Priority: 2
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

header("Location: $http_site_root/contacts/one.php?contact_id=$contact_id");

/**
 * $Log: asteriskdial.php,v $
 * Revision 1.1  2004/08/02 13:49:11  gpowers
 * - CTI / Asterisk Out Dial XRMS Plugin v0.1
 *   - uses the Asterisk Open Source Soft PBX from http://www.asterisk.org/
 * - this plugin has not been tested with asterisk yet.
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
