<?php

//
// +------------------------------------------------------------------------+
// | Files - Part of the PHP Yacs Library                                   |
// +------------------------------------------------------------------------+
// | Copyright (c) 2004-2005 Walter Torres                                  |
// | Email         walter@torres.ws                                         |
// | Web           http://web.php-yacs.org                                  |
// | Mirror        http://php-yacs.sourceforge.net/                         |
// | $Id: files.php,v 1.5 2005/07/22 17:42:46 braverock Exp $                 |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//

/**
 * Files Class - Part of the PHP Yacs Library
 *
 *   This Class provides basic, clear, access to the file system
 *   regardless of system; UNIX, Windows or Mac.
 *
 *   This Class can
 *   - read files
 *   - write files
 *   - copy/move/rename files
 *   - create directories
 *   - create directory listings
 *
 * Many of the methods of this class are designed to be "overloaded"
 *
 * @package    File_Handling
 * @subpackage Files
 *
 * @author      Walter Torres <walter@torres.ws>
 * @contributor Aaron Van Meerten
 *
 * @version   $Id: files.php,v 1.5 2005/07/22 17:42:46 braverock Exp $
 * @date      $Date: 2005/07/22 17:42:46 $
 *
 * @copyright (c) 2004 Walter Torres
 * @license   Licensed under the GNU GPL. For full terms see the file COPYING.
 *            OSI Certified Open Source Software
 *
 * @todo Complete phpDoc-ing this file
 *
 * @filesource
 */

  // ==========================================================
  // Class Constants

   /**
    * Version number of Class
    * @constant FILE_VER
    *
    */
    define('FILE_VER', '1.13', false);


// ===========================================================
// File Access Control Values

   /**
    * Mode to use for reading from files
    * @constant FILE_MODE_READ
    *
    */
    define('FILE_MODE_READ', 'rb', false);

   /**
    * Mode to use for truncating files, then writing
    * @constant FILE_MODE_WRITE
    *
    */
    define('FILE_MODE_WRITE', 'wb', false);

   /**
    * Mode to use for appending to files
    * @constant FILE_MODE_APPEND
    *
    */
    define('FILE_MODE_APPEND', 'ab', false);

   /**
    * Use this when a shared (read) lock is required
    * @constant FILE_LOCK_SHARED
    *
    */
    define('FILE_LOCK_SHARED', LOCK_SH, false);

   /**
    * Use this when an exclusive (write) lock is required
    * @constant FILE_LOCK_EXCLUSIVE
    *
    */
    define('FILE_LOCK_EXCLUSIVE', LOCK_EX, false);


// ===========================================================
// Error codes and messages

   /**
    * CSV Parser Success value
    * @constant FILE_SUCCEED
    *
    */
    define('FILE_SUCCEED', true, false);

   /**
    * CSV Parser Fail value
    * @constant FILE_FAIL
    *
    */
    define('FILE_FAIL', false, false);

   /**
    * Improper parameters
    * @constant INVALID_PARAMETERS
    *
    */
    define('INVALID_PARAMETERS', 50, false);

   /**
    * Unknown File
    * @constant FILE_UNKNOWN
    *
    */
    define('FILE_UNKNOWN', 100, false);

   /**
    * File not found
    * @constant FILE_NOT_FOUND
    *
    */
    define('FILE_NOT_FOUND', 101, false);

   /**
    * Can not open file
    * @constant FILE_CANNOT_OPEN
    *
    */
    define('FILE_CANNOT_OPEN', 102, false);

   /**
    * Can not close file
    * @constant FILE_CANNOT_CLOSE
    *
    */
    define('FILE_CANNOT_CLOSE', 103, false);

   /**
    * Can not read file
    * @constant FILE_NOT_READABLE
    *
    */
    define('FILE_NOT_READABLE', 104, false);

   /**
    * Can not write to file
    * @constant FILE_NOT_WRITEABLE
    *
    */
    define('FILE_NOT_WRITEABLE', 105, false);

   /**
    * Can not create output file
    * @constant FILE_CANNOT_CREATE
    *
    */
    define('FILE_CANNOT_CREATE', 106, false);

   /**
    * Can not copy file
    * @constant FILE_CANNOT_COPY
    *
    */
    define('FILE_CANNOT_COPY', 107, false);

   /**
    * Can not move file
    * @constant FILE_CANNOT_MOVE
    *
    */
    define('FILE_CANNOT_MOVE', 108, false);

   /**
    * File can not be locked
    * @constant FILE_CANNOT_LOCK
    *
    */
    define('FILE_CANNOT_LOCK', 109, false);

   /**
    * File can not be unlocked
    * @constant FILE_CANNOT_UNLOCK
    *
    */
    define('FILE_CANNOT_UNLOCK', 110, false);

   /**
    * File already exists
    * @constant FILE_ALREADY_EXISTS
    *
    */
    define('FILE_ALREADY_EXISTS', 111, false);

   /**
    * Directory Path not defined
    * @constant FILE_NOT_DEFINED
    *
    */
    define('FILE_NOT_DEFINED', 112, false);

   /**
    * Directory Path not found
    * @constant FILE_DIR_NOT_FOUND
    *
    */
    define('FILE_DIR_NOT_FOUND', 113, false);

   /**
    * File Path not defined
    * @constant FILE_PATH_UNDEFINED
    *
    */
    define('FILE_PATH_UNDEFINED', 114, false);

