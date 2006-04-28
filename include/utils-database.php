<?php
/**
 * utils-database.php - this file contains database utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Beth Macknik
 * @package XRMS_API
 *
 * $Id: utils-database.php,v 1.31 2006/04/28 02:44:17 vanmer Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-datadict.inc.php');


/**
  * Create the string to use for a company name search
  *
  * @param string $company_name with partial name of company to search for
  * @param string $search_type with string of 'starts','ends','contains', 'matches'
**/
function company_search_string($company_name, $search_type=false) {

    if (!$search_type) $search_type=get_admin_preference($con, 'company_search_type');
    if (!$search_type) $search_type='contains';
    switch ($search_type) {
    case 'starts':
        return "$company_name%";
    break;
    case 'ends':
        return "%$company_name";
    break;
    case 'contains':
        return "%$company_name%";
        break;
    case 'matches':
        return $company_name;
    break;
    }
    return false;
}
/**
 * Create the array of existing tables.
 *
 * @param handle @$con handle to database connection
 */
function list_db_tables(&$con) {
    return $con->MetaTables('TABLES');
}

/**
 * Confirm that the table does not currently have any records.
 *
 * @param handle @$con  handle to database connection
 * @param string $table table name to check
 */
function confirm_no_records(&$con, $table) {
    $sql = "select count(*) as recCount from $table";

    //execute
    $rst = $con->execute($sql);
    $recCount = $rst->fields['recCount'];

    if ($recCount > 0) {
        return (false);
    } else {
        return (true);
    }
} // end confirm_no_records fn

/**
 * Makes any of the database names singular
 *
 * @param string $word to be singularized
 */
function make_singular($word) {
    switch ($word) {
        case 'company_division':
        return 'division';
    break;
    }
    $word = preg_replace("|([^aeiou])s$|i", "\$1", $word);
    $word = preg_replace("|ies$|i", "y", $word);
    $word = preg_replace("|uses$|i", "us", $word);
    $word = preg_replace("|ases$|i", "ase", $word);
    $word = preg_replace("|ses$|i", "s", $word);
    $word = preg_replace("|es$|i", "e", $word);
    return $word;
}

/**
 * Returns the name/title format for the various tables
 *
 * @param string $table Table name
 *
 * @todo Add naming conventions as needed
 */
function table_name($table) {
    switch ($table) {
        case "contacts":
            return array("first_names", "last_name");
        break;
        case "email_templates":
        case "activities":
        case "cases":
        case "campaigns":
        case "opportunities":
            return array(make_singular($table) . "_title");
        break;
        case "files":
            return array(make_singular($table)."_pretty_name");
        break;
        case "company_division":
            return array("division_name");
        break;
        default:
            return array(make_singular($table) . "_name");
        break;
    }
}

/**
 * Function that generates a URL to the one.php page based on a database table and ID passed in
 *
 * @param string $table with tablename of the entity
 * @param integer $id with ID to pass to the one.php page
 * @return URL relative to $http_site_root
**/
function table_one_url($table, $id) {
    $singular=make_singular($table);
    $field_name=$singular.'_id';
    switch ($table) {
        case 'company_division':
            $return_url="/companies/one.php?division_id=$id";
        break;
        default:
            $return_url="/$table/one.php?$field_name=$id";
        break;
    }
    return $return_url;
}

/**
 * Function that generates a URL to the some.php page based on a database table passed in
 *
 * @param string $table with tablename of the entity
 * @return string with URL relative to $http_site_root
**/
function table_some_url($table) {
    $singular=make_singular($table);
    $return_url="/$singular/some.php";
}

/**
 * Function to execute a SQL file statement by statement using the ; character as a separator
 *
 * @param adodbconnection $con with handle to the database
 * @param string $file_path with path to SQL file to open and execute
 * @return boolean indicating success of SQL file open operation
**/
function execute_batch_sql_file($con, $file_path) {
    if (file_exists($file_path)) {
        $fh = fopen($file_path, 'r');
        $last_buff='';
        while (!feof($fh)) {
            $buffer = fgets($fh, 4096);
            $info_file.=$buffer;
        }
        fclose($fh);
        $info_sql_array=explode(";",$info_file);
        foreach ($info_sql_array as $sql_line) {
            $sql_line_array=explode("\n",$sql_line);
            $sql_array=array();
            foreach ($sql_line_array as $newlined) {
                if (strpos($newlined,'--')!==0) {
                    $sql_array[]=$newlined;
                }
            }
            $sql=trim(implode("\n",$sql_array));
            if (!empty($sql)) {
                $rst=$con->execute($sql);
                if (!$rst) db_error_handler($con, $sql);
            }
        } return true;
    } else return false;
}

/*****************************************************************************/
/**
 * Provides an adodbconnection handle for the XRMS database
 *
 * @return adodbconnection $con connected to XRMS database
 */
function get_xrms_dbconnection() {

    $xcon = &adonewconnection($GLOBALS['xrms_db_dbtype']);
    $xcon->NConnect( $GLOBALS['xrms_db_server'],
                    $GLOBALS['xrms_db_username'],
                    $GLOBALS['xrms_db_password'],
                    $GLOBALS['xrms_db_dbname']);
    return $xcon;

}

/*****************************************************************************/
/**
 * function db_con_cleanup - check for likely database connection objects and clear them
 *
 * @param void
 * @return void
 */
function db_con_cleanup() {

    global $con;
    global $_objDB;

    if (isset($con) and is_object($con)) {
        $con->close();
        unset($con);
    }
    if (isset($_objDB) and is_object($_objDB)) {
        $_objDB->close();
        unset($_objDB);
    }
}

