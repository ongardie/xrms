<?php
/*
*
* Site Weather XRMS Plugin v0.1
* uses wx200 from:
* http://wx200d.sourceforge.net/
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the Open Software License v. 2.0
*
*/

function xrms_plugin_init_sitewx() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['sitewx'] = 'sitewx';
}

function sitewx() {
    global $http_site_root;
    echo "&nbsp;<a href='$http_site_root/plugins/sitewx/sitewx.php'>Site Weather</a>&nbsp;&bull;\n";
}

?>
