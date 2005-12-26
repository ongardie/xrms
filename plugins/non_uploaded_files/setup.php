<?php
/*
 *  setup.php
 *
 * Copyright (c) 2005 The XRMS Project Team
 *
 *  init plugin into xrms
 *
 * This plugin contains the little-used non-uploaded files functionality
 * Florent Jekot <fjekot at fontaine-consultants dot fr> ported the old non-uploaded files code
 * into this plugin.
 *
 * $Id: setup.php,v 1.1 2005/12/26 21:42:51 braverock Exp $
 */


function xrms_plugin_init_non_uploaded_files() {
    global $xrms_plugin_hooks;
    $xrms_plugin_hooks['private_body_bottom']['non_uploaded_files'] = 'nufhome';

}

function nufhome() {

 global $display_how_many_activities_on_company_page, $http_site_root, $con, $session_user_id;

 ////////////////////////////////////
 // Show all non-uploaded files -> size 0
 $sql_files = "select * from files f where file_size = 0 and f.entered_by = ". $session_user_id . " order by file_id asc";

 $rst = $con->selectlimit($sql_files, $display_how_many_activities_on_company_page);

 $classname = 'non_uploaded_file';
 if ($rst) {
    if ($rst->rowcount()>0) {
       $nu_file_rows = "
          <table class=widget cellspacing=1 width='100%'>
                <tr>
                   <td class=widget_header colspan=4>" . _("Non Uploaded Files") . "</td>
                </tr>
                <tr>
           <td class=widget_label>" . _("File ID") . "</td>
                   <td class=widget_label>" . _("Name") . "</td>
                   <td class=widget_label>" . _("Description") . "</td>                        <td class=widget_label>" . _("Date") . "</td>
                </tr>";

       while (!$rst->EOF) {

          $file_id = $rst->fields['file_id'];
          $file_name = $rst->fields['file_pretty_name'];
          $file_description = $rst->fields['file_description'];
          $on_what_id = $rst->fields['on_what_id'];
          $file_date = $rst->fields['entered_at'];

          //now build the file row
          $nu_file_rows .= '<tr>';

      $nu_file_rows .= "<td class='$classname'><a href='$http_site_root/files/one.php?return_url=/private/home.php&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_id'] . '</a></td>';

          $nu_file_rows .= "<td class=non_uploaded_file><a href='$http_site_root/files/one.php?return_url=/contacts/one.php?contact_id=$contact_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></b></td>';
          $nu_file_rows .= '<td class=' . $classname . '>' . $file_description . '</td>';

          $nu_file_rows .= '<td class=' . $classname . '>' . $file_date . '</td>';
          $nu_file_rows .= '</tr>';
          $rst->movenext();
       }
       $rst->close();

     $nu_file_rows .= '</table>';

    }
 } else {
    //no result set - database error
    db_error_handler ($con,$sql_files);
 }

    return $nu_file_rows;
}

/**
 * $Log: setup.php,v $
 * Revision 1.1  2005/12/26 21:42:51  braverock
 * - Initial commit of plugin to restore non_uploaded_files functionality
 *   Florent Jekot <fjekot at fontaine-consultants dot fr> ported the
 *   old non-uploaded files code from private/home.php
 *
 */
?>