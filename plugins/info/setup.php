<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.13 2005/03/07 18:36:00 vanmer Exp $
 */


function xrms_plugin_init_info () {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['company_accounting']['info']
      = 'company_accounting';
    $xrms_plugin_hooks['company_sidebar_bottom']['info']
      = 'company_sidebar_bottom';
    $xrms_plugin_hooks['contact_sidebar_top']['info']
      = 'contact_sidebar_top';
    $xrms_plugin_hooks['contact_sidebar_bottom']['info']
      = 'contact_sidebar_bottom';
    $xrms_plugin_hooks['private_sidebar_bottom']['info']
      = 'private_sidebar_bottom';
    $xrms_plugin_hooks['info_sidebar_bottom']['info']
      = 'info_sidebar_bottom';
    $xrms_plugin_hooks['company_content_bottom']['info']
      = 'company_content_bottom';
    $xrms_plugin_hooks['plugin_admin']['info'] = 'info_setup';
    $xrms_plugin_hooks['xrms_install']['info'] = 'info_install';
    $xrms_plugin_hooks['xrms_update']['info'] = 'info_update';
}

function display_on_menu () {
    global $display_on;
    $menu = "<select name=\"display_on\">";
    $menu .= "<option ";
    if ($display_on == "company_sidebar_bottom") $menu .= "SELECTED ";
    $menu .= " value=\"company_sidebar_bottom\">Company Sidebar Bottom</option>";
    $menu .= "<option ";
    if ($display_on == 'contact_sidebar_top') $menu .= "SELECTED ";
    $menu .= "value=\"contact_sidebar_top\">Contact Sidebar Top</option>";
    $menu .= "<option ";
    if ($display_on == 'contact_sidebar_bottom') $menu .= "SELECTED ";
    $menu .= "value=\"contact_sidebar_bottom\">Contact Sidebar Bottom</option>";
    $menu .= "<option ";
    if ($display_on == "private_sidebar_bottom") $menu .= "SELECTED ";
    $menu .= "value=\"private_sidebar_bottom\">Private Sidebar Bottom</option>";
    $menu .= "<option ";
    if ($display_on == "company_accounting") $menu .= "SELECTED ";
    $menu .= "value=\"company_accounting\">Company Accounting</option>";
    $menu .= "<option ";
    if ($display_on == "company_content_bottom") $menu .= "SELECTED ";
    $menu .= "value=\"company_content_bottom\">Company Content Bottom</option>";
    $menu .= "</select>";
    return $menu;
}

function info_setup() {
    global $http_site_root;
    echo "<tr><td class=widget_content>\n<a href='$http_site_root/plugins/info/admin/some.php'>Manage Info Types</a>\n</td>\n</tr>\n";
}

function info_install($con) {
    global $xrms_file_root;
    $tables=$con->MetaTables('TABLES');
    if (!in_array('info',$tables)) {
        execute_batch_sql_file($con, $xrms_file_root.'/plugins/info/info.sql');
    }
}

function info_update($con) {
    $update_sql = "ALTER TABLE `info_element_definitions` CHANGE `element_type` `element_type` ENUM( 'text', 'select', 'radio', 'checkbox', 'textarea', 'name' ) DEFAULT 'text' NOT NULL ";
    $rst=$con->execute($update_sql);
    if (!$rst) db_error_handler($con, $update_sql);
    
    $update_sql = "UPDATE `info_element_definitions` SET element_type = 'name' WHERE element_label = 'Name'";
    $rst=$con->execute($update_sql);
    if (!$rst) db_error_handler($con, $update_sql);
    
}

function company_content_bottom ($_sidebar) {
    global $xrms_file_root, $http_site_root, $con, $company_id, $division_id, $info_list, $display_on;
    $return_url="/companies/one.php?company_id=$company_id&division_id=$division_id";
    include("info.inc");
    ob_start();
    $display_on = "company_content_bottom";
    include("$xrms_file_root/plugins/info/sidebar.php");
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $_sidebar.$sidebar_string;
}

function company_accounting () {
    global $xrms_file_root, $http_site_root, $con, $company_id, $division_id, $info_list, $display_on;
    $return_url="/companies/one.php?company_id=$company_id&division_id=$division_id";
    include("info.inc");
    ob_start();
    $display_on = "company_accounting";
    include("$xrms_file_root/plugins/info/sidebar.php");
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function company_sidebar_bottom () {
    global $xrms_file_root, $http_site_root, $con, $company_id, $division_id, $info_list, $display_on;
    $return_url="/companies/one.php?company_id=$company_id&division_id=$division_id";
    include("info.inc");
    ob_start();
    $display_on = "company_sidebar_bottom";
    include("$xrms_file_root/plugins/info/sidebar.php");
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function contact_sidebar_top () {
    global $xrms_file_root, $http_site_root, $con, $contact_id, $company_id, $division_id, $info_list, $display_on;
    $return_url="/contacts/one.php?contact_id=$contact_id";
    include("info.inc");
    ob_start();
    $display_on = "contact_sidebar_top";
    include("$xrms_file_root/plugins/info/sidebar.php");
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function contact_sidebar_bottom () {
    global $xrms_file_root, $http_site_root, $con, $contact_id, $company_id, $division_id, $info_list, $display_on;
    $return_url="/contacts/one.php?contact_id=$contact_id";
    include("info.inc");
    ob_start();
    $display_on = "contact_sidebar_bottom";
    include("$xrms_file_root/plugins/info/sidebar.php");
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function private_sidebar_bottom () {
    global $xrms_file_root, $http_site_root, $con, $company_id, $info_list, $display_on;
    $return_url="/private/home.php";
    include("info.inc");
    ob_start();
    $display_on = "private_sidebar_bottom";
    include("$xrms_file_root/plugins/info/sidebar.php");
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function info_sidebar_bottom () {
    global $xrms_file_root, $http_site_root, $con, $company_id, $info_list, $display_on;
    include("info.inc");
    ob_start();
    $display_on = "all";
    include("$xrms_file_root/plugins/info/sidebar.php");
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

?>