/** SESSION HANDLING CODE, PORTED TO ADOdb FROM COMMENTS ON PHP.NET FROM  rafael dot tz at uol dot com dot br
25-Jul-2003 11:38
**/
function check_session_table($con, $table_list=false) {
    global $include_directory;
    require_once($include_directory . 'adodb/adodb-datadict.inc.php');
    $dict = NewDataDictionary( $con );
    if (!$table_list) $table_list = list_db_tables($con);
      $table_name='sessions';
      if (!in_array($table_name,$table_list)) {
        //define details of the table in the fields array
        $table_fields=array();
        $table_fields[]=array('NAME'=>'session_id','TYPE'=>'C','SIZE'=>255,'NOTNULL'=>'NOTNULL','KEY'=>'KEY');
        $table_fields[]=array('NAME'=>'last_updated','TYPE'=>'T', 'NOTNULL'=>'NOTNULL', 'INDEX'=>'INDEX');
        $table_fields[]=array('NAME'=>'data_value','TYPE'=>'X');

        //no global table options needed, so setting to false
        $table_opts=false;

        $sql=$dict->CreateTableSQL( $table_name, $table_fields, $table_opts );

        //create an index on the last_updated field, for easy queryability
        $index_name='last_update';
        $index_options=array();
        $index_columns[]='last_updated';
        $index_sql=$dict->CreateIndexSQL( $index_name, $table_name, $index_columns, $index_options );

        //merge create and index SQL
        $sql = array_merge($sql, $index_sql);
        foreach ($sql AS $sql_line) {
            $rst=$con->execute($sql_line);
            if (!$rst) db_error_handler($con, $sql_line);
        }
        return _("Added sessions table");
    }
    return '';
}

function sessao_open($aSavaPath, $aSessionName)
{
       global $aTime;

       sessao_gc( $aTime );
       return True;
}

function sessao_close()
{
       return True;
}

function sessao_read( $aKey )
{
        $table_name='sessions';
        $con=get_xrms_dbconnection();
       $sql = "SELECT data_value FROM sessions WHERE session_id=".$con->qstr($aKey);
        $rst=$con->execute($sql);
       if($rst AND !$rst->EOF)
       {
            $data=$rst->fields['data_value'];
            $rst->close();
            $con->close();
             return $data;
       } ELSE {
            $data=array();
            $data['session_id']=$aKey;
            $data['last_updated']=time();
            $data['data_value']='';

            $sql = $con->getInsertSQL($table_name, $data);
            if ($sql) {
                $rst=$con->execute($sql);
            }
             $con->close();
             return "";
       }
}

function sessao_write( $aKey, $aVal )
{
        $con=get_xrms_dbconnection();
//       $aVal = addslashes( $aVal );
        $data=array();
        $data['data_value']=$aVal;
        $data['last_update']=time();
       $sql = "SELECT data_value, last_updated FROM sessions WHERE session_id=".$con->qstr($aKey);
       $rst=$con->execute($sql);
       if ($rst) {
            $upd=$con->getUpdateSQL($rst, $data);
            if ($upd) {
                $upd_rst=$con->execute($upd);
                if (!$upd_rst) { db_error_handler($con, $upd); }
            }
            $rst->close();
        }
        $con->close();
       return True;
}

function sessao_destroy( $aKey )
{
        $con=get_xrms_dbconnection();
        $sql = "DELETE FROM sessions WHERE session_id=".$con->qstr($aKey);
        $rst=$con->execute($sql);
        $con->close();
       return True;
}

function sessao_gc( $aMaxLifeTime )
{
        $con=get_xrms_dbconnection();

        $sql = "DELETE FROM sessions WHERE (($time - last_updated) > $aMaxLifeTime)";
        $rst=$con->execute($sql);
        $con->close();
       return True;
}



//make sure the db connection cleanup gets run at the end of the script execution
register_shutdown_function('db_con_cleanup');


