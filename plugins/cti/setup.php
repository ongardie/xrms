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
//    $xrms_plugin_hooks['one_contact_buttons']['cti'] = 'asteriskdial';
    $GLOBALS["use_dial_link"]="y";
//    $xrms_plugin_hooks['menuline']['cti'] = 'voicemail';

    function phone_link_to_display($phone, $phone_to_display) {
        global $http_site_root;
        global $company_id;
        global $contact_id;
        $url_phone = urlencode($phone);
        return "<a href=\"" . $http_site_root . "/plugins/cti/asteriskdial.php?company_id=" . $company_id . "&contact_id=" . $contact_id . "&phone=" . $url_phone . "\">" . $phone_to_display . "</a>";
    }
}

function asteriskdial() {
    global $http_site_root;
    global $contact_id;
    global $company_id;
    echo "<input class=button type=button value=\"" . _("Dial") . "\" onclick=\"javascript: location.href='" . $http_site_root . "/plugins/cti/asteriskdial.php?company_id=" . $company_id . "&contact_id=" . $contact_id . "&phone=" . $work_phone . "';\">";
}

function voicemail() {
    global $http_site_root;
    echo "&nbsp;<a href='$http_site_root/plugins/cti/voicemail.php'>" . _("Voice Mail") . "</a>&nbsp;&bull;\n";
}

?>
