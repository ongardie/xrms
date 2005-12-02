<?php
/**
 * utils-files.php - this file contains file and filesystem utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @package XRMS_API
 *
 * @author Walter Torres
 *
 * $Id: utils-files.php,v 1.12 2005/12/02 01:49:23 vanmer Exp $
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
  * @category file_methods
  * @access public
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

        $_objFile->fileRename( $GLOBALS['file_storage_directory'] . $_newName );
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

        global $include_directory;

        require_once $include_directory . 'classes/File/file_upload.php';

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
  * - last_modified_on       - when was record modified - this will be the same as 'entered_at'
  * - last_modified_by       - who modified the record  - this will be the same as 'entered_by'
  * - file_record_status     - indicates record status; 'a' Active - 'd' Deleted
  *
  * This field is updated after the record is created, since we need to new record ID
  * - file_filesystem_name   - created name [file_id]_[random string]
  *
  * @name add_file()
  * @category file_table_methods
  * @access public
  *
  * @static
  * @final
  *
  * @param  adodbconnection $con handle to the database
  * @param  array  $files_data  with associative array defining file data (extract()'d inside function)
  * @param  string $file_array  name of $_FILES sub-array to use
  * @return int    $activity_id identifying newly created activity or false for failure
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

        // File data
        $rec['file_filesystem_name'] = $objUpFile->getFilename();
        $rec['file_name']            = $objUpFile->getFilename();
        $rec['file_size']            = $objUpFile->getFileSize();
        $rec['file_type']            = $objUpFile->getFileMimeType();

        // A 'pretty name' for this file for display reference and review
        $rec['file_pretty_name']   = ($file_pretty_name) ? $file_pretty_name : $objUpFile->getFilename();

        // A brief description of the file for future reference and review
        $rec['file_description']   = ($file_description) ? $file_description : '';

        // These values, if not defined, will be set by default values defined within the Database
        // Therefore they do not need to be created within this array for RECORD insertion
        $rec['on_what_table'] = ($on_what_table)  ? $on_what_table : '';
        $rec['on_what_id']    = ($on_what_id > 0) ? $on_what_id    : '';


        // Add record to FILES table
        if ( $file_id = add_file_record ( $con, $rec ) )
        {
            // Now we need to UPDATE that same record
            // We need to RENAME the 'file_filesystem_name' name with the record ID
            // and a random string for a "secure" file name
            $rec = array();
            $rec['file_id']              = $file_id;
            $rec['file_filesystem_name'] = $file_id . '_' . random_string ( 24 );

            if ( $_results = modify_file_record( $con, $rec ) )
            {
                // The file needs to be renamed to reflect "secure" file name
                rename_file ( $objUpFile->getFilename(), $rec['file_filesystem_name'] );
            }
        }
    }   // if ( $objUpFile = getFileUpLoad ( 'file1' ) )

    // Send back what we have
    return $_results;
};


// ************************************************************************

 /**
  * Add a new record to the FILES table
  * This will return a new record ID or a FALSE.
  *
  * These 'files' table fields are required.
  * This method will fail without them.
  * - file_filesystem_name   - What is the name of the file on the file system (disk)
  * - file_name              - Orignal File Name from users system
  * - file_size              - Size of Orignal File
  * NOTE: 'size' can not be derived since 'name' is not a full path name
  *
  * These fields are optional
  * - on_what_table          - what the file is attached or related to, must define 'on_what_id'
  * - on_what_id             - which ID to use for this relationship
  * - file_type              - File Type of Orignal File
  * NOTE: 'type' can not be derived since 'name' is not a full path name
  *
  * These fields are optional, they will be derived from other fields if not defined.
  * - file_pretty_name       - if not defined, derived from 'file_name'
  * - file_description       - A short description of the file
  *
  * Do not define these fields, they are auto-defined
  * - file_id                - auto increment field
  * - entered_at             - when was record created, now
  * - entered_by             - who created the record, session_id
  * - last_modified_on       - when was record modified - this will be the same as 'entered_at'
  * - last_modified_by       - who modified the record  - this will be the same as 'entered_by'
  * - file_record_status     - indicates record status; 'a' Active - 'd' Deleted
  *
  * @name add_file_record()
  * @category file_table_methods
  * @access public
  *
  * @static
  * @final
  *
  * @param  adodbconnection $con handle to the database
  * @param  array  $files_data  with associative array defining file data (extract()'d inside function)
  * @return mixed  $_results    int      Created Record ID
  *                             boolean  Indicating success or failure
  */
