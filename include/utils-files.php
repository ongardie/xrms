<?php
/**
 * utils-files.php - this file contains file and filesystem utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Walter Torres
 *
 * $Id: utils-files.php,v 1.2 2005/07/06 18:42:23 jswalter Exp $
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
// ************************************************************************

/**
 * $Log: utils-files.php,v $
 * Revision 1.2  2005/07/06 18:42:23  jswalter
 *  - added 'rename_file()'
 *  - added 'getFileUpload()'
 *
 * Revision 1.1  2005/06/30 21:53:43  jswalter
 *  - initial commit
 *
 */
?>
