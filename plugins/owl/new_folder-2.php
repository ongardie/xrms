<?php
/**
 * owl/new_folder-2.php - This file adds new folders to the system
 *
 * $Id: new_folder-2.php,v 1.2 2005/09/23 20:42:06 daturaarutad Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'mime/mime-array.php');

$session_user_id = session_check('','Create');
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

getGlobalVar($on_what_table, 'on_what_table');
getGlobalVar($on_what_id, 'on_what_id');
getGlobalVar($return_url, 'return_url');
getGlobalVar($folder_name, 'name');
getGlobalVar($folder_description, 'description');
getGlobalVar($folder_entered_at, 'entered_at');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($folder_entered_at == "")
  { $folder_entered_at = time(); }
else
  { $folder_entered_at = strtotime($file_entered_at); }


//save to database
$rec = array();
$rec['folder_name'] = $folder_name;
$rec['folder_description'] = $folder_description;
$rec['on_what_table'] = $on_what_table;
$rec['on_what_id'] = $on_what_id;
$rec['entered_at'] = $folder_entered_at;
$rec['entered_by'] = $session_user_id;


$folder_plugin_params = array($rec);
do_hook_function('file_add_folder', &$folder_plugin_params);


if($file_plugin_params['external_id']) { 
	$rec['external_id'] = $file_plugin_params['external_id']; 
}

$tbl = 'owl_folders';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$file_id = $con->insert_id();


$con->close();

header("Location: " . $http_site_root . $return_url);

/**
 * $Log: new_folder-2.php,v $
 * Revision 1.2  2005/09/23 20:42:06  daturaarutad
 * tidy up comments
 *
 * Revision 1.1  2005/04/28 15:47:10  daturaarutad
 * new files
 *
 */
?>
