<?php
/** 
 * files/new-2.php - This file uploads new files to the server 
 * 
 * Files that are uploaded to the server are moved to the 
 * correct folder and a database entry is made.
 * 
 * $Id: new-2.php,v 1.3 2004/03/04 00:05:13 maulani Exp $
 */ 

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$on_what_table = $_POST['on_what_table'];
$on_what_id = $_POST['on_what_id'];
$return_url = $_POST['return_url'];

$file_pretty_name = $_POST['file_pretty_name'];
$file_description = $_POST['file_description'];

$filename = $_FILES['file1']['name'];
$filetype = $_FILES['file1']['type'];
$filesize = $_FILES['file1']['size'];
$filetmpname = $_FILES['file1']['tmp_name'];

$file_pretty_name = (strlen(trim($file_pretty_name)) > 0) ? $file_pretty_name : $filename;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "insert into files (file_pretty_name, file_description, file_filesystem_name, file_size, file_type, on_what_table, on_what_id, entered_at, entered_by) values (" . $con->qstr($file_pretty_name, get_magic_quotes_gpc()) . ", " . $con->qstr($file_description, get_magic_quotes_gpc()) . ", " . $con->qstr($filename, get_magic_quotes_gpc()) . ", $filesize, " . $con->qstr($filetype, get_magic_quotes_gpc()) . ", " . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ", $on_what_id, " . $con->dbtimestamp(mktime()) . ", $session_user_id)";
$con->execute($sql);

$file_id = $con->insert_id();

move_uploaded_file($_FILES['file1']['tmp_name'], $file_storage_directory . $file_id . '_' . $filename);

// update the file record

$sql = "update files set file_filesystem_name = '" . $file_id . '_' . $filename . "' where file_id = $file_id";
$con->execute($sql);

$con->close();

header("Location: " . $http_site_root . $return_url);

/** 
 * $Log: new-2.php,v $
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
