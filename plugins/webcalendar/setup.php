<?php
/*
 *  WebCalendar setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.3 2004/07/22 16:37:18 gpowers Exp $
 */


function xrms_plugin_init_webcalendar() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['webcalendar'] = 'webcalendar';
    $xrms_plugin_hooks['home_docs']['webcalendar_docs'] = 'webcalendar_docs';
}


function webcalendar() {

    global $http_site_root;

    echo "&nbsp;<a href='$http_site_root/plugins/webcalendar/src/index.php'>" . _("Calendar") . "</a>&nbsp;&bull;\n";
}

function webcalendar_docs() {
echo '
            <tr>
                <td class=widget_label><a href="../plugins/webcalendar/src/docs/WebCalendar-SysAdmin.html">' . _("WebCalendar SysAdmin Manual") . '</a></td>
            </tr>
';
}

?>
