<?php
/**
* owl/new_folder-2.php - This file adds new folders to the system
*
* $Id: new_folder-2.php,v 1.8 2006/07/10 13:20:19 braverock Exp $
*/

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'mime/mime-array.php');

global $http_site_root;

$session_user_id = session_check('','Create');
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

getGlobalVar($on_what_table, 'on_what_table');
getGlobalVar($on_what_id, 'on_what_id');
getGlobalVar($return_url, 'return_url');
getGlobalVar($name, 'name');
getGlobalVar($description, 'description');
getGlobalVar($entered_at, 'entered_at');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($entered_at == "")
{ $entered_at = time(); }
else
{ $entered_at = strtotime($file_entered_at); }


//save to database
$rec = array();
$rec['name'] = $name;
$rec['description'] = $description;
$rec['on_what_table'] = $on_what_table;
$rec['on_what_id'] = $on_what_id;
$rec['entered_at'] = $entered_at;
$rec['entered_by'] = $session_user_id;


$folder_plugin_params = array('folder_info' => $rec);
do_hook_function('file_add_folder', $folder_plugin_params);

$rec = $folder_plugin_params['folder_info'];


$error = false;

$sep = get_url_seperator($return_url);

if($folder_plugin_params['error_status']) {
    $error = true;
    $msg = $folder_plugin_params['error_text'];
    header("Location: " . $http_site_root . $return_url . $sep . "msg=" . htmlentities($msg));
} else {

    $tbl = 'folders';

    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc(), false);
    //echo $ins;
    $rst = $con->execute($ins);
    if(!$rst) {
        db_error_handler($con, $ins);
    }

    $folder_id = $con->insert_id();


    $con->close();

    header("Location: " . $http_site_root . $return_url . $sep . "msg=" . _('Folder Created Successfully'));
}

/**
 * $Log: new_folder-2.php,v $
 * Revision 1.8  2006/07/10 13:20:19  braverock
 * - clean indentation
 * - remove trailing whitespace
 *
 * Revision 1.7  2006/07/10 12:47:41  braverock
 * - remove call time pass by reference in do_hook_function (reference in function def)
 *
 * Revision 1.6  2006/01/17 20:21:28  daturaarutad
 * fix return_url
 *
 * Revision 1.5  2005/12/14 04:48:56  daturaarutad
 * make sure GetInsertSQL does not try to set null values
 *
 * Revision 1.4  2005/12/09 19:24:43  daturaarutad
 * use $sep for ? vs & in return url
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