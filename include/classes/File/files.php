<?php

// =============================================================
// CVS Id Info
// $Id: files.php,v 1.8 2005/09/22 03:13:29 jswalter Exp $

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
   * @tutorial /path/to/tutorial.php Complete Class tutorial
   * @example url://path/to/example.php description
   *
   * @author Walter Torres <walter@torres.ws> [with a *lot* of help!]
   * @contributor Aaron Van Meerten
   *
   * @version   $Id: files.php,v 1.8 2005/09/22 03:13:29 jswalter Exp $
   * @date      $Date: 2005/09/22 03:13:29 $
   *
   * @copyright (c) 2004 Walter Torres
   * @license   Licensed under the GNU GPL. For full terms see the file COPYING.
   *            OSI Certified Open Source Software
   *
   * @todo Complete phpDoc-ing this file
   *
   * @filesource
   *
   * $Id: files.php,v 1.8 2005/09/22 03:13:29 jswalter Exp $
   *
   **/

// ==========================================================
// Class Constants

   /**
    * Version number of Class
    * @constant FILE_VER
    *
    */
    define('FILE_VER', '1.8', false);


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
// Error Codes

   /**
    * FILE Class Success value
    * @constant FILE_SUCCEED
    *
    */
    define('FILE_SUCCEED', true, false);

   /**
    * FILE Class Fail value
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
    * Can not delete file
    * @constant FILE_CANNOT_DELETE
    *
    */
    define('FILE_CANNOT_DELETE', 107, false);

   /**
    * Can not copy file
    * @constant FILE_CANNOT_COPY
    *
    */
    define('FILE_CANNOT_COPY', 108, false);

   /**
    * Can not move file
    * @constant FILE_CANNOT_MOVE
    *
    */
    define('FILE_CANNOT_MOVE', 109, false);

   /**
    * File can not be locked
    * @constant FILE_CANNOT_LOCK
    *
    */
    define('FILE_CANNOT_LOCK', 110, false);

   /**
    * File can not be unlocked
    * @constant FILE_CANNOT_UNLOCK
    *
    */
    define('FILE_CANNOT_UNLOCK', 111, false);

   /**
    * File already exists
    * @constant FILE_ALREADY_EXISTS
    *
    */
    define('FILE_ALREADY_EXISTS', 112, false);

   /**
    * Directory Path not defined
    * @constant FILE_NOT_DEFINED
    *
    */
    define('FILE_NOT_DEFINED', 113, false);

   /**
    * Directory Path not defined
    * @constant FILE_DIR_NOT_DEFINED
    *
    */
    define('FILE_DIR_NOT_DEFINED', 114, false);

   /**
    * Directory Path not found
    * @constant FILE_DIR_NOT_FOUND
    *
    */
    define('FILE_DIR_NOT_FOUND', 115, false);

   /**
    * File Path not defined
    * @constant FILE_PATH_UNDEFINED
    *
    */
    define('FILE_PATH_UNDEFINED', 116, false);

   /**
    * File Path is not a file
    * @constant FILE_PATH_NOT_FILE
    *
    */
    define('FILE_PATH_NOT_FILE', 117, false);

   /**
    * File Path is not a directory
    * @constant FILE_PATH_NOT_DIR
    *
    */
    define('FILE_PATH_NOT_DIR', 118, false);


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
 * @version $Revision: 1.8 $
 *
 */
class File
{
  // ==========================================================
  // Class Properties and their default values

   /**
    * Property private string $_fileOrgPath
    *
    * Orginal file Path given for Class
    * Once File is defined, this property will not be modified
    *
    * @name _fileOrgPath
    * @var string
    * @property string _fileOrgPath Orginal file Path given for Class
    *
    * @access private
    * @since 1.00
    */
    var $_fileOrgPath = null;

   /**
    * Property private string $_fileFullPath
    *
    * Full File System path of File, from system Root
    * Once File is defined, this property will not be modified
    *
    * @name _fileFullPath
    * @var string
    * @property string _fileFullPath Full File System path of File
    *
    * @access private
    * @since 1.00
    */
    var $_fileFullPath = null;

   /**
    * Property private string $_fileSystemPath
    *
    * File System [directory] path of File
    * Once File is defined, this property will not be modified
    *
    * @name _fileSystemPath
    * @var string
    * @property string _fileSystemPath File System path of File
    *
    * @access private
    * @since 1.00
    */
    var $_fileSystemPath = null;

   /**
    * Property private string $_fileName
    *
    * File Name of File to process
    * Once File is defined, this property will not be modified
    *
    * @name $_fileName
    * @var string
    * @property string _fileName  Name of File to process
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
    * @property string _fileExt Extention of File to process
    *
    * @access private
    * @since 1.00
    */
    var $_fileExt = null;

   /**
    * Property private string $_fileMime
    *
    * Mime Type of File.
    * Once File is defined, this property will not be modified
    *
    * @name $_fileMime
    * @var string
    * @property string _fileMime File Type of File.
    *
    * @access private
    * @since 1.00
    */
    var $_fileMime = null;

   /**
    * Property private string $_fileSize
    *
    * Size of File, in Bytes
    * Once File is defined, this property will not be modified
    *
    * @name $_fileSize
    * @var string
    * @property string _fileSize Size of File, in Bytes.
    *
    * @access private
    * @since 1.00
    */
    var $_fileSize = null;

   /**
    * Property private string $_filePerm
    *
    * OS level File permissions. ie.: -rw-rw-r--
    * Once File is defined, this property will not be modified
    *
    * @name $_filePerm
    * @var string
    * @property string _filePerm Size of File, in Bytes.
    *
    * @access private
    * @since 1.00
    */
    var $_filePerm = null;

   /**
    * Property private string $_filePermOctal
    *
    * OS level File permissions, Octal values. ie.: 0644
    * Once File is defined, this property will not be modified
    *
    * @name $_filePermOctal
    * @var string
    * @property string _filePermOctal Size of File, in Bytes.
    *
    * @access private
    * @since 1.00
    */
    var $_filePermOctal = null;


   /**
    * Property private boolean $_fileReadable
    *
    * Indicates if current File is "readable" by the PHP process
    *
    * @name $_fileReadable
    * @var boolean
    * @property boolean _fileReadable  if current File is "readable" by the PHP process
    *
    * @access private
    * @since 1.00
    */
    var $_fileReadable = null;

   /**
    * Property private boolean $_fileWriteable
    *
    * Indicates if current File is "writeable" by the PHP process
    *
    * @name $_fileWriteable
    * @var boolean
    * @property boolean _fileWriteable  if current File is "writeable" by the PHP process
    *
    * @access private
    * @since 1.00
    */
    var $_fileWriteable = null;

