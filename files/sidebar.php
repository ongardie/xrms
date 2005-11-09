<?php
/**
 * Sidebar box for Files
 *
 * $Id: sidebar.php,v 1.24 2005/11/09 22:36:39 daturaarutad Exp $
 */

if ( !defined('IN_XRMS') )
{
  die(_('Hacking attempt'));
  exit;
}

require_once($include_directory . 'utils-files.php');


/*
COMMENTED until ACL is integrated
$fileList=acl_get_list($session_user_id, 'Read', false, 'files');
if (!$fileList) { $file_rows=''; return false; }
else { $fileList=implode(",",$fileList); $file_limit_sql.=" AND files.file_id IN ($fileList) "; }
*/

// Avoid undefined errors until ACL is integrated
$file_limit_sql = '';

    // Build data setup
    if (!$on_what_table AND !$on_what_id) {
        //No attachment specified, so show users files
        $files_data['entered_by']       = $session_user_id;
    } else {
        //show files attached to the currently view entity
        $files_data['on_what_table']    = $on_what_table;
        $files_data['on_what_id']       = $on_what_id;
    }
    $file_sidebar_rst = get_file_records( $con, $files_data );



// files plugin hook
$plugin_params = array('rst' => $file_sidebar_rst, 'on_what_table' => $on_what_table, 'on_what_id' => $on_what_id);
do_hook_function('file_browse_files', $plugin_params);
$file_rows = $plugin_params['file_rows'];


if(!$file_rows) {
    if (!$file_sidebar_label) {
        $file_sidebar_label=_("Files");
    }
        if (!$return_url) {
            $return_url="/$on_what_table/one.php?".make_singular($on_what_table)."_id=".$on_what_id;
        }
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

    if (strlen($file_sidebar_rst->fields['username']) > 0) {
        while (!$file_sidebar_rst->EOF) {

          // get contact id
          $user_contact_id = $file_sidebar_rst->fields['user_contact_id'];

            $file_rows .= "
                 <tr>";
            if ($file_sidebar_rst->fields['file_size'] == "0")
              {
                    $file_rows .= "<td class=non_uploaded_file><a href='$http_site_root/files/one.php?file_id={$file_sidebar_rst->fields['file_id']}&return_url=". urlencode($return_url) . "' title='". $file_sidebar_rst->fields['file_pretty_name']. "'>" . substr( $file_sidebar_rst->fields['file_pretty_name'], 0, 20) . '</a></b></td>';
                    $file_rows .= '<td class=non_uploaded_file><b>' . pretty_filesize($file_sidebar_rst->fields['file_size']) . '</b></td>';
                    $file_rows .= '<td class=non_uploaded_file><b>' . $file_sidebar_rst->fields['username'] . '</b></td>';
                    $file_rows .= '<td class=non_uploaded_file><b>' . $con->userdate($file_sidebar_rst->fields['entered_at']) . '</b></td>';
              }
            else
              {
                    $file_rows .= "<td class=widget_content><a href='$http_site_root/files/one.php?file_id={$file_sidebar_rst->fields['file_id']}&return_url=". urlencode($return_url) . "' title='". $file_sidebar_rst->fields['file_pretty_name']. "'>" . substr( $file_sidebar_rst->fields['file_pretty_name'], 0, 20) .  '</a></td>';
                    $file_rows .= '<td class=widget_content>' . pretty_filesize($file_sidebar_rst->fields['file_size']) . '</td>';
                    $file_rows .= '<td class=widget_content>' . $file_sidebar_rst->fields['username'] . '</td>';
                    $file_rows .= '<td class=widget_content>' . $con->userdate($file_sidebar_rst->fields['entered_at']) . '</td>';
              }
            $file_rows .= "
                 </tr>";
            $file_sidebar_rst->movenext();
        }
        $file_sidebar_rst->close();
    } else {
        $file_rows .= "            <tr> <td class=widget_content colspan=4> "._("No attached files")." </td> </tr>\n";
    }

    //put in the new button
    if (strlen($on_what_table)>0){
        $new_file_button=render_create_button('New', 'submit'); //, false, false, false, 'files'); uncomment extra parameters in order to check permission on files instead of whatever the file is attached to
        $file_rows .= "
                <tr>
                <form action='".$http_site_root."/files/new.php' method='post'>
                    <td class=widget_content_form_element colspan=4>
                            <input type=hidden name=on_what_table value='$on_what_table'>
                            <input type=hidden name=on_what_id value='$on_what_id'>
                            <input type=hidden name=return_url value='$return_url'>
                            $new_file_button
                    </td>
                </form>
                </tr>";
    }


    //now close the table, we're done
    $file_rows .= "        </table>\n</div>";
}

/**
 * $Log: sidebar.php,v $
 * Revision 1.24  2005/11/09 22:36:39  daturaarutad
 * add hooks for files plugin
 *
 * Revision 1.23  2005/10/04 23:01:26  vanmer
 * - added check to ensure that entered_by parameter is not added to file list when viewing pages that aren't
 * private/home.php
 * - added commented parameters for New button files to allow check on files ACL controlled object instead of whatever
 * the file is attached to
 *
 * Revision 1.22  2005/10/01 05:11:33  jswalter
 *  - removed legacy code 'file_limit_sql'
 *
 * Revision 1.21  2005/09/23 19:49:27  daturaarutad
 * updated for file plugin (owl support)
 *
 * Revision 1.20  2005/07/07 16:55:28  jswalter
 *  - added 'ultils-files.php' to use new FILES API
 *  - removed file retrieval SQL
 *  - added 'get_file_records()' call to retrieve FILES list
 *
 * Revision 1.19  2005/07/01 16:16:24  vanmer
 * - added parameter to explicitly set sidebar label from outside of sidebar
 *
 * Revision 1.18  2005/06/30 23:42:46  vanmer
 * - added edit button so files can have edit controlled by ACL
 * - added download link from filename
 * - added tooltip of file description to download link
 *
 * Revision 1.17  2005/06/24 23:26:09  vanmer
 * - changed rst to differ from other rsts
 * - changed to use existing return_url if available
 *
 * Revision 1.16  2005/06/01 16:40:55  ycreddy
 * Adding title attribute to the name html element in the pager and side bar for files
 *
 * Revision 1.15  2005/04/28 18:44:50  daturaarutad
 * added files plugin hook
 *
 * Revision 1.14  2005/04/05 19:41:54  daturaarutad
 * now use current_page() to set return_url
 *
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
