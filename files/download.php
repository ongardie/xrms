<?php
/**
 * files/download.php - This file downloads files from server
 *
 * Files that have been stored on the server are downloaded to
 * the user's default location.
 *
 * $Id: download.php,v 1.20 2006/01/02 23:03:52 vanmer Exp $
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


$con = get_xrms_dbconnection();

$sql = "select * from files where file_id = $file_id";

$rst = $con->execute($sql);

// database errors ???
if ($rst) {
  // no database errors
  if ( !$rst->EOF ) {
    $file_pretty_name       = $rst->fields['file_pretty_name'];
    $file_filesystem_name   = $rst->fields['file_filesystem_name'];
    $file_description       = $rst->fields['file_description'];
    $file_type              = $rst->fields['file_type'];
    $entered_at             = $con->userdate($rst->fields['entered_at']);
    $entered_by             = $con->userdate($rst->fields['entered_by']);
    $file_size              = pretty_filesize($rst->fields['file_size']);
    $file_size_actual       = $rst->fields['file_size'];
  } else {
    $file_pretty_name       = '';
    $file_filesystem_name   = '';
    $file_description       = '';
    $file_type              = '';
    $entered_at             = '';
    $entered_by             = '';
    $file_size              = '';
    $file_size_actual       = 0;
  }
  $rst->close();
} else {
  // yes - log database errors
  db_error_handler($con, $sql);
}

$con->close();

// make sure we have a proper mime type
if ( !$file_type ) {
    //if (!function_exists('mime_content_type') ) {
        // this version of PHP doesn't have the mime functions
        // compiled in, so load our drop-in replacement function
        // instead
        require_once($include_directory.'mime/mime-array.php');
    //}
    $file_type = mime_content_type_ ( $file_filesystem_name );
}

$disposition = "attachment"; // "inline" to view file in browser or "attachment" to download to hard disk


// files plugin hook
// hook is expected to exit() after sending the file to the browser!
$plugin_params = array('file_info' => $rst->fields);
do_hook_function('file_download_file', $plugin_params);


$file_to_open = $file_storage_directory . $file_filesystem_name;
$file_original_name = str_replace($file_id . '_', '', $file_filesystem_name);




//split up mimetype into greater/less mimetypes for use in SendDownloadHeaders
$mime_type_array=explode('/',$file_type);

//send download headers, don't force pop-up download dialog on browser
SendDownloadHeaders($mime_type_array[0],$mime_type_array[1], $file_original_name, false, $file_size_actual);

$chunksize=1*(1024*1024);
	
//open and output file contents
if (is_file($file_to_open)){
    $fp = fopen($file_to_open, 'rb');
    if ($fp) {
        while (!feof($fp)) {
            $buffer = fread($fp, $chunksize);
            print $buffer;
        } //end while
        fclose ($fp);
    } else {
        //file open failed
        //should put an error here
    }
} //end is_file test, should error if this isn't a file

exit();

/**
 * $Log: download.php,v $
 * Revision 1.20  2006/01/02 23:03:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.19  2005/11/09 22:35:42  daturaarutad
 * add hooks for files plugin
 *
 * Revision 1.18  2005/09/23 19:40:49  daturaarutad
 * updated for file plugin (owl support)
 *
 * Revision 1.17  2005/07/08 15:14:25  braverock
 * - only recheck mime type if it isn't already set
 *
 * Revision 1.16  2005/07/06 18:17:36  braverock
 * - change back to custom function as php std mime_content_type fn
 *   causes problems on several configs
 *
 * Revision 1.15  2005/07/06 16:25:32  ycreddy
 * Fixed the syntax errors - missing closing ) in if statements
 *
 * Revision 1.14  2005/07/06 15:09:50  braverock
 * - add is_file and opened file tests to file handling
 *   @todo need more error handling to report to the user
 *
 * Revision 1.13  2005/07/06 15:04:15  braverock
 * - fix $chunksize in fread loop
 *
 * Revision 1.12  2005/07/06 14:59:31  braverock
 * - change to use php standard mime_content_type fn or fall back to our replacement fn
 *
 * Revision 1.11  2005/07/06 14:57:58  braverock
 * - use an fread loop instead of fpassthru to get around
 *   problem with large files on windows server
 *
 * Revision 1.10  2005/06/22 20:38:48  vanmer
 * - no longer force download query, instead allow inline download, and provide correct mime type when downloading
 *
 * Revision 1.9  2005/05/19 13:14:55  maulani
 * - Remove trailing whitespace
 *
 * Revision 1.8  2005/03/02 21:23:25  vanmer
 * - changed to force pop-up of download dialogue, instead of showing attachment inline
 *
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
