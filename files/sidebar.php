<?php
/**
 * Sidebar box for Files
 *
 * $Id: sidebar.php,v 1.13 2005/01/12 02:20:28 introspectshun Exp $
 */

if ( !defined('IN_XRMS') )
{
  die(_('Hacking attempt'));
  exit;
}
/*
COMMENTED until ACL is integrated
$fileList=get_list($session_user_id, 'Read', false, 'files');
if (!$fileList) { $file_rows=''; return false; }
else { $fileList=implode(",",$fileList); $file_limit_sql.=" AND files.file_id IN ($fileList) "; }
*/

// Avoid undefined errors until ACL is integrated
$file_limit_sql = '';

$file_rows = "<div id='file_sidebar'>
        <table class=widget cellspacing=1 width=\"100%\">
            <tr>
                <td class=widget_header colspan=4>"._("Files")."</td>
            </tr>
            <tr>
                <td class=widget_label>"._("Name")."</td>
                <td class=widget_label>"._("Size")."</td>
                <td class=widget_label>"._("Owner")."</td>
                <td class=widget_label>"._("Date")."</td>
            </tr>\n";

//uncomment the debug line to see what's going on with the query
//$con->debug=1;

//build the files sql query
if (strlen($on_what_table)>0){
    $file_sql = "select * from files, users where
            files.entered_by = users.user_id
            and on_what_table = '$on_what_table'
            and on_what_id = '$on_what_id'
            and file_record_status = 'a'
            $file_limit_sql           
            order by entered_at";
    $rst = $con->execute($file_sql);
} else {
    $file_sql = "select * from files, users where
            files.entered_by = '$session_user_id'
            and files.entered_by = users.user_id
            and file_record_status = 'a'
            $file_limit_sql
            order by entered_at";
    $rst = $con->SelectLimit($file_sql, 5, 0);
}

// any errors ???
if ( !$rst ) {
  // yep - report it
  db_error_handler($con, $file_sql);
}

if (strlen($rst->fields['username']) > 0) {
    while (!$rst->EOF) {

      // get contact id
      $user_contact_id = $rst->fields['user_contact_id'];

        $file_rows .= "
             <tr>";
        if ($rst->fields['file_size'] == "0")
          {
          $file_rows .= "<td class=non_uploaded_file><a href='$http_site_root/files/one.php?return_url=/contacts/one.php?contact_id=$user_contact_id&amp;file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></b></td>';
          $file_rows .= '<td class=non_uploaded_file><b>' . pretty_filesize($rst->fields['file_size']) . '</b></td>';
          $file_rows .= '<td class=non_uploaded_file><b>' . $rst->fields['username'] . '</b></td>';
          $file_rows .= '<td class=non_uploaded_file><b>' . $con->userdate($rst->fields['entered_at']) . '</b></td>';
          }
        else
          {
          $file_rows .= "<td class=widget_content><a href='$http_site_root/files/one.php?return_url=/contacts/one.php?contact_id=$user_contact_id&amp;file_id=" . $rst->fields['file_id'] . "'>" . $rst->fields['file_pretty_name'] . '</a></td>';
          $file_rows .= '<td class=widget_content>' . pretty_filesize($rst->fields['file_size']) . '</td>';
          $file_rows .= '<td class=widget_content>' . $rst->fields['username'] . '</td>';
          $file_rows .= '<td class=widget_content>' . $con->userdate($rst->fields['entered_at']) . '</td>';
          }
        $file_rows .= "
             </tr>";
        $rst->movenext();
    }
    $rst->close();
} else {
    $file_rows .= "            <tr> <td class=widget_content colspan=4> "._("No attached files")." </td> </tr>\n";
}

//put in the new button
if (strlen($on_what_table)>0){
    $new_file_button=render_create_button('New', 'submit');
    $file_rows .= "
            <tr>
            <form action='".$http_site_root."/files/new.php' method='post'>
                <td class=widget_content_form_element colspan=4>
                        <input type=hidden name=on_what_table value='$on_what_table'>
                        <input type=hidden name=on_what_id value='$on_what_id'>
                        <input type=hidden name=return_url value='/".$on_what_table."/one.php?".make_singular($on_what_table)."_id=".$on_what_id."'>
                        $new_file_button
                </td>
            </form>
            </tr>";
}

//now close the table, we're done
$file_rows .= "        </table>\n</div>";

/**
 * $Log: sidebar.php,v $
 * Revision 1.13  2005/01/12 02:20:28  introspectshun
 * - Defined $file_limit_sql until ACL is implemented
 *
 * Revision 1.12  2005/01/09 02:34:25  vanmer
 * - added commented ACL restriction on files
 * - added make_singular call instead of using $on_what_string for file sidebar
 * - changed to use render_button functions
 *
 * Revision 1.11  2004/10/22 21:09:45  introspectshun
 * - Localized strings, various fixes
 *
 * Revision 1.10  2004/08/05 15:21:56  braverock
 * - fixed bug where contact_id was overwritten before being needed by including file
 *
 * Revision 1.9  2004/08/03 18:05:56  cpsource
 * - Set mime type when database entry is created
 *
 * Revision 1.8  2004/07/25 16:47:38  johnfawcett
 * - added gettext calls
 *
 * Revision 1.7  2004/07/14 20:19:50  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.6  2004/07/14 14:49:27  cpsource
 * - All sidebar.php's now support IN_XRMS security feature.
 *
 * Revision 1.5  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.4  2004/04/07 19:38:26  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.3  2004/04/07 13:50:53  maulani
 * - Set CSS2 positioning for the home page
 *
 * Revision 1.2  2004/03/12 13:48:12  braverock
 * - added code to change display for zero-size files
 * - patch provided by Olivier Colonna of Fontaine Consulting
 *
 * Revision 1.1  2004/03/07 14:05:13  braverock
 * Initital Checkin of side-bar centralization
 *
 */
?>