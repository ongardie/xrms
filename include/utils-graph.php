<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

function stacked_graph_values($clean_pn = '', $anr = 'n') {
	
	global $db_dbtype, $db_server, $db_username, $db_password, $db_dbname;
	
	$con = &adonewconnection($db_dbtype);
	$con->connect($db_server, $db_username, $db_password, $db_dbname);
	
	for ($i = 0; $i <= 12; $i++) {
	
		$start_date = date("Y-m-d", mktime(0,0,0, date('m') - $i, 1,date('Y')));
		$end_date = date("Y-m-d", mktime(0,0,0, date('m') - $i + 1, 1,date('Y')));
		
		$sql = "SELECT SUM(qty) AS total_quantity FROM market_information_items mii, market_information_item_types miit";
		$sql .= " WHERE mii.market_information_item_type_id = miit.market_information_item_type_id";
		$sql .= " AND  miit.anr = '" . $anr . "'";
		$sql .= " AND clean_pn = " . $con->qstr($clean_pn);
		$sql .= " AND entered_at BETWEEN " . $con->qstr($start_date) . " AND " . $con->qstr($end_date);
		$rst = $con->execute($sql);
		
		if ($rst) {
			$total_quantity = $rst->fields['total_quantity'];
			$rst->close();
		}
		
		if (!$total_quantity) {
			$total_quantity = 0;
		}
		
		$list_array[$i] = $total_quantity;
		
	}
	
	return implode(',', array_reverse($list_array));
	
}

function stacked_graph_scale($clean_pn = '') {
	
	global $db_dbtype, $db_server, $db_username, $db_password, $db_dbname;
	
	$con = &adonewconnection($db_dbtype);
	$con->connect($db_server, $db_username, $db_password, $db_dbname);
	
	for ($i = 0; $i <= 12; $i++) {
	
		$start_date = date("Y-m-d", mktime(0,0,0, date('m') - $i, 1,date('Y')));
		$end_date = date("Y-m-d", mktime(0,0,0, date('m') - $i + 1, 1,date('Y')));
		
		$sql = "SELECT SUM(qty) AS total_quantity FROM market_information_items mii, market_information_item_types miit";
		$sql .= " WHERE mii.market_information_item_type_id = miit.market_information_item_type_id";
		$sql .= " AND clean_pn = " . $con->qstr($clean_pn);
		$sql .= " AND entered_at BETWEEN " . $con->qstr($start_date) . " AND " . $con->qstr($end_date);
		$rst = $con->execute($sql);
		
		if ($rst) {
			$total_quantity = $rst->fields['total_quantity'];
			$rst->close();
		}
		
		if (!$total_quantity) {
			$total_quantity = 0;
		}
		
		$list_array[$i] = $total_quantity;
		
	}
	
	return max($list_array) / 10;
	
}

function price_graph_values($clean_pn = '', $anr = 'n') {
	
	global $db_dbtype, $db_server, $db_username, $db_password, $db_dbname;
	
	$con = &adonewconnection($db_dbtype);
	$con->connect($db_server, $db_username, $db_password, $db_dbname);
	
	for ($i = 0; $i <= 12; $i++) {
	
		$start_date = date("Y-m-d", mktime(0,0,0, date('m') - $i, 1,date('Y')));
		$end_date = date("Y-m-d", mktime(0,0,0, date('m') - $i + 1, 1,date('Y')));
		
		$sql = "SELECT AVG(price) AS avg_price FROM market_information_items mii, market_information_item_types miit";
		$sql .= " WHERE mii.market_information_item_type_id = miit.market_information_item_type_id";
		$sql .= " AND  miit.anr = '" . $anr . "'";
		$sql .= " AND clean_pn = " . $con->qstr($clean_pn);
		$sql .= " AND price > 0";
		$sql .= " AND entered_at BETWEEN " . $con->qstr($start_date) . " AND " . $con->qstr($end_date);
		$rst = $con->execute($sql);
		
		if ($rst) {
			$avg_price = $rst->fields['avg_price'];
			$rst->close();
		}
		
		if (!$avg_price) {
			$avg_price = 0;
		}
		
		$list_array[$i] = $avg_price;
		
	}
	
	return implode(',', array_reverse($list_array));
	
}

function price_graph_scale($clean_pn = '') {
	
	global $db_dbtype, $db_server, $db_username, $db_password, $db_dbname;
	
	$con = &adonewconnection($db_dbtype);
	$con->connect($db_server, $db_username, $db_password, $db_dbname);
	
	for ($i = 0; $i <= 12; $i++) {
	
		$start_date = date("Y-m-d", mktime(0,0,0, date('m') - $i, 1,date('Y')));
		$end_date = date("Y-m-d", mktime(0,0,0, date('m') - $i + 1, 1,date('Y')));
		
		$sql = "SELECT AVG(price) AS avg_price FROM market_information_items mii, market_information_item_types miit";
		$sql .= " WHERE mii.market_information_item_type_id = miit.market_information_item_type_id";
		$sql .= " AND clean_pn = " . $con->qstr($clean_pn);
		$sql .= " AND entered_at BETWEEN " . $con->qstr($start_date) . " AND " . $con->qstr($end_date);
		$rst = $con->execute($sql);
		
		if ($rst) {
			$avg_price = $rst->fields['avg_price'];
			$rst->close();
		}
		
		if (!$avg_price) {
			$avg_price = 0;
		}
		
		$list_array[$i] = $avg_price;
		
	}
	
	return max($list_array) / 10;
	
}

function list_of_months() {
	
	for ($i = 0; $i <= 12; $i++) {
		$monthname = date("M", mktime(0,0,0, date('m') - $i, 1,date('Y')));
		$array_of_months[$i] = "'" . $monthname . "'";
	}
	
	return implode(',', array_reverse($array_of_months));
	
}

?>
