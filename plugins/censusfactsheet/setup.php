<?php
/*
*
* Census Fact Sheet (censusfactsheet) XRMS Plugin v0.1
*
* copyright 2004 Glenn Powers <glenn@net127.com>
*
*/

function xrms_plugin_init_censusfactsheet() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['one_company_buttons']['censusfactsheet'] = 'censusfactsheet';
    $xrms_plugin_hooks['one_contact_buttons']['censusfactsheet'] = 'censusfactsheet';
}

function censusfactsheet() {
    global $line1;
    global $city;
    global $postal_code;
    
    $url_company_name = urlencode($company_name);
    $url = "http://factfinder.census.gov/servlet/SAFFFacts?_event=Search&geo_id=01000US&_geoContext=&_street=" . urlencode($line1) . "&_county=&_cityTown=" . urlencode($city) . "&_state=&_zip=" . urlencode($postal_code) . "&_lang=en&_sse=on";
    if ($postal_code) {
        echo " <input class=button type=button value=\"" . _("Census Fact Sheet") . "\" onclick=\"javascript: location.href='" . $url . "';\">";
    }
}

?>