/*****************************************************************************/

   /**
    * Retrieves the 'primarykey' of a given table
    *
    * @param object $con ADOdb Database handle
    * @param string $_strTableName Name of the table to query
    * @return string $_key primarykey of table
    */
    function get_primarykey_from_table($con, $_strTableName)
    {
        $fields = get_schema_of_table($con, $_strTableName);

        return $fields['primarykey'];
    };

   /**
    * Retrieves the 'record_status' field of a given table
    *
    * @param object $con ADOdb Database handle
    * @param string $_strTableName Name of the table to query
    * @return string $_field name of 'record_status' of table
    */
    function get_status_from_table($con, $_strTableName)
    {
       /**
        * Creates a "singular' name version of a plural name
        *
        * Many tables have a "pluralized' name, but the record ID
        * and status fields are 'singular'. This convertion function
        * solves that issue and helps create these general methods
        *
        * @var string $_singular_name Singular version of plural name
        * @access private
        * @static
        */
        $_singular_name = make_singular($_strTableName);

        $fields = get_schema_of_table($con, $_strTableName);
        $fields = $fields['fields'];

        // Discover 'record_status' field name
        if (array_key_exists( $_singular_name . '_record_status', $fields ))
            $_field = $_singular_name . '_record_status';

        else if (array_key_exists( $_strTableName . '_record_status', $fields ))
            $_field = $_strTableName . '_record_status';

        else
            $_field = false;

        return $_field;
    };


   /**
    * Reads the schema using ADOdb fn MetaColumns()
    *
    * @param object dbh ADOdb Database handle
    * @param string tablename Name of the table to query
    * @todo Add support for ADOdb fn MetaForeignKeys()
    */
    function get_schema_of_table($con, $_strTableName) {

        $struct['tablename'] = $_strTableName;

        $columns = $con->MetaColumns($_strTableName);

        $i=0;

        if(!is_array($columns)) {
            echo "ADOdb_QuickForm error: no columns found for table $_strTableName dbh $con<br>";
            return false;
        }

        foreach($columns as $column) {

            $struct['fields'][$column->name]['name'] = $column->name;

            if($column->primary_key) {

                $struct['primarykey'] = $column->name;
                $struct['fields'][$column->name]['type'] = 'primarykey';

            } else {

                $struct['fields'][$column->name]['type'] = $column->type;
                if('enum' == $column->type) {
                    // set up enum choices and strip ' characters
                    foreach($column->enums as $k => $v) {
                        if("'" == substr($v, 0, 1)) {
                            $v = substr($v, 1);
                        }
                        if("'" == substr($v, -1, 1)) {
                            $v = substr($v, 0, -1);
                        }
                        $struct['fields'][$column->name]['enums'][$v] = $v;
                    }
                }

            }
            $struct['fields'][$column->name]['displayOrder'] = $i;
            $struct['fields'][$column->name]['displayName'] = $column->name;
            $i++;
        }

        /* Not implemented for mysql in ADOdb (yet)
        $foreignkeys = $dbh->MetaForeignKeys($tablename, false, true);
        if($foreignkeys) {
            foreach($foreignkeys as $foreignkey) {
                //do that stuff
        }
        } */

        return $struct;
    };


/*****************************************************************************/

 /**
  * This is a collection of generic DB handing functions. These are designed
  * handle most situations found within the XRMS system.
  *
  * __record_add_update() is the core function. By passing an array of data,
  * the name of the table to use and an ADOdb connection object, this function
  * handles all the "decissions" to determine if the given data is to be used
  * to UPDATE an existing record, to to be INSERTed into a new record.
  *
  * All secondary and DB only fields are properly polulated.
  *
  * This function will return either a copy of the record data in a named array,
  * or an ADOdb record set. Your choice. The array is default.
  *
  * __record_insert() is a secondary function. Use this if you know that
  * the data you have should be INSERTed. It will not do any checking to
  * verify tha the data exists or not. If this call fails, double check
  * to make sure it truely is orginal data, or just use __record_add_update()
  *
  * __record_update() is a secondary function. Use this if you know that
  *
  *
  * @access public
  * @category db_handling
  *
  *
  */


 /**
  * Determines whether to INSERT or UPDATE a record in the database
  *
  * The return value of the Function has 3 possibilities:
  * 1) on INSERT, record ID of new record
  * 2) on UPDATE, full data array for "updated" record, entire record
  * 3) on DB failure, FALSE boolean
  *
  * 1 and 2 can be overridden with the '$return_recordset' parameter set to TRUE,
  * this forces an ADOdb recorset to be returned regardless of operation.
  *
  * In order to handle data from variou sources $_magic_quotes has been added
  * as a final parameter. This boolen informs the function whether in the incoming
  * data is "slash quoted" already or not. It defaults to FALSE, not "slash quoted"
  *
  * @name __record_add_update()
  * @access public
  * @category db_handling
  *
  * @uses ADOdb::GetInsertSQL() Creates INSERT SQL from array data
  * @uses ADOdb::GetUpdateSQL() Creates UPDATE SQL from array data
  * @uses ADOdb::Execute()      Execute generated SQL
  *
  * @static
  * @final
  *
  * @author Walter Torres <walter@torres.ws>
  *
  * @param  object   $_objCon           Connection object ot Database to hit
  * @param  string   $_strTableName     table name to place data
  * @param  string   $_identifier       field name to search for matching record
  * @param  array    $_aryData          Array of data for placement
  * @param  boolean  $_magic_quotes     F - inbound data has not "add slashes", T - data has "add slashes
  * @param  boolean  $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
  *
  * @return mixed  $_retVal       Record ID on sucess, FALSE on failure
  */
