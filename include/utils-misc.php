<?php

function session_check() {
    
    global $http_site_root;
    global $xrms_system_id;
    
    session_start();
    
    if ((!$_SESSION['session_user_id'] > 0) || (strcmp($_SESSION['xrms_system_id'], $xrms_system_id) != 0)) {
		header("Location: $http_site_root" . "/login.php");
		exit;
	}
    
    return $_SESSION['session_user_id'];
    
}

function translate_msg($msg) {
	switch ($msg) {
		case 'noauth':
			return "We could not authenticate you.  Please try again.";
			break;
		case 'saved':
			return "Changes saved.";
			break;
		case 'activity_added':
			return "Activity added.";
			break;
		case 'contact_added':
			return "Contact added.";
			break;
		case 'company_added':
			return "Company added.";
			break;
	}
}

function update_recent_items($con, $user_id, $on_what_table, $on_what_id) {
	$sql1 = "delete from recent_items where user_id = $user_id and on_what_table = '" . $on_what_table . "' and on_what_id = $on_what_id";
	$sql2 = "insert into recent_items (user_id, on_what_table, on_what_id, recent_item_timestamp) values ($user_id, '" . $on_what_table . "', $on_what_id, " . $con->dbtimestamp(time()) . ")";
	// print $sql;
	$con->execute($sql1);
	$con->execute($sql2);
}

function add_audit_item($con, $user_id, $audit_item_type, $on_what_table, $on_what_id) {
	$sql = "insert into audit_items (user_id, audit_item_type, on_what_table, on_what_id, audit_item_timestamp) values ($user_id, " . $con->qstr($audit_item_type, get_magic_quotes_gpc()) . ", " . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ", " . $con->qstr($on_what_id, get_magic_quotes_gpc()) . ", " . $con->dbtimestamp(time()) . ")";
	$con->execute($sql);
}

// just in case we need it at some point...
function fetch_company_name($con, $company_id) {
    
    $rst_company_name = $con->execute("select company_name from companies where company_id = $company_id");
    if ($rst_company_name) {
        $company_name = $rst_company_name->fields['company_name'];
        $rst_company_name->close();
    }
    
    return $company_name;
}

// this nifty function came from someone up at php.net
function pretty_filesize($file_size) {
	$a = array("B", "KB", "MB", "GB", "TB", "PB");
	$pos = 0;
	
	while ($file_size >= 1024) {
    	$file_size /= 1024;
		$pos++;
	}
	return round($file_size,2)." ".$a[$pos];
}

?>
