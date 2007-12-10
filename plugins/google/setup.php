<?php
/*
*
* Google XRMS Plugin
*
* copyright 2004-2007 Glenn Powers <glenn@net127.com>
*
*/

function xrms_plugin_init_google() {
    global $xrms_plugin_hooks;
    $GLOBALS["use_mapquest_link"]="y";
}

function mapquest($line1, $city, $province, $iso_code2, $address_to_display) {
    $url_line1 = urlencode ($line1);
    $url_city = urlencode ($city);
    $url_province = urlencode ($province);
    $url_country = urlencode ($iso_code2);

    return "<a href=\"http://maps.google.com/maps?f=q&hl=en&q=$url_line1%2B$url_city%2B$url_province%2B$url_country&ie=UTF8&om=1&t=h\" target=\"_new\">" . $address_to_display . "</a>";
}

?>