function __record_add_update ( $_objCon, $_strTableName, $_identifier, $_aryData, $_magic_quotes = false, $_return_recordset = false )
{
   /**
    * Default return value
    *
    * Returns record id, data array, ADOdb recordset or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Returns record id, data array, ADOdb recordset or boolean upon failure
    * @access private
    * @static
    */
    $_retVal = false;

    // We need to derive the primary and status keys for this table
    $_primary_key = get_primarykey_from_table($_objCon, $_strTableName);
    $_status_key  = get_status_from_table($_objCon, $_strTableName);

    # Find record in database
    $sql = "SELECT *
              FROM $_strTableName
                WHERE $_identifier = " . $_objCon->qstr($_aryData[$_identifier], get_magic_quotes_gpc());

    if ( $_recordSet = $_objCon->Execute($sql))
    {
        global $users, $session_user_id;

        // Current Time Stamp
        $_timeStamp = date( 'Y-m-d H:i:s', time() );

        // Add 'modified' date and by whom
        $_aryData['modified_by'] = $session_user_id;      // some tables have these field names
        $_aryData['modified_on'] = $_timeStamp;

        $_aryData['last_modified_by'] = $session_user_id; // others use these
        $_aryData['last_modified_at'] = $_timeStamp;

        // Set record status, either way
        $_aryData[$_status_key] = 'a';

        // Keep the primary key ID
        $_primary_key_id = $_recordSet->fields[$_primary_key];

        // UPDATE record
        if ( $_recordSet && ( $_recordSet->RecordCount() != 0 ) )
        {
            $updateSQL = $_objCon->GetUpdateSQL($_recordSet, $_aryData, true, true, ADODB_FORCE_NULLS);

            if ( $rs =& $_objCon->Execute($updateSQL) )
            {
                // Return Recordset
                if ( $_return_recordset )
                    $_retVal = $rs;

                // or return data array
                else
                {
                    $_retVal = $rs->fields;

                    $_retVal[$_primary_key] = $_primary_key_id;
                    $_retVal['primarykey']  = $_primary_key;
                    $_retVal['statuskey']   = $_status_key;
                    $_retVal['action']      = 'update';

                }
            }
            else
            {
                db_error_handler($_objCon, $updateSQL . ' - add/update [2]');
                $_retVal = false;
            }
        }

        // INSERT record
        else if ( $_recordSet && ( $_recordSet->RecordCount() == 0 ) )
        {
            // Add 'created' date and by whom
            $_aryData['created_by'] = $session_user_id;      // some tables have these field names
            $_aryData['created_on'] = $_timeStamp;

            $_aryData['entered_by'] = $session_user_id;      // others use these
            $_aryData['entered_at'] = $_timeStamp;

           /**
            * Generated INSERT SQL from array
            *
            * @var string $sql Generated INSERT SQL
            * @access private
            * @static
            */
            $sql = $_objCon->GetInsertSQL($_strTableName, $_aryData, true);

           /**
            * Record Set of found data
            *
            * @var recordSet $_recordSet Retrieve record of FUND data from database
            * @access private
            * @static
            */
            // No record was found, so we need to INSERT it
            if ( $rs =& $_objCon->Execute($sql) )
            {
                $_retVal = $rs->fields;

                $_retVal[$_primary_key] = $_objCon->Insert_ID();
                $_retVal['primarykey']  = $_primary_key;
                $_retVal['statuskey']   = $_status_key;
                $_retVal['action']      = 'insert';
            }
            else
            {
                db_error_handler($_objCon, $sql . ' - add/update [3]');
            }
        }
    }
    // DB access failure
    else
    {
        db_error_handler($_objCon, $sql . ' - add/update [1]' );
    }

    // Send back what we have
    return $_retVal;
};


 /**
  * Generic INSERT method for database
  *
  * If a record is INSERted, either an named array will be returned (default)
  * or an ADOdb recordset if the $_return_recordset parameter is set.
  *
  * If a data array is returned (default), 3 elements will be returned:
  * 1) '[primarykey]' = 0 - this indicated that a record was not created
  * 1) 'primarykey'  = table primary key
  * 2) 'statuskey'   = table status field
  *
  * @name __insert_record()
  * @access private
  * @category db_handling
  *
  * @uses ADOdb::GetInsertSQL() Creates INSERT SQL from array data
  * @uses ADOdb::Execute()      Execute generated SQL
  *
  * @static
  * @final
  *
  * @author Walter Torres <walter@torres.ws>
  *
  * @param  object   $_objCon            Connection object ot Database to hit
  * @param  string   $_strTableName      table name to INSERT data into
  * @param  array    $_aryData           Array of data for INSERT
  * @param  boolean  $_magic_quotes      F - inbound data has not "add slashes", T - data has "add slashes" (defaults to false)
  * @param  boolean  $_return_recordset  indicating if adodb recordset object should be returned (defaults to false)
  *
  * @return mixed    $_retVal           Record ID on sucess, FALSE on failure
  */
