<?php

/**
 * File/file_upload.php PHP-Yacs: File Upload Class
 *
 * Class fileUpload
 *   With this Class take all the hasle out of uploading files to your server.
 *
 * $RCSfile: file_upload.php,v $
 * $Author: jswalter $
 * $Date: 2006/05/19 19:45:13 $
 *
 * @package    File_Handling
 * @subpackage Processng
 *
 * @originator  Stanislav Karchebny <berk@inbox.ru>
 * @author      Walter Torres <walter@torres.ws>
 * @contributor Aaron Van Meerten
 *
 * @version   $Revision: 1.3 $
 * @copyright (c) 2004 Walter Torres
 * @license   Licensed under the GNU GPL. For full terms see the file COPYING.
 *            OSI Certified Open Source Software
 *
 * @todo Complete phpDoc-ing this file
 *
 * @ID $Id: file_upload.php,v 1.3 2006/05/19 19:45:13 jswalter Exp $
 */

include_once(dirname(__FILE__) . '/files.php');


// =====================================================================

/*
    Main concepts:

    // Instantiate the Class and its property and methods
    // This Class it needs to know the POST FILE Var name
    $objUpFile = new file_upload( 'userfile' );

    // If this is not define, method will grab FIRST sub-array
    // of the $_FILES array
    $objUpFile = new file_upload();

    // First, check if any errors where generated.
    // No use going on if anything was wrong with the upload
    if ( $objUpFile->getErrorCode() )
    {
        echo 'error: ' . $objUpFile->getErrorMsg();
        exit;
    }

    // Now various properties can be defined, depending on what you need

    // Where do you want the new file sent to
    $objUpFile->setDestDir ( '/my/new/path' );

    // This will generate an error as well.
    // If the directory does not exist, or is not writeable...
    // First, check if any errors where generated.
    // No use going on if anything was wrong with the upload
    if ( $objUpFile->getErrorCode() )
    {
        echo 'error: ' . $objUpFile->getErrorMsg();
        exit;
    }

    // We can change the name of the file
    $objUpFile->setFileName ( 'newName' );

    // If there is a file what that name already there, can we over write it?
    $objUpFile->setFileOverWrite(true);

    // What do you want to happen if the file exists?
    // PHP kills the temp file once the script is done,
    // so we really can't turn around and ask the user for instructions.
    // I guess we could copy the temp file into our own storage, turn
    // around a user respsonce, and then process from that.

    // Now we tell the script to process the uploaded file
    $objUpFile->processUpload();

    // One last error check, and we're done!
    if ( $objUpFile->getErrorCode() )
    {
        echo 'error: ' . $objUpFile->getErrorMsg();
        exit;
    }
*/

  // ==========================================================
  // Class Constants

    /**
    * Version number of Class
    * @constant UPLOAD_VER
    *
    */
    define('UPLOAD_VER', '1.2', false);


  // ===========================================================
  // Error codes and messages

   /**
    * POST variable not defined
    * @constant FILE_SUCCEED
    *
    */
    define('POST_VAR_NOT_DEFINED', 200, false);

   /**
    * Something was wrong with File Upload
    * @constant UPLOAD_BAD_FORM
    *
    */
    define('UPLOAD_BAD_FORM', 201, false);

   /**
    * File Upload was not received
    * @constant UPLOAD_NOT_SENT
    *
    */
    define('UPLOAD_NOT_SENT', 202, false);


   /**
    * Class file_upload
    *
    * @extends file
    *
    * General HTTP based File Upload handler
    *
    *
    * @tutorial /path/to/tutorial.php Complete Class tutorial
    * @example url://path/to/example.php description
    *
    * @author Walter Torres <walter@torres.ws>
    * @version $Revision: 1.3 $
    *
    * @copyright copyright information
    * @license URL name of license
    */
class file_upload extends File
{
  // ==========================================================
  // Class Properties

   /**
    * Property private string $_postVar
    *
    * Name of $_FILES sub-array containg UPLOAD info
    * This property can not be modified
    *
    * @name $_postVar
    * @var string
    * @property string $_postVar Name of $_FILES sub-array containg UPLOAD info
    *
    * @access private
    * @since 1.15
    */
    var $_postVar = null;

   /**
    * Property private string $_actualFileName
    *
    * Actual file name from users system
    * This property can be modified for use with RENAME, for example
    *
    * @name $_actualFileName
    * @var string
    * @property string $_filePath Actual file name from users system
    *
    * @access private
    * @since 1.15
    */
    var $_actualFileName = null;



// ==========================================================
// Class methods

