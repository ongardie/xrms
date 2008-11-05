<?php
/*
*
* xing plugin
* by Stefan Pampel <stefan.pampel@polyformal.de> 
* polyformal ( http://www.polyformal.de/ )
* (c) 2006 (GNU GPL - see ../../COPYING)
* 
* This plugin allows to query a contact by first_names+lastname
* against the 'business-platform' xing (former known as openBC) for entries.
* Based on vcard plugin
*/

function xrms_plugin_init_xing() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['one_contact_buttons']['xing'] = 'xing';
}

function xing() {
        global $con, $contact_id, $contact_buttons, $http_site_root;
	    $button= " <input class=button type=button value=\"" .  _("query xing") ."\" onclick=\"javascript: 
window.open('" .$http_site_root. "/plugins/xing/xing.php?contact_id=". $contact_id ."','_blank');\">";
return $contact_buttons.=$button;
}
?>