function __record_insert ( $_objCon, $_strTableName, $_aryData, $_magic_quotes = false, $_return_recordset = false )
{
   /**
    * Default return value
    *
    * Returns record id of new record, ADOdb recordset or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Returns record id, ADOdb recordset or boolean upon failure
    * @access private
    * @static
    */
    $_retVal = false;

    global $users, $session_user_id;

    // What is the primary key for this table
    $_primary_key = get_primarykey_from_table($_objCon, $_strTableName);
    $_status_key = get_status_from_table($_objCon, $_strTableName);

    // Current Time Stamp
    $_timeStamp = date( 'Y-m-d H:i:s', time() );

    // Add 'created' date and by whom
    $_aryData['created_by'] = $session_user_id;      // some tables have these field names
    $_aryData['created_on'] = $_timeStamp;

    $_aryData['entered_by'] = $session_user_id;      // others use these
    $_aryData['entered_at'] = $_timeStamp;

    // Add 'modified' date and by whom
    $_aryData['modified_by'] = $session_user_id;      // some tables have these field names
    $_aryData['modified_on'] = $_timeStamp;

    $_aryData['last_modified_by'] = $session_user_id; // others use these
    $_aryData['last_modified_at'] = $_timeStamp;

    // Set record status
    $_aryData[$_status_key] = 'a';

   /**
    * Generated INSERT SQL from array
    *
    * @var string $_sql Generated INSERT SQL
    * @access private
    * @static
    */
    $_sql = $_objCon->GetInsertSQL($_strTableName, $_aryData, $_magic_quotes);

    // Was a record created?
    if ( $_recordSet =& $_objCon->Execute($_sql) )
    {
        // Return Recordset
        if ( $_return_recordset )
        {
            $_aryData = array();
            $_aryData[$_primary_key] = $_objCon->Insert_ID();

            $_retVal = __record_find ( $_objCon, $_strTableName, $_aryData, $_search_condition, true, $_show_deleted );
        }

        // or return data array
        else
        {
            $_retVal[$_primary_key] = $_objCon->Insert_ID();
            $_retVal['primarykey'] = $_primary_key;
            $_retVal['statuskey']= $_status_key;
        }
    }
    else
    {
        db_error_handler($_objCon, $sql . ' - record_insert [1]');
        $_retVal = false;
    }

    // Send back what we have
    return $_retVal;
};

 /**
  * Generic UPDATE method for database
  *
  * If a record is UPDATEd, either an named array will be returned (default)
  * or an ADOdb recordset if the $_return_recordset parameter is set.
  *
  * If a data array is returned (default), 3 elements will be returned:
  * 1) '[primarykey]' = 0 - this indicated that a record was not updated
  * 1) 'primarykey'  = table primary key
  * 2) 'statuskey'   = table status field
  *
  * This method can also mark a record as a [logical] deleted record
  * via a TRUE in the seventh parameter.
  *
  * @name __record_update()
  * @access private
  * @category db_handling
  *
  * @uses ADOdb::GetUpdateSQL() Creates UPDATE SQL from array data
  * @uses ADOdb::Execute()      Execute generated SQL
  *
  * @static
  * @final
  *
  * @author Walter Torres <walter@torres.ws>
  *
  * @param  object   $_objCon           Connection object ot Database to hit
  * @param  string   $_strTableName     Table name to UPDATE data into
  * @param  string   $_identifier       Field name to search for matching record
  * @param  array    $_aryData          Array of data for UPDATE
  * @param  boolean  $return_recordset  indicating if adodb recordset object should be returned (defaults to false)
  * @param  boolean  $_magic_quotes     F - inbound data has not "add slashes", T - data has "add slashes" (defaults to false)
  * @param  boolean  $_deleteRecord     Indicates whether to mark the record as "deleted' [optional]
  *
  * @return mixed   $_retVal           Record ID on sucess, FALSE on failure
  */
function __record_update ( $_objCon, $_strTableName, $_identifier, $_aryData, $_magic_quotes = false, $_return_recordset = false, $_deleteRecord = false )
{
   /**
    * Default return value
    *
    * Returns record data array of updated record, ADOdb recordset or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Returns record id, ADOdb recordset or boolean upon failure
    * @access private
    * @static
    */
    $_retVal = false;

    global $users, $session_user_id, $msg;

    // If a record was found, it can be UPDATEd
    if ($rs = __record_find ( $_objCon, $_strTableName, $_aryData, 'AND', $_magic_quotes, true ) )
    {
        // What is the primary key for this table
        $_primary_key = get_primarykey_from_table($_objCon, $_strTableName);
        $_status_key  = get_status_from_table($_objCon, $_strTableName);

        // Found record primary key
        $_found_key = $rs->fields[$_primary_key];

        if ( $_deleteRecord )
        {
            // "delete" record by changing record status
            $_aryData[$_status_key] = 'd';
        }
        else
        {
            // change "delete" record status to 'a' since we are to UPDATE the record data
            $_aryData[$_status_key] = 'a';
        }

        // Only UPDATE if any of the fields have changed
        if ( $_objCon->GetUpdateSQL($rs, $_aryData, false, $_magic_quotes) )
        {
            // Since something is different, set the modified fields

            $_timeStamp =  date( 'Y-m-d H:i:s', time());
            // Add 'modified' date and by whom
            $_aryData['modified_by'] = $users['user_id'];     // some tables have these field names
            $_aryData['modified_on'] = $_timeStamp;

            $_aryData['last_modified_by'] = $session_user_id; // others use these
            $_aryData['last_modified_at'] = $_timeStamp;

            // Build new SQL
            $updateSQL = $_objCon->GetUpdateSQL($rs, $_aryData, false, $_magic_quotes);

            if ( $rs =& $_objCon->Execute($updateSQL) )
            {
                // Return Recordset
                if ( $_return_recordset )
                    $_retVal = $rs;

                // or return data array
                else
                {
                    $_retVal[$_primary_key] = $_found_key;

                    $_retVal['primarykey'] = $_primary_key;
                    $_retVal['statuskey']= $_status_key;
                }
            }
            else
            {
                db_error_handler($_objCon, $sql . ' - record_update [2]');
                $_retVal = false;
            }
        }

        // No data change
        else
        {
            // Return Recordset
            if ( $_return_recordset )
                $_retVal = $rs;

            // or return data array
            else
            {
                $_retVal[$_primary_key] = 0;
                $_retVal['primarykey'] = $_primary_key;
                $_retVal['statuskey']= $_status_key;
            }
        }
    }
    else
    {
        // Error message about not find a record to be updated
        $msg = 'Could not locate record to Update';

        $_retVal[$_primary_key] = 0;
        $_retVal['primarykey'] = $_primary_key;
        $_retVal['statuskey']= $_status_key;
    }

    // Send back what we have
    return $_retVal;
};

 /**
  * Generic "FIND" Record method for database
  *
  * This method is a bit different than the others,
  * it needs 3 things:
  *  1) name of the table to access
  *  2) unique "identifier(s)" to search table for
  *  3) conection object to process from
  *
  * The[se] "unique identifier[s]" can be any number of table fields,
  * but they must be in an array with the field name as the key
  * and the field value as the array element value. The method will
  * pull this information apart and construct a query to search the
  * desired table in order to locate the proper record.
  *
  * If a record is found, either an named array will be returned (default)
  * or an ADOdb recordset if the $_return_recordset parameter is set.
  *
  * If a data array is returned (default), 2 additional elements will be
  * added to the array:
  * 1) 'primarykey'  = table primary key
  * 2) 'statuskey'   = table status field
  *
  * If a record is not located, the orginal data will be returned with
  * 3 elements added to the array:
  * 1) '[primarykey]' = 0 - this indicated that a record was not found
  * 2) 'primarykey'  = table primary key
  * 3) 'statuskey'   = table status field
  *
  * @name __record_find()
  * @access private
  * @category db_handling
  *
  * @static
  *
  * @author Walter Torres <walter@torres.ws>
  *
  * @param  object   $_objCon            Connection object ot Database to hit
  * @param  string   $_strTableName      table name to UPDATE data into
  * @param  array    $_aryData           Record data Array, only needs $_identifier
  * @param  boolean  $_return_recordset  Indicating if adodb recordset object should be returned (defaults to false)
  * @param  boolean  $_search_condition  Multiple search fields should use an AND or OR, defaults to AND
  * @param  boolean  $_magic_quotes      F - inbound data has not "add slashes", T - data has "add slashes" (defaults to false)
  * @param  boolean  $_show_deleted      specifying if deleted companies should be included (defaults to false)
  *
  * @return boolean  $_retVal            Data array (or Recordset) on Success or boolean on failure
  */
