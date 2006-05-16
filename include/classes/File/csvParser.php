<?php

//
// +------------------------------------------------------------------------+
// | csvParser - Part of the PHP Yacs Library                               |
// +------------------------------------------------------------------------+
// | Copyright (c) 2004-2005 Walter Torres                                  |
// | Email         walter@torres.ws                                         |
// | Web           http://web.php-yacs.org                                  |
// | Mirror        http://php-yacs.sourceforge.net/                         |
// | $Id: csvParser.php,v 1.5 2006/05/16 02:10:13 jswalter Exp $             |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//

/**
 * CSV File Parser - Part of the PHP Yacs Library
 *
 *   With this Class you can easliy load and parse CSV type Files,
 *   regardless of orginal system; UNIX, Windows or Mac.
 *
 *   This Class can
 *   - specify if column headers should be used or not
 *   - define an external array to use as field headers
 *   - whether to keep or remove empty records
 *   - retrieve single records, single columns or even a individual field
 *   - define a series of characters as field delimiters
 *   NOTE:
 *     This class is based off of work by Stanislav Karchebny.
 *     It is highly modified, way beyond its orginal scope and dimension.<br/>
 *     Thanks Stanislav.
 *
 * @package    File_Handling
 * @subpackage Parsers
 *
 * @originator  Stanislav Karchebny <berk@inbox.ru>
 * @author      Walter Torres <walter@torres.ws>
 * @contributor Aaron Van Meerten
 *
 * @version   $Id: csvParser.php,v 1.5 2006/05/16 02:10:13 jswalter Exp $
 * @date      $Date: 2006/05/16 02:10:13 $
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
    * @constant CSV_VER
    */
    define('CSV_VER', '1.14', false);

   /**
    * CSV Parser Success value
    * @constant CSV_SUCCEED
    */
    define('CSV_SUCCEED', true, false);

   /**
    * CSV Parser Fail value
    * @constant CSV_FAIL
    */
    define('CSV_FAIL', false, false);

   /**
    * Standard field container, quote
    * @constant CSV_QUOTE
    */
    define('CSV_QUOTE', '"', false);

   /**
    * Standard field delimiter, comma
    * @constant CSV_COMMA
    */
    define('CSV_COMMA', ',', false);

   /**
    * Standard record delimiter, return
    * @constant CSV_COMMA
    */
    define('CSV_EOR', "/n", false);

   /**
    * The default number of bytes for reading
    * @constant CSV_FILE_DEFAULT_READSIZE
    */
    define('CSV_FILE_DEFAULT_READSIZE', 1024, false);

   /**
    * Mode to use for reading from files
    * @constant CSV_FILE_MODE_READ
    */
    define('CSV_FILE_MODE_READ', 'rb', false);

   /**
    * Mode to use for truncating files, then writing
    * @constant CSV_FILE_MODE_WRITE
    */
    define('CSV_FILE_MODE_WRITE', 'wb', false);

   /**
    * Mode to use for appending to files
    * @constant CSV_FILE_MODE_APPEND
    */
    define('CSV_FILE_MODE_APPEND', 'ab', false);

   /**
    * Use this when a shared (read) lock is required
    * @constant CSV_FILE_LOCK_SHARED
    */
    define('CSV_FILE_LOCK_SHARED', LOCK_SH, false);

   /**
    * Use this when an exclusive (write) lock is required
    * @constant CSV_FILE_LOCK_EXCLUSIVE
    */
    define('CSV_FILE_LOCK_EXCLUSIVE', LOCK_EX, false);


// ===========================================================
// Error codes and messages

   /**
    * Improper parameters
    * @constant CSV_INVALID_PARAMETERS
    */
    define('CSV_INVALID_PARAMETERS', 50, false);

   /**
    * Invalid Record Number/Name
    * @constant CSV_INVALID_REC_NUM
    */
    define('CSV_INVALID_REC_NUM', 51, false);

   /**
    * Invalid Column Number/Name
    * @constant CSV_INVALID_COL_NUM
    */
    define('CSV_INVALID_COL_NUM', 52, false);

   /**
    * Invalid parameter, must be an integer
    * @constant CSV_NOT_A_INT
    */
    define('CSV_NOT_A_INT', 53, false);

   /**
    * Invalid parameter, must be an number
    * @constant CSV_NOT_A_NUM
    */
    define('CSV_NOT_A_NUM', 54, false);

   /**
    * File not found
    * @constant CSV_FILE_NOT_FOUND
    */
    define('CSV_FILE_NOT_FOUND', 100, false);

   /**
    * Can not open file
    * @constant CSV_FILE_CANNOT_OPEN
    */
    define('CSV_FILE_CANNOT_OPEN', 101, false);

   /**
    * Can not close file
    * @constant CSV_FILE_CANNOT_CLOSE
    */
    define('CSV_FILE_CANNOT_CLOSE', 102, false);

   /**
    * Can not read file
    * @constant CSV_FILE_NOT_READABLE
    */
    define('CSV_FILE_NOT_READABLE', 103, false);

   /**
    * Can not write to file
    * @constant CSV_FILE_NOT_WRITEABLE
    */
    define('CSV_FILE_NOT_WRITEABLE', 104, false);

   /**
    * Can not create output file
    * @constant CSV_FILE_CANNOT_CREATE
    */
    define('CSV_FILE_CANNOT_CREATE', 105, false);

   /**
    * File can not be locked
    * @constant CSV_FILE_CANNOT_LOCK
    */
    define('CSV_FILE_CANNOT_LOCK', 106, false);

   /**
    * File can not be unlocked
    * @constant CSV_FILE_CANNOT_UNLOCK
    */
    define('CSV_FILE_CANNOT_UNLOCK', 107, false);

   /**
    * File already exists
    * @constant CSV_FILE_ALREADY_EXISTS
    */
    define('CSV_FILE_ALREADY_EXISTS', 108, false);

   /**
    * File Path not defined
    * @constant CSV_FILE_NOT_DEFINED
    */
    define('CSV_FILE_NOT_DEFINED', 109, false);


// ===========================================================
// Log types for PHP's native error_log() function.

   /**
    * Use PHP's system logger
    * @constant LOG_TYPE_SYSTEM
    */
    define('LOG_TYPE_SYSTEM', 0, false);

   /**
    * Use PHP's mail() function
    * @constant LOG_TYPE_MAIL
    */
    define('LOG_TYPE_MAIL', 1, false);

   /**
    * Use PHP's debugging connection
    * @constant LOG_TYPE_DEBUG
    */
    define('LOG_TYPE_DEBUG', 2, false);

   /**
    * Append to a file
    * @constant LOG_TYPE_FILE
    */
    define('LOG_TYPE_FILE', 3, false);

/**
 * CSV File Parser - Part of the PHP Yacs Library
 *
 * Easy to use and easy to configure
 *
 * @package    File_Handling
 * @subpackage Parsers
 *
 * @extends File
 *
 * @date  $Date: 2006/05/16 02:10:13 $
 *
 * @todo Have this class extend FILE to utilize common File operations
 *
 * @tutorial http://web.torres.ws/dev/php/csvParser Complete Class tutorial
 * @example http://web.torres.ws/dev/php/csvParser/example.php csvParser examples
 *
 */
class csvParser //extends File
{
  // ==========================================================
  // Class Properties

   /**
    * Property private string $_csvFileName
    *
    * The name of the CSV file this instance is dealing with
    *
    * @var string
    *
    * @access private
    * @since 2.04
    */
    var $_csvFileName = null;

   /**
    * Property private array $_myHeaders
    *
    * Array containing CSV File Headers manually set at runtime
    *
    * @name $_myHeaders
    * @var array
    * @property array $_myHeaders Array containing externally defined
    *                             CSV File Headers, if any.
    *
    * @access private
    * @since 2.0
    */
    var $_myHeaders = false;

