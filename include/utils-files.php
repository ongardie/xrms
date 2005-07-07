<?php
/**
 * utils-files.php - this file contains file and filesystem utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Walter Torres
 *
 * $Id: utils-files.php,v 1.3 2005/07/07 16:49:39 jswalter Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

// ************************************************************************
// ************************************************************************

 /**
  * Process to rename a File
  *
  * This will return a boolean indicating success or failure
  *
  * @name fileRename()
  * @access public
  * @category file_methods
  *
  * @static
  * @final
  *
  * @param  string   $_orgName   Original name of file, with full path
  * @param  string   $_orgName   new name of file, file name ONLY
  * @return boolean  $_retVal    TRUE upon success, or FALSE upon failure of some kind
  *
  */
function rename_file ( $_orgName = null, $_newName = null )
{
    if ( $_orgName )
    {
        $_objFile = new File($GLOBALS['file_storage_directory'] . $_orgName);

        $_objFile->renameFile( $GLOBALS['file_storage_directory'] . $_newName );
    }
};


 /**
  * Process a File Upload
  *
  * This will return 1 of 2 possible types:
  *  1) Boolean FALSE, we failed for some reason
  *  2) Array   Data Array of uploaded file info
  *
  * @name getFileUpLoad()
  * @access public
  * @category file_methods
  *
  * @static
  * @final
  *
  * @param  string  $_upload_name   Name of $_FILES sub-array to process
  * @return mixed   $_retVal        Data Array found item, or FALSE upon failure of some kind
  *
  */
function getFileUpLoad ( $_upload_name = null )
{
   /**
    * Default error message container
    *
    * @var string $_msg Eror message for this operation, if any
    * @access private
    * @static
    */
    global $msg;

   /**
    * Default return value
    *
    * Returns constructed Data Array of Uploaded File info or boolean upon failure
    * Default value is set at TRUE
    *
    * @var mixed $_retVal Indicates if Object was created or not
    * @access private
    * @static
    */
    $_retVal = true;

    // Make sure we have something to work on
    if ( $_upload_name )
    {
        // Did we get a file to process?
        if ( empty($_FILES[$_upload_name]['name']) )
        {
            $msg .= _("Upload File Not Selected") . '. ';
            $msg .= '<br />';
            return false;
        }

        global $class_directory;

        require_once $class_directory . 'classes/File/file_upload.php';

        // Create new Class
        $objUpFile = new file_upload( $_upload_name );

        if ( $objUpFile->getErrorCode() )
        {
            $msg .= _("Could not locate Uploaded File") . '. ';
            $msg .= $objUpFile->getErrorMsg();
            $msg .= '<br />';
            return false;
        }
        // Where do we want this file sent to
        $objUpFile->setDestDir ( $GLOBALS['file_storage_directory'] );

        $_SESSION['uploadPath'] = serialize($GLOBALS['file_storage_directory'] );

        if ( $objUpFile->getErrorCode() )
        {
            $msg .= _("Could not set Upload Directory") . ': ';
            $msg .= $objUpFile->getErrorMsg();
            $msg .= '<br />';
            return false;
        }

        // Now process uploaded file
        $objUpFile->processUpload();

        if ( $objUpFile->getErrorCode() )
        {
            $msg .= _("Could not process upload file") . ': ';
            $msg .= $objUpFile->getErrorMsg();
            $msg .= '<br />';
            return false;
        }
    }   //     if ( $_upload_name )
    else
    {
        $msg .= _("No File Upload information Given") . '.';
        $msg .= '<br />';
        return false;
    }

    // Send back what we have
    return $objUpFile;
};

// ************************************************************************
// File DB access methods

