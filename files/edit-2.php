<?php
/**
 * Insert Updated File information into the database
 *
 * $Id: edit-2.php,v 1.3 2004/06/12 07:20:40 introspectshun Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$file_id = $_POST['file_id'];
$file_pretty_name = $_POST['file_pretty_name'];
$file_description = $_POST['file_description'];
$file_entered_at = $_POST['file_entered_at'];

$file_name = $_FILES['file1']['name'];
$file_type = $_FILES['file1']['type'];
$file_size = $_FILES['file1']['size'];
$file_tmp_name = $_FILES['file1']['tmp_name'];
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($file_name != "") {
  $sql = "SELECT * FROM files WHERE file_id = $file_id";
  $rst = $con->execute($sql);

  $rec = array();
  $rec['file_pretty_name'] = $file_pretty_name;
  $rec['file_description'] = $file_description;
  $rec['file_filesystem_name'] = $file_name;
  $rec['entered_at'] = $file_entered_at;
  $rec['file_size'] = $file_size;

  $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
  $con->execute($upd);

  move_uploaded_file($_FILES['file1']['tmp_name'], $file_storage_directory . $file_id . '_' . $file_name);

  // update the file record
  $sql = "SELECT * FROM files WHERE file_id = $file_id";
  $rst = $con->execute($sql);

  $rec = array();
  $rec['file_filesystem_name'] = $file_id . '_' . $file_name;

  $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
  $con->execute($upd);
} else {
  $sql = "SELECT * FROM files WHERE file_id = $file_id";
  $rst = $con->execute($sql);

  $rec = array();
  $rec['file_pretty_name'] = $file_pretty_name;
  $rec['entered_at'] = $file_entered_at;
  $rec['file_description'] = $file_description;

  $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
  $con->execute($upd);
}

$con->close();

header("Location: one.php?msg=saved&file_id=$file_id");

/**
 * $Log: edit-2.php,v $
 * Revision 1.3  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.2  2004/03/24 12:26:34  braverock
 * - allow editing of more file proprerties
 * - updated code provided by Olivier Colonna of Fontaine Consulting
 *
 */
?>