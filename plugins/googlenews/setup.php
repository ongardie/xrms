<?php
/*
*
* Google Newst (googlenews) XRMS Plugin v0.1
*
* copyright 2004 Glenn Powers <glenn@net127.com>
*
*/

function xrms_plugin_init_googlenews() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['one_company_buttons']['googlenews'] = 'googlenews';
}

function googlenews() {
    global $company_name;
    $url_company_name = urlencode($company_name);
    echo " <input class=button type=button value=\"" . _("Google News") . "\" onclick=\"javascript: location.href='http://news.google.com/news?q=%22" . $url_company_name . "%22';\">";
}

?>