/**
 *
 * Adds a File and file record XRMS based on data in the associative array,
 * returning the id of the newly created activity if successful.
 *
 * These 'files' table fields are required.
 * This method will fail without them.
 * - file_name              - Orignal File Name from users system
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - file_pretty_name       - if not defined, derived from 'file_name'
 * - file_description       - A short description of the file
 * - on_what_table          - what the file is attached or related to
 * - on_what_id             - which ID to use for this relationship
 *
 * Do not define these fields, they are auto-defined
 * - file_id                - auto increment field
 * - file_size              - derived from uploaded file
 * - file_type              - derived from uploaded file
 * - entered_at             - when was record created
 * - entered_by             - who created the record
 * - last_modified_at       - when was record modified - this will be the same as 'entered_at'
 * - last_modified_by       - who modified the record  - this will be the same as 'entered_by'
 * - file_record_status     - indicates record status; 'a' Active - 'd' Deleted
 *
 * This field is updated after the record is created, since we need to new record ID
 * - file_filesystem_name   - created name [file_id]_[random string]
 *
 *
 * @param adodbconnection $con handle to the database
 * @param array  $files_data  with associative array defining file data (extract()'d inside function)
 * @param string $file_array  name of $_FILES sub-array to use
 *
 * @return integer $activity_id identifying newly created activity or false for failure
 */
function add_file($con, $files_data, $file_array = null )
{
   /**
    * Variable local $_results
    *
    * Indicates if we succedded or not
    * Default value: FALSE
    *
    * @var bool $_results indicates success or failure
    *
    * @access private
    * @static
    */
    $_results = false;

    // Right off the bat, if these are not set, we can't do anything!
    if ( (! $con)  ||  (! $files_data ) )
        return false;

    //save to database
    global $session_user_id;

    // first thing to do is process the uploaded file
    // if it successed, we can add the data to the DB
    if ( $objUpFile = getFileUpLoad ( $file_array ) )
    {
        // Turn activity_data array into variables
        extract($files_data);

        // Create new RECORD array '$rec' for SQL INSERT
        $rec = array();

        // These values are auto set, thay can not be modified via API
        $rec['entered_at']       = time();
        $rec['entered_by']       = $session_user_id;

        // Because this is a "create" method, these values are derived from the
        // above values and can not be modified via API
        $rec['last_modified_at'] = $rec['entered_at'];
        $rec['last_modified_by'] = $rec['entered_by'];

        // A 'pretty name' for this file for display reference and review
        $rec['file_pretty_name']   = ($file_pretty_name) ? $file_pretty_name : $objUpFile->getFilename();

        // A brief description of the file for future reference and review
        $rec['file_description']   = ($file_description) ? $file_description : '';

        // File data
        $rec['file_name']            = $objUpFile->getFilename();
        $rec['file_size']            = $objUpFile->getFileSize();
        $rec['file_type']            = $objUpFile->getFileMimeType();

        // These values, if not defined, will be set by default values defined within the Database
        // Therefore they do not need to be created within this array for RECORD insertion
        if ($on_what_table)        { $rec['on_what_table']        = $on_what_table; }
        if ($on_what_id > 0)       { $rec['on_what_id']           = $on_what_id; }

        // Since this a Add, we will default this field to 'a'
        $rec['file_record_status'] = 'a';

        // files plugin hook allows external storage of files.  see plugins/owl/README for example
        // params: (file_field_name, record associative array)
        $file_plugin_params = array('file1', $rec);
        do_hook_function('file_add_file', &$file_plugin_params);

        if($file_plugin_params['external_id']) {
            $rec['external_id'] = $file_plugin_params['external_id'];
        }

        // INSERT values into table
        $tbl = 'files';
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        $rst=$con->execute($ins);

        // Was there a problem?
        if (!$rst) { db_error_handler($con, $ins); return false; }

        // What ID where we given
        $file_id = $con->insert_id();

        // Let big brother know what we did
        add_audit_item($con, $session_user_id, 'created', 'files', $file_id, 1);

        // Now we need to UPDATE that same record
        // update the file record
        $sql = "SELECT * FROM files WHERE file_id = $file_id";
        $rst = $con->execute($sql);

        // create a random string for a "secure" file name
        $_secure_name = random_string ( 24 );

        // We need to RENAME the 'file_filesystem_name' name with the record ID
        $rec = array();
        $rec['file_filesystem_name'] = $file_id . '_' . $_secure_name;

        $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
        $con->execute($upd);
        $con->close();

        // The file needs to be renamed to add the record index to it
        rename_file ( $objUpFile->getFilename(), $rec['file_filesystem_name'] );

        // We succedded
        $_results = true;

    }   // if ( $objUpFile = getFileUpLoad ( 'file1' ) )

    // Send back what we have
    return $_results;
};


