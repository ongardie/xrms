<?php
/**
 * files/new-2.php - This file uploads new files to the server
 *
 * Files that are uploaded to the server are moved to the
 * correct folder and a database entry is made.
 *
 * $Id: new-2.php,v 1.12 2004/08/03 18:05:56 cpsource Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'mime/mime-array.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$on_what_table    = $_POST['on_what_table'];
$on_what_id       = $_POST['on_what_id'];
$return_url       = $_POST['return_url'];
$file_pretty_name = $_POST['file_pretty_name'];
$file_description = $_POST['file_description'];
$file_entered_at  = $_POST['file_entered_at'];
$file_name        = $_FILES['file1']['name'];
$file_type        = $_FILES['file1']['type'];
$file_size        = $_FILES['file1']['size'];
$file_tmp_name    = $_FILES['file1']['tmp_name'];

$file_pretty_name = (strlen(trim($file_pretty_name)) > 0) ? $file_pretty_name : $file_name;

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

if ($file_entered_at == "")
  { $file_entered_at = time(); }
else
  { $file_entered_at = strtotime($file_entered_at); }

//save to database
$rec = array();
$rec['file_pretty_name'] = $file_pretty_name;
$rec['file_description'] = $file_description;
$rec['file_filesystem_name'] = $file_name;
$rec['file_size'] = $file_size;
$rec['on_what_table'] = $on_what_table;
$rec['on_what_id'] = $on_what_id;
$rec['entered_at'] = $file_entered_at;
$rec['entered_by'] = $session_user_id;

$tbl = 'files';
$ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
$con->execute($ins);

$file_id = $con->insert_id();

$error = '';
if (is_uploaded_file($_FILES['file1']['tmp_name'])) {
    move_uploaded_file($_FILES['file1']['tmp_name'], $file_storage_directory . $file_id . '_' . $file_name);
} elseif ($_FILES['file1']['tmp_name'] and (strlen($file_pretty_name))){
        $error = $_FILES['file1']['tmp_name'];
        $msg .= '<p>'
             . 'A PHP error has occurred, your file was not uploaded.'
             . '<br>'
             . 'You attempted to upload file: '
             . htmlspecialchars($file_pretty_name)
             . '<br>'
             . 'If you feel that you have received this message in error, Please try to upload again.'
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
$sql = "SELECT * FROM files WHERE file_id = $file_id";
$rst = $con->execute($sql);

$rec = array();
$rec['file_filesystem_name'] = $file_id . '_' . $file_name;
$rec['file_type']            = mime_get_type ( $file_name );

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$con->close();

if ($error) {
    start_page();
    echo $error;
} else {
    header("Location: " . $http_site_root . $return_url);
}

/**
 * $Log: new-2.php,v $
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
