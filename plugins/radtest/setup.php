<?php
/*
*
* Radius Test (radtest) XRMS Plugin v0.1
* uses radtest from:
* http://www.freeradius.org/
*
* This plugin assumes username is stored in contacts.custom1
* and the password is stored in contacts.custom2
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the Open Software License v. 2.0
*
*/

function xrms_plugin_init_radtest() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['one_contact_buttons']['radtest'] = 'radtest';
}

function radtest() {
    global $http_site_root;
    global $custom1;
    global $custom2;
    global $contact_id;
    return " <input class=button type=button value=\"" . _("Radius Test") . "\" onclick=\"javascript: location.href='" . $http_site_root . "/plugins/radtest/radtest.php?username=" . $custom1 . "&password=" . $custom2 . "&contact_id=" . $contact_id . "';\">";
}

?>