function __record_find ( $_objCon, $_strTableName, $_aryData, $_search_condition = "AND", $_magic_quotes = false, $_return_recordset = false, $_show_deleted = false )
{
   /**
    * Default return value
    *
    * Returns record data/record set, or boolean upon failure
    * Default value is set at FALSE
    *
    * @var mixed $_retVal Indicates if record was found or not
    * @access private
    * @static
    */
    $_retVal = false;

    // This can only work if the data array haas elements within
    if ( count ($_aryData) )
    {
        // What is the primary key for this table
        $_primary_key = get_primarykey_from_table($_objCon, $_strTableName);
        $_status_key  = get_status_from_table($_objCon, $_strTableName);

    /**
        * Generated SELECT SQL from array
        *
        * @var string $_sql Generated SELECT SQL
        * @access private
        * @static
        */
        $_sql = "SELECT *
                FROM $_strTableName
                WHERE ";

        // If the primary key for this table was given, we can ignore
        // everything else
        if ( $_aryData[$_primary_key] )
        {
            // Only way to make sure "slashed quotes" are handled properly
            if ( $_magic_quotes )
                $_key_data = $_objCon->qstr($_aryData[$_primary_key], get_magic_quotes_gpc());
            else
                $_key_data = $_objCon->qstr($_aryData[$_primary_key]);

            $_sql .=  $_primary_key . ' = ' . $_key_data;
        }

        // Otherwise we need to look for this record the hard way ward
        else
        {
            // Force to upper case
            $_search_condition = strtoupper ( $_search_condition );

        // Make sure the '$_search_condition' parameter wasn't overwritten with
            // something we can't use
            if ( ( strcasecmp ( $_search_condition, 'AND' ) != 0 ) && ( strcasecmp ( $_search_condition, 'OR' ) != 0 ) )
                $_search_condition = 'AND';

        /**
            * Fields to search DB with
            *
            * Creates an array of table fields for SQL search of DB
            *
            * @var array $_retVal array of table fields
            * @access private
            * @static
            */
            $where_fields = array();

            foreach ($_aryData as $_field => $_value)
            {
                // Only way to make sure "slashed quotes" are handled properly
                if ( $_magic_quotes )
                    $_field_data = $_objCon->qstr($_value, get_magic_quotes_gpc());
                else
                    $_field_data = $_objCon->qstr($_value);

                $where_fields[] = "$_field LIKE " . $_field_data;
            }

            // Assmeble Query pieces
            $_sql .= implode ( " $_search_condition ", $where_fields );
        }

        // Decide if we need to filter out 'deleted' records
        if ( ( ! $_show_deleted ) && ( $_status_key ) )
            $_sql .= ' AND ' . $_status_key . ' = ' . $_objCon->qstr('a');

        // Find this record
        if ( $rs =& $_objCon->Execute($_sql) )
        {
            // Return Recordset
            if ( $_return_recordset )
                $_retVal = $rs;

            // or return data array
            else
            {
                // If anything was found, return it
                if ( $rs->RecordCount() > 0 )
                {
                    $_retVal = $rs->fields;
                }
                // Otherwise, send back what was given and set key to ZERO
                else
                {
                    $_retVal = $_aryData;
                    $_retVal[$_primary_key] = 0;
                }

                $_retVal['primarykey'] = $_primary_key;
                $_retVal['statuskey']= $_status_key;
            }
        }
        else
        {
            db_error_handler($_objCon, $sql . ' - record_find [1]');
            $_retVal = false;
        }
    }

    // Send back what we have
    return $_retVal;
};

 /**
  * Generic "DELETE" Record method for database
  *
  * This method is a bit different than the others.
  * Since we have to have the table index record ID in order
  * to properly mark the desired record as "deleted", this method
  * needs 4 things:
  *  1) name of the table to access
  *  2) unique "identifier(s)" to search table for
  *  3) name of table record index field
  *  4) conection object to process from
  *
  * The "unique identifier(s)" can be any number of table fields,
  * but they must be in an array with the field name as the key
  * and the field value as the array element value. The method will
  * pull this information apart and construct a query to search the
  * desired table in order to locate the proper record.
  * If a record is found, that record ID is then added to the "record data",
  * the "record_status" field maked "deleted" ('d') and then sent on to the
  * "__record_update()" method so the record can be modified
  *
  * @name __record_delete()
  * @access private
  * @category db_handling
  *
  * @uses __record_update   Just wraps this method
  * @static
  * @depricated
  *
  * @author Walter Torres <walter@torres.ws>
  *
  * @param  object $_objCon       Connection object ot Database to hit
  * @param  string $_strTableName table name to UPDATE data into
  * @param  string $_identifier   field name of table record index field (table record id)
  * @param  array  $_aryData      Record data Array, only needs $_identifier
  * @return boolean $_retVal      Success or failure
  */
