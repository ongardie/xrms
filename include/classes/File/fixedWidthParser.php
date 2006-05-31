<?php
//fixed width parser

//
// +------------------------------------------------------------------------+
// | fixedWidthParser - Part of the PHP Yacs Library                        |
// +------------------------------------------------------------------------+
// | Copyright (c) 2004-2005 Walter Torres                                  |
// | Email         walter@torres.ws                                         |
// | Web           http://web.php-yacs.org                                  |
// | Mirror        http://php-yacs.sourceforge.net/                         |
// | $Id: fixedWidthParser.php,v 1.5 2006/05/31 21:41:35 vanmer Exp $    |
// +------------------------------------------------------------------------+
// | This source file is subject to version 3.00 of the PHP License,        |
// | that is available at http://www.php.net/license/3_0.txt.               |
// | If you did not receive a copy of the PHP license and are unable to     |
// | obtain it through the world-wide-web, please send a note to            |
// | license@php.net so we can mail you a copy immediately.                 |
// +------------------------------------------------------------------------+
//


/**
   *
   * This parser mostly uses the CSV Parser.  It extends and only overrides the function that actual runs fgetcsv, and replaces it with a fixed width parser.
   * This uses the headers and layout from a format array that is passed in before parseFile is called.
**/

require_once('csvParser.php');

class fixedWidthParser extends csvParser {
    
   /**
    * Property private array $_fieldFormat
    *
    * Array with format of array to define layout of fixed width file
    * Array of elements, each element defines a field
    * Each element is an associative array:
    * 'name' => Fieldname
    * 'start' => starting position for field
    * 'end' => ending position in string for file
    * OR
    * 'length' => length of the field
    *
    * @name $_unpackFormat
    * @var string
    * @property private string for use in unpacking fixed width files
    *
    * @access private
    */
    var $_fieldFormat = null;
    var $_recordFormats = null;
    var $_recordIdentifier = null;

   /**
    * Property private array $_fixedLength
    *
    * Flag indicating that file definition is via LENGTH of field
    * vs. START and END defintion
    *
    * DEfaults to FALSE, assuming START and END defintion
    *
    * @name $_fixedLength
    * @var boolean
    * @property bol $_fixedLength file definition is via LENGTH of field
    *
    * @access private
    * @since 1.5
    */
    var $_fixedWidth = false;


function SetRecordFormats($map_data) {
    $this->_recordFormats=$map_data;
    $this->SetFieldFormat(current($map_data));
}

function SetFieldFormat($format_array)
{
    $this->_fieldFormat = $format_array;

    // Look into the first element of this defintion array and
    // determine if this is a LENGTH defintion record
    $this->_fixedWidth = (array_key_exists('length', current($format_array)));

    // Since this is fixed width, we know how long each record is,
    // this value will help improve file read performance
    if ( $this->_fixedWidth )
    {
        $_length = 0;

        foreach ($format_array as $field_data)
        {
            $length += $field_data['length'];
        }
    }
    else
    {
        $_last  = end($format_array);
        $_length = $_last['end'];
    }

    $headers=array();
    foreach ($format_array as $field_data) {
    	$headers[]=$field_data['name'];
    }

    $this->_fixedLength = $_length;

    // Store the names of the files off
    $this->_myHeaders  = $headers;
    $this->_cvsHeaders = $this->_myHeaders;
}

function SetRecordIdentifier($record_identifier) {
    if (!$record_identifier) return false;
    $frec=current($record_identifier);
    if (!is_array($frec)) $record_identifier=array($record_identifier);
    $this->_recordIdentifier=$record_identifier;
}
   
 /**
    * Method private mixed _getCSVrecord( file pointer, delimiter string )
    *
    * Retrieves a single record for the open file, and uses _extractFields function to process row format and return fields
    *
    * @name _getCSVrecord()
    * @access public
    *
    * @uses getFileSize()
    * @static
    * @final
    *
    * @param pointer $_filePointer File Handle to CSV File
    * @return mixed boolean on failure
    *               Record array on success
    *
    * @since 2.0
    */
    function _getCSVrecord($_filePointer)
    {
//        echo "CSV RECORD FETCH<br>";
        $string = fgets($_filePointer, $this->getFileSize());//$this->_fixedLength);
        // Try and pull a record out of the file
//        echo "RESULT: $string<br>";
        if ($data = $this->_extractFields($string))
        {
            // Strip whitespace from each element
//            $data = array_map('trim', $data);

            return $data;
        }
        // No record found
        else
            // Tell caller we failed
            return false;
    }

    //Main function for splitting rows from a fixed width file format
    function _extractFields($string) {
        if (!$string) return false;
        $str_pos=0;
        $fields=array();
        $field_format=$this->getFieldFormatForRecord($string);
        if (!$field_format) { 
            $this->_setErrorMsg("Failed to find field format for row, using single field for entire record\n");
            $this->_parseSuccess = false;
            $this->SetFieldFormat(array(array('name'=>'record')));
            $fields[]=$string; 
            return $fields;
        }
        foreach ($field_format as $field_data) {
            if ($this->_fixedWidth) {
                $length=$field_data['length'];
                $start=$str_pos;
                $end=$str_pos+$length;
            } else {
                $start=$field_data['start'];
                $end=$field_data['end'];
                //add one to get the total number of characters
                $length  = $end-$start + 1;
                //start one behind to ensure that initial character is included
                $str_pos = $start - 1;
            }

            //remove whitespace on either side of substr
            $fields[] = trim(substr($string, $str_pos, $length));
            $str_pos = $end;
        }
        return $fields;
    }

    function getFieldFormatForRecord($record) {
        if (!$this->_recordFormats OR !$this->_recordIdentifier)
            return $this->_fieldFormat;
        else {
            $value_arr=array();
            foreach ($this->_recordIdentifier as $record_identifier) {
                $start=$record_identifier['start'];
                $end=$record_identifier['end'];
    
                $length=$end-$start+1;
                $str_pos=$start-1;
                $value_arr[]=trim(substr($record, $str_pos, $length));
            }
            $value=implode("|", $value_arr);
            if ($value)  $format=$this->_recordFormats[$value];
            if ($format) {
                //set headers to current format
                $this->SetFieldFormat($format);
            }
            return $format;
        }
    }
}

// ========================================================================
// ========================================================================
// CS-RCS Version Control Info

/**
  * $Log: fixedWidthParser.php,v $
  * Revision 1.5  2006/05/31 21:41:35  vanmer
  * - ensure that headers are properly set
  * - added filesize for fgets instead of fixed width length
  *
  * Revision 1.4  2006/05/30 20:22:55  vanmer
  * - fixes to speed fixed width parsing, from jsWalter
  *
  *
  */

?>
