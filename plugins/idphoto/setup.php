<?php
/*
 *  setup.php
 *
 * Copyright (c) 2004 The XRMS Project Team
 *
 * $Id: setup.php,v 1.2 2004/11/09 03:12:54 gpowers Exp $
 */


function xrms_plugin_init_idphoto () {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['contact_sidebar_top']['idphoto'] = 'sidebar';
}

function sidebar () {
    global $con, $contact_id;

    $sql = "select file_filesystem_name from files where file_record_status = 'a' and  file_pretty_name = 'id_photo_" . $contact_id . "' limit 1";
    $rst = $con->execute($sql);
    if ($rst) {
        if (!$rst->EOF) {
            $file_filesystem_name = $rst->fields['file_filesystem_name'];
            $sidebar_string = '<div id="idphoto_sidebar">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=4>'
                ._("ID Photo")
                .'</td>
            </tr>
            <tr>
                <td>
                    <img src="../files/storage/' . $file_filesystem_name . '" height=160 width=120 alt="' . _("ID Photo") . '">
                </td>
</tr>
</table>
';
        }
            $rst->close();
    }
    return $sidebar_string;
}

?>