function __record_delete ( $_objCon, $_strTableName, $_identifier, $_aryData )
{
    // Begin SQL construct
    $sql = "SELECT $_identifier
              FROM $_strTableName
                WHERE ";

    // Built pieces for SQL query
    foreach ( $_aryData AS $_field => $_value )
    {
        $_tmp_sql[] = $_field . ' = ' . $_objCon->qstr($_value, get_magic_quotes_gpc());
    }

    // Assmeble Query pieces
    $sql .= implode ( ' AND ', $_tmp_sql );

    // Find this record
    if ( $rs =& $_objCon->Execute($sql) )
    {
        // set record ID
        $_aryData[$_identifier] = $rs->fields[$_identifier];

        // This is simply an "update" with a specific field changed
        return __record_update ( $_objCon, $_strTableName, $_identifier, $_aryData, true );
    }
    else
    {
        db_error_handler($_objCon, $sql . ' - record_delete [1]');
        return false;
    }

};

/*****************************************************************************/

 /**
   * Upgrade function, intended to change a fieldname in a table from one fieldname to another
   * Database agnostic way to upgrade/change datastructures
**/
function rename_fieldname($con, $table_name, $old_field_name, $new_field_name, &$upgrade_msgs, $show_errors=false) {
    $dict = NewDataDictionary( $con );
    $table_list = list_db_tables($con);
    //ensure that table already exists
    if (in_array($table_name,$table_list)) {
        //get list of fields from table, to ensure that we have a field to change
        $cols=$dict->MetaColumns($table_name);
//        print_r($cols);
        if (array_key_exists(strtoupper($old_field_name), $cols)) {
            //definition of new field, needed by MYSQL
            $field_definition=array();
            $field_definition[]=array('NAME'=>$new_field_name,'TYPE'=>'C','SIZE'=>32);
            $sql=$dict->RenameColumnSQL($table_name, $old_field_name, $new_field_name,$field_definition);
            foreach ($sql AS $sql_line) {
                $rst=$con->execute($sql_line);
                if (!$rst) { if ($show_errors) {db_error_handler($con, $sql_line); } $error=true; }
            }
	    if (!$error) {
                $upgrade_msgs[]="Changed fieldname in $table_name from $old_field_name to $new_field_name<br>";
                return true;
	    } else return false;
        }
    }
    return false;
}


 /**
   * Upgrade function, intended to add a new table
   * Database agnostic way to add tables to the database
**/
function create_table($con, $table_name, $table_fields, $table_opts, &$upgrade_msgs) {
    $dict = NewDataDictionary( $con );
    $table_list = list_db_tables($con);
    //ensure that table is not already in existance
    if (!in_array($table_name,$table_list)) {
        //define details of the table in the fields array
        //no global table options needed, so setting to false

        $sql=$dict->CreateTableSQL( $table_name, $table_fields, $table_opts );
        foreach ($sql AS $sql_line) {
            $rst=$con->execute($sql_line);
            if (!$rst) db_error_handler($con, $sql_line);
        }
        $upgrade_msgs[]="Added new $table_name table<br>";
        return true;
    }
    return false;
}

 /**
   * Upgrade function, intended to add a field to a table
   * Database agnostic way to upgrade/change datastructures
**/
function add_field($con, $table_name, $field_definition, $table_opts='', &$upgrade_msgs) {
    $dict = NewDataDictionary( $con );
    $table_list = list_db_tables($con);
    $old_upgrade_count=count($upgrade_msgs);
    //ensure that table already exists
    if (in_array($table_name,$table_list)) {
        $cols=$dict->MetaColumns($table_name);
//        print_r($cols);
        foreach ($field_definition as $fielddata) {
            $field_name=$fielddata['NAME'];
            if (!array_key_exists($field_name, $cols)) {
                $fdef=array($fielddata);
                $sql=$dict->ChangeTableSQL($table_name, $fdef, $table_opts);
                foreach ($sql AS $sql_line) {
                    $rst=$con->execute($sql_line);
                    if (!$rst) db_error_handler($con, $sql_line);
                }
                $upgrade_msgs[]="Added field $field_name in $table_name<br>";
            }
        }
        $upgrade_count=count($upgrade_msgs);
        if ($upgrade_count>$old_upgrade_count) return true;
     }
    return false;
}

 /**
   * Upgrade function, intended to remove a table from the database
   * Database agnostic way to upgrade/change datastructures
**/
function drop_table($con, $table_name, &$upgrade_msgs) {
    $dict = NewDataDictionary( $con );
    $table_list = list_db_tables($con);
    //ensure that table is already in existance, cannot drop it without it existing
    if (in_array($table_name,$table_list)) {
        $sql=$dict->DropTableSQL( $table_name );
        foreach ($sql AS $sql_line) {
            $rst=$con->execute($sql_line);
            if (!$rst) db_error_handler($con, $sql_line);
        }
        $upgrade_msgs[]="Removed $table_name table<br>";
        return true;
    }
    return false;
}


