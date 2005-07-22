<?php
/**
 * Insert Updated File information into the database
 *
 * $Id: edit-2.php,v 1.6 2005/07/22 15:55:37 ycreddy Exp $
 */

//include required files
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$file_id = $_POST['file_id'];
getGlobalVar($return_url, 'return_url');
$session_user_id = session_check('','Update');

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

if (!$return_url) {
    $return_url="one.php?msg=saved&file_id=$file_id";
}
header("Location: " . $http_site_root . $return_url);

/**
 * $Log: edit-2.php,v $
 * Revision 1.6  2005/07/22 15:55:37  ycreddy
 * Added missing  for return url
 *
 * Revision 1.5  2005/06/30 22:12:52  vanmer
 * - changed to allow saved files to return to passed in return URL instead of always back to files/one.php
 *
 * Revision 1.4  2005/01/13 18:51:23  vanmer
 * - Basic ACL changes to allow create/delete/update functionality to be restricted
 *
 * Revision 1.3  2004/06/12 07:20:40  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 *
 * Revision 1.2  2004/03/24 12:26:34  braverock
 * - allow editing of more file proprerties
 * - updated code provided by Olivier Colonna of Fontaine Consulting
 *
 */
?>
