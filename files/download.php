<?php
/** 
 * files/download.php - This file downloads files from server 
 * 
 * Files that have been stored on the server are downloaded to 
 * the user's default location.
 * 
 * $Id: download.php,v 1.7 2005/01/09 02:32:28 vanmer Exp $
 */ 

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'mime/mime-array.php');

$file_id = $_GET['file_id'];
$on_what_id=$file_id;

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';


$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from files where file_id = $file_id";

$rst = $con->execute($sql);

// database errors ???
if ($rst) {
  // no database errors
  if ( !$rst->EOF ) {
    $file_pretty_name = $rst->fields['file_pretty_name'];
    $file_filesystem_name = $rst->fields['file_filesystem_name'];
    $file_description = $rst->fields['file_description'];
    $file_type = $rst->fields['file_type'];
    $entered_at = $con->userdate($rst->fields['entered_at']);
    $entered_by = $con->userdate($rst->fields['entered_by']);
    $file_size = pretty_filesize($rst->fields['file_size']);
  } else {
    $file_pretty_name = '';
    $file_filesystem_name = '';
    $file_description = '';
    $file_type = '';
    $entered_at = '';
    $entered_by = '';
    $file_size = '';
  }
  $rst->close();
} else {
  // yes - log database errors
  db_error_handler($con, $sql);
}

$con->close();

// make sure we have a proper mime type
if ( !$file_type ) {
  $file_type = mime_get_type ( $file_filesystem_name );
}

$disposition = "attachment"; // "inline" to view file in browser or "attachment" to download to hard disk

// open the file in binary mode
$file_to_open = $file_storage_directory . $file_filesystem_name;
$file_original_name = str_replace($file_id . '_', '', $file_filesystem_name);

//split up mimetype into greater/less mimetypes for use in SendDownloadHeaders
$mime_type_array=explode('/',$file_type);

//send download headers, do not force pop-up download dialog on browser
SendDownloadHeaders($mime_type_array[0],$mime_type_array[1], $file_original_name, false, filesize($file_to_open));
//open and output file contents
$fp = fopen($file_to_open, 'rb');
fpassthru($fp);
exit();

/** 
 * $Log: download.php,v $
 * Revision 1.7  2005/01/09 02:32:28  vanmer
 * - changed to use SendDownloadHeaders function instead of custom headers
 *
 * Revision 1.6  2004/08/03 16:54:14  cpsource
 * - Check for errors in database fetch
 *   Support proper mime type for file download if it's unspecified
 *     in database via new routine mime_get_type
 *   Change undefined user to entered_by
 *
 * Revision 1.5  2004/07/30 12:59:19  cpsource
 * - Handle $msg in the standard way
 *   Fix problem with Date field displaying garbage because
 *     date was undefined, and if E_ALL is turned on.
 *
 * Revision 1.4  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.3  2004/03/04 00:05:13  maulani
 * *** empty log message ***
 *
 * Revision 1.2  2004/03/03 23:53:42  maulani
 * - changed to record file type (mime)
 * - Usestype when downloading file
 * - add phpdoc
 *
 * 
 */ 
?> 
