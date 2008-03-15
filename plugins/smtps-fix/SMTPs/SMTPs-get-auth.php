<?php

// =============================================================
// CVS Id Info
// $Id: SMTPs-get-auth.php,v 1.1 2008/03/15 16:54:31 randym56 Exp $
//
// Get user's own SMTP information from user table and use for e-mail if it exists
// Written by Randy Martinsen


function set_SMTPs_defaults($con, $user_id=false, &$smtpsPort, &$msgReplyTo, &$smtpsHost, &$smtpsID, &$smtpsPW) {
	
    global $session_user_id;
    global $xrms_db_password;
	
    // Right off the bat, if these are not set, we can't do anything!
    if (!$con) return false;
	//$con->debug = 1;

	if (!$user_id) $user_id = $session_user_id;

	//get user settings 	
	$sql = "SELECT * FROM users WHERE user_id = $user_id";
	$rst = $con->execute($sql);
	
	if ($rst) {
	
		if ($rst->fields['email'] > '') $msgReplyTo = $rst->fields['email']; else $msgReplyTo = false;
		$smtpsID = $rst->fields['smtpsID'];
	// decrypt password
		if ($rst->fields['smtpsPW']) {
			$key = $xrms_db_password;
			$td = mcrypt_module_open('tripledes', '', 'ecb', '');
			$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
			mcrypt_generic_init($td, $key, $iv);
			$smtpsPW = mdecrypt_generic($td, $rst->fields['smtpsPW']);
			mcrypt_generic_deinit($td);
			mcrypt_module_close($td);
			} 
		$smtpsHost = $rst->fields['smtpsHost'];
		$smtpsPort = $rst->fields['smtpsPort'];
		$rst->close();
		}
	
	if ( !$msgReplyTo || !$smtpsID || !$smtpsPW || !$smtpsHost || !$smtpsPort) {
		//get admin settings for default - used only if user fails	
		$sql = "SELECT * FROM users WHERE user_id = '1'";
		$rst = $con->execute($sql);
		
		if ($rst) {
		
			if ($rst->fields['email'] > '') $msgReplyTo = $rst->fields['email']; else $msgReplyTo = false;
			$smtpsID = $rst->fields['smtpsID'];
		// decrypt password
			if ($rst->fields['smtpsPW']) {
				$key = $xrms_db_password;
				$td = mcrypt_module_open('tripledes', '', 'ecb', '');
				$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
				mcrypt_generic_init($td, $key, $iv);
				$smtpsPW = mdecrypt_generic($td, $rst->fields['smtpsPW']);
				mcrypt_generic_deinit($td);
				mcrypt_module_close($td);
				}
			$smtpsHost = $rst->fields['smtpsHost'];
			$smtpsPort = $rst->fields['smtpsPort'];
			$rst->close();
			}
		}
	return $rst->fields['username'];
	}
	


// =============================================================
// =============================================================

 /**
  * $Log: SMTPs-get-auth.php,v $
  * Revision 1.1  2008/03/15 16:54:31  randym56
  * Updated SMTPs to allow for individual user SMTP addressing - requires installation and activation of mcrypt in PHP - follow README.txt instructions
  *
  *
  */

?>
