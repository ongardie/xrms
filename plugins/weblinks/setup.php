<?php
/*
*
* Web Links XRMS Plugin v0.1
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed under the GNU GPL
*
*/

function xrms_plugin_init_weblinks() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['company_buttons']['weblinks'] = 'weblinks';
}

function weblinks() {
    global $company_name;
    $url_company_name = urlencode($company_name);
    global $line1;
    global $city;
    global $postal_code;

    $weblinks_rows = "
<form name=\"weblink\">
<select name=\"menu\" onChange=\"location=document.weblink.menu.options[document.weblink.menu.selectedIndex].value;\" value=\"GO\">

<option value=\"\">" . _('Web Links') . "</option>

<option value=\"http://www.google.com/search?q=%22" . $url_company_name . "%22\">" . _('Google') . "</option>

<option value=\"http://news.google.com/news?q=%22" . $url_company_name . "%22\">" . _('Google News') . "</option>

";

    if ($postal_code) {
        $weblinks_rows .= "<option value=\"http://factfinder.census.gov/servlet/SAFFFacts?_event=Search&geo_id=01000US&_geoContext=&_street=" . urlencode($line1) . "&_county=&_cityTown=" . urlencode($city) . "&_state=&_zip=" . urlencode($postal_code) . "&_lang=en&_sse=on\">" . _('US Census Data') . "</option>";
}

   $weblinks_rows .= "
</select>
</form>
";

   return $weblinks_rows;
   
}

?>
