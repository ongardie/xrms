<?php
/*
V4.63 17 May 2005  (c) 2000-2005 John Lim (jlim@natsoft.com.my). All rights reserved.
  Released under both BSD license and Lesser GPL library license. 
  Whenever there is any discrepancy between the two licenses, 
  the BSD license will take precedence.
  Set tabs to 8.
  
  MySQL code that does not support transactions. Use maxdbt if you need transactions.
  Requires maxdb client. Works on Windows and Unix.
  
 28 Feb 2001: MetaColumns bug fix - suggested by  Freek Dijkstra (phpeverywhere@macfreek.com)
*/ 

// security - hide paths
if (!defined('ADODB_DIR')) die();

if (! defined("_ADODB_MAXDB_LAYER")) {
 define("_ADODB_MAXDB_LAYER", 1 );

class ADODB_maxdb extends ADOConnection {
	var $databaseType = 'maxdb';
	var $dataProvider = 'maxdb';
	var $hasInsertID = true;
	var $hasAffectedRows = true;
	var $metaTablesSQL = "SELECT TABLE_NAME FROM USER_TABLES WHERE TABLE_NAME NOT IN ('SYSUPGRADEHISTORY')";	
	var $metaColumnsSQL = "SELECT COLUMNNAME,DATATYPE FROM COLUMNS WHERE TABLENAME='%s'";
	var $fmtTimeStamp = "'Y-m-d H:i:s'";
	var $hasLimit = true;
        //maxdb starts in the commit=false state
        var $autoCommit = false;
	var $hasMoveFirst = true;
	var $hasGenID = true;
	var $isoDates = true; // accepts dates in ISO format
	var $sysDate = 'CURDATE()';
	var $sysTimeStamp = 'NOW()';
	var $hasTransactions = true;
//	var $autoRollback = true; // apparently mysql does not autorollback properly 
	var $forceNewConnect = false;
	var $poorAffectedRows = true;
	var $clientFlags = 0;
	var $substr = "substring";
	var $nameQuote = '"';		/// string to use to quote identifiers and names
	
	function ADODB_maxdb() 
	{
		if (defined('ADODB_EXTENSION')) $this->rsPrefix .= 'ext_';
	}
	
	function ServerInfo()
	{
		$arr['description'] = ADOConnection::GetOne("select version()");
		$arr['version'] = ADOConnection::_findvers($arr['description']);
		return $arr;
	}
	
	function IfNull( $field, $ifNull )
	{
		return " CASE WHEN $field is null THEN $ifNull ELSE $field END ";
	}
/*	
	function &MetaTables($ttype=false,$showSchema=false,$mask=false) 
	{	
		$save = $this->metaTablesSQL;
		if ($showSchema && is_string($showSchema)) {
			$this->metaTablesSQL .= " from $showSchema";
		}
		
		if ($mask) {
			$mask = $this->qstr($mask);
			$this->metaTablesSQL .= " like $mask";
		}
		$ret =& ADOConnection::MetaTables($ttype,$showSchema);
		
		$this->metaTablesSQL = $save;
		return $ret;
	}
*/
	function MetaPrimaryKeys($table)
	{
		$table = $this->Quote(strtoupper($table));

		return $this->GetCol("SELECT columnname FROM COLUMNS WHERE tablename=$table AND mode='KEY' ORDER BY pos");
	}
	
	function &MetaIndexes ($table, $primary = FALSE, $owner=false)
	{
            $table = $this->Quote(strtoupper($table));
    
            $sql = "SELECT INDEXNAME,TYPE,COLUMNNAME FROM INDEXCOLUMNS ".
                    " WHERE TABLENAME=$table".
                    " ORDER BY INDEXNAME,COLUMNNO";
    
            global $ADODB_FETCH_MODE;
            $save = $ADODB_FETCH_MODE;
            $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
            if ($this->fetchMode !== FALSE) {
                    $savem = $this->SetFetchMode(FALSE);
            }
            
            $rs = $this->Execute($sql);
            if (isset($savem)) {
                    $this->SetFetchMode($savem);
            }
            $ADODB_FETCH_MODE = $save;
            
            if (!is_object($rs)) {
                    return FALSE;
            }
            
                    $indexes = array();
                    while ($row = $rs->FetchRow()) {
                $indexes[$row[0]]['unique'] = $row[1] == 'UNIQUE';
                $indexes[$row[0]]['columns'][] = $row[2];
            }
            if ($primary) {
                    $indexes['SYSPRIMARYKEYINDEX'] = array(
                                    'unique' => True,       // by definition
                                    'columns' => $this->GetCol("SELECT columnname FROM COLUMNS WHERE tablename=$table AND mode='KEY' ORDER BY pos"),
                            );
            }
            return $indexes;
	}

 	function &MetaColumns ($table)
	{
            global $ADODB_FETCH_MODE;
            $save = $ADODB_FETCH_MODE;
            $ADODB_FETCH_MODE = ADODB_FETCH_NUM;
            if ($this->fetchMode !== FALSE) {
                    $savem = $this->SetFetchMode(FALSE);
            }
            $table = $this->Quote(strtoupper($table));
            
            $retarr = array();
            $all=$this->GetAll("SELECT COLUMNNAME,DATATYPE,LEN,DEC,NULLABLE,MODE,\"DEFAULT\",CASE WHEN \"DEFAULT\" IS NULL THEN 0 ELSE 1 END AS HAS_DEFAULT FROM COLUMNS WHERE tablename=$table ORDER BY pos");
            if (!$all) return $retarr;
            foreach($all as $column)
            {
                    $fld = new ADOFieldObject();
                    $fld->name = $column[0];
                    $fld->type = $column[1];
                    $fld->max_length = $fld->type == 'LONG' ? 2147483647 : $column[2];
                    $fld->scale = $column[3];
                    $fld->not_null = $column[4] == 'NO';
                    $fld->primary_key = $column[5] == 'KEY';
                    if ($fld->has_default = $column[7]) {
                            if ($fld->primary_key && $column[6] == 'DEFAULT SERIAL (1)') {
                                    $fld->auto_increment = true;
                                    $fld->has_default = false;
                            } else {
                                    $fld->default_value = $column[6];
                                    switch($fld->type) {
                                            case 'VARCHAR':
                                            case 'CHARACTER':
                                            case 'LONG':
                                                    $fld->default_value = $column[6];
                                                    break;
                                            default:
                                                    $fld->default_value = trim($column[6]);
                                                    break;
                                    }
                            }
                    }
                    $retarr[$fld->name] = $fld;	
            }
            if (isset($savem)) {
                    $this->SetFetchMode($savem);
            }
            $ADODB_FETCH_MODE = $save;

            return $retarr;
	}
	
	function MetaColumnNames($table)
	{
		$table = $this->Quote(strtoupper($table));

		return $this->GetCol("SELECT columnname FROM COLUMNS WHERE tablename=$table ORDER BY pos");
	}
	
	function _insertid($table,$column)
	{
		return maxdb_insert_id($this->_connectionID);
// unlike it seems, this depends on the db-session and works in a multiuser environment
//		return empty($table) ? False : $this->GetOne("SELECT $table.CURRVAL FROM DUAL");
	}
	
	// if magic quotes disabled, use maxdb_real_escape_string()
	function qstr($s,$magic_quotes=false)
	{
		if (!$magic_quotes) {
		
			if (ADODB_PHPVER >= 0x4300) {
				if (is_resource($this->_connectionID))
					return "'".maxdb_real_escape_string($this->_connectionID, $s)."'";
			}
			if ($this->replaceQuote[0] == '\\'){
				$s = adodb_str_replace(array('\\',"\0"),array('\\\\',"\\\0"),$s);
			}
			return  "'".str_replace("'",$this->replaceQuote,$s)."'"; 
		}
		
		// undo magic quotes for "
		$s = str_replace('\\"','"',$s);
		return "'$s'";
	}
	
	function GetOne($sql,$inputarr=false)
	{
		if (strncasecmp($sql,'sele',4) == 0) {
			$rs =& $this->SelectLimit($sql,1,-1,$inputarr);
			if ($rs) {
				$rs->Close();
				if ($rs->EOF) return false;
				return reset($rs->fields);
			}
		} else {
			return ADOConnection::GetOne($sql,$inputarr);
		}
		return false;
	}
	
	function BeginTrans()
	{
		if ($this->transOff) return true;
		$this->transCnt += 1;
                maxdb_autocommit($this->_connectionID, FALSE);
//		$this->Execute('SET AUTOCOMMIT=0');
//		$this->Execute('BEGIN');
		return true;
//		if ($this->debug) ADOConnection::outp("Transactions not supported in 'maxdb' driver. Use 'maxdbt' or 'maxdbi' driver");
	}

        function SetAutoCommit($autocommit=true) {
                $this->autoCommit=$autocommit;
                return maxdb_autocommit($this->_connectionID, $autocommit);
        }

	function CommitTrans($ok=true) 
	{
		if ($this->transOff) return true; 
		if (!$ok) return $this->RollbackTrans();
		
		if ($this->transCnt) $this->transCnt -= 1;
                maxdb_commit($this->_connectionID);
                maxdb_autocommit($this->_connectionID, TRUE);
//		$this->Execute('COMMIT');
//		$this->Execute('SET AUTOCOMMIT=1');
		return true;
	}
	
	function RollbackTrans()
	{
		if ($this->transOff) return true;
		if ($this->transCnt) $this->transCnt -= 1;
                maxdb_rollback($this->_connectionID);
                maxdb_autocommit($this->_connectionID, TRUE);
		return true;
	}
	
	function RowLock($tables,$where,$flds='1 as ignore') 
	{
		if ($this->transCnt==0) $this->BeginTrans();
		return $this->GetOne("select $flds from $tables where $where for update");
	}

	
	function _affectedrows()
	{
			return maxdb_affected_rows($this->_connectionID);
	}
  
	function CreateSequence($seqname='adodbseq',$start=1)
	{
		if (empty($this->_genSeqSQL)) return false;
		$ok = $this->Execute(sprintf($this->_genSeqSQL,$seqname));
		if (!$ok) return false;
		$start -= 1;
		return $this->Execute("insert into $seqname values($start)");
	}
	
	var $_dropSeqSQL = 'drop table %s';
	function DropSequence($seqname)
	{
		if (empty($this->_dropSeqSQL)) return false;
		return $this->Execute(sprintf($this->_dropSeqSQL,$seqname));
	}
	
	/*
		This algorithm is not very efficient, but works even if table locking
		is not available.
		
		Will return false if unable to generate an ID after $MAXLOOPS attempts.
	*/
	function GenID($seq='adodbseq',$start=1)
	{	
		// if you have to modify the parameter below, your database is overloaded,
		// or you need to implement generation of id's yourself!
		$MAXLOOPS = 100;
		//$this->debug=1;
		while (--$MAXLOOPS>=0) {
			$num = $this->GetOne("select id from $seq");
			if ($num === false) {
				$this->Execute(sprintf($this->_genSeqSQL ,$seq));	
				$start -= 1;
				$num = '0';
				$ok = $this->Execute("insert into $seq values($start)");
				if (!$ok) return false;
			} 
			$this->Execute("update $seq set id=id+1 where id=$num");
			
			if ($this->affected_rows() > 0) {
				$num += 1;
				$this->genID = $num;
				return $num;
			}
		}
		if ($fn = $this->raiseErrorFn) {
			$fn($this->databaseType,'GENID',-32000,"Unable to generate unique id after $MAXLOOPS attempts",$seq,$num);
		}
		return false;
	}


/** 
	// See http://www.maxdb.com/doc/M/i/Miscellaneous_functions.html
	// Reference on Last_Insert_ID on the recommended way to simulate sequences
 	var $_genIDSQL = "update %s set id=LAST_INSERT_ID(id+1);";
	var $_genSeqSQL = "create table %s (id int not null)";
	var $_genSeq2SQL = "insert into %s values (%s)";
	var $_dropSeqSQL = "drop table %s";
	
	function CreateSequence($seqname='adodbseq',$startID=1)
	{
		if (empty($this->_genSeqSQL)) return false;
		$u = strtoupper($seqname);
		
		$ok = $this->Execute(sprintf($this->_genSeqSQL,$seqname));
		if (!$ok) return false;
		return $this->Execute(sprintf($this->_genSeq2SQL,$seqname,$startID-1));
	}
	

	function GenID($seqname='adodbseq',$startID=1)
	{
		// post-nuke sets hasGenID to false
		if (!$this->hasGenID) return false;
		
		$savelog = $this->_logsql;
		$this->_logsql = false;
		$getnext = sprintf($this->_genIDSQL,$seqname);
		$holdtransOK = $this->_transOK; // save the current status
		$rs = @$this->Execute($getnext);
		if (!$rs) {
			if ($holdtransOK) $this->_transOK = true; //if the status was ok before reset
			$u = strtoupper($seqname);
			$this->Execute(sprintf($this->_genSeqSQL,$seqname));
			$this->Execute(sprintf($this->_genSeq2SQL,$seqname,$startID-1));
			$rs = $this->Execute($getnext);
		}
		$this->genID = maxdb_insert_id($this->_connectionID);
		
		if ($rs) $rs->Close();
		
		$this->_logsql = $savelog;
		return $this->genID;
	}
**/
/** MAXDB doesn't support meta databases directly	

  	function &MetaDatabases()
	{
		$qid = maxdb_list_dbs($this->_connectionID);
		$arr = array();
		$i = 0;
		$max = maxdb_num_rows($qid);
		while ($i < $max) {
			$db = maxdb_tablename($qid,$i);
			if ($db != 'maxdb') $arr[] = $db;
			$i += 1;
		}
		return $arr;
	}
**/
		
	// Format date column in sql string given an input format that understands Y M D
	function SQLDate($fmt, $col=false)
	{	
		if (!$col) $col = $this->sysTimeStamp;
		$s = 'DATE_FORMAT('.$col.",'";
		$concat = false;
		$len = strlen($fmt);
		for ($i=0; $i < $len; $i++) {
			$ch = $fmt[$i];
			switch($ch) {
				
			default:
				if ($ch == '\\') {
					$i++;
					$ch = substr($fmt,$i,1);
				}
				/** FALL THROUGH */
			case '-':
			case '/':
				$s .= $ch;
				break;
				
			case 'Y':
			case 'y':
				$s .= '%Y';
				break;
			case 'M':
				$s .= '%b';
				break;
				
			case 'm':
				$s .= '%m';
				break;
			case 'D':
			case 'd':
				$s .= '%d';
				break;
			
			case 'Q':
			case 'q':
				$s .= "'),Quarter($col)";
				
				if ($len > $i+1) $s .= ",DATE_FORMAT($col,'";
				else $s .= ",('";
				$concat = true;
				break;
			
			case 'H': 
				$s .= '%H';
				break;
				
			case 'h':
				$s .= '%I';
				break;
				
			case 'i':
				$s .= '%i';
				break;
				
			case 's':
				$s .= '%s';
				break;
				
			case 'a':
			case 'A':
				$s .= '%p';
				break;
				
			case 'w':
				$s .= '%w';
				break;
				
			case 'l':
				$s .= '%W';
				break;
			}
		}
		$s.="')";
		if ($concat) $s = "CONCAT($s)";
		return $s;
	}
	

	// returns concatenated string
	// much easier to run "maxdbd --ansi" or "maxdbd --sql-mode=PIPES_AS_CONCAT" and use || operator
	function Concat()
	{
		$s = "";
		$arr = func_get_args();
		
		// suggestion by andrew005@mnogo.ru
		$s = implode(',',$arr); 
		if (strlen($s) > 0) return "CONCAT($s)";
		else return '';
	}
	
	function OffsetDate($dayFraction,$date=false)
	{		
		if (!$date) $date = $this->sysDate;
		return "from_unixtime(unix_timestamp($date)+($dayFraction)*24*3600)";
	}
	
	// returns true or false
	function _connect($argHostname, $argUsername, $argPassword, $argDatabasename, $persistent=false, $forcenew=false)
	{
		$this->forceNewConnect = $forcenew;
		$this->persistent = $persistent;
                $this->_connectionID = maxdb_connect($argHostname,$argUsername,$argPassword, $argDatabasename);
	
		if ($this->_connectionID === false) return false;
		if ($this->autoRollback) $this->RollbackTrans();
//		if ($argDatabasename) return $this->SelectDB($argDatabasename);
		return true;	
	}
	
	// returns true or false
	function _pconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
            return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabasename, true);
	}
	
	function _nconnect($argHostname, $argUsername, $argPassword, $argDatabasename)
	{
            return $this->_connect($argHostname, $argUsername, $argPassword, $argDatabasename, false, true);
	}
	
