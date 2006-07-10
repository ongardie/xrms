<?php
/**
* owl/new_folder-2.php - This file adds new folders to the system
*
* $Id: delete_folder.php,v 1.4 2006/07/10 13:20:19 braverock Exp $
*/

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('folders_lib.php');

$session_user_id = session_check('','Create');
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

getGlobalVar($folder_id, 'folder_id');
getGlobalVar($return_url, 'return_url');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sep = get_url_seperator($return_url);

$error = false;


$folders = GetFolders(null, null, "external_id = $folder_id");

if(count($folders)) {

    $folder_plugin_params = array('folder_info' => $folders[0]);
    do_hook_function('file_delete_folder', $folder_plugin_params);


    if($folder_plugin_params['error_status']) {
        $msg = $folder_plugin_params['error_text'];
        $error = true;
    } else {

        $on_what_table = $folders[0]['on_what_table'];
        $on_what_id = $folders[0]['on_what_id'];

        if(count($folder_plugin_params['folder_info']['files'])) {
            $file_ids = join(',', $folder_plugin_params['folder_info']['files']);
            $file_sql = "delete from files where file_id in ($file_ids)";

            $rst = $con->execute($file_sql);
            if(!$rst) {
                db_error_handler($con, $file_sql);
                $msg .= _('Error deleting files in XRMS.');
                $error = true;
            }

        }
        if(count($folder_plugin_params['folder_info']['folders'])) {
            $folder_ids = join(',', $folder_plugin_params['folder_info']['folders']);
            $folder_sql = "delete from folders where id in ($folder_ids)";

            $rst = $con->execute($folder_sql);
            if(!$rst) {
                db_error_handler($con, $folder_sql);
                $msg .= _('Error deleting files in XRMS.');
                $error = true;
            }
        }
        $owl_parent_url = "owl_parent_id=" . GetEntityFolderID($on_what_table, $on_what_id) . "&";

        if(!$error) {
            $msg = _('Folder deleted successfully.');

        }


    }


} else {
    $msg .= _('Folder not found in XRMS.');
    $error = true;
}

header("Location: " . $http_site_root . $return_url . $sep . $owl_parent_url . "msg=" . htmlentities($msg));

/**
 * $Log: delete_folder.php,v $
 * Revision 1.4  2006/07/10 13:20:19  braverock
 * - clean indentation
 * - remove trailing whitespace
 *
 * Revision 1.3  2006/07/10 12:47:41  braverock
 * - remove call time pass by reference in do_hook_function (reference in function def)
 *
 * Revision 1.2  2005/12/14 04:27:52  daturaarutad
 * fix $msg
 *
 * Revision 1.1  2005/12/09 19:23:00  daturaarutad
 * new file for deleting files and folders
 *
 * Revision 1.3  2005/11/09 22:31:00  daturaarutad
 * updated API to use named keys
 *
 * Revision 1.2  2005/09/23 20:42:06  daturaarutad
 * tidy up comments
 *
 * Revision 1.1  2005/04/28 15:47:10  daturaarutad
 * new files
 */
?>