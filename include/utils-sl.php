<?php

function fetch_current_customer_credit_limit($extref1) {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
	$current_credit_limit = '150000';
	
	return colorize_credit_limit($current_credit_limit);
	
}

function add_accounting_vendor($con, $company_id, $company_name, $company_code, $vendor_credit_limit, $vendor_terms) {
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
	$sql_insert_vendor = "insert into vendor (name, vendornumber) values (" . $sl_con->qstr($company_name, get_magic_quotes_gpc) . ", " . $sl_con->qstr($company_code, get_magic_quotes_gpc) . ")";	
	$sl_con->execute($sql_insert_vendor);
	
	$extref2 = $sl_con->insert_id();
	
	$sl_con->close();
	
	$sql_update_vendor_info = "update companies set extref2 = " . $con->qstr($extref2, get_magic_quotes_gpc()) . " where company_id = $company_id";
	
	$con->execute($sql_update_vendor_info);
	
}

function add_accounting_customer($con, $company_id, $company_name, $company_code, $customer_credit_limit, $customer_terms) {
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
	$sql_insert_customer = "insert into customer (name, customernumber) values (" . $sl_con->qstr($company_name, get_magic_quotes_gpc) . ", " . $sl_con->qstr($company_code, get_magic_quotes_gpc) . ")";	
	$sl_con->execute($sql_insert_customer);
	
	$extref1 = $sl_con->insert_id();
	
	$sl_con->close();
	
	$sql_update_customer_info = "update companies set extref1 = " . $con->qstr($extref1, get_magic_quotes_gpc()) . " where company_id = $company_id";
	
	$con->execute($sql_update_customer_info);
	
}

?>