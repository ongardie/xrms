<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 Glenn Powers <glenn@net127.com>
 * Licensed under the GNU GPL v2
 *
 * $Id: setup.php,v 1.3 2006/03/13 07:49:14 vanmer Exp $
 */


function xrms_plugin_init_tavi() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline_nav_items']['tavi'] = 'tavi';
}


function tavi() {

    global $nav_items;
    //Add link to upper menu
    $nav_items['tavi']=array('href'=>'/plugins/tavi/index.php','title'=>_("Wiki"), 'object'=>'wiki');
}

/**
 * $Log: setup.php,v $
 * Revision 1.3  2006/03/13 07:49:14  vanmer
 * - changed to reflect new method of registering navigational menu items
 *
 * Revision 1.2  2005/04/13 14:24:03  gpowers
 * - added ACL control
 *
 * Revision 1.1  2005/04/12 20:45:10  gpowers
 * - Wiki Plugin
 *   - Uses modified Wikki Tikki Tavi v0.26
 *   - See: http://tavi.sourceforge.net
 *
 *
 */
?>
