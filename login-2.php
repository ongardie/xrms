<?php

require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$username = $_POST['username'];
$password = $_POST['password'];
$target   = $_POST['target'];
    if ($target== '') {
        $target=$http_site_root.'/private/home.php';
    }



$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$ldapok = true;
if ($xrms_use_ldap) {
	//if we use ldap, we check the password there first, and we do not in check it in db
	$ldapok = false;
	ini_set ('error_reporting', 0);
	$ds=ldap_connect($xrms_ldap["server"]);  // connect to the LDAP server!
	if ($ds) {
		$r=ldap_bind($ds, $xrms_ldap["search_user"], $xrms_ldap["search_password"]);  //authenticate as the search user specified
		$justthese = array ("dn","fullname","mail","groupmembership");  //specify which attributes to fetch from ldap
		$sr=ldap_search($ds,$xrms_ldap["search_context"], $xrms_ldap["search_attribute"]."=".$username, $justthese);  // Search for the user name
		$info = ldap_get_entries($ds, $sr);
		
		if ($info[0]["dn"] != "") {  //If we found a user (we assume that usernames are unique)
			$r=ldap_bind($ds, $info[0]["dn"], $password);  //Try to authenthenticate using the password provided
			if ($r) {
			   $ldapok = true;  //Password check was successfull
			}
		}
		ldap_close($ds);
	}
	ini_restore ('error_reporting');
	
	if ($ldapok) {  
		//we have been able to authenticate against ldap, now lets retreive the user info from the database
		$sql = "select * from users where username = " . $con->qstr($username, get_magic_quotes_gpc());
		$rst = $con->execute($sql);
		
		//we check tje user_record_status separatly in order to allow ldap provisioning which
		//should not occur if the user exists but is not marked active...
		if ($rst && !$rst->EOF) {  
			if ($rst->fields['user_record_status'] != "a") {  
				//the user is present but is not marked active, authentication failed
				$ldapok = false;
			}
		} else {
			// if the user does not exist in the database but we were able to authenticate him, we create it automatically in the database
			//*** TO BE DONE ***
			//$rst = ldap_provisioning($con, $info[0]);  
		}
		
	} 
	
} else {
	//We are using db to check the password
	$password = md5($password);
	$sql = "select * from users where username = " . $con->qstr($username, get_magic_quotes_gpc()) . " AND password = " . $con->qstr($password, get_magic_quotes_gpc()) . " AND user_record_status = 'a'";
	$rst = $con->execute($sql);
}



if ($rst && !$rst->EOF && $ldapok) {
    $session_user_id = $rst->fields['user_id'];
    $user_type_id = $rst->fields['user_type_id'];
    $username = $rst->fields['username'];
    $language = $rst->fields['language'];
    $gmt_offset = $rst->fields['gmt_offset'];
    $rst->close();
    session_start();
    $_SESSION['session_user_id'] = $session_user_id;
    $_SESSION['xrms_system_id'] = $xrms_system_id;
    $_SESSION['user_type_id'] = $user_type_id;
    $_SESSION['username'] = $username;
    $_SESSION['language'] = $language;
    $_SESSION['gmt_offset'] = $gmt_offset;
    header("Location: $target");
} else {
    header("Location: $http_site_root/login.php?msg=noauth");
}

?>