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
 * $Id: setup.php,v 1.2 2004/07/22 13:12:30 gpowers Exp $
 */


function xrms_plugin_init_autostatus() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['autostatus'] = 'autostatus';
    //$xrms_plugin_hooks['opportunity_detail']['autostatus'] = 'need_function';
}


function autostatus() {

    global $http_site_root;

    //Add Demo link to upper menu
    echo "&nbsp;<a href='$http_site_root/plugins/autostatus/autostatus.php'>" . _("Server Status") . "</a>&nbsp;&bull;\n";
}

/**
 * $Log: setup.php,v $
 * Revision 1.2  2004/07/22 13:12:30  gpowers
 * - Added i18n support to menuline title
 *   - Removed unrelated phpdoc notes
 *
 * Revision 1.1  2004/05/06 14:30:14  gpowers
 * This is a simple plugin for including an Autostatus page in XRMS.
 *
 */
?>
