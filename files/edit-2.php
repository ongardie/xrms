<?php
/**
* Insert Updated File information into the database
*
* $Id: edit-2.php,v 1.12 2006/07/10 13:18:38 braverock Exp $
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
$file_description = $_POST['file_description'];
$file_entered_at = $_POST['file_entered_at'];
// BOZZ BEGIN
$file_pretty_name = $_POST['file_pretty_name'];
// BOZZ END

$file_name = $_FILES['file1']['name'];
$file_type = $_FILES['file1']['type'];
$file_size = $_FILES['file1']['size'];
$file_tmp_name = $_FILES['file1']['tmp_name'];
$con = get_xrms_dbconnection();
// $con->debug = 1;


$error = false;

if ($file_name != "") {
    $sql = "SELECT * FROM files WHERE file_id = $file_id";
    $rst = $con->execute($sql);


    $rec = array();
    $rec['file_pretty_name'] = $file_pretty_name;
    $rec['file_description'] = $file_description;
    $rec['file_filesystem_name'] = $file_name;
    $rec['entered_at'] = $file_entered_at;
    $rec['file_size'] = $file_size;
    // BEGIN BOZZ
    // store file using plugin
    $file_to_update = $rst->fields;
    $rec['external_id'] = $file_to_update['external_id'];

    $file_plugin_params = array('file_field_name' => 'file1', 'file_info' => $rec);
    do_hook_function('file_update_file', $file_plugin_params);

    $rec = $file_plugin_params['file_info'];

    // END BOZZ

    if($file_plugin_params['error_status']) {
        $error = true;
        $msg = $file_plugin_params['error_text'];
    } else {

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);

        if(!$file_plugin_params['file_stored']) {
            move_uploaded_file($_FILES['file1']['tmp_name'], $file_storage_directory . $file_id . '_' . $file_name);

            // update the file record
            $sql = "SELECT * FROM files WHERE file_id = $file_id";
            $rst = $con->execute($sql);

            $rec = array();
            $rec['file_filesystem_name'] = $file_id . '_' . $file_name;

            $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
            $con->execute($upd);
            $msg = _('File was updated successfully.');
        }
    }

} else {

    $sql = "SELECT * FROM files WHERE file_id = $file_id";
    $rst = $con->execute($sql);

    $rec = array();
    $rec['file_pretty_name'] = $file_pretty_name;
    $rec['entered_at'] = $file_entered_at;
    $rec['file_description'] = $file_description;

    // BEGIN BOZZ
    $file_to_update = array_merge($rst->fields, $rec);

    $file_plugin_params = array('file_field_name' => null, 'file_info' => $file_to_update);

    do_hook_function('file_update_file', $file_plugin_params);

    $rec = $file_plugin_params['file_info'];
    // END BOZZ

    if($file_plugin_params['error_status']) {
        $error = true;
        $msg = $file_plugin_params['error_text'];
    } else {

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);
        $msg = _('File was updated successfully.');
    }
}


$con->close();

if (!$return_url) {
    $return_url="/files/one.php?file_id=$file_id";
}


if($error) {
    header("Location: $http_site_root/files/one.php?file_id=$file_id&msg=$msg&return_url=$return_url");
} else {
    $msg = 'saved';
    $sep = get_url_seperator($return_url);

    header("Location: " . $http_site_root . $return_url . $sep . "msg=$msg");
}

/**
 * $Log: edit-2.php,v $
 * Revision 1.12  2006/07/10 13:18:38  braverock
 * - clean indentation
 * - remove trailing whitespace
 *
 * Revision 1.11  2006/07/10 12:45:38  braverock
 * - remove call time pass by reference in do_hook_function (reference in function def)
 *
 * Revision 1.10  2006/01/02 23:03:52  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.9  2005/12/14 05:03:29  daturaarutad
 * use get_url_seperator() function
 *
 * Revision 1.8  2005/11/29 20:04:19  daturaarutad
 * check for ? in return_url
 *
 * Revision 1.7  2005/11/09 22:36:24  daturaarutad
 * add hooks for files plugin
 *
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
 */
?>