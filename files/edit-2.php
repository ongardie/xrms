<?php
/**
 * Insert Updated File information into the database
 *
 * $Id: edit-2.php,v 1.2 2004/03/24 12:26:34 braverock Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$file_id = $_POST['file_id'];
$file_pretty_name = $_POST['file_pretty_name'];
$file_description = $_POST['file_description'];
$file_entered_at = $_POST['file_entered_at'];

$filename = $_FILES['file1']['name'];
$filetype = $_FILES['file1']['type'];
$filesize = $_FILES['file1']['size'];
$filetmpname = $_FILES['file1']['tmp_name'];
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($filename != "")
  {
  $sql = "update files set file_pretty_name = " . $con->qstr($file_pretty_name, get_magic_quotes_gpc()) . ", file_description = " . $con->qstr($file_description, get_magic_quotes_gpc()) . ", file_filesystem_name = " . $con->qstr($filename, get_magic_quotes_gpc()) . ", entered_at = " . $con->qstr($file_entered_at, get_magic_quotes_gpc()) . ", file_size = " . $con->qstr($filesize, get_magic_quotes_gpc()) . " where file_id = $file_id";

  $con->execute($sql);

  move_uploaded_file($_FILES['file1']['tmp_name'], $file_storage_directory . $file_id . '_' . $filename);

  // update the file record

  $sql = "update files set file_filesystem_name = '" . $file_id . '_' . $filename . "' where file_id = $file_id";
  }
else
  {
  $sql = "update files set file_pretty_name = " . $con->qstr($file_pretty_name, get_magic_quotes_gpc()) . ", entered_at = " . $con->qstr($file_entered_at, get_magic_quotes_gpc()) . ", file_description = " . $con->qstr($file_description, get_magic_quotes_gpc()) . " where file_id = $file_id";
  }
$con->execute($sql);
$con->close();

header("Location: one.php?msg=saved&file_id=$file_id");

/**
 * $Log: edit-2.php,v $
 * Revision 1.2  2004/03/24 12:26:34  braverock
 * - allow editing of more file proprerties
 * - updated code provided by Olivier Colonna of Fontaine Consulting
 *
 */
?>