<?php
/*
*
* Mapquest XRMS Plugin v0.1
*
* copyright 2004 Glenn Powers <glenn@net127.com>
*
*/

function xrms_plugin_init_mapquest() {
    global $xrms_plugin_hooks;
    $GLOBALS["use_mapquest_link"]="y";
}

function mapquest($line1, $city, $province, $iso_code2, $address_to_display) {
    $url_line1 = urlencode ($line1);
    $url_city = urlencode ($city);
    $url_province = urlencode ($province);
    $url_country = urlencode ($iso_code2);

    return "<a href=\"http://www.mapquest.com/maps/map.adp?country=" . $url_country ."&address=" . $url_line1 . "&city=" . $url_city . "&state=" . $url_province . "\">" . $address_to_display . "</a>";
//    return "COUNTRY: " . $url_country . "<BR>city: " . $url_city  . "<BR>address_id: " . $address_id;
}

?>
