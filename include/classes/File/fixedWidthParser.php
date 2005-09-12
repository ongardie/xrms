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

function SetFieldFormat($format_array) {
    $this->_fieldFormat=$format_array;
    foreach ($format_array as $field_data) {
        $headers[]=$field_data['name'];
    }
    $this->_myHeaders=$headers;
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
        foreach ($this->_fieldFormat as $field_data) {
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

}

?>
