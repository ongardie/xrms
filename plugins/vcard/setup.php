<?php
/*
*
* Vcard plugin
*
*/

function xrms_plugin_init_vcard() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['one_contact_buttons']['vcard'] = 'vcard';
}

function vcard() {
    global $http_site_root;
    global $custom1;
    global $custom2;
    global $contact_id;
    return " <input class=button type=button value=\"" .  _("Vcard") ."\" onclick=\"javascript: location.href='vcard.php?contact_id=" . $contact_id . "';\">";

}

?>
