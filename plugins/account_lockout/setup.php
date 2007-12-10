<?php

// Copyright 2007 Glenn Powers <glenn@net127.com>

function xrms_plugin_init_account_lockout() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['bad_password']['account_lockout'] = 'account_lockout';
}


function account_lockout() {
        global $con, $audit_user_id;

// $con->debug=1;

$sql = "SELECT count(*) as attempts
FROM audit_items
WHERE user_id = '" . $audit_user_id . "'
AND audit_item_timestamp > ADDTIME(CURRENT_TIME(), '-1:00:00')
AND audit_item_type like 'login failure'
";

$rst = $con->execute($sql);

    if (!$rst->EOF) {
        if ($rst->fields['attempts'] > 3) {
        $sql = "SELECT * FROM users WHERE user_id = $audit_user_id and user_record_status = 'a'";
        $rst = $con->execute($sql);

    if (!$rst->EOF) {
        $rec = array();

        $rec['user_record_status'] = 'd';

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        if ($upd) {
            $rst2 = $con->execute($upd);
            if(!$rst) {
                db_error_handler($con, $upd);
            }

            add_audit_item($con, $audit_user_id, 'account locked', 'users', $user_id, 1);
$message = $rst->fields['username'] . "'s account, ID #" . $rst->fields['user_id'] . ",  has been locked due to repeated incorrect passwords.";
mail('glenn@net127.com, philip@medchoicefinancial.com', 'XRMS Account Locked', $message);
        }
        }

        $con->close();
            
    }
}

}
?>