   /**
    * Property private boolean $_fileOverWrite
    *
    * Determines if this file be overwritten.
    * Also if it can be "moved" or "renamed" over
    * Default set to FALSE, no it can't
    *
    * @name $_fileOverWrite
    * @var boolean
    * @property boolean $_fileOverWrite Determines if this file be overwritten
    *
    * @access private
    * @since 1.1
    */
    var $_fileOverWrite = false;

   /**
    * Property private string _dirCurrPerm
    *$
    * OS level Directory permissions. ie.: -rw-rw-r--
    * Once Directory is defined, this property will not be modified
    *
    * @name $_dirCurrPerm
    * @var string
    * @property string _dirCurrPerm Directory permissions.
    *
    * @access private
    * @since 1.3
    */
    var $_dirCurrPerm = null;

   /**
    * Property private string $_dirCurrPermOctal
    *
    * OS level Directory permissions, Octal values. ie.: 0644
    * Once Directory is defined, this property will not be modified
    *
    * @name $_dirCurrPermOctal
    * @var string
    * @property string _dirCurrPermOctal Directory permissions.
    *
    * @access private
    * @since 1.3
    */
    var $_dirCurrPermOctal = null;

   /**
    * Property private boolean $_dirDestPath
    *
    * File System Path use for COPY/MOVE
    *
    * @name $_dirDestPath
    * @var string
    * @property string _dirDestPath File System Path used for COPY/MOVE
    *
    * @access private
    * @since 1.3
    */
    var $_dirDestPath = null;

   /**
    * Property private string $_dirDestPerm
    *$
    * OS level Directory permissions. ie.: -rw-rw-r--
    * Once Directory is defined, this property will not be modified
    *
    * @name $_dirDestPerm
    * @var string
    * @property string _dirDestPerm Directory permissions.
    *
    * @access private
    * @since 1.3
    */
    var $_dirDestPerm = null;

   /**
    * Property private string $_dirDestPermOctal
    *
    * OS level Directory permissions, Octal values. ie.: 0644
    * Once Directory is defined, this property will not be modified
    *
    * @name $_dirDestPermOctal
    * @var string
    * @property string _dirDestPermOctal Directory permissions.
    *
    * @access private
    * @since 1.3
    */
    var $_dirDestPermOctal = null;

   /**
    * Property private int $_errCode
    *
    * Error Code upon Failure
    *
    * @name $_errCode
    * @var int
    * @property int _errCode Error Code upon Failure
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
    * @property boolean _fileSuccess Success or Failure of last operation
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
    * @property array _file_error_codes Error Messages for Class
    *
    * @access private
    * @since 1.00
    */
    var $_file_error_codes =
         array( INVALID_PARAMETERS       => 'Improper Parameters Given.',
                FILE_UNKNOWN             => 'Unknown Error.',
                FILE_NOT_FOUND           => 'File [%s] can not be found.',
                FILE_CANNOT_OPEN         => 'File [%s] can not be opened.',
                FILE_CANNOT_CLOSE        => 'File [%s] can not be closed.',
                FILE_NOT_READABLE        => 'File [%s] can not be read from.',
                FILE_NOT_WRITEABLE       => 'File [%s] can not be written to.',
                FILE_CANNOT_CREATE       => 'File [%s] can not be created.',
                FILE_CANNOT_DELETE       => 'File [%s] can not be deleted.',
                FILE_CANNOT_COPY         => 'File [%s] can not be copied.',
                FILE_CANNOT_MOVE         => 'File [%s] can not be moved.',
                FILE_CANNOT_LOCK         => 'File [%s] can not be locked.',
                FILE_CANNOT_UNLOCK       => 'File [%s] can not be unlocked.',
                FILE_ALREADY_EXISTS      => 'File [%s] alread exists.',
                FILE_NOT_DEFINED         => 'File Name not given.',
                FILE_DIR_NOT_DEFINED     => 'Directory not defined.',
                FILE_DIR_NOT_FOUND       => 'Directory [%s] can not be found.',
                FILE_PATH_UNDEFINED      => 'Path is not defined.',
                FILE_PATH_NOT_FILE       => 'Path [%s] is not a File.',
                FILE_PATH_NOT_DIR        => 'Path [%s] is not a Directory.',
              );


// ==========================================================
// Class Methods

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
    * @uses method _setFileName()  Sets File Name, Path and extension
    * @uses method _chkFile()      Performs a system level check on the existance of the file
    * @uses method _flagError()    Set which error has occured
    *
    * @static
    * @final
    *
    * @param string $_filePath  Path to File
    * @return object
    */
    function File ( $_filePath = null )
    {
        // If a file path is not passed, we can't do anything
        if ( empty ( $_filePath ) )
        {
            $this->_flagError ( INVALID_PARAMETERS );
        }

        // Pull apart file path info
        else
        {
            // Keep this, regardless of it validity
            $this->_fileOrgPath = $_filePath;
            $this->_setFileName( $_filePath );
            $this->_setFileMimeType();

            // Only need these properties if the file can be found
            if ( $this->_chkFile( $_filePath ) )
            {
                // Set base properties
                $this->_fileReadable  = $this->fileReadable ($_filePath);
                $this->_fileWriteable = $this->fileWriteable ($_filePath);
                $this->_setFileSize();
                $this->_setFilePerm();
                $this->_setCurrDirPerm();
            }
        }
    }

   /**
    * Method public bool fileExists ( string )
    *
    * Performs a system level check on the existance of a file.
    * This method can be called STATICally
    *
    * @name fileExists
    * @access public
    *
    * @uses method @is_file()    Built-in PHP method
    * @static
    * @final
    *
    * @param  string   $_filePath     Complete or relative File System path of file
    * @return boolean  $_bolFileTest  indicates if File is found
    *
    * @since 1.0
    */
    function fileExists( $_filePath = null )
    {
        $_isFile =  @is_file ( $_filePath );

        // Clears file status cache
        clearstatcache();

        return $_isFile;
    }

   /**
    * Method public bool fileReadable ( string )
    *
    * Performs a system level check to confirm that the file is "readable" by the PHP process.
    * This method can be called STATICally
    *
    * @name fileReadable
    * @access public
    *
    * @uses method @is_readable()    Built-in PHP method
    * @static
    * @final
    *
    * @param  string  $_filePath      Complete or relative File System path of file
    * @return boolean  $_bolFileTest  indicates if File is Readable
    *
    * @since 1.0
    */
    function fileReadable( $_filePath = null )
    {
        $_fileReadable =  @is_readable ( $_filePath );

        // Clears file status cache
        clearstatcache();

        return $_fileReadable;
    }