/*
 	function &MetaColumns($table) 
	{
		global $ADODB_FETCH_MODE;
		$save = $ADODB_FETCH_MODE;
		$ADODB_FETCH_MODE = ADODB_FETCH_NUM;
		if ($this->fetchMode !== false) $savem = $this->SetFetchMode(false);
		$rs = $this->Execute(sprintf($this->metaColumnsSQL,$table));
		if (isset($savem)) $this->SetFetchMode($savem);
		$ADODB_FETCH_MODE = $save;
		if (!is_object($rs)) {
			$false = false;
			return $false;
		}
			
		$retarr = array();
		while (!$rs->EOF){
			$fld = new ADOFieldObject();
			$fld->name = $rs->fields[0];
			$type = $rs->fields[1];
			
			// split type into type(length):
			$fld->scale = null;
			if (preg_match("/^(.+)\((\d+),(\d+)/", $type, $query_array)) {
				$fld->type = $query_array[1];
				$fld->max_length = is_numeric($query_array[2]) ? $query_array[2] : -1;
				$fld->scale = is_numeric($query_array[3]) ? $query_array[3] : -1;
			} elseif (preg_match("/^(.+)\((\d+)/", $type, $query_array)) {
				$fld->type = $query_array[1];
				$fld->max_length = is_numeric($query_array[2]) ? $query_array[2] : -1;
			} elseif (preg_match("/^(enum)\((.*)\)$/i", $type, $query_array)) {
				$fld->type = $query_array[1];
				$arr = explode(",",$query_array[2]);
				$fld->enums = $arr;
				$zlen = max(array_map("strlen",$arr)) - 2; // PHP >= 4.0.6
				$fld->max_length = ($zlen > 0) ? $zlen : 1;
			} else {
				$fld->type = $type;
				$fld->max_length = -1;
			}
			$fld->not_null = ($rs->fields[2] != 'YES');
			$fld->primary_key = ($rs->fields[3] == 'PRI');
			$fld->auto_increment = (strpos($rs->fields[5], 'auto_increment') !== false);
			$fld->binary = (strpos($type,'blob') !== false);
			$fld->unsigned = (strpos($type,'unsigned') !== false);
				
			if (!$fld->binary) {
				$d = $rs->fields[4];
				if ($d != '' && $d != 'NULL') {
					$fld->has_default = true;
					$fld->default_value = $d;
				} else {
					$fld->has_default = false;
				}
			}
			
			if ($save == ADODB_FETCH_NUM) {
				$retarr[] = $fld;
			} else {
				$retarr[strtoupper($fld->name)] = $fld;
			}
				$rs->MoveNext();
			}
		
			$rs->Close();
			return $retarr;	
	}
*/		
	// returns true or false
	function SelectDB($dbName) 
	{
		$this->databaseName = $dbName;
		if ($this->_connectionID) {
			return @maxdb_select_db($this->_connectionID,$dbName);		
		}
		else return false;	
	}
	
	// parameters use PostgreSQL convention, not MySQL
	function &SelectLimit($sql,$nrows=-1,$offset=-1,$inputarr=false,$secs=0)
	{
		$offsetStr =($offset>=0) ? "$offset," : '';
		// jason judge, see http://phplens.com/lens/lensforum/msgs.php?id=9220
		if ($nrows < 0) $nrows = '18446744073709551615'; 
		
		if ($secs)
			$rs =& $this->CacheExecute($secs,$sql." LIMIT $offsetStr$nrows",$inputarr);
		else
			$rs =& $this->Execute($sql." LIMIT $offsetStr$nrows",$inputarr);
		return $rs;
	}
	
	// returns queryID or false
	function _query($sql,$inputarr)
	{
	//global $ADODB_COUNTRECS;
		//if($ADODB_COUNTRECS) 
		return maxdb_query($this->_connectionID, $sql);
		//else return @maxdb_unbuffered_query($sql,$this->_connectionID); // requires PHP >= 4.0.6
	}

	/*	Returns: the last error message from previous database operation	*/	
	function ErrorMsg() 
	{
	
		if ($this->_logsql) return $this->_errorMsg;
		if (empty($this->_connectionID)) $this->_errorMsg = @maxdb_error();
		else $this->_errorMsg = @maxdb_error($this->_connectionID);
		return $this->_errorMsg;
	}
	
	/*	Returns: the last error number from previous database operation	*/	
	function ErrorNo() 
	{
		if ($this->_logsql) return $this->_errorCode;
		if (empty($this->_connectionID))  return @maxdb_errno();
		else return @maxdb_errno($this->_connectionID);
	}
	
	// returns true or false
	function _close()
	{
		@maxdb_close($this->_connectionID);
		$this->_connectionID = false;
	}

	
	/*
	* Maximum size of C field
	*/
	function CharMax()
	{
		return 255; 
	}
	
	/*
	* Maximum size of X field
	*/
	function TextMax()
	{
		return 4294967295; 
	}
	
}
	
