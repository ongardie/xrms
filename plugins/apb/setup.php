<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.1 2004/08/04 15:28:24 gpowers Exp $
 */


function xrms_plugin_init_apb() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['apb'] = 'apb';
}


function apb() {
    global $http_site_root;
    echo "&nbsp;<a href='$http_site_root/plugins/apb/index.php'>Bookmarks</a>&nbsp;&bull;\n";
}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2004/08/04 15:28:24  gpowers
 * - Active PHP Bookmarks (APB) XRMS Plugin
 *
 */
?>
