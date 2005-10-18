<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.2 2005/10/18 21:34:00 vanmer Exp $
 */


function xrms_plugin_init_custom_fields () {
	global $xrms_plugin_hooks;
	$xrms_plugin_hooks['company_accounting_inline_display']['custom_fields']
	  = 'company_accounting_inline_display';
	$xrms_plugin_hooks['company_accounting_inline_edit']['custom_fields']
	  = 'company_accounting_inline_edit';
	$xrms_plugin_hooks['company_edit_2']['custom_fields']
	  = 'company_edit_2';
	$xrms_plugin_hooks['contact_accounting_inline_display']['custom_fields']
	  = 'contact_accounting_inline_display';
	$xrms_plugin_hooks['contact_accounting_inline_edit']['custom_fields']
	  = 'contact_accounting_inline_edit';
	$xrms_plugin_hooks['contact_edit_2']['custom_fields']
	  = 'contact_edit_2';
	$xrms_plugin_hooks['company_sidebar_bottom']['custom_fields']
	  = 'company_sidebar_bottom';
	$xrms_plugin_hooks['contact_sidebar_top']['custom_fields']
	  = 'contact_sidebar_top';
	$xrms_plugin_hooks['contact_sidebar_bottom']['custom_fields']
	  = 'contact_sidebar_bottom';
	$xrms_plugin_hooks['private_sidebar_bottom']['custom_fields']
	  = 'private_sidebar_bottom';
	$xrms_plugin_hooks['custom_fields_sidebar_bottom']['custom_fields']
	  = 'custom_fields_sidebar_bottom';
	$xrms_plugin_hooks['company_content_bottom']['custom_fields']
	  = 'company_content_bottom';
	$xrms_plugin_hooks['plugin_admin']['custom_fields'] = 'custom_fields_setup';
	$xrms_plugin_hooks['xrms_install']['custom_fields'] = 'custom_fields_install';
	$xrms_plugin_hooks['xrms_update']['custom_fields'] = 'custom_fields_update';
}

function custom_fields_setup () {

	global $http_site_root;
	
	echo "<tr><td class=widget_content>\n<a 
		href='$http_site_root/plugins/custom_fields/admin/some.php'>
		Manage Custom Fields</a>\n</td>\n</tr>\n";
}

function custom_fields_install ($con) {
	global $xrms_file_root;
	$tables=$con->MetaTables('TABLES');
	if (!in_array('cf_fields',$tables)) {
		execute_batch_sql_file($con, $xrms_file_root.'/plugins/custom_fields/custom_fields.sql');
	}
}

#function custom_fields_update ($con) {
#	$update_sql = "ALTER TABLE `info_element_definitions` CHANGE `element_type` `element_type` ENUM( 'text', 'select', 'radio', 'checkbox', 'textarea', 'name' ) DEFAULT 'text' NOT NULL ";
#	$rst=$con->execute($update_sql);
#	if (!$rst) db_error_handler($con, $update_sql);
#	
#	$update_sql = "UPDATE `info_element_definitions` SET element_type = 'name' WHERE element_label = 'Name'";
#	$rst=$con->execute($update_sql);
#	if (!$rst) db_error_handler($con, $update_sql);
#	
#}

function company_accounting_inline_display () {
	global $xrms_file_root, $company_id, $division_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	return get_display("company_accounting", $company_id, "", $division_id);
}

function company_accounting_inline_edit () {
	global $xrms_file_root, $company_id, $division_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	return get_inline_edit("company_accounting", $company_id, $division_id);
}

function company_edit_2 () {
	global $xrms_file_root, $company_id, $division_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	do_inline_edit_save("company_accounting", $company_id, $division_id);
}

function company_content_bottom ($_sidebar) {
	global $xrms_file_root, $company_id, $division_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	$return_url = urlencode($_SERVER['PHP_SELF']."?company_id=$company_id");
	return get_display("company_content_bottom", $company_id, $return_url, $division_id);
}

function company_sidebar_bottom () {
	global $xrms_file_root, $company_id, $division_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	$return_url = urlencode($_SERVER['PHP_SELF']."?company_id=$company_id");
	return get_display("company_sidebar_bottom", $company_id, $return_url, $division_id);
}

function contact_accounting_inline_display () {
	global $xrms_file_root, $contact_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	return get_display("contact_accounting", $contact_id, "");
}

function contact_accounting_inline_edit () {
	global $xrms_file_root, $contact_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	return get_inline_edit("contact_accounting", $contact_id);
}

function contact_edit_2 () {
	global $xrms_file_root, $contact_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	do_inline_edit_save("contact_accounting", $contact_id);
}

function contact_sidebar_bottom () {
	global $xrms_file_root, $contact_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	$return_url = urlencode($_SERVER['PHP_SELF']."?contact_id=$contact_id");
	return get_display("contact_sidebar_bottom", $contact_id, $return_url);
}

function contact_sidebar_top () {
	global $xrms_file_root, $contact_id;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	$return_url = urlencode($_SERVER['PHP_SELF']."?contact_id=$contact_id");
	return get_display("contact_sidebar_top", $contact_id, $return_url);
}

function private_sidebar_bottom () {
	global $xrms_file_root, $userid;
	include_once("$xrms_file_root/plugins/custom_fields/display_functions.php");
	$return_url = urlencode($_SERVER['PHP_SELF']);
	return get_display("private_sidebar_bottom", $_SESSION['session_user_id'],
			$return_url);
}

?>