   /**
    * Property private array $_cvsHeaders
    *
    * Array containing CSV Column Headers pulled from file
    *
    * @name $_cvsHeaders
    * @var array
    * @property array $_cvsHeaders Array Column Headers pulled from file
    *
    * @access private
    * @since 1.6
    */
    var $_cvsHeaders = array();

   /**
    * Property private array $_cvsRecords
    *
    * Array containing CSV File Record Data, if any.
    *
    * @name $_cvsRecords
    * @var array
    * @property private array Array containing CSV File Record Data, if any.
    *
    * @access private
    * @since 1.0
    */
    var $_cvsRecords = array();

   /**
    * Property private int $_TuseRecordIDs
    *
    * Do we use a particular column number to use as Record ID
    *
    * @name $_TuseRecordIDs
    * @var int
    * @property private int Do we use a particular column number to use as Record ID
    *
    * @access private
    * @since 2.20
    */
    var $_TuseRecordIDs = false;

   /**
    * Property private int $_TrecordID
    *
    * Record column number to use as Record ID
    *
    * @name $_TrecordID
    * @var int
    * @property private int Record column number to use as Record ID
    *
    * @access private
    * @since 2.20
    */
    var $_TrecordID = 0;

   /**
    * Property private int $_TuseHeaders
    *
    * Do we use a CVS headers as column keys
    * Default to FALSE, don't use first row as a header
    *
    * @name $_TuseHeaders
    * @var bool
    * @property private bool Do we use a CVS headers as column keys
    *
    * @access private
    * @since 2.20
    */
    var $_TuseHeaders = false;

   /**
    * Property private int $_TheaderRow
    *
    * If we use a CVS headers as column keys, then
    * which row is to be used.
    * Default is first row.
    *
    * @name $_TheaderRow
    * @var int
    * @property private int Which record is used as column keys
    *
    * @access private
    * @since 2.14
    */
    var $_TheaderRow = 0;

   /**
    * Property private bol $_TremoveBlankRecord
    *
    * Blank rows from file should not be captured
    * Default is to keep blank rows.
    *
    * @name $_TremoveBlankRecord
    * @var bol
    * @property private bol If blank rows from file should not be captured
    *
    * @access private
    * @since 2.20
    */
    var $_TremoveBlankRecord = false;

   /**
    * Property private int $_numRec
    *
    * Record Count for this file
    * Default ZERO records
    *
    * @name $_numRec
    * @var int
    * @property private int Record Count for this file
    *
    * @access private
    * @since 1.0
    */
    var $_numRec = 0;

   /**
    * Property private int $_numCol
    *
    * Column Count for this file
    * Default ZERO columns
    *
    * @name $_numCol
    * @var int
    * @property private int Column Count for this file
    *
    * @access private
    * @since 2.0
    */
    var $_numCol = 0;

   /**
    * Property private string $_DefaultDelimiter
    *
    * Default Field Delimiter
    * Default value: COMMA
    *
    * @name $_delimiter
    * @var string
    * @property private string Default Field Delimiter
    *
    * @access private
    * @since 2.0
    */
    var $_DefaultDelimiter = ',';

   /**
    * Property private int $_delimiter
    *
    * Current Field Delimiter
    * Default value is set at instantiation time
    *
    * @name $_delimiter
    * @var string
    * @property private string Current Field Delimiter
    *
    * @access private
    * @since 2.0
    */
    var $_delimiter = '';

   /**
    * Property private $_TfileSize
    *
    * File Size of CSV File
    *
    * @name $_errMsg
    * @var string
    * @property private string $_TfileSize Size of CSV File
    *
    * @access private
    * @since 2.20
    */
    var $_TfileSize = null;

   /**
    * Property private string $_preprocess
    *
    * Name of Preprocess Callback Function
    *
    * @name $_preprocess
    * @var boostringl
    * @property private string Preprocess Callback Function name
    *
    * @access private
    * @since 1.5
    */
	var $_preprocess = null;

   /**
    * Property private int $_errCode
    *
    * Error Code upon Failure
    *
    * @name $_errCode
    * @var bool
    * @property private boolean Error Code upon Failure
    *
    * @access private
    * @since 2.04
    */
    var $_errCode = null;

   /**
    * Property private string $_errLogPath
    *
    * Full System Path to place error log file
    *
    * @name $_errLogPath
    * @var string
    * @property private string Full System Path to place error log file
    *
    * @access private
    * @since 2.04
    */
    var $_errLogPath = '/tmp';

   /**
    * Property private string $_errLogFileName
    *
    * Error log file name
    *
    * @name $_errLogFileName
    * @var string
    * @property private string Error log file name
    *
    * @access private
    * @since 2.04
    */
    var $_errLogFileName = 'csv_parser_err.log';