// ===========================================================
// Log types for PHP's native error_log() function.

   /**
    * Use PHP's system logger
    * @constant LOG_TYPE_SYSTEM
    *
    */
    define('LOG_TYPE_SYSTEM', 0, false);

   /**
    * Use PHP's mail() function
    * @constant LOG_TYPE_MAIL
    *
    */
    define('LOG_TYPE_MAIL', 1, false);

   /**
    * Use PHP's debugging connection
    * @constant LOG_TYPE_DEBUG
    *
    */
    define('LOG_TYPE_DEBUG', 2, false);

   /**
    * Append to a file
    * @constant LOG_TYPE_FILE
    *
    */
    define('LOG_TYPE_FILE', 3, false);


// ===========================================================
// ===========================================================
/**
 * Files Class - Part of the PHP Yacs Library
 *
 *   This Class provides basic, clear, access to the file system
 *   regardless of system; UNIX, Windows or Mac.
 *
 *   This Class can
 *   - read files
 *   - write files
 *   - copy/move files
 *   - create directories
 *   - create directory listings
 *
 * Many of the methods of this class are designed to be "overloaded"
 *
 * @package    File_Handling
 * @subpackage Files
 *
 * @tutorial /path/to/tutorial.php Complete Class tutorial
 * @example url://path/to/example.php description
 *
 * @author Walter Torres <walter@torres.ws> [with a *lot* of help!]
 *
 * @version $Revision: 1.5 $
 *
 */
class File
{
  // ==========================================================
  // Class Properties

   /**
    * Property private string $_filePath
    *
    * File System path of File to process
    * This property can be modified for use with MOVE, for example
    *
    * @name $_filePath
    * @var string
    * @property string $_filePath File System path of File to process
    *
    * @access private
    * @since 1.00
    */
    var $_filePath = null;

   /**
    * Property private string $_fileFullPath
    *
    * Full File System path and file name of File to process
    * This property can be modified for use with MOVE, for example
    *
    * @name $_fileFullPath
    * @var string
    * @property string $_filePath Full File System path and file name of File to process
    *
    * @access private
    * @since 1.00
    */
    var $_fileFullPath = null;

   /**
    * Property private string $_fileName
    *
    * File Name of File to process
    * This property can be modified for use with MOVE, for example
    *
    * @name $_fileName
    * @var string
    * @property string $_fileName File Name of File to process
    *
    * @access private
    * @since 1.00
    */
    var $_fileName = null;

   /**
    * Property private string $_fileExt
    *
    * Extention of File to process
    * Once File is defined, this property will not be modified
    *
    * @name $_fileExt
    * @var string
    * @property string $_fileExt Extention of File to process
    *
    * @access private
    * @since 1.00
    */
    var $_fileExt = null;

   /**
    * Property private string $_mimeType
    *
    * Mime Type of File.
    * Once File is defined, this property will not be modified
    *
    * @name $_mimeType
    * @var string
    * @property string $_mimeType File Type of File.
    *
    * @access private
    * @since 1.00
    */
    var $_mimeType = null;

   /**
    * Property private string $_fileFileSize
    *
    * Size of File, in Bytes
    * Once File is defined, this property will not be modified
    *
    * @name $_fileFileSize
    * @var string
    * @property string $_fileFileSize Size of File, in Bytes.
    *
    * @access private
    * @since 1.00
    */
    var $_fileFileSize = null;

