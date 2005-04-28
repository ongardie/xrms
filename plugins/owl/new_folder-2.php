<?php
/**
 * owl/new_folder-2.php - This file adds new folders to the system
 *
 * $Id: new_folder-2.php,v 1.1 2005/04/28 15:47:10 daturaarutad Exp $
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
 * Revision 1.1  2005/04/28 15:47:10  daturaarutad
 * new files
 *
 * Revision 1.15  2005/04/10 16:19:02  maulani
 * - remove errant test code
 *
 * Revision 1.14  2005/04/10 11:44:22  maulani
 * - Retain file type if not found in lookup table
 *
 * Revision 1.13  2005/01/13 18:51:23  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.12  2004/08/03 18:05:56  cpsource
 * - Set mime type when database entry is created
 *
 * Revision 1.11  2004/07/30 12:59:19  cpsource
 * - Handle $msg in the standard way
 *   Fix problem with Date field displaying garbage because
 *     date was undefined, and if E_ALL is turned on.
 *
 * Revision 1.10  2004/07/10 13:37:43  braverock
 * - fixed timestamp on new file attach
 *
 * Revision 1.9  2004/07/07 22:06:16  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.8  2004/06/15 14:26:56  gpowers
 * - correct time formats
 *
 * Revision 1.7  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.6  2004/06/03 16:23:48  braverock
 * - fixed typo
 *
 * Revision 1.5  2004/03/26 23:52:47  maulani
 * - bug fix 923755 === unbalanced parenthesis
 *   fix submitted by anonymous
 *
 * Revision 1.4  2004/03/22 03:16:55  braverock
 * - added check for error codes on file upload
 *   - fixes SF bug 839574
 *
 * Revision 1.3  2004/03/04 00:05:13  maulani
 * *** empty log message ***
 *
 * Revision 1.2  2004/03/03 23:53:42  maulani
 * - changed to record file type (mime)
 * - Usestype when downloading file
 * - add phpdoc
 */
?>
