<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.2 2006/03/13 07:49:06 vanmer Exp $
 */


function xrms_plugin_init_apb() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline_nav_items']['apb'] = 'apb';
}


function apb() {
    global $nav_items;
    $nav_items['apb']=array('href'=>'/plugins/apb/index.php','title'=>_("Bookmarks"));
}

/**
 * $Log: setup.php,v $
 * Revision 1.2  2006/03/13 07:49:06  vanmer
 * - changed to reflect new method of registering navigational menu items
 *
 * Revision 1.1  2004/08/04 15:28:24  gpowers
 * - Active PHP Bookmarks (APB) XRMS Plugin
 *
 */
?>