/*--------------------------------------------------------------------------------------
	 Class Name: Recordset
--------------------------------------------------------------------------------------*/


class ADORecordSet_maxdb extends ADORecordSet {	
	
	var $databaseType = "maxdb";
	var $canSeek = true;
	
	function ADORecordSet_maxdb($queryID,$mode=false) 
	{
		if ($mode === false) { 
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		switch ($mode)
		{
		case ADODB_FETCH_NUM: $this->fetchMode = MAXDB_NUM; break;
		case ADODB_FETCH_ASSOC:$this->fetchMode = MAXDB_ASSOC; break;
		case ADODB_FETCH_DEFAULT:
		case ADODB_FETCH_BOTH:
		default:
			$this->fetchMode = MAXDB_BOTH; break;
		}
		$this->adodbFetchMode = $mode;
		$this->ADORecordSet($queryID);

		// the following is required for mysql odbc driver in 4.3.1 -- why?
//		$this->EOF = false;
		$this->_currentRow = -1;

	}
	
	function _initrs()
	{
	//GLOBAL $ADODB_COUNTRECS;
	//	$this->_numOfRows = ($ADODB_COUNTRECS) ? @maxdb_num_rows($this->_queryID):-1;
		$this->_numOfRows = @maxdb_num_rows($this->_queryID);
		$this->_numOfFields = @maxdb_num_fields($this->_queryID);

                //seek backwards to 0
                $this->_seek(0);
	}

	function &FetchField($fieldOffset = -1) 
	{	
	  $fieldnr = $fieldOffset;
	  if ($fieldOffset != -1) {
	    $fieldOffset = maxdb_field_seek($this->_queryID, $fieldnr);
	  }
	  $o = maxdb_fetch_field($this->_queryID);
	  return $o;
	}

/*
	//this is mostly guesswork based on the php.net documentation.
	function &FetchField($fieldOffset = -1) 
	{	
		if ($fieldOffset != -1) {
			$o = @maxdb_fetch_field_direct($this->_queryID, $fieldOffset);
//			$f = @maxdb_field_flags($this->_queryID,$fieldOffset);
//			$o->max_length = @maxdb_field_len($this->_queryID,$fieldOffset); // suggested by: Jim Nicholson (jnich@att.com)
			//$o->max_length = -1; // mysql returns the max length less spaces -- so it is unrealiable
			$o->binary = (strpos($f->type,'binary')!== false);
		}
*/
//		else if ($fieldOffset == -1) {	/*	The $fieldOffset argument is not provided thus its -1 	*/
/*
			$o = @maxdb_fetch_field($this->_queryID);
			//$o->max_length = @maxdb_field_len($this->_queryID); // suggested by: Jim Nicholson (jnich@att.com)
			//$o->max_length = -1; // mysql returns the max length less spaces -- so it is unrealiable
		}
			
		return $o;
	}
*/
	function &GetRowAssoc($upper=true)
	{
		if ($this->fetchMode == MAXDB_ASSOC && !$upper) return $this->fields;
		$row =& ADORecordSet::GetRowAssoc($upper);
		return $row;
	}
	
	/* Use associative array to get fields array */
	function Fields($colname)
	{	
		// added @ by "Michael William Miller" <mille562@pilot.msu.edu>
		if ($this->fetchMode != MAXDB_NUM) return @$this->fields[$colname];
		
		if (!$this->bind) {
			$this->bind = array();
			for ($i=0; $i < $this->_numOfFields; $i++) {
				$o = $this->FetchField($i);
				$this->bind[strtoupper($o->name)] = $i;
			}
		}
		 return $this->fields[$this->bind[strtoupper($colname)]];
	}
	

	function _seek($row)
	{
	  if ($this->_numOfRows == 0) 
	    return false;

	  if ($row < 0)
	    return false;


        if ($row===0)
            $row=(-1*$this->_numOfRows)-1;

	  maxdb_data_seek($this->_queryID, $row);
	  $this->EOF = false;
	  return true;
	}

/*
	function _seek($row)
	{
		if ($this->_numOfRows == 0) return false;
		return @maxdb_data_seek($this->_queryID,$row);
	}
*/	
	function MoveNext()
	{
		//return adodb_movenext($this);
		//if (defined('ADODB_EXTENSION')) return adodb_movenext($this);
		if (@$this->fields =& maxdb_fetch_array($this->_queryID,$this->fetchMode)) {
			$this->_currentRow += 1;
			return true;
		}
		if (!$this->EOF) {
			$this->_currentRow += 1;
			$this->EOF = true;
		}
		return false;
	}
	
	function _fetch()
	{
                $row=maxdb_fetch_array($this->_queryID, $this->fetchMode);
//                echo "ROW ROW:<pre>"; print_r($row); echo " <p> BWAHAHA";
                $this->fields=$row;
//                print_r($this->fields);
//		$this->fields =  @maxdb_fetch_array($this->_queryID); //,$this->fetchMode);
		return is_array($this->fields);
	}
	
	function _close() {
		@maxdb_free_result($this->_queryID);	
		$this->_queryID = false;	
	}
	
	function MetaType($t,$len=-1,$fieldobj=false)
	{
		if (is_object($t)) {
			$fieldobj = $t;
			$t = $fieldobj->type;
			$len = $fieldobj->max_length;
		}
		
		$len = -1; // maxdb max_length is not accurate
		switch (strtoupper($t)) {
		case 'STRING': 
		case 'CHAR':
		case 'VARCHAR': 
		case 'TINYBLOB': 
		case 'TINYTEXT': 
		case 'ENUM': 
		case 'SET': 
			if ($len <= $this->blobSize) return 'C';
			
		case 'TEXT':
		case 'LONGTEXT': 
		case 'MEDIUMTEXT':
			return 'X';
			
		// php_maxdb extension always returns 'blob' even if 'text'
		// so we have to check whether binary...
		case 'IMAGE':
		case 'LONGBLOB': 
		case 'BLOB':
		case 'MEDIUMBLOB':
			return !empty($fieldobj->binary) ? 'B' : 'X';
			
		case 'YEAR':
		case 'DATE': return 'D';
		
		case 'TIME':
		case 'DATETIME':
		case 'TIMESTAMP': return 'T';
		
		case 'INT': 
		case 'INTEGER':
		case 'BIGINT':
		case 'TINYINT':
		case 'MEDIUMINT':
		case 'SMALLINT': 
			
			if (!empty($fieldobj->primary_key)) return 'R';
			else return 'I';
		
		default: return 'N';
		}
	}

}

class ADORecordSet_ext_maxdb extends ADORecordSet_maxdb {	
	function ADORecordSet_ext_maxdb($queryID,$mode=false) 
	{
		if ($mode === false) { 
			global $ADODB_FETCH_MODE;
			$mode = $ADODB_FETCH_MODE;
		}
		switch ($mode)
		{
		case ADODB_FETCH_NUM: $this->fetchMode = MAXDB_NUM; break;
		case ADODB_FETCH_ASSOC:$this->fetchMode = MAXDB_ASSOC; break;
		case ADODB_FETCH_DEFAULT:
		case ADODB_FETCH_BOTH:
		default:
		$this->fetchMode = MAXDB_BOTH; break;
		}
		$this->adodbFetchMode = $mode;
		$this->ADORecordSet($queryID);
	}
	
	function MoveNext()
	{
		return @adodb_movenext($this);
	}
}

}
?>
