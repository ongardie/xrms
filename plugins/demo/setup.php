<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 *  init plugin into xrms
 *
 * @example Create a function called
 *      xrms_plugin_init_pluginname
 *      where pluginname is the name of your pluign directory
 *      inside this function, you will register all the hooks
 *      that you wish your plugin to be called by
 *
 * You should also put the called functions in your setup,php file
 * Please take care to keep this file as small as possible, as it
 * is included on every page load.  Place your actualy functionality
 * in another file.  It will improve the performance of the entire
 * system.
 *
 * $Id: setup.php,v 1.2 2004/03/20 22:43:02 braverock Exp $
 */


function xrms_plugin_init_demo() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['demo'] = 'demo';
}


function demo() {
    //Add Demo link to upper menu
    echo "&nbsp;<a href='$http_site_root/plugins/demo/demo.php'>Demo</a>&nbsp;&bull;\n";
}

/**
 * $Log: setup.php,v $
 * Revision 1.2  2004/03/20 22:43:02  braverock
 * - changed to use $http_site_root
 *
 * Revision 1.1  2004/03/20 20:09:35  braverock
 * Initial Revision of Demo plugin to demonstrate using hooks
 *
 */
?>