   /**
    * Property private boolean $_parseSuccess
    *
    * Success or Failure
    * Default value is set at TRUE, think positive!
    *
    * @name $_parseSuccess
    * @var bool
    * @property private boolean Success or Failure
    *
    * @access private
    * @since 2.04
    */
    var $_parseSuccess = true;


// ==========================================================
// Class methods

// {{{ csvParser
   /**
    * Constructor public object CsvParser(void)
    *
    * Class constructor
    *
    * @name csvParser()
    * @access public
    * @category Constructor
    * @since 1.0
    *
    * @static
    * @final .
    *
    * @return object
    */
    function csvParser()
    {
        // Set delimiter from default delimiter property
        $this->resetDelimiter();
    }
// }}}
// {{{ parseFile
   /**
    * Method public boolean parseFile(file path, read header boolean)
    *
    * The central method of this Class. All the real work is performed here.
    *
    * @name parseFile()
    * @access public
    * @category categoryname
    * @since 1.0
    *
    * @uses $_cvsHeaders            Store header names from either file or array
    * @uses $_cvsRecords            Store current Record data in Class Object
    * @uses $_headerColCount        How many fields do we have?
    * @uses $_myHeaders             Externaly defined array
    * @uses $_numRec                How many records do we have?
    * @uses $_setCSVfile            Store the file to parse
    * @uses getHeaderRow()          pull the defined row out
    * @uses getRemoveBlankRecord()  determines if to keep empty records
    * @uses getUseRecordID()        use a record ID or a placement ID
    * @uses getHeaderField()        get column name
    * @uses getRecordID()           get Record Index Column
    * @uses useHeaders()            determine if Header Row is to be used
    * @uses _flagError()            set which error has occured
    * @uses _labelArray()           adds field labels to record array
    * @uses _getCSVrecord()         parse individual record from file
    * @uses _getUseHeaders()        determine if named columns are to be used
    * @uses _setFileSize()          how large, in bytes, is current file
    * @uses constant CSV_FILE_NOT_FOUND
    * @uses constant CSV_FILE_NOT_READABLE
    * @uses constant CSV_FILE_NOT_DEFINED
    *
    * @static
    * @final .
    *
    * @author Walter Torres <walter@torres.ws> [with a *lot* of help!]
    * @contributor Aaron VanMeeden <ke@braverock.com>
    *
    * @todo This loop reads a row at a time, until we hit the row
    *       defined as 'Header'.
    *
    *       What is does not do is actually REMOVE thes rows for the data set.
    *
    *       This is not as simple a POPping each row as we read it.
    *       We might have a need to "read" the header row, but still keep
    *       That [those] row[s] in place.
    *
    *       Right now, off the top of my head:
    *        - create new property: keepHeader - boolean
    *        - if FALSE (default) POP row, otherwise leave it alone.
    *
    * @param  string $file         system-based path to file to parse
    * @param  bool   $readHeaders  Read first Row of file as Headers, optional
    * @return bool   $_bolRead     Indicates if file was read or not
    */
    function parseFile($file = NULL, $readHeaders = null)
    {
        // This will only work if we have a file to hit
        if ( (! $file) || ( ! strlen($file)) )
        {
            $this->_flagError ( CSV_FILE_NOT_DEFINED, true );
            return;
        }

        // Store the file to parse
        $this->_setCSVfile ( $file );

        // Do we use the first row as Column Headers?
        // Just because this parameter ($readHeaders) might be TRUE,
        // doesn't necessarily mean we need to use the first row as Headers.
        // It might mean just pull the first row and toss it.

        // To determine whether to use the Headers as column titles,
        // 'useHeaders' must be read. If $readHeaders is TRUE and
        // 'useHeaders' is FALSE, then the first row will be tossed.
        // Otherwise the Headers will be used as column titles.
        // *** issue: how to handle "tossing" header row
        // ***        This commented out to defer this issue for later review
        // if ( $this->_getUseHeaders() == 'not set' )  // methods can not return NULL
        if ( isset ( $readHeaders ) )
            $this->useHeaders( $readHeaders );

       /**
        * Variable local bool $_bolRead
        *
        * Indicates if file was read or not.
        * Default value is set at TRUE, think positive!
        *
        * @var bool $_bolRead Indicates if file was read or not
        * @access private
        * @static
        */
        $_bolRead = true;

       /**
        * Variable local string $_fileHandle
        *
        * File Handle to CSV File
        *
        * @var string $_fileHandle File Pointer to CSV File
        * @access private
        * @static
        */
        $_fileHandle = null;

       /**
        * Variable local array $_aryRecord
        *
        * Entire Record in a simple array
        *
        * @var array $_aryRecord Entire Record in a simple array
        * @access private
        * @static
        */
        $_aryRecord = null;

       /**
        * Variable local int $_recIdnet
        *
        * Record identifier used during each record iteration
        *
        * @var int $_recIdnet Current Record identifier
        * @access private
        * @static
        */
        $_recIdnet = null;


        // See if the file even exists
        if (file_exists($file))
        {
            // See if we can open the file
            $_fileHandle = fopen($file, CSV_FILE_MODE_READ);

            // Looks good!...
            if ($_fileHandle)
            {
                // How big is this file
                $this->_setFileSize( filesize($file) );

                // Do we use a Header Row?
                if ( $this->_getUseHeaders() )
                {
                    // This pulls the defined row out and drops into Header array
                    for ($i = 0; $i <= $this->getHeaderRow(); $i++)
                    {
                        // add code to capture pre-header rows - Aaron

                        // This pulls current row out of file,
                        // and if we come back, it gets replaced with the next row.
                        // We are left with the row indicated by getHeaderRow()
                        $this->_cvsHeaders = ( $this->_myHeaders )
                                             ? $this->_myHeaders
                                             : $this->_getCSVrecord($_fileHandle);


/**
 * @TODO This loop reads a row at a time, until we hit the row
 *       defined as 'Header'.
 *
 *       What is does not do is actually REMOVE thes rows for the data set.
 *
 *       This is not as simple a POPping each row as we read it.
 *       We might have a need to "read" the header row, but still keep
 *       That [those] row[s] in place.
 *
 *       Right now, off the top of my head:
 *        - create new property: keepHeader - boolean
 *        - if FALSE (default) POP row, otherwise leave it alone.
 *
 */

/*
                        if ( $this->_myHeaders )
                            $this->_cvsHeaders = $this->_myHeaders;

                        else
                            $this->_cvsHeaders = $this->_getCSVrecord($_fileHandle);
*/
                    }   // for ($i = 0; $i <= $this->getHeaderRow(); $i++)

                    // "fix" any blank header fields
                    foreach ( $this->_cvsHeaders as $key => $value)
                        if ( $value == '' )
                            $this->_cvsHeaders[$key] = $key;

                    // How many fields do we have?
                    $this->_headerColCount = count ( $this->_cvsHeaders );

                    // guess not, toss them
//                    else
//                        unset ( $this->_cvsHeaders );
                }   // if ( $this->_getUseHeaders() )

                // Start record counter
                $recID = 0;

                // Loop through entire file, pulling out 1 record at a time
                // This will return FALSE at EOF
                while ( $_aryRecord = $this->_getCSVrecord($_fileHandle) )
                {
                     // See if this record is empty
                    if ( $this->getRemoveBlankRecord () )
                        if ( implode("", $_aryRecord) == '' )
                        {
                            continue;
                        }

                    // See if we should use named array, using headers, or not
                    if ( $this->_getUseHeaders() )
                        $_aryRecord = $this->_labelArray( $_aryRecord );

                    // Do we use a record ID or a placement ID
                    if ( $this->getUseRecordID() && $this->_getUseHeaders() )
                    {
                        // Get name of the Record index to use if we are given a number
                        if ( is_string ( $this->getRecordID() ) )
                            $_colIndex = $this->getRecordID();
                        else
                            $_colIndex = $this->getHeaderField($this->getRecordID());

                        // pull the value from that location
                        $_recIdnet = $_aryRecord[$_colIndex];

                        // Now kill that location
                        unset($_aryRecord[$this->getHeaderField($_colIndex)]);

                        // Some records might not have an ID,
                        // so just use their record number
                        if ( ! $_recIdnet )
                            $_recIdnet = $recID;
                    }
                    else
                        $_recIdnet = $recID;

					// Add a callback method here to custom process the inbound record before
					// it is handed off to the Class
					if (function_exists($this->_preprocess))
					{
						$_aryRecord = call_user_func($this->_preprocess, $_aryRecord );
					}

                    // Store current Record data in Class Object
                    $this->_cvsRecords[$_recIdnet] = $_aryRecord;

                    // How many fields do we have?
                    // We only want the value if it is bigger than last time
                    if ( ( count ( $_aryRecord )) > $this->_numCol )
                        $this->_numCol = count ( $_aryRecord );

                    // Next Record
                    $recID++;
                }

                // How many records do we have?
                $this->_numRec = count ( $this->_cvsRecords );
            }   // if ($_fileHandle)

            // I guess not...
            else
                $_bolRead = $this->_flagError( CSV_FILE_NOT_READABLE );

            // OK, we're done, close the file
            fclose($_fileHandle);
        }   // if (file_exists($file))

        // I guess the file does not exists
        else
            $_bolRead = $this->_flagError( CSV_FILE_NOT_FOUND );

    }
// }}}
// {{{ _setFileSize
   /**
    * Method private int _setFileSize( void )
    *
    * Retrieves the size of the open CSV file
    *
    * @name _setFileSize()
    * @access private
    *
    * @uses $_TfileSize
    * @static
    * @final .
    *
    * @param pointer $_filePointer File Handle to CSV File
    * @return mixed boolean on failure
    *               Record array on success
    *
    * @since 2.20
    */
    function _setFileSize( $_fileSize = NULL )
    {
        if ( $_fileSize )
            $this->_TfileSize = $_fileSize;
    }
// }}}
// {{{ getFileSize
   /**
    * Method public int getFileSize( void )
    *
    * Retrives the size of the open CSV file
    *
    * @name getFileSize())
    * @access public
    *
    * @uses $_TfileSize
    * @static
    * @final .
    *
    * @param pointer $_filePointer File Handle to CSV File
    * @return mixed boolean on failure
    *               Record array on success
    *
    * @since 2.0
    */
    function getFileSize()
    {
        return $this->_TfileSize;
    }
// }}}
// {{{ _getCSVrecord
   /**
    * Method private mixed _getCSVrecord( file pointer, delimiter string )
    *
    * Retrieves a single record for the open CSV file
    *
    * @name _getCSVrecord()
    * @access public
    *
    * @uses getFileSize()
    * @static
    * @final .
    *
    * @param pointer $_filePointer File Handle to CSV File
    * @return mixed boolean on failure
    *               Record array on success
    *
    * @since 2.0
    */
    function _getCSVrecord($_filePointer)
    {
        // Try and pull a record out of the file
        if ($data = fgetcsv($_filePointer, $this->getFileSize(), $this->getDelimiter()))
        {
            // Strip whitespace from each element
            $data = array_map('trim', $data);

            return $data;
        }

        // No record found
        else
            // Tell caller we failed
            return false;
    }
// }}}
// {{{ _labelArray
   /**
    * Method private mixed _labelArray( array record )
    *
    * Retrives a single record for the open CSV file
    *
    * @name _labelArray()
    * @access public
    *
    * @static
    * @final .
    *
    * @param array $_oldRecord Orginal array to 'update'
    * @return array modified array
    *
    * @since 2.20
    */
    function _labelArray( $_oldRecord )
    {
        $header=$this->_recordHeaders($_oldRecord);
        foreach ($_oldRecord as $key => $value)
        {
            if ( isset ( $header[$key] ) )
                $fieldID = $header[$key];
            else
                $fieldID = $key;

            $_newRecord[$fieldID] = $_oldRecord[$key];
        }

        return $_newRecord;
    }
// }}}
// {{{ _recordHeaders
   /**
    * Method private mixed _recordHeaders( array record )
    *
    * Retrieves the headers for a single record for the open CSV file
    *
    * @name _recordHeaders()
    * @access public
    *
    * @static
    * @final 
    *
    * @param array $_oldRecord array for which to retrieves headers 
    * @return array header array for passed in record
    *
    * @since r1.2 
    */
    function _recordHeaders($_oldRecord) {
        $headers=$this->_cvsHeaders;
        return $headers;
    }
// }}}
// {{{ getCSVfile
   /**
    * Method private mixed _setCSVfile( void )
    *
    * Returns the name of the CSV file this instance is dealing with
    *
    * @name _setCSVfile())
    * @access public
    *
    * @uses $_csvFileName
    * @final .
    *
    * @param string Name of CSV file this instance is dealing with
    * @return void
    *
    * @since 2.04
    */
    function _setCSVfile( $_file = null )
    {
        $this->_csvFileName = $_file;
    }
// }}}
// {{{ getCSVfile
   /**
    * Method public mixed getCSVfile( void )
    *
    * Returns the name of the CSV file this instance is handling
    *
    * @name getCSVfile()
    * @access public
    *
    * @uses $_csvFileName
    * @static
    * @final .
    *
    * @param void
    * @return string Name of CSV file this instance is dealing with
    *
    * @since 2.04
    */
    function getCSVfile()
    {
        return $this->_csvFileName;
    }
// }}}

// ==========================================================
// Record manipulation methods

// {{{ setHeaders
   /**
    * Method public mixed setHeaders( array $myHeaders )
    *
    * Insert external defined header for data
    *
    * @name setHeaders())
    * @access public
    * @category Record manipulation
    *
    * @uses $_myHeaders
    * @static
    * @final .
    * @author Aaron Van Meerden <ke@braverock.com>
    *
    * @param  array $myHeaders with headers
    * @return void
    *
    * @since version number
    */
   function setHeaders($myHeaders = false)
   {
        if ($myHeaders !== false)
        {
            $this->_myHeaders = $myHeaders;
        }
    }
// }}}
// {{{ getHeaders
   /**
    * Method public mixed getHeaders( void )
    *
    * Returns an array containing Headers for this file
    *
    * @name getHeaders()
    * @access public
    * @category Record manipulation
    *
    * @uses property $_cvsHeaders File Header Names
    * @static
    * @final .
    *
    * @return array File Header Names
    *
    * @since version number
    */
    function getHeaders()
    {
        return $this->_cvsHeaders;
    }
// }}}
// {{{ getHeaderField
   /**
    * Method public mixed getHeaderField( int column number )
    *
    * Returns Field data as requested from Header array
    *
    * @name getHeaderField)()
    * @access public
    * @category Record manipulation
    *
    * @uses $_cvsHeaders
    * @uses CSV_INVALID_PARAMETERS
    * @static
    * @final .
    *
    * @param  mixed  $_colNum which column to retrieve name
    * @return string File Header Name
    *
    * @since 2.20
    */
    function getHeaderField( $_colNum = null )
    {
        // See if we have anything to deal with
        if ( isset ($_colNum ) )
        {
            if ( is_string ( $_colNum ) )
                $_colNum = array_search($_colNum, $this->getHeaders());

            // Make sure we are asked for something we have
            else if ( ($_colNum >= 0) && ($_colNum < $this->_headerColCount ) )
                $_colNum = $this->_cvsHeaders[$_colNum];
        }

        else
            // set error
            $_colNum = $this->_flagError( CSV_INVALID_PARAMETERS );

       return $_colNum;
    }
// }}}
// {{{ getRecord
   /**
    * Method public mixed getRecord( int record to retrieve )
    *
    * Returns an array containing the data from a particular record
    *
    * @name getRecord()
    * @access public
    * @category Record manipulation
    *
    * @uses property $_cvsRecords      File Records
    * @uses method   getRecordCount()  How many Records in this file
    * @static
    * @final . [or above] [declare a method that cannot be overridden in a child class]
    *
    * @param int $_recNum Which record to pull
    * @return mixed boolean on failure
    *               Record array on success

    * @since 1.0
    */
    function getRecord($_recNum = null)
    {
       /**
        * Variable local $_results
        *
        * Indicates if we succedded or not
        * Default value: FALSE
        *
        * @var mixed $_results array requested record
        *                      bool  FALSE indicates we did not succedded
        *
        * @access private
        * @static
        */
        $_results = false;

        // Does this record set use Record IDs?
        if ( $this->getUseRecordID() )
        {
            if (array_key_exists($_recNum, $this->_cvsRecords))
                $_results =  $this->_cvsRecords[$_recNum];
        }

        // Make sure we are asked for a record we have
        else if ( ($_recNum >= 0) && ($_recNum < $this->getRecordCount() ) )
            $_results = $this->_cvsRecords[$_recNum];

        // Send back what we have
        return $_results;
    }
// }}}
// {{{ getRecords
   /**
    * Method public mixed getRecords( void )
    *
    * Returns array of entire parsed CSV file
    *
    * @name getRecords())
    * @access public
    * @category Record manipulation
    *
    * @uses property $_cvsRecords File Records
    * @static
    * @final .
    *
    * @param void
    * @return array entire parsed CSV file
    *
    * @since 2.05
    */
    function getRecords()
    {
        return $this->_cvsRecords;
    }
// }}}
// {{{ getColumn
   /**
    * Method public mixed getColumn( int|string column to retrieve )
    *
    * Returns an array containing the data from a particular column
    *
    * @name getColumn()
    * @access public
    * @category Record manipulation
    *
    * @uses property $_cvsRecords File Records
    * @uses method getColumnCount() How many Columns in this file
    * @uses CSV_INVALID_PARAMETERS
    * @static
    * @final .
    *
    * @param int    $_col  Which column to pull
    * @param string col    Which column, by name, to pull
    * @return mixed boolean on failure
    *               Record array on success
    *
    * @since 1.0
    */
    function getColumn($_colNum = null)
    {
       /**
        * Variable local $_results
        *
        * Indicates if we succedded or not
        * Default value: TRUE
        *
        * @var mixed $_results array requested column data
        *                      bool  FALSE indicates we did not succedded
        *
        * @access private
        * @static
        */
        $_results = true;

        // Inbound parameter could be a string, Column Name
        // Were we told to use the Header Row
        if ( $this->_getUseHeaders() )
        {
            if ( is_integer ( $_colNum  ) )
                $_colNum = $this->getHeaderField( $_colNum );

            else
                if ( array_search($_colNum, $this->getHeaders()) === false )
                    // set error
                    return $this->_flagError( CSV_INVALID_PARAMETERS );
        }
        // First row was not Header
        else
        {
            if ( is_string ( $_colNum  ) )
                // set error
                return $this->_flagError( CSV_INVALID_PARAMETERS );
        }

        // Make sure we are asked for a column we have
        if ( ($_colNum >= 0) && ($_colNum < $this->getColumnCount() ) )
        {
            // Define a new, temp array
            $_results = array();

            // We need to loop through the entire record set and pull
            // out just the field asked for
            foreach ( $this->getRecords() as $row => $data )
            {
                if (  $this->getUseRecordID() )
                    $_results[$row] = $data[$_colNum];
                else
                    $_results[] = $data[$_colNum];
            }
        }
        else
        {
            // set error
            return $this->_flagError( CSV_INVALID_PARAMETERS );
        }

        // Send back what we have
        return $_results;
    }
// }}}
// {{{ getField
   /**
    * Method public mixed getField( int record number, mixed column name or number )
    *
    * Returns Field data as requested
    *
    * @name getField()
    * @access public
    * @category Record manipulation
    *
    * @uses property $_cvsHeaders File Header Names
    * @uses method array_search_r recursive array search
    * @uses constant CSV_INVALID_PARAMETERS
    * @uses constant CSV_INVALID_REC_NUM
    * @uses constant CSV_INVALID_COL_NUM
    * @static
    * @final .
    *
    * @param int $_recNum Which record the field is in
    * @param int $_colNum Which column the field is in
    * @return array File Header Names
    *
    * @since 2.0
    */
    function getField( $_recNum = null, $_colNum = null )
    {
       /**
        * Variable local $_results
        *
        * Indicates if we succedded or not
        * Default value: FALSE
        *
        * @var mixed $_results string Field data
        *                      bool   FALSE indicates we did not succedded
        *
        * @access private
        * @static
        */
        $_results = false;

        // Make sure we have the record asked for
        if ( ! ($this->array_search_r ( $_recNum,  $this->getRecords() )) )
            $_results = $this->_flagError( CSV_INVALID_REC_NUM );

        // Are we using File Headers
        else if ( $this->getUseHeaders() )
            $_results = $this->_cvsRecords[$_recNum][$_colNum];

        // See if we have anything to deal with
        else if ( ( $_recNum < 0 ) || ( $_colNum == null ) )
            // set error
            $_results = $this->_flagError( CSV_INVALID_COL_NUM );

        // Make sure we are asked for something we have
        else if ( ($_colNum >= 0) && ($_colNum < $this->getColumnCount() ) )
            $_results =  $this->_cvsRecords[$_recNum][$_colNum];

        else
            // set error
            $_results = $this->_flagError( CSV_INVALID_PARAMETERS );

        return $_results;
    }
// }}}
// {{{ setRecordIndexColumn
   /**
    * Method public void setRecordIndexColumn( int record number )
    *
    * Which data column to use as record identifier
    *
    * @name setRecordIndexColumn()
    * @access public
    * @category Record manipulation
    *
    * @uses _useRecordID boolen indicating that records are labeld
    * @uses _TrecordID   column number to use as Record ID
    * @uses _flagError    set which error has occured
    * @uses useHeaders()  determine if Header Row is to be used
    * @uses constant CSV_INVALID_PARAMETERS
    * @static
    * @final .
    *
    * @param mixed $_colID which column of data to use as record index labels
    * @return void
    *
    * @since 2.04
    */
    function setRecordIndexColumn ( $_colID = null )
    {
       /**
        * Variable local $_results
        *
        * Default state value
        * Default value: FALSE
        *
        * @var bool indicates whether to use labels rows
        *
        * @access private
        * @static
        */
        $_toUse = false;

        // If nothing was sent in, we do nothing but error out
        // We only need to do this if anything was sent
        if ( ( isset ( $_colID ) ) && ( $_colID !== false ) )
        {
            // We accept three types of variables; int, str and bool
            $_toUse = true;

                // Could be a string
            if ( is_string($_colID) )
            {
                //Header needs to be used, so turn it on
                $this->useHeaders ( true );
            }
            // We may have a boolen
            else if ( is_bool ( $_colID ) )
            {
                $_toUse = $_colID;
                $_colID = ( $_colID ) ? 0 : '';
            }
            // We got something else
            else if ( ! is_integer ( $_colID ) )
//            else
            {
                $_toUse = false;
                $this->_err = true;

                // We can't use what we recieved
                $this->_flagError( CSV_INVALID_PARAMETERS );

            }
        }   // if ( ( isset ( $_colID ) ) && ( $_colID !== false ) )

        $this->_useRecordID ( $_toUse );
        $this->_TrecordID = $_colID;
    }
// }}}
// {{{ getRecordIndexColumn
    function getRecordIndexColumn (  )
    {
        return $this->_TrecordID;
    }
// }}}
// {{{ setRecordID
   /**
    * Method public void setRecordID( int column number )
    *
    * Define which data column to use as record identifier
    *
    * @name setRecordID()
    * @access public
    * @category Record manipulation
    *
    * @static
    * @final .
    *
    * @param int $_recID Which data column to use as record identifier
    * @return void
    *
    * @since 2.04
    * @deprecated Use setRecordIndexColumn instead
    */
    function setRecordID ( $_recID = NULL )
    {
        // This method has been replaced and renamed
        // "forwarding" to proper method
        $this->setRecordIndexColumn( $_recID );
    }
// }}}
// {{{ getRecordID
   /**
    * Method public int column number getRecordID( void )
    *
    * Returns which data column is used as record identifier
    *
    * @name setRecordID()
    * @access public
    * @category Record manipulation
    *
    * @uses $_cvsHeaders Pull header names
    * @static
    * @final .
    *
    * @param void
    * @return  int $_recID Which data column is used as record identifier
    *
    * @since 2.04
    * @deprecated Use getRecordIndexColumn instead
    */
    function getRecordID (  )
    {
        // This method has been replaced and renamed
        // "forwarding" to proper method
        return $this->getRecordIndexColumn();
    }
// }}}
// {{{ useRecordID
   /**
    * Method private void _useRecordID( bool )
    *
    * Configure Class to use a particular column number to use as Record ID
    *
    * @name _useRecordID())
    * @access private
    * @category Record manipulation
    *
    * @uses $_TuseRecordIDs Use a particular column number to use as Record ID
    * @static
    * @final .
    *
    * @param bool $_use set flag to use particular column number
    * @return void
    *
    * @since 1.0
    */
    function _useRecordID ( $_use = true )
    {
        // Set Class Property
        $this->_TuseRecordIDs = $_use;
    }

// }}}
// {{{ getUseRecordID
   /**
    * Method public bool getUseRecordID( void )
    *
    * Returns Boolean indicating whether this Class is to use a particular
    * column number to use as Record ID
    *
    * @name getUseRecordID())
    * @access public
    * @category Record manipulation
    *
    * @uses $_TuseRecordIDs Use a particular column number to use as Record ID
    * @static
    * @final .
    *
    * @param void
    * @return bool $_use set flag to use particular column number
    *
    * @since 1.0
    */
    function getUseRecordID ()
    {
        // Return Class Property
        return $this->_TuseRecordIDs;
    }
// }}}
// {{{ useHeaders
   /**
    * Method public void useHeaders( boolean )
    *
    * Set flag to utilize Column Headers
    *  There may be a situation where the Headers of a file need
    *  to be discarded. If this is set to FALSE *before* the parse
    *  method is called, the first row of the CSV file will be
    *  pulled from the file and not used.
    *
    * @name useHeaders()
    * @access public
    * @category Record manipulation
    *
    * @uses $_TuseHeaders Do we use a CVS headers as column keys
    * @static
    * @final .
    *
    * @param void
    * @return bool $_TuseHeaders Class Property
    *
    * @since 2.04
    */
    function useHeaders ( $_use = true )
    {
        // Set Class Property
        $this->_TuseHeaders = $_use;
    }
// }}}
// {{{ _getUseHeaders
   /**
    * Method private boolean _getUseHeaders( void )
    *
    * Flag to utilize Column Headers
    * There may be a situation where the Headers of a file need
    * to be discarded. If this is set to FALSE *before* the parse
    * method is called, the first row of the CSV file will be
    * pulled from the file and not used.
    *
    * @name _getUseHeaders
    * @access public
    * @category Record manipulation
    *
    * @uses $_TuseHeaders Do we use a CVS headers as column keys
    * @static
    * @final .
    *
    * @param void
    * @return mixed boolean Flag to utilize Column Headers
    *               NULL if property is not set
    *
    * @since 2.20
    */
    function _getUseHeaders()
    {
        // Return Class Property
        return ( isset ( $this->_TuseHeaders ) ) ? $this->_TuseHeaders : 'not set';
    }
// }}}
// {{{ setHeaderRow
   /**
    * Method private void setHeaderRow( int )
    *
    * Set which record in file to use as Header record
    * This will drop all rows above given row.
    * - If a aplpha is passed, this will fail.
    *   If a float is passed, the decimal will be dropped
    *   and the whole number used.
    *
    * @name setHeaderRow
    * @access public
    * @category Record manipulation
    *
    * @uses $_TheaderRow      Which record is used as column keys
    * @uses useHeaders()  determine if Header Row is to be used
    * @uses _flagError()  set which error has occured
    * @uses constant CSV_NOT_A_INT
    * @uses constant CSV_NOT_A_NUM
    * @static
    * @final .
    *
    * @param int $_intRow Row to use as Header. Zero based.
    * @return void
    *
    * @since 2.04
    */
    function setHeaderRow( $_intRow = null )
    {
        // Could be inbound variable from a POST
        if ( is_numeric ( $_intRow ) )
        {
            // If it is, we need to make sure it is an integer
            //settype($_intRow, 'integer');

            if ( is_integer ( (int) $_intRow ) )
            {
                $this->useHeaders();
                $this->_TheaderRow = $_intRow;
            }
            else
                $this->_flagError( CSV_NOT_A_NUM );
        }
        else
            $this->_flagError( CSV_NOT_A_INT );
    }
// }}}
// {{{ getHeaderRow
   /**
    * Method private int getHeaderRow( void )
    *
    * Returns which record in file was used as Header record
    *
    * @name getHeaderRow
    * @access public
    * @category Record manipulation
    *
    * @uses $_TheaderRow      Which record is used as column keys
    * @uses useHeaders()  determine if Header Row is to be used
    * @uses _flagError()  set which error has occured
    * @static
    * @final .
    *
    * @param  void
    * @return int $_intRow Row to use as Header. Zero based.
    *
    * @since 2.04
    */
    function getHeaderRow ()
    {
        // Return Class Property
        return $this->_TheaderRow;
    }
// }}}
// {{{ setRemoveBlankRecord
   /**
    * Method public void setRemoveBlankRecord( bool )
    *
    * Set Class Property to decide if empty records are to be removed
    *
    * @name setRemoveBlankRecord()
    * @access public
    * @category Record manipulation
    *
    * @uses $_TremoveBlankRecord Class Property to decide if empty records are to be removed
    * @static
    * @final .
    *
    * @param  bool $_remove Boolean to decide if empry records are to be removed
    * @return void
    *
    * @since 2.04
    */
    function setRemoveBlankRecord ( $_remove = false )
    {
        // Set Class Property
        $this->_TremoveBlankRecord = $_remove;
    }
// }}}
// {{{ getRemoveBlankRecord
   /**
    * Method public bool getRemoveBlankRecord( void )
    *
    * Returns Class Property to decide if empty records are to be removed
    *
    * @name getRemoveBlankRecord()
    * @access public
    * @category Record manipulation
    *
    * @uses $_TremoveBlankRecord Class Property to decide if empty records are to be removed
    * @static
    * @final .
    *
    * @param  void
    * @return bool $_remove Boolean to decide if empty records are to be removed
    *
    * @since 2.04
    */
    function getRemoveBlankRecord ()
    {
        // Return Class Property
        return $this->_TremoveBlankRecord;
    }
// }}}
// {{{ getRecordCount
   /**
    * Method public int getRecordCount( void )
    *
    * Returns the the total Record count of current CSV File
    *
    * @name getRecordCount()
    * @access public
    * @category Record manipulation
    *
    * @uses $_numRec  How many records do we have?
    * @static
    * @final .
    *
    * @param  void
    * @return int $_numRec total Record count
    *
    * @since 1.0
    */
    function getRecordCount ()
    {
        // Return Class Property
        return $this->_numRec;
    }
// }}}
// {{{ getColumnCount
   /**
    * Method public int getColumnCount( void )
    *
    * Returns the the total Column count of current CSV File
    *
    * @name getColumnCount()
    * @access public
    * @category Record manipulation
    *
    * @uses _numCol  How many Column do we have?
    * @static
    * @final .
    *
    * @param  void
    * @return int _numCol total Column count
    *
    * @since 1.0
    */
    function getColumnCount ()
    {
        // Return Class Property
        return $this->_numCol;
    }
// }}}


// ==========================================================
// File manipulation methods

// {{{ resetDelimiter
   /**
    * Method public void resetDelimiter( void )
    *
    * Reset Field Delimiter to default value
    *
    * @name resetDelimiter()
    * @access public
    * @category File manipulation
    *
    * @uses $_delimiter        Class Property with current delimiter value
    * @uses $_DefaultDelimiter Class Property with default delimiter value
    * @static
    * @final .
    *
    * @param  void
    * @return void
    *
    * @since 1.0
    */
    function resetDelimiter()
    {
        $this->_delimiter = $this->_DefaultDelimiter;
    }
// }}}
// {{{ setDelimiter
   /**
    * Method public void setDelimiter( string )
    *
    * Set Field Delimiter
    *
    * @name setDelimiter()
    * @access public
    * @category File manipulation
    *
    * @uses $_delimiter  Class Property with current delimiter value
    * @static
    * @final .
    *
    * @param string $_strDelimiter Character[s] to use as field delimiter
    * @return void
    *
    * @since 1.0
    */
    function setDelimiter ( $_strDelimiter  = null )
    {
        // parameter could be the NAME of the delimiter
        // Case insensitive
        switch (strtolower($_strDelimiter))
        {
            case 'comma':
                $_strDelimiter = ",";
                break;
            case 'tab':
                $_strDelimiter = "\t";
                break;
            case 'semicolon':
                $_strDelimiter = ";";
                break;
            case 'pipe':
                $_strDelimiter = "|";
                break;
        }

        // Otherwise just use whatever was sent in.
        $this->_delimiter = $_strDelimiter;
    }

// }}}
// {{{ getDelimiter
   /**
    * Method public string getDelimiter( void )
    *
    * Returns Current Field Delimiter
    *
    * @name getDelimiter()
    * @access public
    * @category File manipulation
    *
    * @uses $_delimiter  Class Property with current delimiter value
    * @static
    * @final .
    *
    * @param  void
    * @return string $_delimiter  Class Property with current delimiter value
    *
    * @since 1.0
    */
    function getDelimiter ()
    {
        // Return Class Property
        return $this->_delimiter;
    }
// }}}


// ==========================================================
// Error handling methods

// {{{ setErrLogPath
   /**
    * Method private void setErrLogPath( string )
    *
    * Sets error message
    *
    * @name setErrLogPath()
    * @access private
    * @category Error handling
    *
    * @uses $_errLogPath   String  Full System Path to place error log file
    * @static
    * @final .
    *
    * @param  const  $conErrCode  Constant defining error
    * @return void
    *
    * @since 1.1
    */
    function setErrLogPath ( $_errLogPath = false)
    {
        if ( $_errLogPath )
            $this->_errLogPath = $_errLogPath;
    }
// }}}
// {{{ setErrLogFileName
   /**
    * Method private void setErrLogFileName( string )
    *
    * Sets error message
    *
    * @name setErrLogFileName())
    * @access private
    * @category Error handling
    *
    * @uses $_errLogFileName   String  Error log file name
    * @static
    * @final .
    *
    * @param  const  $_errLogFileName  Error log file name
    * @return void
    *
    * @since 1.1
    */
    function setErrLogFileName ( $_errLogFileName = false)
    {
        if ( $_errLogFileName )
            $this->_errLogFileName = $_errLogFileName;
    }
// }}}
// {{{ _flagError
   /**
    * Method private void _flagError( string, bool )
    *
    * Sets error message
    *
    * @name _flagError())
    * @access private
    * @category Error handling
    *
    * @uses $_parseSuccess     Class Property Succss or failure on parsing given file
    * @uses _setErrorMsg()     Sets error message based upon given parameter
    * @uses $_errLogPath       Full System Path to place error log file
    * @uses $_errLogFileName   Error log file name
    * @uses constant LOG_TYPE_FILE
    * @static
    * @final .
    *
    * @param  const  $conErrCode  Constant defining error
    * @param  bool   $bolAddLog   Add message to error log, or not
    * @param  const  $logType     Constant defining where to place error msg
    * @return void
    *
    * @since 1.0
    */
    function _flagError ( $conErrCode  = null, $bolAddLog = true, $logType = LOG_TYPE_FILE )
    {
        if ( $conErrCode )
        {
            // Set failure indicator
            $this->_parseSuccess = false;

            // Set Error Code
            $this->_errCode = $conErrCode;

            // Stick it the log, maybe
            if ( $bolAddLog )
            {
                $_errMsg  = date("n/j/Y g:i:s A") . "\t";
                $_errMsg .= $this->getErrorMsg() . "\n";
                error_log( $_errMsg, $logType, $this->_errLogPath . '/' . $this->_errLogFileName );
            }
        }
    }
// }}}
// {{{ _setErrorMsg
   /**
    * Method private void _setErrorMsg( string new error message )
    *
    * Sets property with current error message
    *
    * @name _setErrorMsg()
    * @access private
    * @category Error handling
    *
    * @uses $_errMsg  Store current error message, if any
    * @static
    * @final .
    *
    * @param string $_strErrMsg Error Message
    * @return void
    *
    * @since 1.0
    */
    function _setErrorMsg ( $_strErrMsg  = null )
    {
        // Set Class Property
        $this->_errMsg = $_strErrMsg;
    }
// }}}
// {{{ getErrorMsg
   /**
    * Method public string getErrorMsg( void )
    *
    * Retrieves current error message from Class property
    *
    * @name getErrorMsg())
    * @access public
    * @category Error handling
    *
    * @uses $_errMsg  Store current error message, if any
    * @uses constant CSV_INVALID_PARAMETERS
    * @uses constant CSV_INVALID_REC_NUM
    * @uses constant CSV_INVALID_COL_NUM
    * @uses constant CSV_NOT_A_INT
    * @uses constant CSV_NOT_A_NUM
    * @uses constant CSV_FILE_NOT_FOUND
    * @uses constant CSV_FILE_CANNOT_OPEN
    * @uses constant CSV_FILE_CANNOT_CLOSE
    * @uses constant CSV_FILE_NOT_READABLE
    * @uses constant CSV_FILE_NOT_WRITEABLE
    * @uses constant CSV_FILE_CANNOT_CREATE
    * @uses constant CSV_FILE_CANNOT_LOCK
    * @uses constant CSV_FILE_CANNOT_UNLOCK
    * @uses constant CSV_FILE_ALREADY_EXISTS
    * @uses constant CSV_FILE_NOT_DEFINED
    * @static
    * @final .
    *
    * @param void
    * @return string $_errMsg Class property Error Message
    *
    * @since 1.0
    */
    function getErrorMsg()
    {
    /**
        * Error Code Messages
        *
        * Each error code Constant has an associated message. By linking the
        * Constant with an elemnt in the eror array, messages can be easliy
        * accessed or even added to.
        *
        * @example sprintf($this->$_error_codes[CSV_FILE_NOT_FOUND], $fileName)
        *
        * @var local array $_error_codes
        * @access private
        * @static
        *
        * @since 1.0
        */
        $_error_codes =
             array( CSV_INVALID_PARAMETERS   => 'Improper parameters given.',
                    CSV_INVALID_REC_NUM      => 'Invalid Record Number/Name.',
                    CSV_INVALID_COL_NUM      => 'Invalid Column Number/Name.',
                    CSV_NOT_A_INT            => 'Invalid Parameter, must be an Integer.',
                    CSV_NOT_A_NUM            => 'Invalid Parameter, must be an Number.',
                    CSV_FILE_NOT_FOUND       => 'The File, \'%s\', can not be found.',
                    CSV_FILE_CANNOT_OPEN     => 'The File, \'%s\', can not be opened.',
                    CSV_FILE_CANNOT_CLOSE    => 'The File, \'%s\', can not be closed.',
                    CSV_FILE_NOT_READABLE    => 'The File, \'%s\', can not be read from.',
                    CSV_FILE_NOT_WRITEABLE   => 'The File, \'%s\', can not be written to.',
                    CSV_FILE_CANNOT_CREATE   => 'The File, \'%s\', can not be created.',
                    CSV_FILE_CANNOT_LOCK     => 'The File, \'%s\', can not be locked.',
                    CSV_FILE_CANNOT_UNLOCK   => 'The File, \'%s\', can not be unlocked.',
                    CSV_FILE_ALREADY_EXISTS  => 'The File, \'%s\', alread exists.',
                    CSV_FILE_NOT_DEFINED     => 'No Path/File given to parse.'
                  );

        // Return the message
        return sprintf($_error_codes[$this->getErrorCode()], $this->getCSVfile() );
    }
// }}}
// {{{ getErrorCode
   /**
    * Method public int getErrorCode( void )
    *
    * Returns current error code
    *
    * @name getErrorCode()
    * @access public
    * @category Error handling
    *
    * @uses $_errMsg  Store current error code, if any
    * @static
    * @final .
    *
    * @param  void
    * @return int _errCode current error code
    *
    * @since 1.0
    */
    function getErrorCode()
    {
        // Return Class Property
        return $this->_errCode;
    }
// }}}
// {{{ succeed
   /**
    * Method public boolean succeed( void )
    * Returns property indicating success or failure of some kind
    *
    * @name succeed()
    * @access public
    * @category Error handling
    *
    * @uses $_parseSuccess   Store boolean indicating success or failure
    * @static
    * @final .
    *
    * @param  void
    * @return bool $_parseSuccess   boolean indicating success or failure
    *
    * @since 1.0
    */
    function succeed()
    {
        // Return Class Property
        return $this->_parseSuccess;
    }
// }}}


// ==========================================================
// Miscellanous Tools

// {{{ array_search_r
   /**
    * Search an array recursively
    *
    *    This function will search an array recursively
    *    till it finds what it is looking for. An array
    *    within an array within an array within array
    *    is all good :-)
    *
    * @name array_search_r()
    * @access private
    * @category Miscellanous Tools
    *
    * @static
    * @final .
    *
    * @author    Richard Sumilang     <richard@richard-sumilang.com>
    * @contributor Walter Torres <walter@torres.ws> [with a *lot* of help!]
    *
    * @param     string   $needle     What are you searching for?
    * @param     array    $haystack   What you want to search in
    * @return    boolean  $match      was anything found
    *
    * @since 1.0
    */
    function array_search_r($needle, $haystack)
    {
        $match = false;

        foreach($haystack as $value)
        {
            if(is_array($value))
                $match = $this->array_search_r($needle, $value);
                    if ( $match )
                        break;

            else if($value == $needle)
                $match = true;
        }
        return $match;
    }
// }}}

};

