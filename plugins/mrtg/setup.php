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
 * $Id: setup.php,v 1.3 2006/03/13 07:49:12 vanmer Exp $
 */


function xrms_plugin_init_mrtg() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline_nav_items']['mrtg'] = 'mrtg';
}


function mrtg() {

    global $nav_items;

    //Add link to upper menu
    $nav_items['mrtg']=array('href'=>'/plugins/mrtg/mrtg.php','title'=>_("MRTG"));
}

/**
 * $Log: setup.php,v $
 * Revision 1.3  2006/03/13 07:49:12  vanmer
 * - changed to reflect new method of registering navigational menu items
 *
 * Revision 1.2  2004/07/22 12:57:27  gpowers
 * - Removed references to 'demo' plugin.
 *
 */
?>