   /**
    * Property private string $_fileRealPath
    *
    * Full File System Path, from system ROOT
    * Once File is defined, this property will not be modified
    *
    * @name $_fileRealPath
    * @var string
    * @property string $_fileRealPath Full File System Path
    *
    * @access private
    * @since 1.00
    */
    var $_fileRealPath = null;

   /**
    * Property private boolean $_overWriteFile
    *
    * Determines if this file be overwritten.
    * Also if it can be "moved" [which is a copy/delete] or deleted
    * Default set to TRUE, yes it can
    *
    * @name $_overWriteFile
    * @var boolean
    * @property boolean $_overWriteFile Determines if this file be overwritten
    *
    * @access private
    * @since 1.00
    */
    var $_overWriteFile = true;

   /**
    * Property private string $_fileOrgPath
    *
    * Original Path of File.
    * Once File is defined, this property will not be modified
    *
    * @name $_fileOrgPath
    * @var string
    * @property string $_fileOrgPath Original Path of File
    *
    * @access private
    * @since 1.00
    */
    var $_fileOrgPath = null;

   /**
    * Property private string $_fileOrgName
    *
    * Original Name of File.
    * Once File is defined, this property will not be modified
    *
    * @name $_fileOrgName
    * @var string
    * @property string $_fileOrgName Original Name of File
    *
    * @access private
    * @since 1.00
    */
    var $_fileOrgName = null;

   /**
    * Property private boolean $_destDirPath
    *
    * File System Path use for COPY/MOVE
    *
    * @name $_destDirPath
    * @var string
    * @property string $_destDirPath File System Path used for COPY/MOVE
    *
    * @access private
    * @since 1.00
    */
    var $_destDirPath = null;

   /**
    * Property private string $_filePerm
    *
    * OS File Permissions
    * Once File is defined, this property will not be modified
    *
    * @name $_destDirPath
    * @var string
    * @property string $_filePerm OS File Permissions
    *
    * @access private
    * @since 1.00
    */
    var $_filePerm = null;

   /**
    * Property private int $_errCode
    *
    * Error Code upon Failure
    *
    * @name $_errCode
    * @var int
    * @property int $_errCode Error Code upon Failure
    *
    * @access private
    * @since 1.00
    */
    var $_errCode = null;

   /**
    * Property private boolean $_fileSuccess
    *
    * Success or Failure of last operation
    *
    * @name $_fileSuccess
    * @var boolean
    * @property int $_fileSuccess Success or Failure of last operation
    *
    * @access private
    * @since 1.00
    */
    var $_fileSuccess = true;