/**
 *
 * Retrieves a Found Files records
 * This will return a recordset or a FALSE.
 *
 * These 'files' table fields are required.
 * This method will fail without them.
 * - file_name              - Orignal File Name from users system
 *
 * These fields are conditioanlly required
 * - entered_at             - when was record created, thi smust be defined if 'on_what_table' is not
 * - on_what_table          - what the file is attached or related to, must define 'on_what_id'
 * - on_what_id             - which ID to use for this relationship
 *
 * These fields are optional, some may be derived from other fields if not defined.
 * - file_pretty_name       - if not defined, derived from 'file_name'
 * - file_description       - A short description of the file
 *
 * These fields not used, they will be ignored
 * - file_id                - auto increment field
 * - file_size              - derived from uploaded file
 * - file_type              - derived from uploaded file
 * - entered_by             - who created the record
 * - last_modified_at       - when was record modified - this will be the same as 'entered_at'
 * - last_modified_by       - who modified the record  - this will be the same as 'entered_by'
 * - file_record_status     - indicates record status; 'a' Active - 'd' Deleted
 *
 * @param adodbconnection $con handle to the database
 * @param array  $files_data  with associative array defining file data (extract()'d inside function)
 * @param string $file_array  name of $_FILES sub-array to use
 *
 * @return mixed $_results  recordset Found Records
                            boolean   Indicating success or failure
 */
function get_file_records( $con, $files_data )
{
    // Right off the bat, if these are not set, we can't do anything!
    if ( (! $con)  ||  (! $files_data) )
        return false;

   /**
    * Variable local $_results
    *
    * Indicates if we succedded or not
    * Default value: FALSE
    *
    * @var bool $_results indicates success or failure
    *
    * @access private
    * @static
    */
    $_results = false;

    // Turn activity_data array into variables
    extract($files_data);

    // We need one or the other to run this
    if ( ($entered_by)  ||  ($on_what_table) )
    {
        // Find files by table type
        if (strlen($on_what_table) > 0)
        {
            $var_sql = "WHERE on_what_table = '$on_what_table'
                        AND on_what_id = '$on_what_id' ";
        }
        // or find files by user ID
        else
        {
            $var_sql = "WHERE files.entered_by = '$entered_by' ";
        }

        // Define SQL
        $file_sql = "SELECT * from files, users
                            $var_sql
                        AND files.entered_by = users.user_id
                        AND file_record_status = 'a'
                        $file_limit_sql
                ORDER BY entered_at";

        // return all found files for this table type
        if (strlen($on_what_table) > 0)
        {
            $_results = $con->execute($file_sql);
        }
        // return only the first 5 records for this user ID
        else
        {
            $_results = $con->SelectLimit($file_sql, 5, 0);
        }

        // any errors ???
        if ( ! $_results )
        {
            // yep - report it
            echo '<pre>';
            print_r($con);
            echo '</pre>';
            db_error_handler($con, $file_sql);
        }

    }

    // Return what we have
    return $_results;

};







// ************************************************************************
// ************************************************************************

/**
 * $Log: utils-files.php,v $
 * Revision 1.3  2005/07/07 16:49:39  jswalter
 *  - added 'add_file()' API method
 *  - added 'get_file_records()' method
 *
 * Revision 1.2  2005/07/06 18:42:23  jswalter
 *  - added 'rename_file()'
 *  - added 'getFileUpload()'
 *
 * Revision 1.1  2005/06/30 21:53:43  jswalter
 *  - initial commit
 *
 */
?>
