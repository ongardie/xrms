<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.3 2004/11/10 07:29:33 gpowers Exp $
 */


function xrms_plugin_init_info () {

    global $xrms_plugin_hooks;

    $xrms_plugin_hooks['menuline']['info'] = 'menu';
    $xrms_plugin_hooks['company_sidebar_bottom']['info'] = 'company_sidebar';
    $xrms_plugin_hooks['contact_sidebar_bottom']['info'] = 'contact_sidebar';
    $xrms_plugin_hooks['private_sidebar_bottom']['info'] = 'private_sidebar';
    $xrms_plugin_hooks['info_sidebar_bottom']['info'] = 'info_sidebar';
    $xrms_plugin_hooks['plugin_admin']['info'] = 'info_setup';

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

function company_sidebar () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "company_sidebar_bottom";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function contact_sidebar () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "contact_sidebar";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function private_sidebar () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list, $display_on;

    require_once("info.inc");

    ob_start();
    $display_on = "private_sidebar";
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

function info_sidebar () {

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
