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
 * $Id: setup.php,v 1.7 2006/03/13 07:49:09 vanmer Exp $
 */


function xrms_plugin_init_demo() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline_nav_items']['demo'] = 'demo';
    //$xrms_plugin_hooks['opportunity_detail']['demo'] = 'need_function';
}


function demo() {

    global $nav_items;

    //Add Demo link to upper menu
    $nav_items['demo']=array('href'=>'/plugins/demo/demo.php', 'title'=>_("Demo"));
}

/**
 * $Log: setup.php,v $
 * Revision 1.7  2006/03/13 07:49:09  vanmer
 * - changed to reflect new method of registering navigational menu items
 *
 * Revision 1.6  2004/05/06 15:12:01  gpowers
 * Note to self: do not copies CVS/ directories!
 *
 * Revision 1.4  2004/03/29 21:14:40  maulani
 * - Add opportunity_detail hook as a commented-out example
 *
 * Revision 1.3  2004/03/20 22:49:42  braverock
 * - need global $http_site_root in some cases
 *
 * Revision 1.2  2004/03/20 22:43:02  braverock
 * - changed to use $http_site_root
 *
 * Revision 1.1  2004/03/20 20:09:35  braverock
 * Initial Revision of Demo plugin to demonstrate using hooks
 *
 */
?>