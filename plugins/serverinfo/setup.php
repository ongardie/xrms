<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.1 2004/07/06 19:57:02 gpowers Exp $
 */


function xrms_plugin_init_serverinfo () {

    global $xrms_plugin_hooks;

    $xrms_plugin_hooks['menuline']['serverinfo'] = 'menu';
    $xrms_plugin_hooks['company_sidebar_bottom']['serverinfo'] = 'sidebar';
}

function menu () {

    global $http_site_root;

    require("serverinfo.inc");
    
    //Add link to upper menu
    echo "&nbsp;<a
    href='$http_site_root/plugins/serverinfo/serverinfo.php'>".$server_info_heading."</a>&nbsp;&bull;\n";
}

function sidebar () {

    global $xrms_file_root, $http_site_root, $con, $company_id, $server_list;
    
    require_once("serverinfo.inc");
    
    ob_start();
    require_once "$xrms_file_root/plugins/serverinfo/sidebar.php";
    $sidebar_string = ob_get_contents();
    ob_end_clean();
    return $sidebar_string;
}

?>
