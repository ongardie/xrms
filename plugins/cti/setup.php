<?php
/*
*
* CTI XRMS Plugin v0.1
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the GNU GPL v. 2.0
*
*/

function xrms_plugin_init_cti() {
    global $xrms_plugin_hooks;
//    $xrms_plugin_hooks['one_contact_buttons']['cti'] = 'asteriskdial';
    $xrms_plugin_hooks['admin_user_edit_sidebar']['cti'] = 'do_admin_user_edit_sidebar';
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

function do_admin_user_edit_sidebar() {
    global $new_username, $user_contact_id, $email, $edit_user_id, $http_site_root;
    return "<form action=\"" . $http_site_root . "/plugins/cti/provision/new-cisco-7960.php\" method=post>
    <input type=hidden name=user_id value=\"" . $edit_user_id . "\">
    <input type=hidden name=email value=\"" . $email . "\">
    <input type=hidden name=username value=\"" . $new_username . "\">
    <table class=widget cellspacing=1>
    <tr>
        <td class=widget_header colspan=2>
            " . _("Provision Cisco Phone") . "
        </td>
    </tr>
    <tr>
        <td class=widget_content>" . _("Extension") . "</td>
        <td class=widget_content>
            <input type=text size=40 name=extension value=\"" . $extension . "\">
        </td>
    </tr>
    <tr>
        <td class=widget_content>" . _("MAC Address") . "</td>
        <td class=widget_content>
            <input type=text size=40 name=mac value=\"" . $mac . "\">
        </td>
    </tr>
    <tr>
        <td class=widget_content>" . _("VM Password") . "</td>
        <td class=widget_content>
            <input type=text size=40 name=vm_password value=\"" . $vm_password . "\">
        </td>
    </tr>
    <tr>
        <td class=widget_content colspan=2>
            <input class=button type=submit value=\"" . _("Create") . "\"
        </td>
    </tr>
</table>
</form>
";
}
         
?>