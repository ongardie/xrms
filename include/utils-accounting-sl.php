<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

$inventory_account_number = '1520';
$income_account_number = '4020';
$expense_account_number = '5010';

function fetch_inventory_accno_id($con) {
	
	global $inventory_account_number;
	
	$sql = "select id from chart where accno = " . $inventory_account_number;
	
	$rst = $con->execute($sql);
	
	$chartid = $rst->fields['id'];
	
	$rst->close();
	
	return $chartid;
	
}

function fetch_income_accno_id($con) {
	
	global $income_account_number;
	
	$sql = "select id from chart where accno = " . $income_account_number;
	
	$rst = $con->execute($sql);
	
	$chartid = $rst->fields['id'];
	
	$rst->close();
	
	return $chartid;
	
}

function fetch_expense_accno_id($con) {
	
	global $expense_account_number;
	
	$sql = "select id from chart where accno = " . $expense_account_number;
	
	$rst = $con->execute($sql);
	
	$chartid = $rst->fields['id'];
	
	$rst->close();
	
	return $chartid;
	
}

function fetch_current_customer_credit_limit($extref1) {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	// $sl_con->debug = 1;
	
	$sql_fetch_customer_credit_limit = "select creditlimit from customer where id = $extref1";
		$rst_customer_credit_limit = $sl_con->execute($sql_fetch_customer_credit_limit);
	if ($rst_customer_credit_limit && !$rst_customer_credit_limit->EOF) {
		$customer_credit_limit = $rst_customer_credit_limit->fields['creditlimit'];
		$rst_customer_credit_limit->close();
	}
	
	$sql_fetch_current_customer_credit_limit = "select sum(amount - paid) as receivables from ar where customer_id = $extref1";
	$rst_current_customer_credit_limit = $sl_con->execute($sql_fetch_current_customer_credit_limit);
	if ($rst_current_customer_credit_limit && !$rst_current_customer_credit_limit->EOF) {
		$receivables = $rst_current_customer_credit_limit->fields['receivables'];
		$rst_current_customer_credit_limit->close();
	}
	
	$sl_con->close();
	
	return colorize_credit_limit($customer_credit_limit - $receivables);
	// return colorize_credit_limit(0);
}

function fetch_current_vendor_credit_limit($extref2) {
	
	global $xrms_db_dbtype;
	global $xrms_db_server;
	global $xrms_db_username;
	global $xrms_db_password;
	global $xrms_db_dbname;
	
	global $xrms_db_dbtype;
	global $xrms_db_server;
	global $xrms_db_username;
	global $xrms_db_password;
	global $xrms_db_dbname;
	
	$bs_con = &adonewconnection($bs_db_dbtype);
	$bs_con->connect($bs_db_server, $bs_db_username, $bs_db_password, $bs_db_dbname);
	// $bs_con->debug = 1;
	
	$sql_fetch_vendor_credit_limit = "select vendor_credit_limit from companies where extref2 = '" . $extref2 . "'";
	$rst_vendor_credit_limit = $bs_con->execute($sql_fetch_vendor_credit_limit);
	$vendor_credit_limit = $rst_vendor_credit_limit->fields['vendor_credit_limit'];
	$rst_vendor_credit_limit->close();
	
	$bs_con->close();
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	// $sl_con->debug = 1;
	
	$sql_fetch_current_vendor_credit_limit = "select sum(amount - paid) as payables from ap where vendor_id = $extref2";
	$rst_current_vendor_credit_limit = $sl_con->execute($sql_fetch_current_vendor_credit_limit);
	if ($rst_current_vendor_credit_limit && !$rst_current_vendor_credit_limit->EOF) {
		$current_vendor_credit_limit = $rst_current_vendor_credit_limit->fields['payables'];
		$rst_current_vendor_credit_limit->close();
	}
	$sl_con->close();
	
	return colorize_credit_limit($vendor_credit_limit - $payables);
	// return colorize_credit_limit(1);
}

function add_accounting_vendor($con, $company_id, $company_name, $company_code, $vendor_credit_limit, $vendor_terms) {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
    //save to database
    $rec = array();
    $rec['name'] = $company_name;
    $rec['vendornumber'] = $company_code;

    $tbl = 'vendor';
    $sql_insert_vendor = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
	$sl_con->execute($sql_insert_vendor);
	
	$sql_which_vendor = "select max(id) as vendor_id from vendor";
	$rst_which_vendor = $sl_con->execute($sql_which_vendor);
	$extref2 = $rst_which_vendor->fields['vendor_id'];
	$rst_which_vendor->close();
	$sl_con->close();
	
    $sql_which_company = "SELECT * FROM companies WHERE company_id = $company_id";
    $rst_which_company = $con->execute($sql_which_company);
	
    $rec = array();
    $rec['extref2'] = $extref2;

	$sql_update_vendor_info = $con->GetUpdateSQL($rst_which_company, $rec, false, get_magic_quotes_gpc());
	$con->execute($sql_update_vendor_info);
	
}

