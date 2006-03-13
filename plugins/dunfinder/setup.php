<?php
/*
 * Dial-Up Number Finder (dunfinder) XRMS Plugin
 * Copyright (c) 2004 Glenn Powers <glenn@net127.com> and
 * Copyright (c) 2004 The XRMS Project Team
 *
 *  init plugin into xrms
 *
 * $Id: setup.php,v 1.2 2006/03/13 07:49:11 vanmer Exp $
 */


function xrms_plugin_init_dunfinder() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline_nav_items']['dunfinder'] = 'dunfinder';
}


function dunfinder() {
    global $nav_items;
    $nav_items['dunfinder']=array('href'=>'/plugins/dunfinder/dunfinder.php', 'title'=>_("DUN Finder"));
}

/**
 * $Log: setup.php,v $
 * Revision 1.2  2006/03/13 07:49:11  vanmer
 * - changed to reflect new method of registering navigational menu items
 *
 * Revision 1.1  2004/08/03 17:28:59  gpowers
 * - This is a semi-standard Dialup Number Finder,
 *   modified to work inside of XRMS.
 *   - The table schema and insert.pl script are designed to work with
 *     POP lists from megapop.net (StarNet).
 *
 */
?>
