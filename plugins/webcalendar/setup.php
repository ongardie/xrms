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
 * $Id: setup.php,v 1.1 2004/05/06 14:00:24 gpowers Exp $
 */


function xrms_plugin_init_webcalendar() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['webcalendar'] = 'webcalendar';
}


function webcalendar() {

    global $http_site_root;

    //Add Demo link to upper menu
    echo "&nbsp;<a href='$http_site_root/plugins/webcalendar/index.php'>Calendar</a>&nbsp;&bull;\n";
}

?>
