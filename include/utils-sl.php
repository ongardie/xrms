<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

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
	
    //save to database
    $rec = array();
    $rec['name'] = $company_name;
    $rec['vendornumber'] = $company_code;
    
    $tbl = 'vendor';
    $sql_insert_vendor = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
	$sl_con->execute($sql_insert_vendor);
	
	$extref2 = $sl_con->insert_id();
	
	$sl_con->close();
	
    $sql_which_company = "SELECT * FROM companies WHERE company_id = $company_id";
    $rst_which_company = $con->execute($sql_which_company);
	
    $rec = array();
    $rec['extref2'] = $extref2;
    
	$sql_update_vendor_info = $con->GetUpdateSQL($rst_which_company, $rec, false, get_magic_quotes_gpc());
	$con->execute($sql_update_vendor_info);
	
}

function add_accounting_customer($con, $company_id, $company_name, $company_code, $customer_credit_limit, $customer_terms) {
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
    //save to database
    $rec = array();
    $rec['name'] = $company_name;
    $rec['customernumber'] = $company_code;
    
    $tbl = 'customer';
    $sql_insert_customer = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
	$sl_con->execute($sql_insert_customer);
	
	$extref1 = $sl_con->insert_id();
	
	$sl_con->close();
	
    $sql_which_company = "SELECT * FROM companies WHERE company_id = $company_id";
    $rst_which_company = $con->execute($sql_which_company);
    
    $rec = array();
    $rec['extref1'] = $extref1;
	
	$sql_update_customer_info = $con->GetUpdateSQL($rst_which_company, $rec, false, get_magic_quotes_gpc());
	$con->execute($sql_update_customer_info);
	
}

?>