function add_file_record( $con, $files_data )
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

    // We need these to work
    if ( ($file_name) && ($file_size) && ($file_filesystem_name) )
    {
        //save to database
        global $session_user_id;

        // Create new RECORD array '$rec' for SQL INSERT
        $rec = array();

        // File data
        $rec['file_filesystem_name'] = $file_filesystem_name;
        $rec['file_name']            = $file_name;
        $rec['file_size']            = $file_size;
        $rec['file_type']            = $file_type;

        // These values are auto set, thay can not be modified via API
        $rec['entered_at']       = time();
        $rec['entered_by']       = $session_user_id;

        // Because this is a "create" method, these values are derived from the
        // above values and can not be modified via API
        $rec['last_modified_on'] = $rec['entered_at'];
        $rec['last_modified_by'] = $rec['entered_by'];

        // And we need to se tthis record to "Active" - 'a'
        $rec['file_record_status'] = 'a';

        // A 'pretty name' for this file for display reference and review
        $rec['file_pretty_name'] = ($file_pretty_name) ? $file_pretty_name : $file_name;

        // A brief description of the file for future reference and review
        $rec['file_description'] = ($file_description) ? $file_description : '';

        // These values, if not defined, will be set by default values defined within the Database
        // Therefore they do not need to be created within this array for RECORD insertion
        $rec['on_what_table'] = ($on_what_table)  ? $on_what_table : '';
        $rec['on_what_id']    = ($on_what_id > 0) ? $on_what_id    : '';

        // files plugin hook allows external storage of files.  see plugins/owl/README for example
        // params: (file_field_name, record associative array)
        $file_plugin_params = array('file1', $rec);
        do_hook_function('file_add_file', $file_plugin_params);

        if($file_plugin_params['external_id']) {
            $rec['external_id'] = $file_plugin_params['external_id'];
        }

        // INSERT values into table
        $tbl = 'files';
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());

        // Was there a problem?
        if ( $rst = $con->execute($ins) )
        {
            // What ID where we given
            $_results = $con->insert_id();

            // Let big brother know what we did
            add_audit_item($con, $session_user_id, 'created', $tbl, $_results, 1);
        }
        else
        {
            db_error_handler($con, $ins);
        }
    }

    // Return what we have
    return $_results;
};

 /**
  * Modifiy a record in the FILES table
  * This will return a new record ID or a FALSE.
  *
  * These 'files' table fields are required.
  * This method will fail without them.
  * - file_id            - which record to modify
  *
  * Do not define these fields, they are auto-defined
  * - last_modified_on   - when was record modified - this will be the same as 'entered_at'
  * - last_modified_by   - who modified the record  - this will be the same as 'entered_by'
  *
  * All other fields are optional.
  * include only firlds that need to be UPDATEd
  *
  * @name modify_file_record()
  * @category file_table_methods
  * @access public
  *
  * @static
  * @final
  *
  * @param  adodbconnection $con handle to the database
  * @param  array    $files_data  with associative array defining file data (extract()'d inside function)
  * @return boolean  $_results  T on success, F on failure
  */
