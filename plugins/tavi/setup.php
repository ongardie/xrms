<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 Glenn Powers <glenn@net127.com>
 * Licensed under the GNU GPL v2
 *
 * $Id: setup.php,v 1.1 2005/04/12 20:45:10 gpowers Exp $
 */


function xrms_plugin_init_tavi() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['tavi'] = 'tavi';
}


function tavi() {

    global $http_site_root;

    //Add link to upper menu
    echo "&nbsp;<a href='$http_site_root/plugins/tavi/index.php'>" . _("Wiki") . "</a>&nbsp;&bull;\n";
}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2005/04/12 20:45:10  gpowers
 * - Wiki Plugin
 *   - Uses modified Wikki Tikki Tavi v0.26
 *   - See: http://tavi.sourceforge.net
 *
 *
 */
?>
