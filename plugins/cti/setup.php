<?php
/*
*
* CTI XRMS Plugin v0.1
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the Open Software License v. 2.0
*
*/

function xrms_plugin_init_cti() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['one_contact_buttons']['cti'] = 'asteriskdial';
}

function asteriskdial() {
    global $http_site_root;
    global $work_phone;
    global $contact_id;
    echo "<input class=button type=button value=\"" . _("Dial") . "\" onclick=\"javascript: location.href='" . $http_site_root . "/plugins/cti/asteriskdial.php?contact_id=" . $contact_id . "&phone=" . $work_phone . "';\">";
}

?>
