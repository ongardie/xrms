<?php
/**
* @package JPSpan
* @subpackage CodeWriter
* @version $Id: CodeWriter.php,v 1.1 2005/04/11 19:50:52 gpowers Exp $
*/
//-----------------------------------------------------------------------------

/**
* Javascript is written via an instance of this class
* @access public
* @package JPSpan
* @subpackage CodeWriter
*/
class JPSpan_CodeWriter {

    /**
    * Serialized Javascript
    * @var string
    * @access private
    */
    var $code = '';
    
    /**
    * Disables further writing of code
    * Used when errors are generated
    * @var boolean
    * @access public
    */
    var $enabled = TRUE;

    /**
    * Write some code - overwrites the existing code
    * @param string
    * @return void
    * @access public
    */
    function write($code) {
        if ( $this->enabled ) {
            $this->code = $code;
        }
    }
    
    /**
    * Append some code to the existing code
    * @param string
    * @return void
    * @access public
    */
    function append($code) {
        if ( $this->enabled ) {
            $this->code .= $code;
        }
    }

    /**
    * Return all the written code
    * @return string Javascript
    * @access public
    */
    function toString() {
        return $this->code;
    }
}


