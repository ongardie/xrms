<?php
/**
 * Sidebar box for notes
 *
 * $Id: sidebar.php,v 1.3 2004/04/07 19:38:26 maulani Exp $
 */

$note_rows = "<div id='note_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header colspan=4>Notes</td>
            </tr>
            <tr>
                <td class=widget_label>Name</td>
                <td class=widget_label>Size</td>
                <td class=widget_label>Owner</td>
                <td class=widget_label>Date</td>
            </tr>\n";

//build the notes sql query
if (strlen($on_what_table)>0){
    $note_sql = "select note_id, note_description, entered_by, entered_at, username from notes, users
            where notes.entered_by = users.user_id
            and on_what_table = '$on_what_table'
            and on_what_id = $on_what_id
            and note_record_status = 'a'
            order by entered_at desc";
} else {
    $note_sql = "select note_id, note_description, entered_by, entered_at, username from notes, users
            where notes.entered_by = '$session_user_id'
            and notes.entered_by = users.user_id
            and note_record_status = 'a'
            order by entered_at
            limit 5";
}

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

$rst = $con->execute($note_sql);

if (strlen($rst->fields['username']) > 0) {
    while (!$rst->EOF) {
        $note_rows .= "
             <tr>
                 <td class=widget_content colspan=4>
                 <font class=note_label>"
               . $con->userdate($rst->fields['entered_at']) . " &bull; "
               . $rst->fields['username'] . " &bull;
                 <a href='../notes/edit.php?note_id=" . $rst->fields['note_id'] . "&return_url=/companies/one.php?company_id=" . $company_id . "'>Edit</a>
                 </font>
                 <br>"
               . $rst->fields['note_description'] .'
                 </td>
             </tr>';
        $rst->movenext();
    }
    $rst->close();
} else {
    $note_rows .= "            <tr> <td class=widget_content colspan=5> No attached notes </td> </tr>\n";
}

//put in the new button
if (strlen($on_what_table)>0){
    $note_rows .= "
            <tr>
            <form action='".$http_site_root."/notes/new.php' method='post'>
                <td class=widget_content_form_element colspan=4>
                        <input type=hidden name=on_what_table value='$on_what_table'>
                        <input type=hidden name=on_what_id value='$on_what_id'>
                        <input type=hidden name=return_url value='/".$on_what_table."/one.php?".$on_what_string."_id=".$on_what_id."'>
                        <input type=submit class=button value='New'>
                </td>
            </form>
            </tr>";
}

//now close the table, we're done
$note_rows .= "        </table>\n</div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.3  2004/04/07 19:38:26  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.2  2004/04/07 13:50:53  maulani
 * - Set CSS2 positioning for the home page
 *
 * Revision 1.1  2004/03/07 14:03:05  braverock
 * Initital Checkin of side-bar centralization
 *
 */
?>