<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.1 2004/07/14 16:50:16 gpowers Exp $
 */


function xrms_plugin_init_info () {

    global $xrms_plugin_hooks;

    $xrms_plugin_hooks['menuline']['info'] = 'menu';
    $xrms_plugin_hooks['company_sidebar_bottom']['info'] = 'sidebar';
    $xrms_plugin_hooks['contact_sidebar_bottom']['info'] = 'sidebar';
}

function menu () {

    global $http_site_root;

    require("info.inc");

    //Add link to upper menu
    //echo "&nbsp;<a
    //href='$http_site_root/plugins/info/info.php'>".$server_info_heading."</a>&nbsp;&bull;\n";
}

function sidebar () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list;

    require_once("info.inc");

    ob_start();
    require_once "$xrms_file_root/plugins/info/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

?>