   /**
    * Property private array $_file_error_codes
    *
    * Error Messages for Class
    * Example: sprintf($this->$_file_error_codes[CSV_FILE_NOT_FOUND], $fileName)
    *
    * @name $_file_error_codes
    * @var array
    * @property array $_file_error_codes Error Messages for Class
    *
    * @access private
    * @since 1.00
    */
    var $_file_error_codes =
         array( INVALID_PARAMETERS       => 'Improper parameters given.',
                FILE_UNKNOWN             => 'Unknown Error.',
                FILE_NOT_FOUND           => '\'%s\' can not be found.',
                FILE_CANNOT_OPEN         => '\'%s\' can not be opened.',
                FILE_CANNOT_CLOSE        => '\'%s\' can not be closed.',
                FILE_NOT_READABLE        => '\'%s\' can not be read from.',
                FILE_NOT_WRITEABLE       => '\'%s\' can not be written to.',
                FILE_CANNOT_CREATE       => '\'%s\' can not be created.',
                FILE_CANNOT_COPY         => '\'%s\' can not be copied.',
                FILE_CANNOT_MOVE         => '\'%s\' can not be moved.',
                FILE_CANNOT_LOCK         => '\'%s\' can not be locked.',
                FILE_CANNOT_UNLOCK       => '\'%s\' can not be unlocked.',
                FILE_ALREADY_EXISTS      => '\'%s\' alread exists.',
                FILE_NOT_DEFINED         => 'File Name not given.',
                FILE_DIR_NOT_FOUND       => '\'%s\' can not be found.',
                FILE_PATH_UNDEFINED      => 'Path is not defined'
              );


// {{{ fileControl - class constructor
   /**
    * Constructor public object File ( string )
    *
    * Class constructor
    *
    * @name File()
    * @access public
    * @category Constructor
    * @since 1.0
    *
    * @uses _setFileOrgName()  Keep Orginal File name
    * @uses setFileName()     Store File Name
    * @uses setFileExt()      Store File extention
    * @uses _setRealPath()    Store complete File File System Path
    * @uses _flagError()      Set which error has occured
    *
    * @static
    * @final .
    *
    * @param string $_file Path to File
    * @return object
    */
    function File ( $_file = null )
    {
        // If a file path is not passed, we can't do anything
        if ( empty ( $_file ) )
            $this->_flagError ( INVALID_PARAMETERS );

        // Pull apart file path info
        else
        {
            $this->_setFileOrgName( $_file );
            $this->setFileName( $_file );
            $this->setFileMimeType();
            $this->setFileExt();
            $this->_setFileSize();
            $this->_setRealPath();
        }
    }
// }}}
// {{{ _chkFile
   /**
    * Method public bool checkFile( string )
    *
    * general description
    *
    * @name checkFile
    * @access public
    *
    * @uses _flagError()      Set which error has occured
    * @static
    * @final .
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  string $_filePath     Complete or relative File System path of file
    * @return bool   $_bolFileTest  validity of given File
    *
    * @since 1.0
    */
    function checkFile( $_filePath = null )
    {
       /**
        * Variable local $_bolFileTest
        *
        * Indicates validity of given File
        * Default value: TRUE
        *
        * @var bool $_bolFileTest TRUE indicates we did not succeed
        *
        * @access private
        * @static
        */
        $_bolFileTest = true;

        if ( empty ( $_filePath ) )
        {
            $this->_flagError ( FILE_PATH_UNDEFINED );
            $_bolFileTest = false;
        }

        else if ( ! @is_file ( $_filePath ) )
        { echo $_filePath . ' ';
            $this->_setErrorMsg ( $_filePath );
            $this->_flagError ( FILE_DIR_NOT_FOUND );
            $_bolFileTest = false;
        }

        else if (!is_readable ($_filePath))
        {
            $this->_setErrorMsg ( $_filePath );
            $this->_flagError ( FILE_NOT_READABLE );
            $_bolFileTest = false;
        }

        else if (!is_writeable ($_filePath))
        {
            $this->_setErrorMsg ( $_filePath );
            $this->_flagError ( FILE_NOT_WRITEABLE );
            $_bolFileTest = false;
        }

        // Send back what we have
        return $_bolFileTest;
    }
// }}}
// {{{ setFileExt
   /**
    * Method public void setFileExt( string )
    *
    * Set File Extension
    *
    * @name setFileExt()
    * @access public
    * @category Setter
    *
    * @uses getFileOrgName() Retrieves File Name
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  string $_ext description
    * @return void
    *
    * @since 1.0
    */
    function setFileExt( $_ext = null )
    {
        // If an extention is not passed in, then derive it
        // from the existing File Name
        if ( empty ($_ext ) )
        {
            $path_parts = pathinfo($this->getFileOrgName());
            $_ext = $path_parts["extension"];
        }

        // Set extention
        $this->_fileExt = $_ext;
    }
// }}}
// {{{ getFileExt
   /**
    * Method public string getFileExt( void )
    *
    * Set File Extension
    *
    * @name setFileExt()
    * @access public
    * @category Getter
    *
    * @uses $_fileExt  File extention
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  void
    * @return string File extention
    *
    * @since 1.0
    */
    function getFileExt()
    {
        // Retrieve from Class Property
        return $this->_fileExt;
    }
// }}}
// {{{ _setFileOrgName
   /**
    * Method private void _setFileOrgName( string )
    *
    * Set Original Name of File
    * Once File is defined, this property will not be modified
    *
    * @name _setFileOrgName()
    * @access private
    * @category Setter
    *
    * @uses $_fileOrgName   Original Name of File
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  string Original Name of File
    * @return  void
    *
    * @since 1.0
    */
    function _setFileOrgName( $_name = null )
    {
        if ( ! $this->_fileOrgName )
        {
            $path_parts = pathinfo($_name);

            $this->_fileOrgName = $path_parts['basename'];
            $this->_setFileOrgPath( $path_parts['dirname'] );
        }
    }
// }}}
// {{{ getFileOrgName
   /**
    * Method public string getFileOrgName( void )
    *
    * Retrieve Original Name of File
    *
    * @name getFileOrgName()
    * @access public
    * @category Getter
    *
    * @uses $_fileOrgName   Original Name of File
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  void
    * @return string Original Name of File
    *
    * @since 1.0
    */
    function getFileOrgName()
    {
        // Retrieve from Class Property
        return $this->_fileOrgName;
    }
// }}}
// {{{ _setFileOrgPath
   /**
    * Method private void _setFileOrgPath( string )
    *
    * Set Original Path of File
    * Once Path is defined, this property can not be modified
    *
    * @name _setFileOrgPath()
    * @access private
    * @category Setter
    *
    * @uses $_fileOrgName   Original Name of File
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  string Original Path of File
    * @return  void
    *
    * @since 1.0
    */
    function _setFileOrgPath( $_filePath = null )
    {
        if ( ! $this->_fileOrgPath )
        {
            $this->_fileOrgPath = $_filePath . '/';
        }
    }
// }}}
// {{{ getFileOrgPath
   /**
    * Method public string getFileOrgPath( void )
    *
    * Retrieve Original Path of File
    *
    * @name getFileOrgPath()
    * @access public
    * @category Getter
    *
    * @uses $_fileOrgName   Original Path of File
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  void
    * @return string Original Path of File
    *
    * @since 1.0
    */
    function getFileOrgPath()
    {
        // Retrieve from Class Property
        return $this->_fileOrgPath;
    }
// }}}
// {{{ setFileName
   /**
    * Method public void setFileName( string )
    *
    * Set Name of File
    * This proprty is used to define a new Name, if desired
    *
    * @name setFileName()
    * @access public
    * @category Setter
    *
    * @uses setFileFullPath   Define Full File System Path
    * @uses getFileOrgName()  Retrieves Orginal File Name
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  string Original Name of File
    * @return  void
    *
    * @since 1.0
    */
    function setFileName( $_name = null )
    {
        // If a name is not given, derive one from orginal properties
        if ( ! $_name )
        {
            $_name = $this->getFileOrgName();
            $_path = $this->getFileOrgPath();
        }
        else
        {
            // Derive properties
            $path_parts = pathinfo($_name);
            $_name = $path_parts['basename'];
            $_path = $path_parts['dirname'];
        }

        // Define properties
        $this->_fileName = $_name;
        $this->_filePath = $_path . '/';
    }
// }}}
// {{{ getFilename
    function getFileName()
    {
        return $this->_fileName;
    }
// }}}
// {{{ setFileFullPath
    function setFileFullPath()
    {
       $this->_fileFullPath = $this->getDestDir() . '/' . $this->getFileName();
    }
// }}}
// {{{ getFileFullPath
    function getFileFullPath()
    {
        if ( ! $this->$_fileFullPath )
            $this->setFileFullPath();

        return $this->_fileFullPath;
    }
// }}}
// {{{ getFilePath
    function getFilePath()
    {
        if ( empty ( $this->_filePath ) )
            $this->_filePath = dirname( $this->_fileOrgName ) . '/';

        return $this->_filePath;
    }
// }}}
// {{{ $this->getFullOrgPath
    function getFullOrgPath()
    {
        return $this->_fileOrgPath . $this->_fileOrgName;
    }
    // }}}
// {{{ setFileMimeType
    function setFileMimeType($_mimeType = null)
    {
        if ( $_mimeType )
            $this->_mimeType = $_mimeType;

        else
        {
            if (!function_exists('mime_content_type_')) {
                global $include_directory;
                require_once($include_directory.'mime/mime-array.php');
            }

            $this->_mimeType = mime_content_type_( $this->getFullOrgPath() );
        }
    }
// }}}
// {{{ getFileMimeType
    function getFileMimeType()
    {
        if ( empty ( $this->_mimeType ) )
            $this->setMineType();

        return $this->_mimeType;
    }
// }}}
// {{{ _setRealPath
    function _setRealPath()
    {
        $this->_fileRealPath = dirname(realpath( $this->_fileOrgPath )) . '/';
    }
// }}}
// {{{ getRealPath
    function getRealPath()
    {
        if ( empty ( $this->_fileRealPath ) )
            $this->_setRealPath;

        return $this->_fileRealPath;
    }
// }}}
// {{{ setFileSize
    function _setFileSize()
    {
        $this->_fileFileSize = filesize( $this->getFullOrgPath() );
    }
// }}}
// {{{ getFileSize
    function getFileSize()
    {
        if ( empty ( $this->_fileFileSize ) )
            $this->_fileFileSize = filesize( $this->_fileOrgName );

        return $this->_fileFileSize;
    }
// }}}
// {{{ copyFile
    function copyFile( $_newFile, $_overWrite = false )
    {
        // If we care, see if a duplicate exists
        if ( ! $_overWriteFile && ( @is_file ( $newFile ) ) )
        {
            // OK, try adding the orginals path to see if this file exists
            if ( $this->_fileRealPath . $newFile )
            {
                $this->_setErrorMsg ( $newFile );
                $this->_flagError ( FILE_ALREADY_EXISTS );
            }
        }

        // See if orginal exists
        else if ( ! @is_file ( $this->getFullOrgPath() ) )
        {
            $this->_setErrorMsg ( $this->getFileOrgName() );
            $this->_flagError ( FILE_NOT_FOUND );
        }

        // Make sure the copy worked
        // Using PHP COPY method
        else if ( ! copy( $this->getFullOrgPath(), $_newFile ) )
            $this->_flagError ( FILE_UNKNOWN );
    }
// }}}
// {{{ renameFile
   /**
    * Method public string renameFile( string Orginal File Name, sting New File Name )
    *
    * Rename File
    *
    * @name renameFile()
    * @access public
    *
    * @uses copyFile    Copies a file, even across partions
    * @uses deleteFile  Deletes a file
    * @static
    * @final
    *
    * @author Walter Torres <walter@torres.ws>
    *
    * @param  string  $_orgName     Original name of file, with full path
    * @param  string  $_orgName     New name of file, with full path
    * @param boolean  $_overWrite   Boolean whether to overwrite same named file, if found
    * @return string  Original Name of File
    *
    * @since 1.0
    */
    function renameFile( $_newFile = null, $_overWrite = false )
    {
        if ( $_newFile)
        {
            // All a 'rename' really is, is a COPY/DELETE
            // First COPY the file
            $this->copyFile( $_newFile, $_overWrite );

            // If the file COPY succeeded, then kill the orginal
            if ( $this->getSuccess() )
            {
                // if the file was copied, then we can delete it
                    $this->deleteFile();
            }
        }
    }
// }}}
// {{{ moveFile
    function moveFile( $_newFile = null, $_overWrite = false )
    {
        if ( $_newFile )
        {
            // MOVE and RENAME is the same thing!
            $this->renameFile( $_newFile, $_overWrite );
        }
    }
// }}}
// {{{ deleteFile
    function deleteFile( $_killFile = null )
    {
        if ( ! $_killFile )
            $_killFile = $this->getFullOrgPath();

        // Just a wrapper for the PHP 'unlink'
        // See if orginal exists
        if ( @file_exists ( $_killFile ) )
        {
            if ( ! @unlink ( $_killFile ) )
            {
                // this failed for some reason
                $this->_setErrorMsg ( $_killFile );
                $this->_flagError ( FILE_UNKNOWN );
            }
        }
        // didn't find it...
        else
        {
            $this->_setErrorMsg ( $_killFile );
            $this->_flagError ( FILE_NOT_FOUND );
        }
    }
// }}}
// {{{ setOverWriteFile
    function setOverWriteFile( $_overWrite = false )
    {
        $this->_overWriteFile = $_overWrite;
    }
// }}}
// {{{ getOverWriteFile
    function getOverWriteFile()
    {
        $this->_overWriteFile;
    }
// }}}

// }}}
// {{{ openContents
    // by hhw
    // http://us2.php.net/manual/en/function.file.php
    function openContents ( $_path = null )
    {
        if ( empty ( $_path ) )
            $_path = this::getFileFullPath();

        $fp = fopen($_path, FILE_MODE_READ );
        $buffer = fread($fp, filesize($_path));
        fclose($fp);
        $lines = preg_split("/\r?\n|\r/", $buffer);
        return implode("\n", $lines);
//        return $lines;
    }
// }}}



// ====================================================================
// Directory Methods

// {{{ setDestDir
    function setDestDir ( $_destDirPath = null )
    {
        if ( empty ( $_destDirPath ) )
        {
            $this->_flagError ( FILE_PATH_UNDEFINED );
            $_bolDirTest = false;
        }
        else
        {
           // We need to convert './' and  '../' to their real paths
           // if ( ( $_destDirPath == './' ) || ( $_destDirPath == '../' ) )
            $_destRealPath = realpath( $_destDirPath );

            if ( $this->checkDir( $_destRealPath ) )
            {
                $this->_destDirPath = $_destRealPath;
                $this->setFileFullPath();
            }
            else
            {
                $this->_setErrorMsg ( $_destDirPath );
                $this->_flagError ( FILE_DIR_NOT_FOUND );
                $_bolDirTest = false;
            }
        }
    }
// }}}
// {{{ getDestDir
    function getDestDir()
    {
        return $this->_destDirPath;
    }
// }}}
// {{{ checkDir
    function checkDir( $_destDirPath = null )
    {
      /** Variable local $_bolDirTest
       * @var bool $_bolSize Indicates validity of directory
       * @name $_bolDirTest
       *
       * Indicates validity of directory
       *
       * @access private
       * @since 1.0
       *
       */
        $_bolDirTest = true;

        if ( empty ( $_destDirPath ) )
        {
            $this->_flagError ( FILE_PATH_UNDEFINED );
            $_bolDirTest = false;
        }

        else if ( ! @is_dir ( $_destDirPath ) )
        {
            $this->_setErrorMsg ( $_destDirPath );
            $this->_flagError ( FILE_DIR_NOT_FOUND );
            $_bolDirTest = false;
        }

        else if (! is_readable ($_destDirPath))
        {
            $this->_setErrorMsg ( $_destDirPath );
            $this->_flagError ( FILE_NOT_READABLE );
            $_bolFileTest = false;
        }

        else if (! is_writeable ($_destDirPath))
        {
           $this->_setErrorMsg ( $_destDirPath );
           $this->_flagError ( FILE_NOT_WRITEABLE );
           $_bolDirTest = false;
        }

        return $_bolDirTest;
    }
// }}}
// {{{ setOverwrite
    function setOverwrite ( $_overwrite = false )
    {
        $this->_fileOverwrite = $_overwrite;
    }
// }}}
// {{{ getOverwrite
    function getOverwrite()
    {
        return $this->_fileOverwrite;
    }
// }}}
// {{{ filePerm
    // Right straight from the manual!
    function filePerm ($_path = null )
    {
        $perms = fileperms( $_path );

        if (($perms & 0xC000) == 0xC000) {
            // Socket
            $info = 's';
        } elseif (($perms & 0xA000) == 0xA000) {
            // Symbolic Link
            $info = 'l';
        } elseif (($perms & 0x8000) == 0x8000) {
            // Regular
            $info = '-';
        } elseif (($perms & 0x6000) == 0x6000) {
            // Block special
            $info = 'b';
        } elseif (($perms & 0x4000) == 0x4000) {
            // Directory
            $info = 'd';
        } elseif (($perms & 0x2000) == 0x2000) {
            // Character special
            $info = 'c';
        } elseif (($perms & 0x1000) == 0x1000) {
            // FIFO pipe
            $info = 'p';
        } else {
            // Unknown
            $info = 'u';
        }

        // Owner
        $info .= (($perms & 0x0100) ? 'r' : '-');
        $info .= (($perms & 0x0080) ? 'w' : '-');
        $info .= (($perms & 0x0040) ?
                 (($perms & 0x0800) ? 's' : 'x' ) :
                 (($perms & 0x0800) ? 'S' : '-'));

        // Group
        $info .= (($perms & 0x0020) ? 'r' : '-');
        $info .= (($perms & 0x0010) ? 'w' : '-');
        $info .= (($perms & 0x0008) ?
                 (($perms & 0x0400) ? 's' : 'x' ) :
                 (($perms & 0x0400) ? 'S' : '-'));

        // World
        $info .= (($perms & 0x0004) ? 'r' : '-');
        $info .= (($perms & 0x0002) ? 'w' : '-');
        $info .= (($perms & 0x0001) ?
                 (($perms & 0x0200) ? 't' : 'x' ) :
                 (($perms & 0x0200) ? 'T' : '-'));

        return $perms . ': ' . $info;
    }
// }}}
// ==========================================================
// Error handling methods

// {{{ _flagError

