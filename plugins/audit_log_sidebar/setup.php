<?php

// copyright 2007 Glenn Powers <glenn@net127.com>

function xrms_plugin_init_zzz_audit_sidebar() {
    global $xrms_plugin_hooks;

    $xrms_plugin_hooks['company_sidebar_bottom']['zzz_audit_sidebar'] = 'audit_company_sidebar';
    $xrms_plugin_hooks['contact_sidebar_bottom']['zzz_audit_sidebar'] = 'audit_contact_sidebar';
}

function audit_company_sidebar() {
    global $include_directory;
    require_once($include_directory . '/../plugins/zzz_audit_sidebar/gup-audit-items.php');
    return gup_audit_items();
}

function audit_contact_sidebar() {
    global $include_directory;
    require_once($include_directory . '/../plugins/zzz_audit_sidebar/gup-audit-items.php');
    return gup_audit_items();
}

?>