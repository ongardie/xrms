<?php
/**
 * Sidebar box for Files
 *
 * $Id: sidebar.php,v 1.31 2006/04/17 19:03:43 vanmer Exp $
 */

if ( !defined('IN_XRMS') )
{
  die(_('Hacking attempt'));
  exit;
}

require_once($include_directory . 'utils-files.php');

// Set up the pager to display the current dir's data
global $include_directory;
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $return_url;




///*
//COMMENTED until ACL is integrated
$fileList=acl_get_list($session_user_id, 'Read', false, 'files');
if (!$fileList) { $file_rows=''; return false; }
else { if ($fileList!==true) { $fileList=implode(",",$fileList); $file_limit_sql.=" AND files.file_id IN ($fileList) "; } }
//*/

// Avoid undefined errors until ACL is integrated
$file_limit_sql = '';

    // Build data setup
    if (!$on_what_table AND !$on_what_id) {
        //No attachment specified, so show users files (private/home.php)
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

if($plugin_params['error_status']) {
        $msg = $plugin_params['error_text'];
}


if(!$file_rows && !$file_rows['error_status']) {
    if (!$file_sidebar_label) {
        $file_sidebar_label=_("Files");
    }
    if (!$return_url) {
        $return_url="/$on_what_table/one.php?".make_singular($on_what_table)."_id=".$on_what_id;
    }

    $files = array();

    if (strlen($file_sidebar_rst->fields['username']) > 0) {
        while (!$file_sidebar_rst->EOF) {

          // get contact id
          $file_info['name'] = "<a href='$http_site_root/files/one.php?file_id={$file_sidebar_rst->fields['file_id']}&return_url=". urlencode($return_url) . "' title='". $file_sidebar_rst->fields['file_pretty_name']. "'>" . substr( $file_sidebar_rst->fields['file_pretty_name'], 0, 20) .  '</a>';
          $file_info['size'] = $file_sidebar_rst->fields['file_size'];
          $file_info['description'] = $file_sidebar_rst->fields['file_description'];
          $file_info['owner'] =  $file_sidebar_rst->fields['username'];
          $file_info['date'] = $con->userdate($file_sidebar_rst->fields['entered_at']);
          $file_info['id'] = $file_sidebar_rst->fields['file_id'];

          $files[] = $file_info;

          $file_sidebar_rst->movenext();
        }
        $file_sidebar_rst->close();
    }


    $columns=array();
    $columns[] = array('name' => _("Summary"), 'index_calc' => 'name');
    $columns[] = array('name' => _("Size"), 'index_calc' => 'size', 'type' => 'filesize');
    $columns[] = array('name' => _("Owner"), 'index_calc' => 'owner');
    $columns[] = array('name' => _("Description"), 'index_calc' => 'description');
    $columns[] = array('name' => _("Date"), 'index_calc' => 'date');
    $columns[] = array('name' => _("ID"), 'index_calc' => 'id');

    if(!$file_sidebar_default_columns) $file_sidebar_default_columns = array('name', 'size','owner', 'date');

    $file_pager_columns = new Pager_Columns('Files_Sidebar', $columns, $file_sidebar_default_columns, 'Files_Sidebar_Form');
    $file_pager_columns_button = $file_pager_columns->GetSelectableColumnsButton();
    $file_pager_columns_selects = $file_pager_columns->GetSelectableColumnsWidget();

    $columns = $file_pager_columns->GetUserColumns('default');
    $colspan = count($columns);


    $pager = new GUP_Pager($con, null, $files, $file_sidebar_label, 'Files_Sidebar_Form', 'Files_Sidebar', $columns, false, true);


    //put in the new button
    if (strlen($on_what_table)>0){
        $new_file_button=render_create_button('New', 'button', "javascript: location.href='$http_site_root/files/new.php?on_what_table=$on_what_table&on_what_id=$on_what_id&return_url=$return_url';"); //, false, false, false, 'files'); uncomment extra parameters in order to check permission on files instead of whatever the file is attached threfo

    }
    $end_rows = "
                <tr>
                    <td class=widget_content_form_element colspan=$colspan>
                            $new_file_button
                            $file_pager_columns_button
                    </td>
                </tr>";


    $pager->AddEndRows($end_rows);

    $file_rows = "<form name=Files_Sidebar_Form method=POST>
                    $file_pager_columns_selects
                    <input type=hidden name=contact_id value=$contact_id>
                    <input type=hidden name=company_id value=$company_id>
                    <input type=hidden name=division_id value=$division_id>\n"
                    .  $pager->Render() . "
                  </form>\n";
}

/**
 * $Log: sidebar.php,v $
 * Revision 1.31  2006/04/17 19:03:43  vanmer
 * - added proper ACL restriction to sidebar output
 *
 * Revision 1.30  2006/01/19 18:35:23  daturaarutad
 * add Pager_Columns include
 *
 * Revision 1.29  2006/01/05 14:07:18  braverock
 * - localize column headers
 *
 * Revision 1.28  2006/01/05 13:55:15  braverock
 * - add id to sidebar
 * - add selectable columns widget to search
 *
 * Revision 1.27  2005/12/14 05:05:30  daturaarutad
 * change Name to Summary, add Description as possible field
 *
 * Revision 1.26  2005/12/09 19:26:47  daturaarutad
 * display error msg from plugin if exists
 *
 * Revision 1.25  2005/12/06 19:08:05  daturaarutad
 * update to use pager for display
 *
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