  /** Method private void _flagError( string new error message, bool add to system log )
   * Sets error message
   *
   * @name flagError()
   * @author Walter Torres <walter@torres.ws>
   *
   * @uses method _setErrorMsg()
   *       property _fileSuccess
   * @final
   * @access public
   *
   * @since 1.0
   *
   * @param string $conErrCode Constant defining error
   *        bool $bolAddLog Add message to system log, or not
   * @return none
   *
   */
    function _flagError ( $conErrCode  = null, $bolAddLog = true, $logType = LOG_TYPE_FILE )
    {
        if ( $conErrCode )
        {
            // Set failure indicator
            $this->setSuccess ( false );

            // Set Error Code
            $this->_errCode = $conErrCode;

            // Stick it the log, maybe
            if ( $bolAddLog )
            {
                $_errMsg  = date("Y.j.Y g:i:s A") . "\t";
                $_errMsg .= $this->getErrorMsg() . "\n";
//                error_log( $_errMsg, $logType, 'logs/statements.err' );
            }

        }
    }

// }}}
// {{{ _setErrorMsg

  /** Method private void _setErrorMsg( string new error message )
   * Sets property with current error message
   *
   * @name _setErrorMsg()
   * @author Walter Torres <walter@torres.ws>
   *
   * @uses Class property $_errMsg
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @property string $strErrMsg New Error Message
   * @return void
   *
   */
    function _setErrorMsg ( $_strErrMsg  = null )
    {
        $this->_errMsg = $_strErrMsg;
    }

// }}}
// {{{ getErrorMsg

