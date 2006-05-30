<?php
//fixed width parser

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

function SetRecordFormats($map_data) {
    $this->_recordFormats=$map_data;
    $this->SetFieldFormat(current($map_data));
}

function SetFieldFormat($format_array) {
    $this->_fieldFormat=$format_array;
    $headers=array();
    foreach ($format_array as $field_data) {
        $headers[]=$field_data['name'];
    }
    $this->_myHeaders=$headers;
    $this->_cvsHeaders=$headers;
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
        $string=fgets($_filePointer, $this->getFileSize());
        // Try and pull a record out of the file
//        echo $string;
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
            if (array_key_exists('length',$field_data)) {
                $length=$field_data['length'];
                $start=$str_pos;
                $end=$str_pos+$length;
            } else {
                $start=$field_data['start'];
                $end=$field_data['end'];
                //add one to get the total number of characters
                $length=$end-$start+1;
                //start one behind to ensure that initial character is included
                $str_pos=$start-1;
            }

            //remove whitespace on either side of substr
            $fields[]=trim(substr($string, $str_pos, $length));
            $str_pos=$end;
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

?>
