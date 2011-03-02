<?php

// copyright 2007 Glenn Powers <glenn@net127.com>

function xrms_plugin_init_phpsysinfo() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['system_monitoring']['phpsysinfo'] = 'sysinfo';
}

function sysinfo() {
    global $http_site_root;
    echo "
<table class=widget cellspacing=1 width=\"100%\">
    <tr>
        <td class=widget_header>" . _("System Information") . "</td>
    </tr>
    <tr>
        <td>
            <a href=\"" . $http_site_root . "/plugins/phpsysinfo/\">" . _("System Info") . "</a>       
        </td>
    </tr>
    <tr>
        <td><a href=\"" . $http_site_root . "/plugins/phpsysinfo/phpinfo.php\">" . _("PHP Info") . "</a></td>
    </tr>
</table>
";
}

?>