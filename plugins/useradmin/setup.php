<?php
/*
*
* User Administration XRMS Plugin v0.1
*
* copyright 2004 Glenn Powers <glenn@user127.com>
* Licensed Under the Open Software License v. 2.0
*
*/

function xrms_plugin_init_useradmin() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['menuline_nav_items']['useradmin'] = 'useradmin';
}

function useradmin() {

    global $nav_items;

    $nav_items['useradmin']=array('href'=>'/plugins/useradmin/useradmin.php', 'title'=>_("User Admin"));
}

function onlineusers() {
    global $http_site_root;
    echo "&nbsp;<a href='$http_site_root/plugins/useradmin/onlineusers.php'>Online Users</a>&nbsp;&bull;\n";
}

//function radtest() {
//    global $http_site_root;
//    global $custom1;
//    global $custom2;
//    global $contact_id;
//    echo "<input class=button type=button value=\"Radius Test\" onclick=\"javascript: location.href='" . $http_site_root . "/plugins/useradmin/radtest.php?username=" . $custom1 . "&password=" . $custom2 . "&contact_id=" . $contact_id; . "';\">";
// }

?>