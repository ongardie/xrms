<?php
/** 
 * files/download.php - This file downloads files from server 
 * 
 * Files that have been stored on the server are downloaded to 
 * the user's default location.
 * 
 * $Id: download.php,v 1.5 2004/07/30 12:59:19 cpsource Exp $
 */ 

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$file_id = $_GET['file_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from files where file_id = $file_id";

$rst = $con->execute($sql);

if ($rst) {
	$file_pretty_name = $rst->fields['file_pretty_name'];
	$file_filesystem_name = $rst->fields['file_filesystem_name'];
	$file_description = $rst->fields['file_description'];
	$file_type = $rst->fields['file_type'];
	$entered_at = $con->userdate($rst->fields['entered_at']);
	$username = $rst->fields['username'];
	$file_size = pretty_filesize($rst->fields['file_size']);
	$rst->close();
}

$con->close();

$disposition = "attachment"; // "inline" to view file in browser or "attachment" to download to hard disk

// open the file in binary mode
$file_to_open = $file_storage_directory . $file_filesystem_name;
$file_original_name = str_replace($file_id . '_', '', $file_filesystem_name);

if (isset($_SERVER["HTTPS"])) {
	header("Pragma: ");
	header("Cache-Control: ");
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
	header("Cache-Control: post-check=0, pre-check=0", false);
} else if ($disposition == "attachment") {
	header("Cache-control: private");
} else {
	header("Cache-Control: no-cache, must-revalidate");
	header("Pragma: no-cache");
}

header("Content-Disposition:$disposition; filename=\"".trim(htmlentities($file_original_name))."\"");
header("Content-Description: ".trim(htmlentities($file_original_name)));
header("Content-Length: ".(string)(filesize($file_to_open)));
header("Content-Type: $file_type");

$fp = fopen($file_to_open, 'rb');
fpassthru($fp);
exit();

/** 
 * $Log: download.php,v $
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