   /**
    * Method public bool fileWriteable ( string )
    *
    * Performs a system level check to confirm that the file is "writeable" by the PHP process.
    * This method can be called STATICally
    *
    * @name fileWriteable
    * @access public
    *
    * @uses method @is_writeable()    Built-in PHP method
    * @static
    * @final
    *
    * @param  string  $_filePath      Complete or relative File System path of file
    * @return boolean  $_bolFileTest  indicates if File is Readable
    *
    * @since 1.0
    */
    function fileWriteable( $_filePath = null )
    {
        $_fileReadable =  @is_writeable ( $_filePath );

        // Clears file status cache
        clearstatcache();

        return $_fileReadable;
    }

   /**
    * Method public bool _chkFile( string )
    *
    * Performs a system level check on the existance of the file.
    *
    * @name _chkFile
    * @access public
    *
    * @uses method _flagError()    Set which error has occured
    * @uses method _setErrorMsg()  Sets additional error message
    * @static
    * @final
    *
    * @param  string $_filePath     Complete or relative File System path of file
    * @return bool   $_bolFileTest  validity of given File
    *
    * @since 1.0
    */
    function _chkFile( $_filePath = null )
    {
       /**
        * Variable local $_bolFileTest
        *
        * Indicates validity of given File
        * Default value: TRUE
        *
        * @var bool $_bolFileTest TRUE indicates we did succeeded
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

        // See if the File really exists
        else if ( ! file_exists ( $_filePath ) )
        {
            $this->_setErrorMsg ( $_filePath );
            $this->_flagError ( FILE_NOT_FOUND );
            $_bolFileTest = false;
        }

        // Is it really file?
        else if ( ! @is_file ( $_filePath ) )
        {
            $this->_setErrorMsg ( $_filePath );
            $this->_flagError ( FILE_PATH_NOT_FILE );
            $_bolFileTest = false;
        }

        // Send back what we have
        return $_bolFileTest;
    }

// ==========================================================
// Class Properties Definition Methods

   /**
    * Method public string getFileFullPath( string )
    *
    * Returns Full File Path Name
    *
    * @name getFileFullPath()
    *
    * @access public
    * @category Getter
    *
    * @static
    * @final
    *
    * @param  void
    * @return string Full OS File Path for file
    *
    * @since 1.0
    */
    function getFileFullPath( $_path = false )
    {
        return $this->_fileFullPath;
    }

   /**
    * Method public string getFileSystemPath( string )
    *
    * Returns File Path Name
    *
    * @name getFileSystemPath()
    *
    * @access public
    * @category Getter
    *
    * @static
    * @final
    *
    * @param  void
    * @return string Full OS File Path for file
    *
    * @since 1.0
    */
    function getFileSystemPath( $_path = false )
    {
        // If '$_path' is defined, call this statically
        if ( $_path )
        {
            // Retreive File Property data in a common way across
            // all STATIC capable methods
            $_fileSystemPath = File::_getStaticFileData ( $_path, 'getFileSystemPath', '_fileSystemPath' );
        }
        else
            $_fileSystemPath = $this->_fileSystemPath;

        return $_fileSystemPath;
    }

   /**
    * Method public void _setFileName( string )
    *
    * Sets Name of File, full path and file extension
    *
    * @name _setFileName()
    *
    * @access private
    * @category Setter
    *
    * @uses property _filePath        Full File System path and file name of File to process
    * @uses property _fileSystemPath  File System path of File to process
    * @uses property _fileName        File Name of File to process
    * @uses property _fileExt         Extention of File to process
    *
    * @static
    * @final
    *
    * @param  string  Name of File
    * @return void
    *
    * @since 1.0
    */
    function _setFileName( $_filePath )
    {
        // Pull File Path apart into its base parts
        $path_parts = pathinfo( $_filePath );

        $this->_fileSystemPath = dirname(realpath( $_filePath )) . '/';
        $this->_fileName       = $path_parts['basename'];
        $this->_fileExt        = $path_parts['extension'];
        $this->_fileFullPath   = $this->_fileSystemPath . $this->_fileName;
    }

   /**
    * Method public string getFileName( void )
    *
    * Returns Name of File
    *
    * @name getFileName()
    *
    * @access public
    * @category Getter
    *
    * @uses property _fileName   File Name of File to process
    *
    * @static
    * @final
    *
    * @param  void
    * @return string Name of File
    *
    * @since 1.0
    */
    function getFileName( )
    {
        return $this->_fileName;
    }

   /**
    * Method public void _setFileSize ( void )
    *
    * Performs a system level check on the file and retrieves its size
    * This method can NOT be called STATICally
    *
    * @name _setFileSize
    * @access private
    *
    * @uses method filesize()    Built-in PHP method
    * @final
    *
    * @param  none
    * @return none
    *
    * @since 1.0
    */
    function _setFileSize()
    {
        $this->_fileSize = filesize( $this->getFileFullPath() );
    }

   /**
    * Method public int _setFileSize ( string )
    *
    * Retrieves size of File
    * This method can be called STATICally
    *
    * @name fileExists
    * @access private
    *
    * @static
    * @final
    *
    * @param  string  $_path      Path to File to retrieve info
    * @return int     $_fileSize  size of File
    *
    * @since 1.0
    */
    function getFileSize( $_path = false )
    {
        // If '$_path' is defined, call this statically
        if ( $_path )
        {
            // Retreive File Property data in a common way across
            // all STATIC capable methods
            $_fileSize = File::_getStaticFileData ( $_path, 'getFileSize', '_fileSize' );
        }
        else
            $_fileSize = $this->_fileSize;

        return $_fileSize;
    }

   /**
    * Method public void _setFileMimeType ( string )
    *
    * Defines the MIME Type of File
    * This method can NOT be called STATICally
    *
    * @name _setFileMimeType
    * @access private
    *
    * @static
    * @final
    *
    * @param  string  $_fileMime   MIME Type to define file
    * @return void
    *
    * @since 1.0
    */
    function _setFileMimeType($_fileMime = false)
    {
        global $include_directory;

        if ( $_fileMime )
            $this->_fileMime = $_fileMime;

        else
        {
            require_once($include_directory . 'mime/mime-array.php');

            $this->_fileMime = mime_content_type_ ( $this->getFileFullPath() );
        }
    }

   /**
    * Method public string getFileMimeType ( string )
    *
    * Defines the MIME Type of File
    * This method can be called STATICally
    *
    * @name getFileMimeType
    * @access public
    *
    * @static
    * @final
    *
    * @param  string  $_path      Full Path to File to 'read'
    * @return string  $_fileMime  mime Type of File
    *
    * @since 1.0
    */
    function getFileMimeType( $_path = false )
    {
        // If '$_path' is defined, call this statically
        if ( $_path )
        {
            // Retreive File Property data in a common way across
            // all STATIC capable methods
            $_fileMime = File::_getStaticFileData ( $_path, 'getFileMimeType', '_fileMime' );
        }
        else
            $_fileMime = $this->_fileMime;

        return $_fileMime;
    }

   /**
    * Method public void _getPermisions( string )
    *
    * Sets Class property for OS File Persimmsions of File or Directory
    * Right straight from the manual!
    *
    * @name _getPermisions()
    *
    * @access private
    * @category Setter
    *
    * @static
    * @final
    *
    * @param  void
    * @return string Name of File
    *
    * @since 1.3
    */
    function _getPermisions ( $_path = false )
    {
        if ( ! $_path )
            return false;

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

        $_permArray['info'] = $info;
        $_permArray['octal'] = substr(sprintf('%o', $perms), -4);

        return $_permArray;
    }

   /**
    * Method public void _setFilePerm( string )
    *
    * Sets Class property for OS File Persimmsions of File
    * Right straight from the manual!
    *
    * @name _setFilePerm()
    *
    * @access private
    * @category Setter
    *
    * @uses class    getFileFullPath()   Full Path to File
    * @uses property _filePerm           String based File Permissions
    * @uses property _filePermOctal      Octal value of File Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return string Name of File
    *
    * @since 1.0
    */
    function _setFilePerm ()
    {
        $_permArray = $this->_getPermisions($this->getFileFullPath());

        $this->_filePerm      = $_permArray['info'];
        $this->_filePermOctal = $_permArray['octal'];
    }

   /**
    * Method public void getFilePerm( string )
    *
    * Retrieves Class property for OS File Permissions of File
    * This method can be called STATICally
    *
    * @name _setFilePerm()
    *
    * @access public
    * @category Getter
    *
    * @uses property _filePerm      String based File Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return string permission set for File
    *
    * @since 1.0
    */
    function getFilePerm ( $_path = false )
    {
        // If '$_path' is defined, call this statically
        if ( $_path )
        {
            $_filePerm = File::_getPermisions ( $_path );
            $_filePerm = $_filePerm['info'];
        }
        else
            $_filePerm = $this->_filePerm;

        return $_filePerm;
    }

   /**
    * Method public void getFilePermOctal( string )
    *
    * Retrieves Class property for OS File Permissions of File
    * This method can be called STATICally
    *
    * @name getFilePermOctal()
    *
    * @access public
    * @category Getter
    *
    * @uses property _filePermOctal    Octal value of File Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return string permission set for File in Octal
    *
    * @since 1.0
    */
    function getFilePermOctal ( $_path = false )
    {
        // If '$_path' is defined, call this statically
        if ( $_path )
        {
            $_filePermOctal = File::_getPermisions ( $_path );
            $_filePermOctal = $_filePermOctal['octal'];
        }
        else
            $_filePermOctal = $this->_filePerm;

        return $_filePermOctal;
    }

// ==========================================================
// Directory Class Methods

   /**
    * Method public bool isDir( string )
    *
    * Performs a system level check on the existance of the directory.
    * This method can be called STATICally
    *
    * @name isDir
    * @access public
    *
    * @uses method _flagError()    Set which error has occured
    * @uses method _setErrorMsg()  Sets additional error message
    * @static
    * @final
    *
    * @param  string $_dirPath      Complete or relative File System path of a directory
    * @return bool   $_bolFileTest  validity of given File
    *
    * @since 1.0
    */
    function isDir ( $_dirPath = null )
    {
       /**
        * Variable local $_bolDirTest
        *
        * Indicates validity of given File
        * Default value: TRUE
        *
        * @var bool $_bolDirTest TRUE indicates we did succeeded
        *
        * @access private
        * @static
        */
        $_bolDirTest = true;

        // Do we have a Path to deal with
        if ( empty ( $_dirPath ) )
        {
            if ( $this )
                $this->_flagError ( FILE_PATH_UNDEFINED );

            $_bolDirTest = false;
        }

        // If relative paths, we don't need to do anything
        else if ( ( $_dirPath == './' ) || ( $_dirPath == '../' ) )
        {
            $_bolDirTest = true;
        }

        // See if the Directory really exists
        else if ( ! file_exists ( $_dirPath ) )
        {
            if ( $this )
            {
                $this->_setErrorMsg ( $_dirPath );
                $this->_flagError ( FILE_DIR_NOT_FOUND );
            }

            $_bolDirTest = false;
        }

        // Is it really Directory?
        else if ( ! @is_dir ( $_dirPath ) )
        {
            if ( $this )
            {
                $this->_setErrorMsg ( $_dirPath );
                $this->_flagError ( FILE_PATH_NOT_DIR );
            }

            $_bolDirTest = false;
        }

        // Send back what we have
        return $_bolDirTest;
    }

   /**
    * Method public void setDestDir ( string )
    *
    * Defined Directory Path to place MOVEd or RENAMEd files
    *
    * @name setDestDir()
    *
    * @access public
    * @category Setter
    *
    * @uses property _dirDestPath    Directory to MOVE or RENAME files into
    *
    * @final
    *
    * @param  string $_dirDestPath    Full path to directory
    * @return void
    *
    * @since 1.3
    */
    function setDestDir ( $_dirDestPath = null )
    {
       /**
        * Variable local $_bolDirTest
        *
        * Indicates validity of given File
        * Default value: TRUE
        *
        * @var bool $_bolDirTest TRUE indicates we did succeeded
        *
        * @access private
        * @static
        */
        $_bolDirTest = true;

        if ( empty ( $_dirDestPath ) )
        {
            $this->_flagError ( FILE_DIR_NOT_DEFINED );
            $_bolDirTest = false;
        }
        else
        {
            // We have to make an assumption on what './' [current] and '../' [parent
            // directory] really means.
            // It could be 'current' to the File defined in the Class, or
            // it could be 'current' to what the Web Server/current app thinks
            // is the 'current' directory.
            // Our assumption will be that './' and '../' will be relative to the File
            // defined within the Class.
            if ( ( $_dirDestPath == '.' ) || ( $_dirDestPath == './' ))
            {
                // Set full path to destination directory
                $_dirDestPath = $this->getFileSystemPath();
            }
            else if ( ( $_dirDestPath == '..' ) || ( $_dirDestPath == '../' ))
            {
                // Set full path to destination directory
                $_dirDestPath = dirname( $this->getFileSystemPath() ) . '/';
            }

            if ( $this->isDir( $_dirDestPath ) )
            {
                $this->_dirDestPath = $_dirDestPath;
                $this->_setDestDirPerm();
            }
            else
            {
                $_bolDirTest = false;
            }
        }

        return $_bolDirTest;
    }

   /**
    * Method public string getDestDir ( void )
    *
    * Retrieves Directory Path to place MOVEd or RENAMEd files
    *
    * @name getDestDir()
    *
    * @access public
    * @category Getter
    *
    * @uses property _dirDestPath    Directory to MOVE or RENAME files into
    *
    * @final
    *
    * @param  string $_dirDestPath    Full path to directory
    * @return void
    *
    * @since 1.3
    */
    function getDestDir ()
    {
        return $this->_dirDestPath;
    }

   /**
    * Method public void _setCurrDirPerm( void )
    *
    * Sets Class property for OS File Persimmsions of Directory
    * Right straight from the manual!
    *
    * @name _setDirPerm()
    *
    * @access private
    * @category Setter
    *
    * @uses method   getFileSystemPath()     Defined Current Directory
    * @uses property _dirCurrPerm            String based Directory Permissions
    * @uses property _dirCurrPermOctal       Octal value of Directory Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return void
    *
    * @since 1.3
    */
    function _setCurrDirPerm ()
    {
        $_permArray = $this->_getPermisions($this->getFileSystemPath());

        $this->_dirCurrPerm      = $_permArray['info'];
        $this->_dirCurrPermOctal = $_permArray['octal'];
    }

   /**
    * Method public string getCurrDirPerm()
    *
    * Retrieves Class property for OS File Permissions of Current Directory
    * This method can *not* be called STATICally
    *
    * @name getCurrDirPerm()
    *
    * @access public
    * @category Getter
    *
    * @uses property _filePerm      String based Directory Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return string permission set for Directory
    *
    * @since 1.3
    */
    function getCurrDirPerm ()
    {
        return $this->_dirCurrPerm;
    }

   /**
    * Method public string getCurrDirPermOctal( void )
    *
    * Retrieves Class property for OS File Permissions of Current Directory
    * This method can *not* be called STATICally
    *
    * @name getCurrDirPermOctal()
    *
    * @access public
    * @category Getter
    *
    * @uses property _filePermOctal    Octal value of Directory Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return string permission set for Directory in Octal
    *
    * @since 1.3
    */
    function getCurrDirPermOctal ()
    {
        return $this->_dirCurrPermOctal;
    }

   /**
    * Method public void _setDestDirPerm( void )
    *
    * Sets Class property for OS File Persimmsions of Directory
    * Right straight from the manual!
    *
    * @name _setDestDirPerm()
    *
    * @access private
    * @category Setter
    *
    * @uses method   getDestDir()            Defined Destination Directory
    * @uses property _dirCurrPerm            String based Directory Permissions
    * @uses property _dirCurrPermOctal       Octal value of Directory Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return void
    *
    * @since 1.3
    */
    function _setDestDirPerm ()
    {
        $_permArray = $this->_getPermisions($this->getDestDir() );

        $this->_dirDestPerm      = $_permArray['info'];
        $this->_dirDestPermOctal = $_permArray['octal'];
    }

   /**
    * Method public string getDestDirPerm()
    *
    * Retrieves Class property for OS File Permissions of Destination Directory
    * This method can *not* be called STATICally
    *
    * @name getDestDirPerm()
    *
    * @access public
    * @category Getter
    *
    * @uses property _filePerm      String based Directory Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return string permission set for Directory
    *
    * @since 1.3
    */
    function getDestDirPerm ()
    {
        return $this->_dirDestPerm;
    }

   /**
    * Method public string getDestDirPermOctal( void )
    *
    * Retrieves Class property for OS File Permissions of Current Directory
    * This method can *not* be called STATICally
    *
    * @name getDestDirPermOctal()
    *
    * @access public
    * @category Getter
    *
    * @uses property _filePermOctal    Octal value of Directory Permissions
    *
    * @static
    * @final
    *
    * @param  void
    * @return string permission set for Directory in Octal
    *
    * @since 1.3
    */
    function getDestDirPermOctal ()
    {
        return $this->_dirDestPermOctal;
    }

// ==========================================================
// Generic Class Property Definition Method

   /**
    * Method public string _getStaticFileData ( string, string )
    *
    * Retrieves defined File property as a STATIC method call
    *
    * @name _getStaticFileData()
    *
    * @access private
    * @category Getter
    *
    * @static
    * @final
    *
    * @param  string $_path       Full path to File to access
    * @param  string $_method     Which Method to call
    * @param  string $_property   Which Property to retrieve
    * @return string $_property   File Property Value
    *
    * @since 1.0
    */
    function _getStaticFileData ( $_path = false, $_method = false, $_property = false )
    {
        if ( ! $_path )
        {
            $_property = false;
        }

        // If we need to use this STATICally
        else if ( ( ! empty($_path) ) && ( ! $this->$_property ) )
        {
            $_objFile = new File ( $_path );

            $_property = $_objFile->$_method();
        }

        else
        {
            $_property = $this->$_property;
        }

        return $_property;
    }

  /**
   * Method public void setFileOverWrite( boolean )
   *
   * Sets property indicating whether various file operations
   * can overwrite an existing file.
   *
   * @name setFileOverWrite()
   *
   * @uses property $_fileOverWrite Determines if an existing file be overwritten
   *
   * @final
   * @access public
   *
   * @since 1.1
   *
   * @param  boolean  $_fileOverWrite  Determines if an existing file be overwritten
   * @return void
   *
   */
    function setFileOverWrite( $_bolOverwrite = false )
    {
        // Set property
        $this->_fileOverWrite = $_bolOverwrite;
    }

  /**
   * Method public boolean getFileOverWrite ( void )
   *
   * Returns property indicating whether various file operations
   * can overwrite an existing file.
   *
   * @name getFileOverWrite()
   *
   * @uses property $_fileOverWrite Determines if an existing file be overwritten
   *
   * @final
   * @access public
   *
   * @since 1.1
   *
   * @param  void
   * @return boolean  $_fileOverWrite  Determines if an existing file be overwritten
   *
   */
    function getFileOverWrite()
    {
        // return property value
        return $this->_fileOverWrite;
    }

// ==========================================================
// File Manipulation Methods

   /**
    * Method public bool fileDelete ( string )
    *
    * Attempts to remove a file.
    * If the DELETE succeeds, a boolean TRUE is returned.
    * If the DELETE failes, an error message will be returned.
    * This method can be called STATICally
    *
    * @name fileDelete
    * @access public
    *
    * @uses method @unlink()    Built-in PHP method
    * @static
    * @final
    *
    * @param  string $_filePath  Complete or relative File System path of file
    * @return mixed  boolean     Indicates success
    *                string      Error message on failure
    *
    * @since 1.1
    */
    function fileDelete ( $_path = false )
    {
       /**
        * Variable local $_retValue
        * @var bool $_bolSize Indicates success or failure of operation
        * @name $_bolDirTest
        *
        * Indicates success or failure of operation
        * default value of TRUE
        *
        * @access private
        *
        */
        // Default Return Value
        $_retValue = true;

        // Determine if this is a STATIC or a CLASS call

        // This is a CLASS call
        if ( $this )
        {
            // Verify that the file actually exists, regardless of what the Class thinks
            if ( File::fileExists( $this->getFileFullPath() ) )
            {
                // OK, try and DELETE the file
                if ( ! @unlink($this->getFileFullPath() ) )
                {
                    // File can not be deleted, update the error code
                    $this->_setErrorMsg ( $this->getFileFullPath() );
                    $this->_flagError ( FILE_CANNOT_DELETE );
                    $_retValue = false;
                }
                else
                {
                    // Well, PHP thinks it deleted the file, but make sure
                    if ( File::fileExists( $this->getFileFullPath() ) )
                    {
                        // File was not deleted and we don't know why, update the error code
                        $this->_setErrorMsg ( $this->getFileFullPath() );
                        $this->_flagError ( FILE_UNKNOWN );
                        $_retValue = false;
                    }
                }   // if ( ! @unlink($this->getFileFullPath() ) )
            }   // if ( File::fileExists( $this->getFileFullPath() ) )

            else
            {
                // File was not found, update the error code
                $this->_setErrorMsg ( $this->getFileFullPath() );
                $this->_flagError ( FILE_NOT_FOUND );
                $_retValue = false;
            }   // else - if ( File::fileExists( $this->getFileFullPath() ) )
        }   // if ( $this )

        // This is a STATIC call
        else
        {
            // Verify that the file actually exists
            if ( File::fileExists( $_path ) )
            {
                // OK, try and DELETE the field
                if ( ! @unlink( $_path ) )
                {
                    // File can not be deleted, update the error code
                    $_retValue = 'Error: File Can Not Be Deleted';
                }
                else
                {
                    // Well, PHP thinks it deleted the file, make sure
                    if ( File::fileExists( $_path ) )
                    {
                        // File was not deleted and we don't know why, update the error code
                        $_retValue = 'Error: Unknown Error, File Not Deleted';
                    }
                }   // else - if ( ! @unlink($this->getFileFullPath() ) )
            }   // if ( File::fileExists( $_path ) )

            else
            {
                $_retValue = 'Error: File Not Found';
            }   // else - if ( File::fileExists( $_path ) )

        }   // if ( $this )

        clearstatcache();

        return $_retValue;
    }

   /**
    * Method public bool fileCopy ( string )
    *
    * Attempts to copy a file.
    * If the COPY succeeds, a boolean TRUE is returned.
    * If the COPY failes, an error message will be returned.
    * This method can be called STATICally
    *
    * @name fileCopy
    * @access public
    *
    * @uses method @copy()    Built-in PHP method
    * @static
    * @final
    *
    * @param  string  $_param_1    STATIC Use: Complete or relative File System path of file
    * @param  string  $_param_1    CLASS Use: Complete or relative File System path for new file
    * @param  string  $_param_2    STATIC Use: Complete or relative File System path for new file
    * @param  string  $_param_2    CLASS Use: Indicates whether this operation can overwrite a file
    * @param  boolean $_param_3    STATIC Use: Indicates whether this operation can overwrite a file
    * @param  boolean $_param_3    STATIC Use: Not used
    * @return mixed   boolean      Indicates success
    *                 string       Error message on failure
    *
    * @since 1.1
    */
    function fileCopy ( $_param_1 = false, $_param_2 = false, $_param_3 = false )
    {
       /**
        * Variable local $_retValue
        * @var bool $_bolSize Indicates success or failure of operation
        * @name $_bolDirTest
        *
        * Indicates success or failure of operation
        * default value of TRUE
        *
        * @access private
        *
        */
        // Default Return Value
        $_retValue = true;

        // Determine if this is a STATIC or a CLASS call

        // This is a CLASS call
        if ( $this )
        {
            // Verify that the orginal file actually exists, regardless
            // of what the Class thinks
            if ( File::fileExists( $this->getFileFullPath() ) )
            {
                // Tear apart the new file name given.
                // We need to determine its directory path and the new
                // file name to use
                $path_parts  = pathinfo($_param_1);
                $newFileName = $path_parts['basename'];
                $dirName     = $path_parts['dirname'];

                // Make sure we have a destination
                if ( ! $this->getDestDir () )
                    $this->setDestDir( $dirName . '/' );

                // If we don't have a new file name, but we have a new directory,
                // then this is really is a MOVE.
                // Is the path given to RENAME the file to just a file name or
                // a complete path?

                // If no file name was given, but a path was, then we need to "move" this file
                if ( ( empty ($newFileName) ) &&
                     // Just make sure the target directory is not the same as the current one
                     ( $this->getFileFullPath() != $this->getDestDir() ) )
                {
                    $newFileName = $this->getFileName;
                }

                // If everything is fine so far, we can complete the procedure!
                if ( $this->getSuccess() )
                {
                    $_orgFile = $this->getFileFullPath();
                    $_newFile = $this->getDestDir() . $newFileName;

                    // Now see if the file path we are to copy to exists
                    if ( ( File::fileExists( $_newFile ) ) && ( ! $this->getFileOverWrite() ) )
                    {
                        // File can not be renamed, update the error code
                        $this->_setErrorMsg ( $_newFile );
                        $this->_flagError ( FILE_ALREADY_EXISTS );
                        $_retValue = false;
                    }

                    else if ( @copy($_orgFile, $_newFile ) )
                    {
                        // Well, PHP thinks it renamed the file, but make sure
                        if ( ! File::fileExists( $_newFile ) )
                        {
                            // File was not copied/renamed and we don't know why, update the error code
                            $this->_setErrorMsg ( $_orgFile );
                            $this->_flagError ( FILE_UNKNOWN );
                            $_retValue = false;
                        }
                    }
                    else
                    {
                        // File can not be renamed, update the error code
                        $this->_setErrorMsg ( $_orgFile );
                        $this->_flagError ( FILE_CANNOT_COPY );
                        $_retValue = false;
                    }   // else - if ( @copy($_orgFile, $_orgFile ) )
                }   // if ( $this->getSuccess() )
            }   // if ( File::fileExists( $this->getFileFullPath() ) )
        }   // if ( $this )

        else
        // This is a STATIC Call
        {
            // Verify that the orginal file actually exists
            if ( File::fileExists( $_param_1 ) )
            {
                // Tear apart the new file name given.
                // We need to determine its directory path and the new
                // file name to use
                $_path_parts  = pathinfo($_param_2);
                $_newFileName = $_path_parts['basename'];
                $_dirPath     = $_path_parts['dirname'];

                // We have to make an assumption on what './' [current] and '../' [parent
                // directory] really means.
                // It could be 'current' to the File defined in the Class, or
                // it could be 'current' to what the Web Server/current app thinks
                // is the 'current' directory.
                // Our assumption will be that './' and '../' will be relative to the File
                // defined within the Class.
                if ( ( $_dirPath == '.' ) || ( $_dirPath == './' ))
                {
                    // Set full path to destination directory
                    $_dirPath = dirname( $_param_1 );
                }
                else if ( ( $_dirPath == '..' ) || ( $_dirPath == '../' ))
                {
                    // Set full path to destination directory
                    $_dirPath = dirname( dirname( $_param_1 )  );
                }

                // Now, put new file path name back together
                $_param_2 = $_dirPath  . '/' . $_newFileName;

                // Now see if the file path we are to COPY to exists
                if ( ( File::fileExists( $_param_2 ) ) && ( ! $_param_3 ) )
                {
                    // File can not be renamed, update the error code
                    $_retValue = 'Error: File Already Exists';
                }

                // OK, try and COPY the file
                else
                {
                    if ( @copy($_param_1, $_param_2 ) )
                    {
                        // Well, PHP thinks it copied the file, but make sure
                        if ( ! File::fileExists( $_param_2 ) )
                        {
                            // File was not copied and we don't know why, update the error code
                            $_retValue = 'Error: Unknown Error, File Not Copied';
                        }
                    }
                    else
                    {
                        // File can not be copied, update the error code
                        $_retValue = 'Error: File Can Not Be Copied';
                    }   // else - if ( ! @copy($this->getFileFullPath(), $_param_1 ) )
                }   // else - if ( ( File::fileExists( $_param_1 ) ) && ( ! $this->getFileOverWrite() ) )
            }   // if ( File::fileExists( $this->getFileFullPath() ) )

            else
            {
                // File was not found, update the error code
                $_retValue = 'Error: File Not Found';
            }   // else - if ( File::fileExists( $this->getFileFullPath() ) )

        }   // else - if ( $this )


        clearstatcache();

        return $_retValue;
    }
   /**
    * Method public bool fileRename ( string )
    *
    * Attempts to rename a file.
    * If the RENAME succeeds, a boolean TRUE is returned.
    * If the RENAME failes, an error message will be returned.
    * This method can be called STATICally
    *
    * @name fileRename
    * @access public
    *
    * @uses method @rename()    Built-in PHP method
    * @static
    * @final
    *
    * @param  string  $_param_1    STATIC Use: Complete or relative File System path of file
    * @param  string  $_param_1    CLASS Use: Complete or relative File System path for new file
    * @param  string  $_param_2    STATIC Use: Complete or relative File System path for new file
    * @param  string  $_param_2    CLASS Use: Indicates whether this operation can overwrite a file
    * @param  boolean $_param_3    STATIC Use: Indicates whether this operation can overwrite a file
    * @param  boolean $_param_3    STATIC Use: Not used
    * @return mixed   boolean      Indicates success
    *                 string       Error message on failure
    *
    * @since 1.1
    */
    function fileRename ( $_param_1 = false, $_param_2 = false, $_param_3 = false )
    {
       /**
        * Variable local $_retValue
        * @var bool $_bolSize Indicates success or failure of operation
        * @name $_bolDirTest
        *
        * Indicates success or failure of operation
        * default value of TRUE
        *
        * @access private
        *
        */
        // Default Return Value
        $_retValue = true;

        // Determine if this is a STATIC or a CLASS call

        // This is a CLASS call
        if ( $this )
        {
            // Verify that the orginal file actually exists, regardless
            // of what the Class thinks
            if ( File::fileExists( $this->getFileFullPath() ) )
            {
                // Tear apart the new file name given.
                // We need to determine its directory path and the new
                // file name to use
                $path_parts  = pathinfo($_param_1);
                $newFileName = $path_parts['basename'];
                $dirName     = $path_parts['dirname'];

                // Make sure we have a destination
                if ( ! $this->getDestDir () )
                    $this->setDestDir( $dirName . '/' );

                // If we don't have a new file name, but we have a new directory,
                // then this is really is a MOVE.
                // Is the path given to RENAME the file to just a file name or
                // a complete path?

                // If no file name was given, but a path was, then we need to "move" this file
                if ( ( empty ($newFileName) ) &&
                     // Just make sure the target directory is not the same as the current one
                     ( $this->getFileFullPath() != $this->getDestDir() ) )
                {
                    $newFileName = $this->getFileName();
                }

                // If everything is fine so far, we can complete the procedure!
                if ( $this->getSuccess() )
                {
                    $_orgFile = $this->getFileFullPath();
                    $_newFile = $this->getDestDir() . '/' . $newFileName;

                    // Now see if the file path we are to renamed to exists
                    if ( ( File::fileExists( $_newFile ) ) && ( ! $this->getFileOverWrite() ) )
                    {
                        // File can not be renamed, update the error code
                        $this->_setErrorMsg ( $_newFile );
                        $this->_flagError ( FILE_ALREADY_EXISTS );
                        $_retValue = false;
                    }

                    else if ( @rename($_orgFile, $_newFile ) )
                    {
                        // Well, PHP thinks it renamed the file, but make sure
                        if ( ! File::fileExists( $_newFile ) )
                        {
                            // File was not copied/renamed and we don't know why, update the error code
                            $this->_setErrorMsg ( $_orgFile );
                            $this->_flagError ( FILE_UNKNOWN );
                            $_retValue = false;
                        }

                        // Now modify the file name property
                        $this->_setFileName( $_newFile );
                    }
                    else
                    {
                        // File can not be renamed, update the error code
                        $this->_setErrorMsg ( $_orgFile );
                        $this->_flagError ( FILE_CANNOT_COPY );
                        $_retValue = false;
                    }   // else - if ( @copy($_orgFile, $_orgFile ) )
                }   // if ( $this->getSuccess() )
            }   // if ( File::fileExists( $this->getFileFullPath() ) )
        }   // if ( $this )

        else
        // This is a STATIC Call
        {
            // Verify that the orginal file actually exists
            if ( File::fileExists( $_param_1 ) )
            {
                // Tear apart the new file name given.
                // We need to determine its directory path and the new
                // file name to use
                $_path_parts  = pathinfo($_param_2);
                $_newFileName = $_path_parts['basename'];
                $_dirPath     = $_path_parts['dirname'];

                // We have to make an assumption on what './' [current] and '../' [parent
                // directory] really means.
                // It could be 'current' to the File defined in the Class, or
                // it could be 'current' to what the Web Server/current app thinks
                // is the 'current' directory.
                // Our assumption will be that './' and '../' will be relative to the File
                // defined within the Class.
                if ( ( $_dirPath == '.' ) || ( $_dirPath == './' ))
                {
                    // Set full path to destination directory
                    $_dirPath = dirname( $_param_1 );
                }
                else if ( ( $_dirPath == '..' ) || ( $_dirPath == '../' ))
                {
                    // Set full path to destination directory
                    $_dirPath = dirname( dirname( $_param_1 )  );
                }

                // Now, put new file path name back together
                $_param_2 = $_dirPath  . '/' . $_newFileName;

                // Now see if the file path we are to RENAME to exists
                if ( ( File::fileExists( $_param_2 ) ) && ( ! $_param_3 ) )
                {
                    // File can not be renamed, update the error code
                    $_retValue = 'Error: File Already Exists';
                }

                // OK, try and RENAME the file
                else
                {
                    if ( @rename($_param_1, $_param_2 ) )
                    {
                        // Well, PHP thinks it renamed the file, but make sure
                        if ( ! File::fileExists( $_param_2 ) )
                        {
                            // File was not renamed and we don't know why, update the error code
                            $_retValue = 'Error: Unknown Error, File Not Copied';
                        }
                    }
                    else
                    {
                        // File can not be renamed, update the error code
                        $_retValue = 'Error: File Can Not Be Copied';
                    }   // else - if ( ! @copy($this->getFileFullPath(), $_param_1 ) )
                }   // else - if ( ( File::fileExists( $_param_1 ) ) && ( ! $this->getFileOverWrite() ) )
            }   // if ( File::fileExists( $this->getFileFullPath() ) )

            else
            {
                // File was not found, update the error code
                $_retValue = 'Error: File Not Found';
            }   // else - if ( File::fileExists( $this->getFileFullPath() ) )

        }   // else - if ( $this )


        clearstatcache();

        return $_retValue;
    }

   /**
    * Method public bool fileMove ( string )
    *
    * Attempts to move a file.
    * If the MOVE succeeds, a boolean TRUE is returned.
    * If the MOVE failes, an error message will be returned.
    * This method is an alias to fileRename(), since 'rename' performs the same function
    * This method can be called STATICally
    *
    * @name fileMove
    * @access public
    *
    * @uses method File::fileRename()    Method to rename a file
    * @static
    * @final
    *
    * @param  string  $_param_1    STATIC Use: Complete or relative File System path of file
    * @param  string  $_param_1    CLASS Use: Complete or relative File System path for new file
    * @param  string  $_param_2    STATIC Use: Complete or relative File System path for new file
    * @param  string  $_param_2    CLASS Use: Indicates whether this operation can overwrite a file
    * @param  boolean $_param_3    STATIC Use: Indicates whether this operation can overwrite a file
    * @param  boolean $_param_3    STATIC Use: Not used
    * @return mixed   boolean      Indicates success
    *                 string       Error message on failure
    *
    * @since 1.1
    */
    function fileMove ( $_param_1 = false, $_param_2 = false, $_param_3 = false )
    {
        // 'rename' can act like a 'move', so we will re-use 'rename'
        // And just pass back what it gave us
        if ( $this )
            $_returnValue = $this->fileRename( $_param_1, $_param_2 );
        else
            $_returnValue = File::fileRename( $_param_1, $_param_2, $_param_3 );

        return $_returnValue;
    }

// ==========================================================
// Error handling methods

  /**
   * Method private void _flagError( string new error message, bool add to system log )
   * Sets error message
   *
   * @name flagError()
   *
   * @uses method   _setErrorMsg()  Sets property with current error message
   * @uses property $_fileSuccess   Success or Failure of last operation
   *
   * @final
   * @access public
   *
   * @since 1.0
   *
   * @param string $conErrCode Constant defining error
   * @param bool   $bolAddLog Add message to system log, or not
   * @return none
   *
   */
    function _flagError ( $conErrCode = null, $bolAddLog = true, $logType = null )
    {
        if ( $conErrCode )
        {
            // Set failure indicator
            $this->_setSuccess ( false );

            // Set Error Code
            $this->_errCode = $conErrCode;

            // Stick it the log, maybe
            if ( $bolAddLog )
            {
                $_errMsg  = date("Y.j.Y g:i:s A") . "\t";
                $_errMsg .= $this->getErrorMsg() . "\n";

                // Send error to file
                if ( $logType == LOG_TYPE_FILE )
                    error_log( $_errMsg, $logType, 'logs/statements.err' );
            }
        }
    }

  /**
   * Method private void _setErrorMsg( string new error message )
   *
   * Sets property with current error message
   *
   * @name _setErrorMsg()
   * @author Walter Torres <walter@torres.ws>
   *
   * @uses property _errMsg      Current Error Message, if any
   *
   * @final
   * @access private
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

  /**
   *  Method public string getErrorMsg( string )
   *
   * Sets property with current error message
   *
   * @name _setErrorMsg()
   *
   * @uses method   getFileName()       Retrieves File Name
   * @uses method   getErrorCode()      Current Error Code, if any
   * @uses property _file_error_codes   Array of possible error codes
   * @uses property _errMsg             Current Error Message, if any
   *
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @param  void
   * @return string  Error Message
   *
   */
    function getErrorMsg( )
    {
        $_errMsg = 'No Errors Generated.';

        if ( $this->getErrorCode() )
        {
            $_errMsg = sprintf($this->_file_error_codes[$this->getErrorCode()], $this->_errMsg );
        }

        return $_errMsg;
    }

  /**
   * Method public int getErrorCode( void )
   *
   * Sets property with current error message
   *
   * @name getErrorCode()
   *
   * @uses property $_errCode  Error Code upon Failure
   *
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @param void
   * @return int  Error Code
   *
   */
    function getErrorCode()
    {
        return $this->_errCode;
    }

  /**
   * Method public void _setSuccess( boolean )
   *
   * Defines property indicating success or failure of some kind
   *
   * @name _setSuccess()
   *
   * @uses property $_fileSuccess   Success or Failure of last operation
   *
   * @final
   * @access private
   *
   * @since 1.00
   *
   * @param boolean $_status defines success or failure of some kind
   * @return void
   *
   */
    function _setSuccess( $_status = true )
    {
        $this->_fileSuccess = $_status;
    }

  /**
   * Method public boolean getSuccess( void )
   *
   * Returns property indicating success or failure of some kind
   *
   * @name getSuccess()
   *
   * @uses property $_fileSuccess   Success or Failure of some operation
   *
   * @final
   * @access public
   *
   * @since 1.00
   *
   * @param void
   * @return boolean  Indicating success or failure of some kind
   *
   */
    function getSuccess()
    {
        return $this->_fileSuccess;
    }

// =============================================================
};  // end of Class



// =============================================================
// =============================================================
// ** CSV Version Control Info

/**
 * $RCSfile: files.php,v $
 * $Revision: 1.8 $
 * $Date: 2005/09/22 03:13:29 $
 * $Author: jswalter $
 *
 * $Log: files.php,v $
 * Revision 1.8  2005/09/22 03:13:29  jswalter
 *  - slight change to 'fileRename()' to update file name property
 *
 * Revision 1.7  2005/09/21 22:58:50  vanmer
 * - added include directory required file and global declaration for files.php and files_test.php
 *
 * Revision 1.6  2005/09/08 17:05:38  jswalter
 *  - overhaul of Class
 *  - new methods, many revamped mathods
 *
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