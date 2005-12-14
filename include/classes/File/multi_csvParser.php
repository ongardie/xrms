<?php

require_once('csvParser.php');

class multi_csvParser extends csvParser{
    var $_recordIdentifier;
    var $_multiHeaders;

    function multi_csvParser() {
        $this->csvParser();
    }

    function SetRecordIdentifier($key) {
        $this->_recordIdentifier=$key;
    }

    function SetMultiHeaders($multiHeaders) {
        $this->_multiHeaders=$multiHeaders;
    }

    function addMultiHeader($key, $headers) {
        if (!is_array($this->_multiHeaders)) $this->_multiHeaders=array();
        $this->_multiHeaders[$key]=$headers;
    }

    function removeMultiHeader($key) {
        unset($this->_multiHeaders[$key]);
    }

    function _recordHeaders($_oldRecord) {
        $headers=$this->_cvsHeaders;
        if ($this->_recordIdentifier AND is_array($this->_multiHeaders)) {
            $identifier=$_oldRecord[$this->_recordIdentifier];
            if (array_key_exists($identifier, $this->_multiHeaders)) {
                $headers= $this->multiHeaders[$identifier];
            }
        }
        return $headers;
    }
}

?>