    // {{{ file_upload - class constructor
   /** Constructor public class file_upload( string )
    * general description
    *
    * @name file_upload
    * @author Walter Torres <walter@torres.ws>
    *
    * @category File
    * @uses class|method|global|variable description
    * @static
    * @final
    * @access public
    *
    * @since v1.0
    * @deprecated version/info string
    *
    * @param string $_POSTvarName Name of POST var to use
    * @return void
    *
    */
    function file_upload( $_POSTvarName = null )
    {
        // Add our error codes to Master list
        $this->_defineErrorCodes();

        // Form method check
        if ( ( !isset($_SERVER['CONTENT_TYPE'] )) ||
             ( strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== 0) )
        {
            return $this->_flagError(UPLOAD_BAD_FORM);
        }

        // We don't know the name of the POST var, then
        // we can't do anything
        if ( empty ( $_POSTvarName ) )
        {
            $_POSTvarName = current ( $_FILES );

            if ( empty ( $_POSTvarName ) )
            {
                $this->_flagError ( POST_VAR_NOT_DEFINED );
                return;
            }   // if ( empty ($_POSTvarName) )
        }

        // OK, so store the POST var for further processing
        $this->_postVar = $_POSTvarName;

        // Where we given a File Name to deal with?
        if ( $_FILES[$this->_postVar]['tmp_name'] )
        {
            // Keep File Name
            $this->File ( $_FILES[$this->_postVar]['tmp_name'] );

            // What is the actual name of the file
            $this->_setActualFileName();

            // Do we "think" anything was sent up?
            if ( $this->getFileSize() )
            {
                // What size do we think was sent up
               // $this->_flagError ( UPLOAD_NOT_SENT );
            }
        }
        else
        {
            $this->_flagError ( FILE_NOT_DEFINED );
            return;
        }   // if ( $_FILES[$this->_postVar]['name'] == '' )

        // Pull Mime Type
        $this->_setFileMimeType ( $_FILES[$this->_postVar]['type'] );

       /**
        * Last security issue
        * @TODO; Did the file size sent match
        *        the file size the browser told us it sent?
        *        File recieved could be larger or smaller than defined,
        *        or it might be larger than allowed via MAX_ULOAD_SIZE
        * NOTE: Need code here for this
        */
    }
    // }}}
    // {{{ processUpload
    function processUpload ()
    {
        // Final check is here, now we can see if there is file
        // in the Destination Directory with the same name as
        // the file sent us.
        // Parent Class 'moveFile()' will perform this check.
        // If a duplicate is found, the file will not be moved
        // and an error will be thrown
        $this->fileMove ( $this->getActualFileName(), $this->getFileOverWrite() );
    }
    // }}}
    // {{{ getMimeType
    function getFileMimeType ()
    {
        if ( empty ( $this->_fileMimeType ) )
            $this->_fileMimeType = $_FILES[$this->_postVar]['type'];

        return $this->_fileMimeType;
    }
    // }}}
    // {{{ setFileSize
    function _setFileSize ()
    {
       /** Variable local $_bolSize
        * @var bool $_bolSize Indicates if operation secceeded or not
        * @name $_bolSize
        *
        * Indicates if operation secceeded or not
        *
        * @access private
        * @since 0.0
        *
        */
        $_bolSize = false;

        // Get value from POST array
        $_size = $_FILES[$this->_postVar]['size'];

        if ( $_size > 0 )
        {
            File::_setFileSize ( $_size );
            $_bolSize = true;
        }
        else
        {
            $this->_flagError ( UPLOAD_NOT_SENT );
        }

        // Send back conclusion
        return $_bolSize;
    }
    // }}}
    // {{{ getFileSize
    function getFileSize ()
    {
        if ( empty ( $this->_fileSize ) )
            $this->_setFileSize ( $_FILES[$this->_postVar]['size'] );

        return $this->_fileSize;
    }
    // }}}
    // {{{ getMaxUploadSize
    function getMaxUploadSize()
    {
        $this->maxUploadSize = $_POST ['MAX_FILE_SIZE'];
    }
    // }}}
    // {{{ _setActualFileName
    function _setActualFileName ( $_actualName = null )
    {
        $this->_actualFileName = $_FILES[$this->_postVar]['name'];
    }
    // }}}
    // {{{ getActualFileName
    function getActualFileName ( $_actualName = null )
    {
        return $this->getDestDir() . '/' . $this->_actualFileName;
    }
    // }}}
    // {{{ setFileName
    function setFileName ( $_newName = null )
    {
        if ( $_newName )
            // This is just a pass through to the parent class
            File::_setFileName ( $_newName );
    }
    // }}}


// ==========================================================
// Error handling methods

    // {{{ _errorCodes
    function _defineErrorCodes ()
    {
       /**
        * Property private array $_error_codes
        *
        * Error Messages
        * This is called like this: sprintf($this->$_error_codes[FILE_NOT_FOUND], $fileName)
        *
        * @property private array Error Messages
        * @name $_error_codes
        *
        * @access private
        * @since 1.00
        *
        */
        $this->_error_codes = &$this->_error_codes;

        $this->_file_error_codes[POST_VAR_NOT_DEFINED]  = 'Upload POST Variable not defined.';
        $this->_file_error_codes[UPLOAD_BAD_FORM]       = 'Bad Form Data was recieved.';
        $this->_file_error_codes[UPLOAD_NOT_SENT]       = '\'%s\', did not arrive at Server.';
    }
    // }}}

};

// =====================================================================
// =====================================================================

/**
 * $RCSfile: file_upload.php,v $
 * $Revision: 1.3 $
 * $Date: 2006/05/19 19:45:13 $
 * $Author: jswalter $
 *
 * $Log: file_upload.php,v $
 * Revision 1.3  2006/05/19 19:45:13  jswalter
 *  * $this->_fileFileSize was not a define property, changed to use FILE::_fileSize
 *  *  Example at top used "setOverwrite(), this does not exist, corrected to "setFileOverWrite()"
 *
 * Revision 1.2  2005/09/22 02:57:53  jswalter
 *  - modifed a few methods to reflect updates to File Class
 *
 * Revision 1.1  2005/07/06 18:12:39  jswalter
 *  - initial commit to sourceforge
 *  - these files come from php-yacs.org
 *
 * Revision 1.4   2005/07/01 16:22:14  walter
 * - additional comments
 *
 * Revision 1.3   2005/07/01 16:02:56  walter
 * - removed 'Get_File_Type()', usng PHP method instead
 * - added 'setMimeType()' to File definition
 * - added comments
 *
 * Revision 1.2   2004/11/16 06:18:11  walter
 * - removed "setDestDir()', it now lives in 'files.php'
 *
 * Revision 1.1   2004/10/26 04:05:38  walter
 * - Initial revision
 *
 */

?>