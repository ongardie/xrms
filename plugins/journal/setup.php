<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 Glenn Powers
 *
 * $Id: setup.php,v 1.1 2004/11/09 03:41:19 gpowers Exp $
 */


function xrms_plugin_init_journal() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['journal'] = 'journal';
    $xrms_plugin_hooks['plugin_admin']['journal'] = 'setup';
}


function journal() {
    global $http_site_root;
    echo "&nbsp;<a href='$http_site_root/plugins/journal/index.php'>Journal</a>&nbsp;&bull;\n";
}

function setup() {
    global $http_site_root;
    echo "<td class=widget_content>\n<a href='$http_site_root/plugins/journal/index.php?wl_mode=edit+setup'>Journal Settings</a>\n</td>\n";
}

/**
/**
 * $Log: setup.php,v $
 * Revision 1.1  2004/11/09 03:41:19  gpowers
 * - Journal Plugin v0.1
 *
 */
?>