  /** Method public string getErrorMsg( string )
   * Sets property with current error message
   *
   * @name _setErrorMsg()
   * @author Walter Torres <walter@torres.ws>
   *
   * @uses Class method getCSVfile()
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @param  string  Text to add to default error message
   * @return string  Error Message
   *
   */
    function getErrorMsg( )
    {
        // Define file that we had a problem with
        $_msg = $this->getFileName();
        // Return the message
        return sprintf($this->_file_error_codes[$this->getErrorCode()], $this->_errMsg );

    }

// }}}
// {{{ getErrorCode

  /** Method public int getErrorCode( void )
   * Sets property with current error message
   *
   * @name getErrorCode()
   * @author Walter Torres <walter@torres.ws>
   *
   * @uses Class property $_errMsg
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @property string $strErrMsg New Error Message
   * @return string $strErrMsg New Error Message
   *
   */
    function getErrorCode()
    {
        return $this->_errCode;
    }

// }}}
// {{{ setSuccess
  /** Method public void setSuccess( bollean )
   * Defines property indicating success or failure of some kind
   *
   * @name setSuccess()
   *
   * @uses Class property $_fileSuccess
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @parameter boolean $_status defines success or failure of some kind
   * @return void
   *
   */
    function setSuccess( $_status = true )
    {
        $this->_fileSuccess = $_status;
    }
// }}}
// {{{ getSuccess
  /** Method public boolean getSuccess( void )
   * Returns property indicating success or failure of some kind
   *
   * @name getSuccess()
   *
   * @uses Class property $_fileSuccess
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @return boolean $_fileSuccess indicating success or failure of some kind
   *
   */
    function getSuccess()
    {
        return $this->_fileSuccess;
    }
// }}}

}

/**
 * $RCSfile: files.php,v $
 * $Revision: 1.5 $
 * $Date: 2005/07/22 17:42:46 $
 * $Author: braverock $
 *
 * $Log: files.php,v $
 * Revision 1.5  2005/07/22 17:42:46  braverock
 * - move include of mime-array.php to inside the function that needs it
 * - only include if function not already defined
 *
 * Revision 1.4  2005/07/20 18:44:32  jswalter
 *  - corrected include mime_type.php problem
 *
 * Revision 1.3  2005/07/19 18:38:07  vanmer
 * - this file requires that the mime type function be available for it to use, so adding a require_once at the top of
 * the file
 *
 * Revision 1.2  2005/07/12 17:42:58  braverock
 * - change to use custom mime function because the PHP core fn isn't reliable
 *
 * Revision 1.1  2005/07/06 18:12:39  jswalter
 *  - initial commit to sourceforge
 *  - these files come from php-yacs.org
 *
 *
 */

?>