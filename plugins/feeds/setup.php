<?php
/*
 *  setup.php
 *
 * Copyright (c) 2007 Glenn Powers <glenn@net127.com>
 *
 * $Id: setup.php,v 1.1 2007/12/10 18:06:44 gpowers Exp $
 */


function xrms_plugin_init_feeds () {
    global $xrms_plugin_hooks;
    // $xrms_plugin_hooks['contact_sidebar_top']['feeds'] = 'sidebar';
}

function sidebar () {
    global $con, $contact_id;

    return $sidebar_string;
}

?>
