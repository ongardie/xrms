<?php
/**
 * files/new-2.php - This file uploads new files to the server
 *
 * Files that are uploaded to the server are moved to the
 * correct folder and a database entry is made.
 *
 * $Id: new-2.php,v 1.5 2004/03/26 23:52:47 maulani Exp $
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
$file_entered_at = $_POST['file_entered_at'];

$filename = $_FILES['file1']['name'];
$filetype = $_FILES['file1']['type'];
$filesize = $_FILES['file1']['size'];
$filetmpname = $_FILES['file1']['tmp_name'];

$file_pretty_name = (strlen(trim($file_pretty_name)) > 0) ? $file_pretty_name : $filename;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($file_entered_at == "")
  { $file_entered_at = $con->dbtimestamp(mktime()); }
else
  { $file_entered_at = $con->dbtimestamp($file_entered_at . ' 23:59:59'); }

$sql = "insert into files (file_pretty_name, file_description, file_filesystem_name, file_size, on_what_table, on_what_id, entered_at, entered_by) values (" . $con->qstr($file_pretty_name, get_magic_quotes_gpc()) . ", " . $con->qstr($file_description, get_magic_quotes_gpc()) . ", " . $con->qstr($filename, get_magic_quotes_gpc()) . ", $filesize, " . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ", $on_what_id, " . $file_entered_at . ", $session_user_id)";
$con->execute($sql);

$file_id = $con->insert_id();

if (is_uploaded_file($_FILES['file1']['tmp_name'])) {
    move_uploaded_file($_FILES['file1']['tmp_name'], $file_storage_directory . $file_id . '_' . $filename);
} elseif ($_FILES['file1']['tmp_name'] and (strlen($file_pretty_name))){
        $error = $_FILES['file1']['tmp_name'];
        $msg .= '<p>'
             . 'A PHP error has occurred, your file was not uploaded.'
             . '<br>'
             . 'You attempted to upload file: '
             . htmlspecialchars($file_pretty_name)
             . '<br>'
             . 'If you feel that you have received this message in error, Please back up and try again.'
             . '<br>'
             . 'If this error recurs, please contact your system administrator for assistance.'
             . "\n";

        switch ($error) {
            case '1':
                $msg .= '<p><b>UPLOAD_ERR_INI_SIZE: The uploaded file exceeds the upload_max_filesize directive in php.ini.</b>';
                break;
            case '2':
                $msg .= '<p><b>UPLOAD_ERR_FORM_SIZE: The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form.</b>';
                break;
            case '3':
                $msg .= '<p><b>UPLOAD_ERR_PARTIAL: The uploaded file was only partially uploaded.</b>';
                break;
            case '4':
                $msg .= '<p><b>UPLOAD_ERR_NO_FILE: No file was uploaded. </b>';
                break;
        };
    };

// update the file record

$sql = "update files set file_filesystem_name = '" . $file_id . '_' . $filename . "' where file_id = $file_id";
$con->execute($sql);

$con->close();

if ($error) {
    start_page();

    echo $msg;
} else {
    header("Location: " . $http_site_root . $return_url);
}

/**
 * $Log: new-2.php,v $
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