function add_accounting_customer($con, $company_id, $company_name, $company_code, $customer_credit_limit, $customer_terms) {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
    //save to database
    $rec = array();
    $rec['name'] = $company_name;
    $rec['customernumber'] = $company_code;

    $tbl = 'customer';
    $sql_insert_customer = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
	$sl_con->execute($sql_insert_customer);
	
	$sql_which_customer = "select max(id) as customer_id from customer";
	$rst_which_customer = $sl_con->execute($sql_which_customer);
	$extref1 = $rst_which_customer->fields['customer_id'];
	$rst_which_customer->close();
	
	$sl_con->close();
	
    $sql_which_company = "SELECT * FROM companies WHERE company_id = $company_id";
    $rst_which_company = $con->execute($sql_which_company);
	
    $rec = array();
    $rec['extref1'] = $extref1;

	$sql_update_customer_info = $con->GetUpdateSQL($rst_which_company, $rec, false, get_magic_quotes_gpc());
	$con->execute($sql_update_customer_info);
	
}

function update_vendor_account_information($extref2, $vendor_credit_limit, $vendor_terms) {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
    $sql_which_vendor = "SELECT * FROM vendor WHERE extref = $extref2";
    $rst_which_vendor = $sl_con->execute($sql_which_vendor);

    $rec = array();
    $rec['terms'] = $vendor_terms;

	$sql_update_vendor_info = $sl_con->GetUpdateSQL($rst_which_vendor, $rec, false, get_magic_quotes_gpc());
	$sl_con->execute($sql_update_vendor_info);
	
	$sl_con->close();
	
}

function update_customer_account_information($extref1, $customer_credit_limit, $customer_terms) {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
    $sql_which_customer = "SELECT * FROM customer WHERE id = $extref1";
    $rst_which_customer = $sl_con->execute($sql_which_customer);

    $rec = array();
    $rec['terms'] = $customer_sterms;
    $rec['creditlimit'] = $customer_credit_limit;

	$sql_update_customer_info = $sl_con->GetUpdateSQL($rst_which_customer, $rec, false, get_magic_quotes_gpc());
	$sl_con->execute($sql_update_customer_info);
	
	$sl_con->close();
}

