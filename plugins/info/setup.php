<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.4 2004/11/12 06:36:36 gpowers Exp $
 */


function xrms_plugin_init_info () {

    global $xrms_plugin_hooks;

    $xrms_plugin_hooks['menuline']['info'] = 'menu';
    $xrms_plugin_hooks['company_sidebar_bottom']['info']
      = 'company_sidebar_bottom';
    $xrms_plugin_hooks['contact_sidebar_top']['info']
      = 'contact_sidebar_top';
    $xrms_plugin_hooks['contact_sidebar_top']['info']
      = 'contact_sidebar_top';
    $xrms_plugin_hooks['private_sidebar_bottom']['info']
      = 'private_sidebar_bottom';
    $xrms_plugin_hooks['info_sidebar_bottom']['info']
      = 'info_sidebar_bottom';
    $xrms_plugin_hooks['plugin_admin']['info'] = 'info_setup';

}

function display_on_menu () {
    global $display_on;
    $menu = "<select name=\"display_on\">";
    $menu .= "<option ";
    if ($display_on == "company_sidebar_bottom") $menu .= "SELECTED ";
    $menu .= " value=\"company_sidebar_bottom\">company_sidebar_bottom</option>";
    $menu .= "<option ";
    if ($display_on == 'contact_sidebar_top') $menu .= "SELECTED ";
    $menu .= "value=\"contact_sidebar_top\">contact_sidebar_top</option>";
    $menu .= "<option ";
    if ($display_on == "private_sidebar_bottom") $menu .= "SELECTED ";
    $menu .= "value=\"private_sidebar_bottom\">private_sidebar_bottom</option>";
    // $menu .= "<option value=\"\"></option>";
    $menu .= "</select>";
    return $menu;
}

function info_setup() {
    global $http_site_root;
    echo "<tr><td class=widget_content>\n<a href='$http_site_root/plugins/info/admin/some.php'>Manage Info Types</a>\n</td>\n</tr>\n";
}

function menu () {

    global $http_site_root;

    require("info.inc");

    //Add link to upper menu
    //echo "&nbsp;<a
    //href='$http_site_root/plugins/info/info.php'>".$server_info_heading."</a>&nbsp;&bull;\n";
}

function company_sidebar_bottom () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "company_sidebar_bottom";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function contact_sidebar_top () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "contact_sidebar_top";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function contact_sidebar_bottom () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "contact_sidebar_bottom";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function private_sidebar_bottom () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "private_sidebar_bottom";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function info_sidebar_bottom () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "all";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

?>
