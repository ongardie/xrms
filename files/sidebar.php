<?php
/**
 * Sidebar box for Files
 *
 * $Id: sidebar.php,v 1.2 2004/03/12 13:48:12 braverock Exp $
 */

$file_rows = "
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=5>Files</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Size</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Date</td>
                <td class=widget_label>File Id</td>
            </tr>\n";

//build the files sql query
if (strlen($on_what_table)>0){
    $file_sql = "select * from files, users where
            files.entered_by = users.user_id
            and on_what_table = '$on_what_table'
            and on_what_id = $on_what_id
            and file_record_status = 'a'
            order by entered_at";
} else {
    $file_sql = "select * from files, users where
            files.entered_by = '$session_user_id'
            and files.entered_by = users.user_id
            and file_record_status = 'a'
            order by entered_at
            limit 5";
}

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

$rst = $con->execute($file_sql);

if (strlen($rst->fields['username']) > 0) {
    while (!$rst->EOF) {
        $file_rows .= "
             <tr>";
        if ($rst->fields['file_size'] == "0")
          {
          $file_rows .= "<td class=non_uploaded_file><a href='$http_site_root/files/one.php?return_url=/contacts/one.php?contact_id=$contact_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></b></td>';
          $file_rows .= '<td class=non_uploaded_file><b>' . pretty_filesize($rst->fields['file_size']) . '</b></td>';
          $file_rows .= '<td class=non_uploaded_file><b>' . $rst->fields['username'] . '</b></td>';
          $file_rows .= '<td class=non_uploaded_file><b>' . $con->userdate($rst->fields['entered_at']) . '</b></td>';
          $file_rows .= '<td class=non_uploaded_file><b>' . $rst->fields['file_id'] . '</b></td>';
          }
        else
          {
          $file_rows .= "<td class=widget_content><a href='$http_site_root/files/one.php?return_url=/contacts/one.php?contact_id=$contact_id&file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></td>';
          $file_rows .= '<td class=widget_content>' . pretty_filesize($rst->fields['file_size']) . '</td>';
          $file_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
          $file_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['entered_at']) . '</td>';
          $file_rows .= '<td class=widget_content>' . $rst->fields['file_id'] . '</td>';
          }
        $file_rows .= "
             </tr>";
        $rst->movenext();
    }
    $rst->close();
} else {
    $file_rows .= "            <tr> <td class=widget_content colspan=5> No attached files </td> </tr>\n";
}

//put in the new button
if (strlen($on_what_table)>0){
    $file_rows .= "
            <tr>
            <form action='".$http_site_root."/files/new.php' method='post'>
                <td class=widget_content_form_element colspan=5>
                        <input type=hidden name=on_what_table value='$on_what_table'>
                        <input type=hidden name=on_what_id value='$on_what_id'>
                        <input type=hidden name=return_url value='/".$on_what_table."/one.php?".$on_what_string."_id=".$on_what_id."'>
                        <input type=submit class=button value='New'>
                </td>
            </form>
            </tr>";
}

//now close the table, we're done
$file_rows .= "        </table>\n";

/**
 * $Log: sidebar.php,v $
 * Revision 1.2  2004/03/12 13:48:12  braverock
 * - added code to change display for zero-size files
 * - patch provided by Olivier Colonna of Fontaine Consulting
 *
 * Revision 1.1  2004/03/07 14:05:13  braverock
 * Initital Checkin of side-bar centralization
 *
 */
?>