function add_sales_order_to_accounting_system($order_id) {
	
	global $bs_db_dbtype;
	global $bs_db_server;
	global $bs_db_username;
	global $bs_db_password;
	global $bs_db_dbname;
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	// open database connections
	
	$bs_con = &adonewconnection($bs_db_dbtype);
	$bs_con->connect($bs_db_server, $bs_db_username, $bs_db_password, $bs_db_dbname);
	// $bs_con->debug = 1;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	// $sl_con->debug = 1;
	
	// fetch order details from Brokerstream
	
	$sql_fetch_order_details = "select o.*, c.extref1 as customer from orders o, companies c where o.company_id = c.company_id and order_id = $order_id";
	$rst_order_details = $bs_con->execute($sql_fetch_order_details);
	
	$customer_id = $rst_order_details->fields['customer'];
	$ordnumber = $rst_order_details->fields['order_name'];
	$transdate = $rst_order_details->fields['entered_at'];
	$employee_id = $rst_order_details->fields['entered_by'];
	$amount = order_total($bs_con, $order_id);
	$netamount = $amount;
	$terms = $rst_order_details->fields['terms'];
	$reqdate = $rst_order_details->fields['ship_date'];
	$shippingpoint = $rst_order_details->fields['ship_via'];
	$vendor_id = 0;
	$taxincluded = 'f';
	$notes = $rst_order_details->fields['order_note'];
	$curr = 'USD';
	$closed = 't';
	
	$rst_order_details->close();
	
	// insert those order details into SQL-Ledger
    $rec = array();
    $rec['ordnumber'] = $ordnumber;
    $rec['transdate'] = $sl_con->dbdate($transdate);
    $rec['vendor_id'] = $vendor_id;
    $rec['customer_id'] = $customer_id;
    $rec['amount'] = $amount;
    $rec['netamount'] = $netamount;
    $rec['reqdate'] = $sl_con->dbdate($reqdate);
    $rec['taxincluded'] = $taxincluded;
    $rec['shippingpoint'] = $shippingpoint;
    $rec['notes'] = $notes;
    $rec['curr'] = $curr;
    $rec['closed'] = $closed;
	
    $tbl = 'oe';
    $sql_add_new_order = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
	$sl_con->execute($sql_add_new_order);
	
	// fetch the new trans id
	
	$sql_fetch_new_transid = "select max(id) as transid from oe";
	$rst_new_transid = $sl_con->execute($sql_fetch_new_transid);
	$transid = $rst_new_transid->fields['transid'];
	$rst_new_transid->close();
	
    $sql_which_order = "SELECT * FROM orders WHERE order_id = $order_id";
    $rst_which_order = $bs_con->execute($sql_which_order);

    $rec = array();
    $rec['extref1'] = $transid;

	$sql_update_order_extref = $bs_con->GetUpdateSQL($rst_which_order, $rec, false, get_magic_quotes_gpc());
	$bs_con->execute($sql_update_order_extref);
	
	$sql_fetch_items_in_order = "select * from items_in_orders where order_id = $order_id";
	
	$rst_items_in_order = $bs_con->execute($sql_fetch_items_in_order);
	
	if ($rst_items_in_order) {
		while (!$rst_items_in_order->EOF) {
			// is this part on file?
			$sql_find_part_on_record = "select * from parts where partnumber = " . $sl_con->qstr($rst_items_in_order->fields['clean_pn'], get_magic_quotes_gpc());
			$rst_matching_parts = $sl_con->execute($sql_find_part_on_record);
			if ($rst_matching_parts && !$rst_matching_parts->EOF) {
				// yep
				$part_id = $rst_matching_parts->fields['id'];
				$rst_matching_parts->close();
			} else {
				// nope
				$inventory_accno_id = fetch_inventory_accno_id($sl_con);
				$income_accno_id = fetch_income_accno_id($sl_con);
				$expense_accno_id = fetch_expense_accno_id($sl_con);
				$unit = '';
                
                //save to database
                $rec = array();
                $rec['partnumber'] = $rst_items_in_order->fields['clean_pn'];
                $rec['unit'] = $unit;
                $rec['inventory_accno_id'] = $inventory_accno_id;
                $rec['income_accno_id'] = $income_accno_id;
                $rec['expense_accno_id'] = $expense_accno_id;
                
                $tbl = 'parts';
                $sql_insert_part = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
				$sl_con->execute($sql_insert_part);
                
				$sql_fetch_new_part_id = "select max(id) as max_partid from parts";
				$rst_new_part_id = $sl_con->execute($sql_fetch_new_part_id);
				$part_id = $rst_new_part_id->fields['max_partid'];
				$rst_new_part_id->close();
			}
			
			// we've got a part, now add the item to the order in SQL-Ledger
            $rec = array();
            $rec['trans_id'] = $trans_id;
            $rec['parts_id'] = $part_id;
            $rec['description'] = $rst_items_in_order->fields['clean_pn'];
            $rec['qty'] = $rst_items_in_order->fields['qty'];
            $rec['sellprice'] = $rst_items_in_order->fields['price'];

            $tbl = 'orderitems';
            $sql_add_item_to_order = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
			$sl_con->execute($sql_add_item_to_order);
            
			$rst_items_in_order->movenext();
		}
		
		
	}
	
	return 1;
	
	$bs_con->close();
	$sl_con->close();
}

