<?php
/*
 * Dial-Up Number Finder (dunfinder) XRMS Plugin
 * Copyright (c) 2004 Glenn Powers <glenn@net127.com> and
 * Copyright (c) 2004 The XRMS Project Team
 *
 *  init plugin into xrms
 *
 * $Id: setup.php,v 1.1 2004/08/03 17:28:59 gpowers Exp $
 */


function xrms_plugin_init_dunfinder() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['dunfinder'] = 'dunfinder';
}


function dunfinder() {
    global $http_site_root;
    echo "<br /><a href='$http_site_root/plugins/dunfinder/dunfinder.php'>DUN Finder</a>&nbsp;&bull;\n";
}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2004/08/03 17:28:59  gpowers
 * - This is a semi-standard Dialup Number Finder,
 *   modified to work inside of XRMS.
 *   - The table schema and insert.pl script are designed to work with
 *     POP lists from megapop.net (StarNet).
 *
 */
?>
