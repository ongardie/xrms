<?php
/*
 *  setup.php
 *
 * Copyright (c) 2007 Glenn Powers <glenn@net127.com>
 *
 * $Id: setup.php,v 1.1 2007/12/10 18:25:25 gpowers Exp $
 */


function xrms_plugin_init_time_register () {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['loginbar']['time_register'] = 'clock';
}

function clock () {
    echo date('r');
}

?>