function add_purchase_order_to_accounting_system($order_id) {
	
	global $bs_db_dbtype;
	global $bs_db_server;
	global $bs_db_username;
	global $bs_db_password;
	global $bs_db_dbname;
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	// open database connections
	
	$bs_con = &adonewconnection($bs_db_dbtype);
	$bs_con->connect($bs_db_server, $bs_db_username, $bs_db_password, $bs_db_dbname);
	// $bs_con->debug = 1;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	// $sl_con->debug = 1;
	
	// fetch order details from Brokerstream
	
	$sql_fetch_order_details = "select o.*, c.extref2 as vendor from orders o, companies c where o.company_id = c.company_id and order_id = $order_id";
	$rst_order_details = $bs_con->execute($sql_fetch_order_details);
	
	$vendor_id = $rst_order_details->fields['vendor'];
	$ordnumber = $rst_order_details->fields['order_name'];
	$transdate = $rst_order_details->fields['entered_at'];
	$employee_id = $rst_order_details->fields['entered_by'];
	$amount = order_total($bs_con, $order_id);
	$netamount = $amount;
	$terms = $rst_order_details->fields['terms'];
	$reqdate = $rst_order_details->fields['ship_date'];
	$shippingpoint = $rst_order_details->fields['ship_via'];
	$customer_id = 0;
	$taxincluded = 'f';
	$notes = $rst_order_details->fields['order_note'];
	$curr = 'USD';
	$closed = 't';
	
	$rst_order_details->close();
	
	// insert those order details into SQL-Ledger
    $rec = array();
    $rec['ordnumber'] = $ordnumber;
    $rec['transdate'] = $sl_con->dbdate($transdate);
    $rec['vendor_id'] = $vendor_id;
    $rec['customer_id'] = $customer_id;
    $rec['amount'] = $amount;
    $rec['netamount'] = $netamount;
    $rec['reqdate'] = $sl_con->dbdate($reqdate);
    $rec['taxincluded'] = $taxincluded;
    $rec['shippingpoint'] = $shippingpoint;
    $rec['notes'] = $notes;
    $rec['curr'] = $curr;
    $rec['closed'] = $closed;
	
    $tbl = 'oe';
    $sql_add_new_order = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
	$sl_con->execute($sql_add_new_order);
	
	// fetch the new trans id
	
	$sql_fetch_new_transid = "select max(id) as transid from oe";
	$rst_new_transid = $sl_con->execute($sql_fetch_new_transid);
	$transid = $rst_new_transid->fields['transid'];
	$rst_new_transid->close();
	
    $sql_which_order = "SELECT * FROM orders WHERE order_id = $order_id";
    $rst_which_order = $bs_con->execute($sql_which_order);

    $rec = array();
    $rec['extref1'] = $transid;

	$sql_update_order_extref = $bs_con->GetUpdateSQL($rst_which_order, $rec, false, get_magic_quotes_gpc());
	$bs_con->execute($sql_update_order_extref);
	
	$sql_fetch_items_in_order = "select * from items_in_orders where order_id = $order_id";
	
	$rst_items_in_order = $bs_con->execute($sql_fetch_items_in_order);
	
	if ($rst_items_in_order) {
		while (!$rst_items_in_order->EOF) {
			// is this part on file?
			$sql_find_part_on_record = "select * from parts where partnumber = " . $sl_con->qstr($rst_items_in_order->fields['clean_pn'], get_magic_quotes_gpc());
			$rst_matching_parts = $sl_con->execute($sql_find_part_on_record);
			if ($rst_matching_parts && !$rst_matching_parts->EOF) {
				// yep
				$part_id = $rst_matching_parts->fields['id'];
				$rst_matching_parts->close();
			} else {
				// nope
				$inventory_accno_id = fetch_inventory_accno_id($sl_con);
				$income_accno_id = fetch_income_accno_id($sl_con);
				$expense_accno_id = fetch_expense_accno_id($sl_con);
				$unit = '';
                
                //save to database
                $rec = array();
                $rec['partnumber'] = $rst_items_in_order->fields['clean_pn'];
                $rec['unit'] = $unit;
                $rec['inventory_accno_id'] = $inventory_accno_id;
                $rec['income_accno_id'] = $income_accno_id;
                $rec['expense_accno_id'] = $expense_accno_id;
                
                $tbl = 'parts';
                $sql_insert_part = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
				$sl_con->execute($sql_insert_part);
                
				$sql_fetch_new_part_id = "select max(id) as max_partid from parts";
				$rst_new_part_id = $sl_con->execute($sql_fetch_new_part_id);
				$part_id = $rst_new_part_id->fields['max_partid'];
				$rst_new_part_id->close();
			}
			
			// we've got a part, now add the item to the order in SQL-Ledger
            $rec = array();
            $rec['trans_id'] = $trans_id;
            $rec['parts_id'] = $part_id;
            $rec['description'] = $rst_items_in_order->fields['clean_pn'];
            $rec['qty'] = $rst_items_in_order->fields['qty'];
            $rec['sellprice'] = $rst_items_in_order->fields['price'];

            $tbl = 'orderitems';
            $sql_add_item_to_order = $sl_con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
			$sl_con->execute($sql_add_item_to_order);
            
			$rst_items_in_order->movenext();
		}
		
		
	}
	
	return 1;
	
	$bs_con->close();
	$sl_con->close();
}

function add_sales_invoice_to_accounting_system() {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
	$sl_con->close();
}

function add_purchase_invoice_to_accounting_system() {
	
	global $sl_db_dbtype;
	global $sl_db_server;
	global $sl_db_username;
	global $sl_db_password;
	global $sl_db_dbname;
	
	$sl_con = &adonewconnection($sl_db_dbtype);
	$sl_con->connect($sl_db_server, $sl_db_username, $sl_db_password, $sl_db_dbname);
	
	$sl_con->close();
}

?>