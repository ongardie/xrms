<?php
/*
 *  WebCalendar setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.2 2004/07/22 15:51:40 gpowers Exp $
 */


function xrms_plugin_init_webcalendar() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline']['webcalendar'] = 'webcalendar';
}


function webcalendar() {

    global $http_site_root;

    echo "&nbsp;<a href='$http_site_root/plugins/webcalendar/index.php'>" . _("Calendar") . "</a>&nbsp;&bull;\n";
}

?>