// ========================================================================
// ========================================================================
// CS-RCS Version Control Info

/**
  * $Log: csvParser.php,v $
  * Revision 1.5  2006/05/16 02:10:13  jswalter
  *  * Added Preprocess Callback Function.
  *  * Added '$_preprocess; to store user defined callback
  *  * Need to create get and setter methods for this new property
  *
  * Revision 1.4  2006/01/31 20:42:01  jswalter
  * row count was unchanged if 'setRemoveBlankRecord()' was set. remove rows are no longer counted
  *
  * Revision 1.3  2005/12/14 02:04:54  vanmer
  * - changed csvParser to call a function to retrieves headers for each record as it is being loaded
  * - this is useful so the parser can be extended to provide different headers based on the contents of the record
  *
  * Revision 1.2  2005/10/27 19:14:47  jswalter
  *  - added '$_errLogFileName' & '$_errLogPath' as Class properties
  *  - added 'setErrLogPath()' & 'setErrLogFileName()' methods to define these properties
  *
  * Revision 1.1  2005/07/06 18:12:39  jswalter
  *  - initial commit to sourceforge
  *  - these files come from php-yacs.org
  *
  * Revision 1.14  2005/07/01 15:58:07  walter
  *  - massive comments added
  *
  * Revision 1.13  2004/11/18 23:26:11  walter
  *  - added NOTE on Header Row "keep or remove" issue [line 610]
  *
  * Revision 1.12  2004/11/17 22:14:49  walter
  *  - setHeaderRow modified to handle POSTed data. Checks to make sure parameter is a numeric.
  *  - Alphas are dropped, and floats have their decimals stripped.
  *
  * Revision 1.11  2004/11/16 06:15:48  walter
  *  - correct boolean check within 'getColumn()'
  *
  * Revision 1.10  2004/11/01 16:23:35  walter
  * - modified 'setDelimiter' to accept delimiter English name ['comma'], case insensitive
  *
  * Revision 1.9  2004/10/22 17:40:20  walter
  * - updated parse section to handle externally define header properly
  *
  * Revision 1.8  2004/10/21 21:32:21  walter
  * - modified setHeaders() to accept a ZERO instead of thinking it was a FALSE
  *
  * Revision 1.7  2004/10/05 19:14:59  walter
  * - getRecord() was not working
  *
  * Revision 1.6  2004/10/01 22:23:17  ke
  * - added ability to set headers manually using setHeaders function
  * - added private member property _myHeaders for use as headers if defined
  *
  * Revision 1.5  2004/09/30 22:29:45  walter
  * - Class will now use any given row as Header row
  * - added '_TheaderRow' to hold which row to use as Header
  * - set/getHeaderRow()
  * - added CSV_NOT_A_INT error code
  *
  * Revision 1.4  2004/09/30 21:46:12  walter
  * - added setRecordIndexColumn()
  * - depricated setRecordID()
  * - added getRecordIndexColumn()
  * - depricated getRecordID()
  * - setRecordIndexColumn() will accept column name,
  *   column index or boolean
  *
  * Revision 1.3  2004/09/25 17:31:59  walter
  * - fix result handling for row
  * Bug 60
  *
  * Revision 1.2  2004/09/21 22:28:00  walter
  * - updated 'getColumn()' to handle an integer or a string as inbound
  *   parameter.
  * - update 'getColumn()' to give single dim array if recordID is FALSE
  *   creates 2 dim array (key=>value) if recordID  is TRUE
  *
  * Revision 1.1  2004/09/21 15:52:25  walter
  * - initial revision
  * - csvParser Class
  * - Parseres CSV files into maluable entities
  * bug 60
  *
  * Revision 2.08  2004-09-20 16:27:35  walter
  * - useHeader() ow is used to determine if Header row is to be used.
  *   useHeader(true), or parseFile($file, true) will do the same thing
  * - parseFile($file, true), the second parameter is now optional
  * - if serRecordID is set, that field is removed from record
  *
  * Revision 2.07  2004-09-17 15:09:70  walter
  * - Corrected col number ID in 'getColumn'.
  * - modified 'setRecordID()' to handle Booleans, integers properly
  *   and give proper errors otherwise
  *
  * Revision 2.06  2004-08-20 19:20:20  walter
  * - ???? no idea! :(
  *
  * Revision 2.05  2004-08-20 19:20:06  walter
  * - Simplified the use of Headers as column names, or not.
  *
  * Revision 2.04  2004/08/17 03:52:12  walter
  * - Added Class constants
  * - Added error codes and text to error flag method
  * - Added success property/method
  *
  * Revision 2.03  2004/08/11 16:23:08  walter
  * - Added methods to define whether or not to capture blank rows from file
  * - Added 'getRecords' to return entire Record Set
  * - Added methods to define which column is to be used as a "record ID" value
  *   and to turn on the flag to utilize this value as the Record ID instead
  *   a simple array key sequence value
  *
  * Revision 2.02  2004/08/04 20:57:35  walter
  * - Added 'getHeaderField' to retrieve an individual label
  * - modified '_getCSVrecord' to use PHP standard method 'fgetcsv'
  *   this built-in method handles all issues regarding importing CSV files.
  * - "fix" blank header fields to have column sequence number instead
  *   of an empty string value
  * - If Headers is defined, than record array is created as a named array,
  *   the names coming from the header data. Otherwise the array is a simple
  *   sequence key.
  * - "fixed" named array to use sequence placement number in place of a
  *   blank value when the matching sequence in Headers is not defined.
  *
  * Revision 2.02  2004/07/23 18:02:17  walter
  * - Added method to retrieve a particular field from all records
  * - Added method to retrieve a series fields from all records
  * - Created methods to set and get delimiter for parsing CSV file.
  *
  * Revision 2.01  2004/07/23 16:52:43  walter
  * - Added properties to hold record and column count
  * - Added methods to set and get record and column count
  * - Added method to retrieve array of header data: getHeaders
  * - Added method to retrieve a single record
  * - Changed 'readFile' method to 'parseFile'
  * - began phpDoc commenting of methods, properties and variables
  *
  * Revision 2.00  2004/07/22 17:42:28  walter
  * - Created central method to retrieve csv record: _getCSVrecord
  * - Created central method to handle errors w/ messages: _flagError
  * - Added flag ($readFile) and code to 'readFile' so it can read
  *   first line of CSV file as header info, or not.
  *
  * Revision 1.00  2004/07/22 14:02:41  walter
  * - Retrieve original from phpClasses.org
  *   http://www.phpclasses.org/browse/package/849.html
  * - Original class by Stanislav Karchebny <berk at inbox dot ru>
  *
  */

?>
