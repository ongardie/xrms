<?php

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$file_id = $_GET['file_id'];
$on_what_id=$file_id;
$session_user_id = session_check('','Delete');

getGlobalVar($return_url, 'return_url');

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "SELECT * FROM files WHERE file_id = $file_id";
$rst = $con->execute($sql);

$rec = array();
$rec['file_record_status'] = 'd';

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

// uncomment the following line to remove files from the filesystem
// when they are deleted by a user. Use with caution.
// system("rm storage/" . $rst->fields['file_filesystem_name'] );

$con->close();

header("Location: {$http_site_root}{$return_url}");

?>