// ============================================================================

/**
 * $Log: utils-database.php,v $
 * Revision 1.31  2006/04/28 02:44:17  vanmer
 * - added parameter in getUpdateSQL to fix bug where updates were forced where fields were not defined when magic_quotes is enabled, fix passes magic quotes in the right parameter order
 *
 * Revision 1.30  2006/01/11 21:49:21  vanmer
 * - changed to only display errors renaming a field if requested
 * - changed to only report success when no database errors occurred
 *
 * Revision 1.29  2006/01/10 16:05:01  braverock
 * - revert to NConnect instead of Connect/PConnect
 *
 * Revision 1.28  2006/01/10 15:03:16  braverock
 * - update to use $GLOBALS array for database connection to eliminate scope issue
 *
 * Revision 1.27  2005/12/22 23:01:26  jswalter
 *  - modified '__record_insert()' to return an actual recordset with record data
 *  - modified '__record_find()' to hand AND and OR searches
 *
 * Revision 1.26  2005/12/20 07:56:20  jswalter
 *  - added '$search_condtion' to '__record_find()' in order to do AND or OR searches
 * Bug 776
 *
 * Revision 1.25  2005/12/19 04:57:33  jswalter
 *  - extensive work on '__record_insert()', '__record_update()' and '__record_find()'
 *    to handle record sets, "magic quotes", etc. All thee methods now handle the
 *    same parameter list and return the same types of data; except '__record_find()',
 *    it returns a full data array (or recordset) as the others return the record
 *    id of the record INSERTed or UPDATEd and '__record_update()' which wants to
 *    know which field to use to locate the record to UPDATE.
 *
 * Revision 1.24  2005/12/18 02:55:04  vanmer
 * - moved functions for upgrading table structure from examples into functions in utils-database
 * - intended to be used when upgrading the database, instead of straight SQL calls
 *
 * Revision 1.23  2005/12/15 00:17:48  jswalter
 *  - better commonality in retrun options across "generic" DB access functions
 *
 * Revision 1.22  2005/12/10 20:09:08  vanmer
 * - removed parameters for getInsertSQL, to allow for strange characters
 *
 * Revision 1.21  2005/12/07 17:00:28  jswalter
 *  - removed left over debug code
 *
 * Revision 1.20  2005/12/07 00:18:13  jswalter
 *  - added new API methods to handle and process field names vs table names
 *  - added '__record_insert()' as a generic INSERT record method
 *  - added '__record_update()' as a generic UPDATE record method
 *  - added '__record_find()' as a generic FIND record method
 *
 * Revision 1.19  2005/12/03 00:25:37  vanmer
 * - added code to handle session data in the XRMS database
 *
 * Revision 1.18  2005/12/02 00:55:20  vanmer
 * - added more PHP doc to utils-database
 * - added XRMS_API package tag
 *
 * Revision 1.17  2005/08/05 21:33:56  vanmer
 * - added function to create string for company name search.  Queries system preferences and adds % to the string
 * according the preference
 *
 * Revision 1.16  2005/07/08 01:29:03  vanmer
 * - added new function to make a URL out of a table and id combination
 * - added new function to make a URL of a table combination to redirect to some.php
 *
 * Revision 1.15  2005/06/25 12:49:45  braverock
 * - fix variable typo in cleanup fn
 *
 * Revision 1.14  2005/06/24 22:39:34  vanmer
 * - added case for handing email templates in table_name function
 *
 * Revision 1.13  2005/06/24 20:02:01  braverock
 * - add shutdown function to kill any wayward database connection when the script is done
 *   not perfect, but better than leaving open connections
 *
 * Revision 1.12  2005/06/06 18:30:27  vanmer
 * - added better handling for automagic table functions for divisions, to allow relationships on divisions
 * to operate properly
 *
 * Revision 1.11  2005/05/06 00:43:41  vanmer
 * - added a new function to instantiate an xrms db connection
 *
 * Revision 1.10  2005/04/28 22:02:04  introspectshun
 * - Updated list_db_tables to use ADODB MetaTables fn
 *   - Inspired by eduqate's post regarding Postgres compat
 *
 * Revision 1.9  2005/01/25 05:59:59  vanmer
 * - altered to use current function instead of hardcoded element 0
 * - added function for executing a batch sql file
 *
 * Revision 1.8  2005/01/12 20:11:45  braverock
 * - add company_division to table_name fn
 *
 * Revision 1.7  2005/01/10 23:56:53  vanmer
 * - changed multiple ifs into a switch/case statement
 * - added files, cases, campaigns handling for determining which field in the database provides the name of the entity
 *
 * Revision 1.6  2004/07/14 21:09:16  neildogg
 * - Added activities to table_name
 *
 * Revision 1.5  2004/07/14 20:54:33  neildogg
 * - Added name for opportunities table
 *
 * Revision 1.4  2004/07/14 11:50:50  cpsource
 * - Added security feature IN_XRMS
 *
 * Revision 1.3  2004/07/09 15:36:34  neildogg
 * Returns array of values of usable names in a table
 *
 * Revision 1.2  2004/07/08 22:12:24  neildogg
 * - Converts all current database names (and most plural words) to singular form
 *
 * Revision 1.1  2004/07/01 12:43:26  braverock
 * - add utils-database.php file
 * - move list_db_tables and confirm_no_records fns to utils-database.php file
 *
 */
?>