function modify_file_record( $con, $files_data )
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

    // Make sure we have an ID to work with
    if ( $files_data['file_id'] )
    {
        //save to database
        global $session_user_id;

        // update the file record
        $tbl = 'files';
        $sql = 'SELECT * FROM ' . $tbl . ' WHERE file_id = ' . $files_data['file_id'];
        $rst = $con->execute($sql);

        // Track who and when this record was modified
        $files_data['last_modified_on'] = time();
        $files_data['last_modified_by'] = $session_user_id;

        $upd = $con->GetUpdateSQL($rst, $files_data, false, get_magic_quotes_gpc());

        if ( $_results = $con->execute($upd) )
        {
            // Let big brother know what we did
            add_audit_item($con, $session_user_id, 'updated', $tbl, $files_data['file_id'], 1);
        }
        else
        {
            db_error_handler($con, $ins);
        }
    }

    // Return what we have
    return $_results;
};


 /**
  * Retrieves Found Files records
  * This will return a recordset or a FALSE.
  *
  * At least one field must be defined in file_data
  * - file_name              - Orignal File Name from users system
  * - entered_at             - when was record created, this must be defined if 'on_what_table' is not
  * - on_what_table          - what the file is attached or related to, must define 'on_what_id'
  * - on_what_id             - which ID to use for this relationship
  *
  * These fields are optional, some may be derived from other fields if not defined.
  * - file_pretty_name       - if not defined, derived from 'file_name'
  * - file_description       - A short description of the file
  *
  * Do not define these fields, they are auto-defined
  * - file_id                - auto increment field
  * - file_size              - derived from uploaded file
  * - file_type              - derived from uploaded file
  * - entered_by             - who created the record
  * - last_modified_on       - when was record modified - this will be the same as 'entered_at'
  * - last_modified_by       - who modified the record  - this will be the same as 'entered_by'
  * - file_record_status     - indicates record status; 'a' Active - 'd' Deleted
  *
  * @name get_file_records()
  * @category file_table_methods
  * @access public
  *
  * @static
  * @final
  *
  * @param adodbconnection $con handle to the database
  * @param  array  $files_data  with associative array defining file data
  * @param  string $file_array  name of $_FILES sub-array to use
  * @param boolean $allow_acl_restriction specifying if extra IN clause should be added with list of allowed files from the ACL (defaults to true, add extra security clause)
  * @return mixed  $_results    recordset Found Records
  *                             boolean   Indicating success or failure
  */
function get_file_records( $con, $files_data, $allow_acl_restriction=true )
 {
    global $session_user_id;
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

    // We need one or the other to run this
    //if ( ($files_data['entered_by'])  ||  ($files_data['on_what_table']) )
    if (count($files_data))
    {
        // build where clause from files_data
        foreach($files_data as $field => $value) {
            $where_clause[] = "$field = '$value'";
        }

        if ($where_clause AND $allow_acl_restriction) {
            $list=acl_get_list($session_user_id, 'Read', false, 'files');
            if ($list AND is_array($list)) {
                $where_clause[]="file_id IN (".implode(",",$list).")";
            } elseif (!$list) {
                $where_clause[]="1 = 2";
            }
        }

        $where_sql = 'WHERE ' . join(" AND ", $where_clause);

        // Define SQL
        $file_sql = "SELECT * from files, users
                            $where_sql
                        AND files.entered_by = users.user_id
                        AND file_record_status = 'a'
                ORDER BY entered_at";

// change this?
        // return all found files for this table type
        if (strlen($files_data['on_what_table']) > 0)
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
 * Revision 1.12  2005/12/02 01:49:23  vanmer
 * - added XRMS_API package tag
 *
 * Revision 1.11  2005/10/05 21:53:49  vanmer
 * - changed ACL control of files recordset to add a false clause if no file list was provided
 *
 * Revision 1.10  2005/10/04 23:02:26  vanmer
 * - added parameter to control ACL security parameter to files SQL
 * - added ACL list call to get list of allowed files added by default to all file queries
 *
 * Revision 1.9  2005/10/01 05:12:23  jswalter
 *  - removed legacy code 'file_limit_sql'
 *
 * Revision 1.8  2005/09/23 21:00:46  daturaarutad
 * update get_file_records so that any field can be passed in as search criteria
 *
 * Revision 1.7  2005/09/22 02:42:04  jswalter
 *  - modified 'rename_file()' to reflect changes in File Class
 *
 * Revision 1.6  2005/07/22 18:09:51  braverock
 * - remove $class_directory and replace with $include_directory
 *
 * Revision 1.5  2005/07/17 16:02:57  maulani
 * - Remove runtime pass-by-reference.  Function is defined as pass-by-reference
 *
 * Revision 1.4  2005/07/08 19:21:37  jswalter
 *  - clarified comments in 'add_file()'
 *  - removed the DB INSERT from 'add_file()', now uses new external method
 *  - removed the DB MODIFY from 'add_file()', now uses new external method
 *  - created 'add_file_record()' to handle File Table INSERT
 *  - created 'modify_file_record()' to handle File Table MODIFIY
 *  - corrected modified data field name
 *  - added 'a' to record status definition
 *  - added paramater comments to 'get_file_records()'
 *
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
