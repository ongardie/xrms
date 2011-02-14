<?php
    require_once('adodb-xmlschema.inc.php');

    class adoSchema_File extends adoSchema {
        var $sqlFile = NULL;
        var $sqlFileHandle=false;
        var $sqlDataFile = NULL;
        var $sqlDataFileHandle=false;

        function adoSchema_File(&$db, $sqlFile, $sqlDataFile=false) {
            $this->sqlFile=$sqlFile;

            if ($sqlDataFile) {
                $this->sqlDataFile=$sqlDataFile;
                $this->openSQLDataFile($sqlDataFile);
                $this->seperateDataSQL=true;
            }
            $this->openSQLFile($sqlFile);
            $this->adoSchema($db);
        }

        function openSQLFile($sqlFile) {
            $this->sqlFileHandle=fopen($sqlFile, "w+");
        }
    
        function openSQLDataFile($sqlFile) {
            $this->sqlDataFileHandle=fopen($sqlFile, "w+");
        }

        function writeSQL($sql) {        
            if (!$this->sqlFileHandle) $this->openSQLFile($this->sqlFile);
            $ret=fwrite($this->sqlFileHandle, $sql);
            return $ret;
        }

        function writeDataSQL($sql) {        
            if ($this->sqlDataFile) {
                if (!$this->sqlDataFileHandle) $this->openSQLDataFile($this->sqlDataFile);
                $ret=fwrite($this->sqlDataFileHandle, $sql);
                return $ret;
            } else return $this->writeSQL($sql);
        }
    
        function addSQL( $sql = null ) {
            if( is_array( $sql ) ) {
                    foreach( $sql as $line ) {
                            $this->addSQL( $line );
                    }
                    
                    return TRUE;
            }

            if( is_string( $sql ) ) {
                return $this->writeSQL($sql.";\n");
            }
    
            return FALSE;
    
        }
    
        function addDataSQL( $sql = null ) {
            if( is_array( $sql ) ) {
                    foreach( $sql as $line ) {
                            $this->addDataSQL( $line );
                    }
                    
                    return TRUE;
            }

            if( is_string( $sql ) ) {
                return $this->writeDataSQL($sql.";\n");
            }
    
            return FALSE;
    
        }
    }

?>