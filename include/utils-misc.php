<?php
/**
 * utils-misc.php - this file contains non-UI utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Chris Woofter
 * @author Brian Peterson
 *
 * @package XRMS_API
 * $Id: utils-misc.php,v 1.175 2006/07/12 01:03:22 vanmer Exp $
 */
require_once($include_directory.'classes/acl/acl_wrapper.php');
require_once($include_directory.'utils-preferences.php');
require_once($include_directory.'utils-database.php');
require_once($include_directory.'vars.php');

//establish the database connection variables as globals
$GLOBALS['xrms_db_server']   = $xrms_db_server;
$GLOBALS['xrms_db_username'] = $xrms_db_username;
$GLOBALS['xrms_db_password'] = $xrms_db_password;
$GLOBALS['xrms_db_dbname']   = $xrms_db_dbname;
$GLOBALS['xrms_db_dbtype']   = $xrms_db_dbtype;

if ( !defined('IN_XRMS') )
{
  die(_('Hacking attempt'));
  exit;
}

/**
 * strip any tags added to the url from PHP_SELF.
 *  This fixes hand crafted url XXS expoits for any
 *  page that uses PHP_SELF as the FORM action
 */
$_SERVER['PHP_SELF'] = strip_tags($_SERVER['PHP_SELF']);

/**
 * Verify a session has been started.  If it hasn't, start a session up.
 * php.net doesn't tell you that $_SESSION (even though autoglobal),
 * is not created unless a session is started, unlike $_POST, $_GET and such
 */
function session_startup () {
  global $include_directory;

  $sessid = session_id();
  if ( empty( $sessid ) ) {
    // only call session_start once
    //check for system preference to store sessions in the database
    if (!function_exists('get_xrms_dbconnection')) { include($include_directory.'utils-database.php'); }
    $con = get_xrms_dbconnection();
    $session_flag=get_admin_preference($con, 'session_storage_type');
    switch ($session_flag) {
      case 'adodb-session':
        //make sure we have connection information
        global $xrms_db_dbtype, $xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname;
        $table_list=$con->MetaTables();
        require_once($include_directory.'adodb/session/adodb-session.php');
        $table_name='adodb_sessions';
        ADODB_Session::driver($xrms_db_dbtype);
        ADODB_Session::host($xrms_db_server);
        ADODB_Session::user($xrms_db_username);
        ADODB_Session::password($xrms_db_password);
        ADODB_Session::database($xrms_db_dbname);
        ADODB_Session::table('adodb_sessions');
        ADODB_Session::open('xrms_sessions',$xrms_system_id);
        //make sure table exists
        if (!in_array($table_name,$table_list)) {
            //make sure adodb session table exists
            require_once($include_directory.'adodb/adodb-xmlschema.inc.php');
            $schemaFile=$include_directory.'adodb/session/adodb_sessions_schema.xml';
            adodb_session_create_table($schemaFile);
        }
        break;
        case 'db':
            session_set_save_handler("sessao_open", "sessao_close", "sessao_read", "sessao_write", "sessao_destroy", "sessao_gc");
        break;
    }
    $con->close();

    global $xrms_system_id;
    session_name($xrms_system_id);
    session_start();
  }
}

/**
 * First, create a valid session if not already done so.
 * Then, check to see if we are logged in,
 * and if not, we just go straight to login.php.
 *
 * Otherwise, we return the session id to our current caller.
 *
 * @param string $c_role - the user's role
 * @param string $action - optionally specify what action to check user permission for (defaults to Read)
 * @return integer user_id of the logged in user
 */
function session_check($c_role='', $action='Read', $check_user_permission=false) {

    global $http_site_root;
    global $xrms_system_id;
    global $on_what_table;
    global $on_what_id;
    global $xrms_default_language;
    global $xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname, $xrms_db_dbtype;

    // get our eventual target
    if (isset($_SERVER['REQUEST_URI'])) {
        $target = urlencode($_SERVER['REQUEST_URI']);
    } else {
        $target = urlencode($_SERVER['PHP_SELF']);
    }

    // make sure the session has started
    session_startup();

    $con=get_xrms_dbconnection();
    $user_language=get_user_preference($con, $_SESSION['session_user_id'], 'user_language');

    if ($user_language AND ($user_language!=$xrms_default_language)) {
        set_up_language($user_language, false, false, true);
    }

    // make sure we've logged in
    if ( isset($_SESSION['session_user_id']) && 0 == strcmp($_SESSION['xrms_system_id'], $xrms_system_id) ) {
      if ($check_user_permission) { $on_what_table='users'; $on_what_id=$_SESSION['session_user_id']; }
      if ($on_what_id)
         $role_ok = check_permission_bool($_SESSION['session_user_id'], false, $on_what_id, $action, $on_what_table, false, $con);
      else
         if ($on_what_table)
             $role_ok = check_object_permission_bool($_SESSION['session_user_id'], false, $action, $on_what_table, false, $con);
         else $role_ok=true;
    // make sure we have a role to do this
    if ( $c_role AND $c_role=='Admin' ) {
        $admin_object='Administration';
        $action='Update';
        $role_ok = check_object_permission_bool($_SESSION['session_user_id'],  $admin_object, $action, false, false, $con);
    }

      // we are logged in
      if ( !$role_ok ) {
        // we are logged in, go straight to logout.php
        header("Location: $http_site_root" . "/private/home.php?msg=noperm");
        exit;
      }
      // just return our current session id
      return $_SESSION['session_user_id'];
    }

    // we are not logged in, go straight to login.php
    header("Location: $http_site_root" . "/login.php?target=$target");
    exit;
}

/**
 * Add current item to the recent items list for this area of XRMS
 *
 * @param  handle  $con database connection
 * @param  integer $user_id
 * @param  string  $on_what_table where are we in the interface?
 * @param  integer $on_what_id what record are we viewing
 * @return void
 */
function update_recent_items($con, $user_id, $on_what_table, $on_what_id, $recent_action=false) {

    if(!$recent_action) {
        $recent_action = '';
    }

    $sql1 = "delete from recent_items where
             user_id = $user_id
             and on_what_table = " . $con->qstr($on_what_table, get_magic_quotes_gpc())  ."
             and recent_action = " . $con->qstr($recent_action, get_magic_quotes_gpc()) . "
             and on_what_id = $on_what_id";

    $con->execute($sql1);

    //save to database
    $rec = array();
    $rec['user_id'] = $user_id;
    $rec['on_what_table'] = $on_what_table;
    $rec['on_what_id'] = $on_what_id;
    $rec['recent_action'] = $recent_action;
    $rec['recent_item_timestamp'] = time();

    $tbl = 'recent_items';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $rst = $con->execute($ins);
    if (!$rst) {
        db_error_handler($con, $ins);
    }
}

/**
 * Get the current system audit level from the system parameters table
 *
 *   0 - no logging
 *   1 - inserts & updates
 *   2 - and login/logout
 *   3 - and views
 *   4 - and searches
 *
 * @return the audit level
 */
function current_audit_level(&$con) {
    $level = get_system_parameter($con, "Audit Level");
    return $level;  // 0 is the most restrictive level, 4 logs everything
}

/**
 * Add current item to the XRMS audit table
 *
 * @param  handle  $con database connection
 * @param  integer $user_id
 * @param  string  $audit_item_type
 * @param  string  $on_what_table   where are we in the interface?
 * @param  integer $on_what_id what record are we viewing
 * @param  integer $level optionally specifying what level of item is this (used to determine if item will be logged or placed in the db).  Defaults to 4.
 * @return void
 */
function add_audit_item(&$con, $user_id, $audit_item_type, $on_what_table, $on_what_id, $level=4) {
    $log_level = current_audit_level($con);
    if ($level <= $log_level) {
        //save to database
        $rec = array();
        $rec['user_id'] = $user_id;
        $rec['audit_item_type'] = $audit_item_type;
        $rec['on_what_table'] = $on_what_table;
        $rec['on_what_id'] = $on_what_id;
        if (isSet($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            $rec['remote_addr'] = $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isSet($_SERVER["HTTP_CLIENT_IP"])) {
            $rec['remote_addr'] = $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isSet($_SERVER["HTTP_PC_REMOTE_ADDR"])) {
            $rec['remote_addr'] = $_SERVER["HTTP_PC_REMOTE_ADDR"];
        } else {
            $rec['remote_addr'] = $_SERVER["REMOTE_ADDR"];
        }
        if (isset($_SERVER['REMOTE_PORT'])) {
            $rec['remote_port'] = $_SERVER['REMOTE_PORT'];
        }
        $rec['session_id'] = session_id();
        $rec['audit_item_timestamp'] = time();

        $tbl = 'audit_items';
        $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
        $con->execute($ins);
    }
}

/**
 * Fetch the company name from the database
 * just in case we need it at some point...
 *
 * @param  handle  $con adodb database connection handle
 * @param  integer $company_id
 * @return string  $company_name
 */
function fetch_company_name($con, $company_id) {

    $rst_company_name = $con->execute("select company_name from companies where company_id = $company_id");
    if ($rst_company_name) {
        $company_name = $rst_company_name->fields['company_name'];
        $rst_company_name->close();
    }

    return $company_name;
}


/**
 * Fetch the company for a division from the database
 *
 * @param  handle  $con adodb database connection handle
 * @param  integer $division_id
 * @return integer  $company_id
 */
function fetch_company_id_for_division($con, $division_id) {
    if (!$con OR !$division_id) return false;

    $sql = "SELECT company_id FROM company_division WHERE division_id=$division_id";
    $rst = $con->execute($sql);

    if (!$rst) { db_error_handler($con, $sql); }
    if (!$rst->EOF) {
        return $rst->fields['company_id'];
    } else return false;
}

/**
 * Return the filesize in human readable string
 * this nifty function came from someone up at php.net
 *
 * @param  integer $file_size
 * @return string  file size rounded and typed with size
 */
function pretty_filesize($file_size) {
    $a = array("B", "KB", "MB", "GB", "TB", "PB");
    $pos = 0;

    while ($file_size >= 1024) {
        $file_size /= 1024;
        $pos++;
    }
    return round($file_size,2)." ".$a[$pos];
}

/**
 * Get the contents of the CSV file as an array
 *
 * modified from a function on php.net ref:fgetcsv
 *
 * @param  string  $file           path to the uploaded file
 * @param  boolean $hasFieldNames  should we get the description list off the first line
 * @param  char    $delimiter      delimiter for the CSV file
 * @param  char    $enclosure      are the fields enclosed in anything (like quotes)
 *
 * @return array   $result         array in the form of rows with keys and values
 *
 * @todo to support MS Outlook import, revise the $keys array to rtrim,strtolower, and str_replace space with underscore
 */
function CSVtoArray($file, $hasFieldNames = false, $delimiter = ',', $enclosure='') {
    $result_arr = Array();

    if (!substr_count($file,"http://")) {
        //urls don't have a filesize, so, don't check it.
        $size = filesize($file) +1;
    }
    $handle = fopen($file, 'r');
    if (!$handle) {
        echo _("Unable to open file") . ": $file \n";
        echo _("Please correct this error.");
        exit;
    }

    if ($hasFieldNames) $keys = fgetcsv($handle, 4096, $delimiter);

    //trim,strtolower, and strreplace keys for Outlook support
    //create a temporary array to put things in
    $cleankeys = array();
    foreach ($keys as $key) {
        //munge the array key
        $key = str_replace(' ', '_', trim(strtolower($key)));
        $key =  preg_replace("/[-#\*.]/i",'', $key);
        //assign the munged key to the temp array
        $cleankeys[] = $key;
    }
    //copy the cleaned array to the $keys array
    $keys = $cleankeys;

    while ($row = fgetcsv($handle, 4096, $delimiter))
    {
        for($i = 0; $i < count($row); $i++)
        {
            if(array_key_exists($i, $keys))
            {
                $row[$keys[$i]] = $row[$i];
            }
        }
        $result_arr[] = $row;
    }

    fclose($handle);

    return $result_arr;

} //end CSVtoArray fn

/**
 * Search for the var $name in $_SESSION, $_POST, $_GET,
 * $_COOKIE, or $_SERVER and set it in provided var.
 *
 * example:
 *    getGlobalVar('username',$username);
 *  -- no quotes around last param!
 *
 * modified from Squirrelmail
 *
 * @param string $name variable to search for
 * @param @$value value found to pass back
 * @return boolean
 *
 * Returns FALSE if variable is not found.
 * Returns TRUE if it is.
 */

function getGlobalVar( &$value, $name ) {

    if( isset($_SESSION[$name]) ) {
        $value = $_SESSION[$name];
        return TRUE;
        break;
    }
    if( isset($_POST[$name]) ) {
        $value = $_POST[$name];
        return TRUE;
        break;
    }
    if ( isset($_GET[$name]) ) {
        $value = $_GET[$name];
        return TRUE;
        break;
    }
    if ( isset($_COOKIE[$name]) ) {
        $value = $_COOKIE[$name];
        return TRUE;
        break;
    }
    if ( isset($_SERVER[$name]) ) {
        $value = $_SERVER[$name];
        return TRUE;
        break;
    }
    return FALSE;
}

/**
 * function random_string
 *
 * Function to create random lowercase strings of a given length.
 *
 * @param integer $length  The length of the random string to generate
 * @return string $ret     The Random String generated.
 *
 */
function random_string ($length = 20)
 {
    $nps = '';
    for( $i = 0; $i < $length; $i++ )
    {
        $nps .= chr( mt_rand(97, 122) );
    }
    return $nps;
 }


/**
 * Add a variable to the session.
 *
 * modified from Squirrelmail
 *
 * @param mixed $var the variable to register
 * @param string $name the name to refer to this variable
 * @return void
 */
function xrms_session_register ($var, $name) {

  session_startup();

    $_SESSION["$name"] = $var;

  session_register("$name");
}

/**
 * Delete a variable from the session.
 *
 * modified from Squirrelmail
 *
 * @param string $name the name of the var to delete
 * @return void
 */
function xrms_session_unregister ($name) {

    session_startup();

    unset($_SESSION[$name]);

    session_unregister("$name");
}

/**
 * Checks to see if a variable has already been registered in the session.
 *
 * modified from Squirrelmail
 *
 * @param string $name the name of the var to check
 * @return bool whether the var has been registered
 */
function xrms_session_is_registered ($name) {
    $test_name = &$name;
    $result = false;

    if (isset($_SESSION[$test_name])) {
        $result = true;
    }

    return $result;
}

/**
 * Find company id from company name
 * to see if the company exists before adding it
 *
 * @param  handle  $con database connection
 * @param  string  $company_name to search for
 * @return integer $company_id found ID or 0 (ZERO) if no match
 */
function fetch_company_id($con, $company_name) {

    $sql_fetch_company_id = 'select company_id from companies where
                             company_name = ' . $con->qstr($company_name, get_magic_quotes_gpc());

    $rst_company_id = $con->execute($sql_fetch_company_id);

    if ($rst_company_id) {
        $company_id = $rst_company_id->fields['company_id'];
        $rst_company_id->close();
    } else {
        $company_id = 0;
    }

    return $company_id;
}

/**
 * Find the appropriate default address for an
 * existing company so that contacts have it set properly
 *
 * @param  handle  $con database connection
 * @param  integer $company_id to search for
 * @return integer $address_id found ID or 1 if no match
 */
function fetch_default_address($con, $company_id) {
    $sql_fetch_address_id = "select default_primary_address from companies where company_id = $company_id";
    if ($rst_address_id = $con->execute($sql_fetch_address_id)) {
       // no SQL error
       if($rst_address_id->NumRows()) {
          // record found in in table
          $address_id = $rst_address_id->fields['default_primary_address'];
          $rst_address_id->close();
      } else { $address_id = 1; }
    } else { db_error_handler ($con,$sql_fetch_address_id);} // SQL error
    return $address_id;
}

/**
 * Find division id from division name and company id
 * to see if the division exists before adding it
 * or appending the division id to a new contact
 *
 * @param  handle  $con database connection
 * @param  string  $division_name to search for
 * @param  integer $company_id to search for
 * @return integer $division_id found ID or 0 (ZERO) if no match
 */
function fetch_division_id($con, $division_name, $company_id) {
    $sql_fetch_division_id = "select division_id from company_division
                           where division_name = '". addslashes($division_name). "'
                           and company_id = $company_id";
    if ($rst_division_id = $con->execute($sql_fetch_division_id)) {
         // no SQL error
        if($rst_division_id->NumRows()) {
           // record found in in table
           $division_id = $rst_division_id->fields['division_id'];
           $rst_division_id->close();
        } else { $division_id = 0; }
    } else { db_error_handler ($con,$sql_fetch_division_id);} // SQL error
    return $division_id;
}

/**
 * Stop everything and display parameters selected by the programmer.
 * This is a development test routine only, and should not be used in production code.
 *
 * @param1-5 Variables that the programmer needs to see
 */
function show_test_values($param1 = '', $param2 = '', $param3 = '', $param4 = '', $param5 = '') {

    $session_username = $_SESSION['username'];
    $p1 = print_r($param1, true);
    $p2 = print_r($param2, true);
    $p3 = print_r($param3, true);
    $p4 = print_r($param4, true);
    $p5 = print_r($param5, true);

    $p1 = htmlentities ($p1, ENT_QUOTES );
    $p2 = htmlentities ($p2, ENT_QUOTES );
    $p3 = htmlentities ($p3, ENT_QUOTES );
    $p4 = htmlentities ($p4, ENT_QUOTES );
    $p5 = htmlentities ($p5, ENT_QUOTES );

    echo <<<EOQ
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
    <html>
    <head>
    <title>Test Results</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
    Username = $session_username<BR>
    Param1   = $p1<BR>
    Param2   = $p2<BR>
    Param3   = $p3<BR>
    Param4   = $p4<BR>
    Param5   = $p5<BR>
    </body>
    </html>
EOQ;
exit;
} // end show_test_values fn

/**
 * Retrieve a value from the system_parameters.
 * Now a wrapper to get_admin_preference from the user preferences code
 *
 * @param handle &$con handle to the database connection
 * @param string $param System Parameter to be retrieved
 */
function get_system_parameter(&$con, $param) {
    $ret=get_admin_preference($con, $param);
    return $ret;
} //end fn get_system_parameter

/**
 * function db_error_handler : display the error to the user
 *
 * @author Brian Peterson
 *
 * @param handle &$con handle to the database connection
 * @param string $sql SQL that was attempted
 * @param optional integer $colspan
 *
 * @example
 * <code><pre>
 *    $rst = $con->execute($sql);
 *
 *    if ($rst) {
 *        // do stuff to process result set
 *    } else {
 *        // no result set, something is wrong
 *        // call db_error_handler fn to get some useful data
 *        db_error_handler (&$con,$sql)
 *    }
 * </pre></code>
 *
 */
function db_error_handler (&$con,$sql,$colspan=20) {
        $error = $con->ErrorMsg();
				global $http_site_root;
        // figure out where to print this out.
        if ($error) {
            echo "\n<tr>\n\t<td class=widget_error colspan=$colspan>"
                 ."\t<br>"
                 ._("Unable to execute your query.").' '
                 ._("Please correct this error.")
                 ."<br>"
								 ."<a href=$http_site_root/admin/index.php>"
								 ._("You may need to update your database structure.")
								 .'</a>'
								 ."<br>"
                 . htmlspecialchars($error)
                 ."\t<br>"
                 ._("I tried to execute:")
                 ."<br>"
                 . htmlspecialchars ($sql)
                 ."\t</td>\n</tr>\n";
        }
} //end fn db_error_handler


/**
 * function get_country_id
 *
 * Takes an Country name or ISO code and returns the XRMS country id
 *
 * @author walter Torres
 *
 * @param object $con Database connection
 * @param mixed $country_info Country name, ISO code or named array with info; keys are table field names
 *
 * @return array $country entire found country record, or array of found country records
 */
function get_country($con, $country_info)
{
   /**
    *  Country data comes in 5 flavors:
    *   - country_name     Full [American English] spelling of Country Name
    *   - un_code          3 digit UN country code
    *   - iso_code1        no idea ;)
    *   - iso_code2        2 character ISO code for Country
    *   - iso_code3        3 character ISO code for Country
    */

   /**
    * Default return value
    *
    * Returns a found country record as a named array, or, if multiples are found, an array of arrays,
    * of FALSE for a failure of some kind.
    * Default value is set at FALSE
    *
    * @var mixed $_retVal found country record as array or false if failure occured
    * @access private
    * @static
    */
    $_retVal = false;

    // If inbound is an array, assume one of the key names matches a table field name
    if ( is_array($country_info) )
    {


    }
    // inbound string could be anything
    else
    {
        // Define fields to searches
        $extra_where['country_name'] = $country_info;
        $extra_where['un_code'] = strtoupper($country_info);
        $extra_where['iso_code1'] = $country_info;
        $extra_where['iso_code2'] = $country_info;
        $extra_where['iso_code3'] = $country_info;

        $_table_name = 'countries';

        // Determine if this contact already exists
        $_retVal = __record_find ( $con, $_table_name, $extra_where, 'OR', $_magic_quotes );
    }

    return $_retVal;
};


/**
 * function get_country_from_address
 *
 * Takes an address and returns the country id for the address, either from the database or the cache
 *
 * @author Aaron van Meerten
 *
 * @param object $con Database connection
 * @param int $address_id with address to find country_id for
 *
 * @return integer $country_id with country db identifier for which address resides
 */
function get_country_from_address($con, $address_id) {
    if (!$address_id OR !$con) return false;
    $func_name='get_country_from_address';
    $params=array($address_id);
    if (function_cache_bool($func_name, $params)) {
        return function_cache_get($func_name, $params);
    }
    $sql = "select country_id FROM addresses where address_id=$address_id";
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql);  return false; }
    if (!$rst->EOF) {
        $country_id=$rst->fields['country_id'];
        function_cache_set($func_name, $params, $country_id, false);
        return $country_id;
    }
    return false;
}

/**
 * function get_phone_format_from_country
 *
 * takes a country id and returns the phone format, either from the database or the cache
 *
 * @author Aaron van Meerten
 *
 * @param object $con Database connection
 * @param int $country_id with database id of country to load format for
 *
 * @return string $phone_format to use for formatting phone number
 */
 function get_phone_format_from_country($con, $country_id) {
    if (!$country_id OR !$con) return false;
    $func_name='get_phone_format_from_country';
    $params=array($country_id);
    if (function_cache_bool($func_name, $params)) {
//        echo "<pre>"; print_r($_SESSION); echo "</pre>";
        return function_cache_get($func_name, $params);
    }
    $sql = "select countries.phone_format FROM countries WHERE country_id=$country_id";
    $rst=$con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql);  return false; }
    if (!$rst->EOF) {
        $phone_format=$rst->fields['phone_format'];
        function_cache_set($func_name, $params, $phone_format, false);
        return $phone_format;
    }
    return false;
}
/**
 * function get_formatted_phone : get the phone number and format it based on the country
 *
 * A hook exists here that can override the phone formatting, for any desired reason.
 * The plugin will take the raw and formatted phone number as parameters
 *
 * @author Neil Roberts
 *
 * @param object $con Database connection
 * @param int $address_id Address ID tied to the account tied to the number
 * @param int $phone Phone number to be formatted
 *
 * @return string $phone_to_display
 */
function get_formatted_phone ($con, $address_id, $phone, $country_id=false) {
    global $company_id;
    global $contact_id;
    global $default_country_id;

    if (!$country_id) {
        $country_id=get_country_from_address($con, $address_id);
    }
    if (!$country_id) {
    $country_id = $default_country_id;
    }

    $expression=get_phone_format_from_country($con, $country_id);

    $phone_to_display = $phone;
    $pos = 0;
    $number_length = 0;

    $phone = preg_replace("|[^0-9]+|", "", $phone);

    if(trim($expression)) {
        preg_match_all("|[#]+|", $expression, $matched);
        $matched = $matched[0];
        foreach($matched as $match) {
            $number_length += strlen($match);
        }
        if(strlen($phone) > $number_length) {
            $extra = substr($phone, $number_length);
            $phone = substr($phone, 0, $number_length);
        }
        if(strlen($phone) == $number_length) {
            foreach($matched as $match) {
                $expression = substr_replace($expression, substr($phone, $pos, strlen($match)), strpos($expression, $match), strlen($match));
                $pos += strlen($match);
            }
            $phone_to_display = $expression;
        }
    }
    $phone_array['phone']=$phone;
    $phone_array['phone_to_display']=$phone_to_display;
    $temp_phone = do_hook_function("data_format_phone", $phone_array);
    if($temp_phone) {
        $phone_to_display = $temp_phone;
    }
    if(isset($extra) && $extra) {
        $phone_to_display .= " x" . $extra;
    }

    // don't return an empty link:
    if ($phone_to_display) {

        // for the CTI/dial plugin:
        global $use_dial_link;
        if ($use_dial_link == "y") {
            // this is defined in plugins/cti/setup.php:
            return phone_link_to_display($phone, $phone_to_display);
        } else {
            return $phone_to_display;
        }
    }
}

/**
 *
 * Strips many fields in array of phone field formatting strings, taking an array by reference and modifying its contents
 *
 * @param array $array to modify and clean the phone fields in (by references)
 * @param array $array of array_keys in the first array which correspond to phone fields
 *
 * @return integer $count of fields where phone fields were cleaned
 */

function clean_phone_fields(&$fields, $phone_fields) {
    $count=0;
    foreach ($phone_fields as $pf) {
        if (array_key_exists($pf, $fields)) {
            $fields[$pf]=preg_replace("/[^\d]/", '', $fields[$pf]);
            $count++;
        }
    }
    return $count;
}

/**********************************************************************/

/**
 * This function returns an HTML select widget with the addresses available for a company
 *
 * @param handle $con handle to the database connection
 * @param int $company_id specify company to retrieve addresses for company
 * @param int $address_id with address currently selected
 * @param string $fieldname with HTML form fieldname, defaults to 'address_id'
 * @param string $extra_html_elements optionally providing extra attributes to the HTML select
 * @param boolean $show_blank_first indicating with a blank element should be provided, defaults to true
 * @return string $html_ret with HTML for select box with addresses for company
**/
function get_company_address_select($con, $company_id, $address_id=false, $fieldname='address_id', $extra_html_elements='', $show_blank_first=true) {
    if (!$company_id) return false;
    $ret=array();
    $addresses=get_company_addresses($con, $company_id);
    if (!$addresses) return false;

    foreach ($addresses as $c_address_id) {
        $ret[$c_address_id]= get_formatted_address ($con,$c_address_id, $company_id, true);
    }
    $html_ret=create_select_from_array($ret, $fieldname, $address_id, $extra_html_elements, $show_blank_first);
    return $html_ret;
}

/**
 * function get_formatted_address : get the address and format it
 *
 * @param handle $con handle to the database connection
 * @param int $company_id specify company to retrieve addresses for company
 * @return array $addresses with address_id of each address associated with company
**/
function get_company_addresses($con, $company_id=false) {
    if (!$company_id) return false;
    $company_addresses=array();

    $sql = "SELECT address_id FROM addresses WHERE company_id=$company_id AND address_record_status='a'";
    $rst = $con->execute($sql);
    if (!$rst) { db_error_handler($con, $sql); return false; }
    while (!$rst->EOF) {
        $company_addresses[]=$rst->fields['address_id'];
        $rst->movenext();
    }
    if (count($company_addresses)>0) return $company_addresses;
    else return false;
}

/**
 * function get_formatted_address : get the address and format it
 *
 * @author Beth Macknik
 *
 * @param handle &$con handle to the database connection
 * @param int $address_id id of the address to be retrieved (optional, use either address_id or company_id)
 * @param int $company_id optionally specify company to retrieve primary address for company
 * @param bool $single_line conditional to indicate if output should be restricted to one line (false by default)
 *
 * @return string $address_to_display
 * @todo turn mapquest/whereis links into plugin hook
 */
function get_formatted_address (&$con,$address_id=false, $company_id=false, $single_line=false, $show_country=true, $strip_tags=false) {
    if (!$address_id) {
        if (!$company_id) return false;
        $address_id=fetch_default_address($con, $company_id);
    }
    if (!$address_id) return false;
    $sql = "select a.address_body, a.line1, a.line2, a.city, a.province, a.postal_code, a.use_pretty_address, ";
    $sql .= 'afs.address_format_string, c.country_name, c.iso_code2 ';
    $sql .= 'from addresses a, address_format_strings afs, countries c ';
    $sql .= "where a.address_id=$address_id ";
    $sql .= 'and a.country_id=c.country_id ';
    $sql .= 'and c.address_format_string_id=afs.address_format_string_id';
    $rst = $con->execute($sql);

    if ($rst) {
        $address_body = $rst->fields['address_body'];
        $GLOBALS["line1"] = $line1 = $rst->fields['line1'];
        $GLOBALS["line2"] = $line2 = $rst->fields['line2'];
        $GLOBALS["city"] = $city = $rst->fields['city'];
        $GLOBALS["province"] = $province = $rst->fields['province'];
        $GLOBALS["postal_code"] = $postal_code = $rst->fields['postal_code'];
        $use_pretty_address = $rst->fields['use_pretty_address'];
        $address_format_string = $rst->fields['address_format_string'];
        if ($show_country) {
            $GLOBALS["country_name"] = $country = $rst->fields['country_name'];
        }
        $GLOBALS["iso_code2"] = $iso_code2 = $rst->fields['iso_code2'];

        if (!$single_line) {
            //multiline address processing
            if ($use_pretty_address == 't') {
                $address_to_display = nl2br($address_body);
            } else {
                $lines = (strlen($line2) > 0) ? "$line1<br>$line2" : $line1;
                eval("\$address_to_display = \"$address_format_string\";");
                // eval ("\$str = \"$str\";");
                // Remove lines that contain only a comma (fixes SF Bug #1028807)
                $address_to_display = preg_replace("/<br>, +<br>/", "<br>", $address_to_display);
                // Remove blank lines (double <br>s)
                $address_to_display = preg_replace("/<br>(\s+)<br>/", "<br>", $address_to_display);
            }
            //end multiline address processing
        } else {
            //create our single line address
            $address_array=array();
            if ($line1) { $address_array[]=$line1; }

            if ($city) { $address_array[]=$city; }

            if ($province) { $address_array[]=$province; }

            if ($country_name) { $address_array[]=$country; }

           if (count($address_array)>0) {
                $address_to_display=implode(", ",$address_array);
           } else {
                $address_to_display = '';
           }
           //end single line address processing
        }
    } else {
        // database error, return some useful information.
        ob_start();
        db_error_handler ($con,$sql);
        $address_to_display = ob_get_contents();
        ob_end_clean();
    }

    // do not return an empty link
    if ($address_to_display) {

        // added for mapquest plugin
        global $use_mapquest_link;
        // added for whereis plugin
        global $use_whereis_link;
        if ($use_mapquest_link == "y") {
            // this is defined in plugins/mapquest/setup.php:
            return mapquest($line1, $city, $province, $iso_code2, $address_to_display);
        } elseif ($use_whereis_link == "y"){
            // this is defined in plugins/whereis/setup.php:
            return whereis($line1, $city, $province, $iso_code2, $address_to_display);
        } else {
            return $address_to_display;
        }
    }
} //end fn get_formatted_address


/**
 * Time zone offset
 *
 * This returns the GMT offset for a number it can be sure of
 * Otherwise, it calls a hook to find the time
 * Otherwise, it does nothing
 *
 * @author Neil Roberts
 *
 * @param object $con Database Connection
 * @param int $address_id ID of address
 *
 * @return array $array("daylight_savings_id" => int, "offset", float)
 * @return boolean False if no records
 */

function time_zone_offset($con, $address_id) {
    global $only_confirmed_time_zones;
    $sql = "SELECT country_id, province, city, postal_code
            FROM addresses
            WHERE address_id=" . $address_id;
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif(!$rst->EOF) {
        $country_id = $rst->fields['country_id'];
        $province = $rst->fields['province'];
        $city = $rst->fields['city'];
        $postal_code = $rst->fields['postal_code'];

        //This can be applied to other countries that always will have provinces
        if($country_id == 218) {
            $sql = "SELECT daylight_savings_id, gmt_offset, confirmed,
                            (CASE WHEN (province = '" . $province . "') THEN 0 ELSE 1 END) AS has_province,
                            (CASE WHEN (city = " . $con->qstr($city) . ") THEN 0 ELSE 1 END) AS has_city,
                            (CASE WHEN (postal_code='" . $postal_code . "') THEN 0 ELSE 1 END) AS has_postal_code
                        FROM time_zones
                        WHERE country_id=" . $country_id . "
                        AND province = '" . $province . "'";
        }
        else {
            $sql = "SELECT daylight_savings_id, gmt_offset, confirmed,
                            (CASE WHEN (province = '" . $province . "') THEN 0 ELSE 1 END) AS has_province,
                            (CASE WHEN (city = " . $con->qstr($city) . ") THEN 0 ELSE 1 END) AS has_city,
                            (CASE WHEN (postal_code='" . $postal_code . "') THEN 0 ELSE 1 END) AS has_postal_code
                        FROM time_zones
                        WHERE country_id=" . $country_id;
        }
        if($only_confirmed_time_zones == 'y') {
            $sql .= " AND confirmed = 'y' ";
        }
        $sql .= " ORDER BY has_province, has_city, has_postal_code";
        $rst = $con->SelectLimit($sql, 1);
        if(!$rst) {
            db_error_handler($con, $sql);
        }
        elseif(!$rst->EOF) {
            $confirmed = $rst->fields['confirmed'];
            $daylight_savings_id = $rst->fields['daylight_savings_id'];
            $offset = $rst->fields['gmt_offset'];

            return array("daylight_savings_id" => $daylight_savings_id, "offset" => $offset);
        }
        else {
            return false;
        }
    }
    else {
        return false;
    }
}

/**
 * Calculate time zone offset
 *
 * Returns timestamp based on daylight savings ID and offset
 * You must use gmdate() rather than date() on this tiem
 *
 * @author Neil Roberts
 *
 * @param object $con Database Connection
 * @param int $daylight_savings_id ID of daylight savings
 * @param float offset Amount of offset in hours
 *
 * @return timestamp Time
 */

 function calculate_time_zone_time($con, $daylight_savings_id, $offset) {
    if(!empty($daylight_savings_id)) {
        update_daylight_savings($con);
        $sql = "SELECT current_hour_shift
                FROM time_daylight_savings
                WHERE daylight_savings_id=" . $daylight_savings_id;
        $rst = $con->execute($sql);
    }

    if(!$rst) {
        db_error_handler($con, $sql);
    }
    elseif(!$rst->EOF) {
        return time() + ($offset*3600) + ($rst->fields['current_hour_shift']*3600);
    }
    elseif($offset) {
        return time() + ($offset*3600);
    }
    else {
        return false;
    }
 }

 /**
  * Update daylight savings
  *
  * Must be run before a search (but only once a day)
  * in order for a comparison
  * to be done in the search pages.
  *
  * @author Neil Roberts
  *
  * @param object $con Database Connection
  *
  */

function update_daylight_savings($con) {
    $sql = "SELECT *
            FROM time_daylight_savings
            WHERE last_update < " . $con->DBTimeStamp(time());
    $rst = $con->execute($sql);
    if(!$rst) {
        db_error_handler($con, $sql);
    }
    else {
        while(!$rst->EOF) {
            $daylight_savings_id = $rst->fields['daylight_savings_id'];
            $start_position = $rst->fields['start_position'];
            $start_day = $rst->fields['start_day'];
            $start_month = $rst->fields['start_month'];
            $end_position = $rst->fields['end_position'];
            $end_day = $rst->fields['end_day'];
            $end_month = $rst->fields['end_month'];
            $hour_shift = $rst->fields['hour_shift'];

            if(!$start_month) {
                $current_hour_shift = $hour_shift;
            }
            else {
                if($start_position == "last") {
                    // This should always work because there are no daylight months in Dec
                    ++$start_month;
                }
                $start_timestamp = strtotime("$start_position $start_day", strtotime(date("Y-$start_month-1", time())));
                if($end_position == "last") {
                    ++$end_month;
                }
                $end_timestamp = strtotime("$end_position $end_day", strtotime(date("Y-$end_month-1", time())));

                if($start_month < $end_month) {
                    if(($start_timestamp <= time()) and ($end_timestamp > time())) {
                        $current_hour_shift = $hour_shift;
                    }
                    else {
                        $current_hour_shift = 0;
                    }
                }
                else {
                    if((time() <= $start_timestamp) or (time() >= $end_timestamp)) {
                        $current_hour_shift = $hour_shift;
                    }
                    else {
                        $current_hour_shift = 0;
                    }
                }
            }

            $dst_sql = "SELECT * FROM time_daylight_savings WHERE daylight_savings_id = " . $daylight_savings_id;
            $dst_rst = $con->execute($dst_sql);

            $rec = array();
            $rec['current_hour_shift'] = $current_hour_shift;
            $rec['last_update'] = $con->DBTimeStamp(time());

            $upd = $con->GetUpdateSQL($dst_rst, $rec, false, get_magic_quotes_gpc());
            if (strlen($upd) > 0) {
                $upd_rst = $con->execute($upd);
                if (!$upd_rst) {
                    db_error_handler($con, $upd);
                }
            }

            $rst->movenext();
        }
    }
}

/**
 * Get the current page including any GET variables, relative to the http_site_root
 *
 * This function can be called and used to set return_url, with $return_url=current_page(); where a page uses:
 * Header("Location: $http_site_root.$return_url");
 *
 * @param string $vars with string of variables to add to the URL (or replace if already exist in current URL), parseable by parse_str
 * @param string $anchor providing the anchor to append to the current URL (overriding current anchor, if exists)
 * @return string $page with current URL relative to http_site_root, including parameters and anchor
 * @author Neil Roberts
 * @author Aaron van Meerten
 */
function current_page($vars = false, $anchor = false) {
    global $http_site_root;
    //start with blank page
    $page = '';

    //get array of subdirectories for base site URL
    $site_directories = explode('/', $http_site_root);

    //Get the REQUEST_URI or QUERY_STRING from the server (works differently for apache vs. IIS)
    if(!trim($_SERVER['REQUEST_URI'])) {
        if (array_key_exists('argv',$_SERVER)) {
            $request_uri = substr($_SERVER['argv'][0], strpos($_SERVER['argv'][0], ';') + 1);
    } else {
        $request_uri=$_SERVER['URL'] . '?'.$_SERVER['QUERY_STRING'];
    }
    } else {
        $request_uri = $_SERVER['REQUEST_URI'];
    }

    //Split up request into URL and CGI parameters
    $parts = explode('?', $request_uri, 2);

    //split directories for current URL
    $directories = explode('/', $parts[0]);
    //loop on each directory in current URL
    foreach($directories as $directory) {
        //if directory is not not in the base site root directory list, add it to the page
        //(so if base is /xrms then xrms/activities/one.php will split into xrms and activities, xrms will be ignored, activities will be added)
        if(!in_array($directory, $site_directories) and $directory) {
            $page .= '/' . $directory;
        }
    }
    //check to see that we have any CGI parameters at all in the current URL
    if(count($parts) > 1) {
        //make sure passed in CGI parameters get split into array
        parse_str($vars, $vars);

        //if there is no anchor specified, split CGI parameters into parameters and anchor
        if(!$anchor) {
            list($parts[1], $anchor) = split('#', $parts[1], 2);
        }

        //split the CGI parameters (without anchor tag) into array called parts
        parse_str($parts[1], $parts);

        //Loop on passed in parameters, if they are already set in the current page then remove them from the current page array (will be overrided by passed in parameter)
        foreach($vars as $key => $value) {
            if(in_array($key, array_keys($parts))) {
                unset($parts[$key]);
            }
        }

        //merge previous parameters (parts) with passed in parameters (vars)
        $parts = array_merge($parts, $vars);

        //Add CGI parameter separator to page
        $page .= '?';
        $pagevars=array();

        //loop on  all CGI parameters, turn them into an array of key=value pairs
        foreach ($parts as $key => $value) {
            $pagevars[] = $key . '=' . $value;
        }
        //append all key=value pairs into URL seperated by & seperator
        $page .= implode('&',$pagevars);
    }
    else {
        //No existing CGI parameters exist for current page, if we have passed in parameters simply append them to the URL
        if($vars) {
            $page .= '?' . $vars;
        }
    }

    //If an anchor has been set, append it to the URL with the anchor separator
    if($anchor) {
        $page .= '#' . $anchor;
    }

    //return URL (relative to http_site_root)
    return $page;
}

/**
 * The arr_vars sub-system
 *
 * This is an attempt to simplify processing of passing variables in and
 * out of modules.
 *
 * THE PROBLEM
 * -----------
 *
 * Typically, you have:
 *
 * if ($clear) {
 *   $sort_column = '';
 *   ...
 *  } elseif ($use_post_vars) {
 *   $sort_column = $_POST['sort_column'];
 *   ...
 *  } else {
 *    $sort_column = $_SESSION['companies_sort_column'];
 *    ...
 *  }
 *
 * The trouble with this construction is that it's error prone -
 * you have to make sure all variables are listed multiple times.
 *
 * THE SOLUTION
 * ------------
 *
 * Instead, just build an array as follows:
 *
 * $ary = array ( 'sort_column' => array ('companies_sort_column', behavior),
 *                ...
 *              )
 *
 * and then call arr_vars_get_all ( $ary );
 *
 * The index into the array is the local and post variable name,
 * and the value of the index is the name used in the session.
 *
 * Later, when you want to set all session variables,
 * just call arr_vars_session_set ( $ary );
 *
 */

// determine BEHAVIOR for getting data when !$clear && !$use_post_vars
define ( "arr_vars_SESSION"           , 0 );  // just try a SESSION
define ( "arr_vars_GET"               , 1 );  // just try a GET
define ( "arr_vars_GET_SESSION"       , 2 );  // try a GET first, and then if it fails, do a SESSION
define ( "arr_vars_GET_STRLEN_SESSION", 3 );  // try a GET first with length > 0, and then if it fails, do a SESSION
define ( "arr_vars_REQUEST"           , 4 );  // just try a POST/GET
define ( "arr_vars_REQUEST_SESSION"   , 5 );  // just try a POST/GET

// for function arr_vars_post_with_cmd ( $ary )
define ( "arr_vars_POST"              , 6 );  // just try a POST
define ( "arr_vars_POST_UNDEF"        , 7 );  // try a POST then set to null if undef

//
// get all variables
//
// Description: Get variables passed into a php script
//
// Do so as follows:
//
// 1) if the script is started with http://somehost/xrms/some-script.php?clear=1
//    then all variables in $ary[0..n-1] are set to '' and then
//    arr_vars_get_all will return.
//
// 2) if $post_vars is false and $_POST['usr_post_vars'] is set to 1
//    then all variables in $ary[0..n-1] are set to $_POST[$ary[0..n-1]]. If
//    the variable hasn't been POST'ed and you have full error logging on
//    an unused variable error will be generated. Then,
//    arr_vars_get_all will return.
//
// 3) if $post_vars is true
//    then all variables in $ary[0..n-1] are set to $_POST[$ary[0..n-1]]. If
//    the variable hasn't been POST'ed, a '' will be used instead. Then,
//    arr_vars_get_all will return.
//
// 4) Otherwise, the subroutine will attempt to retrieve $ary[0..n-1] as
//    indicated by the BEHAVIOR stored in $ary[0..n-1][1]. Refer to the
//    BEHAVIOR flags just above.
//
//       arr_vars_SESSION
//         the datasource is $_SESSION[$ary[0..n-1][0]] and if that isn't defined, use ''
//
//       arr_vars_GET
//         the datasource is $_GET[$ary[0..n-1][0]] and if that isn't defined, use ''
//
//       arr_vars_GET_SESSION
//         the datasource is $_GET[$ary[0..n-1][0]] and if that isn't defined, treat as
//         arr_vars_SESSION.
//
//       arr_vars_GET_STRLEN_SESSION
//         the datasource is $_GET[$ary[0..n-1][0]] and if the length of the result is 0 or undefined, treat as
//         arr_vars_SESSION.
//

function arr_vars_get_all ( $ary, $post_vars = false )
{
  global $clear;
  global $use_post_vars;
  global $msg;
  global $resort;
  global $offset;

  $msg    = isset($_GET['msg'])     ? $_GET['msg']     : '';
  $resort = isset($_POST['resort']) ? $_POST['resort'] : '';
  $offset = isset($_POST['offset']) ? $_POST['offset'] : '';

  if ( isset($_GET['clear']) ) {
    $clear = ($_GET['clear'] == 1) ? 1 : 0;
  } else {
    $clear = 0;
  }
  if ( $post_vars ) {
    $use_post_vars = 1;
  } else {
    if ( isset($_POST['use_post_vars']) ) {
      $use_post_vars = ($_POST['use_post_vars'] == 1) ? 1 : 0;
    } else {
      $use_post_vars = 0;
    }
  }

  if ( $clear ) {
    arr_vars_clear ( $ary );
  } elseif ( $use_post_vars ) {
    arr_vars_post_get ( $ary, $post_vars );
  } else {
    arr_vars_session_get ( $ary );
  }
}

// clear all variables
function arr_vars_clear ( $ary )
{
  foreach ($ary as $key => $value) {
    $GLOBALS[$key] = '';
  }
}

// get variables from session
function arr_vars_session_get ( $ary )
{
  foreach ($ary as $key => $value) {

    $flag = $value[1];
    switch ( $flag )
      {
      case arr_vars_SESSION:      // just try a SESSION
        $GLOBALS[$key] = isset($_SESSION["$value[0]"]) ? $_SESSION["$value[0]"] : '';
        break;

      case arr_vars_GET:         // just try a GET
        $GLOBALS[$key] = isset($_GET["$value[0]"]) ? $_GET["$value[0]"] : '';
        break;


      case arr_vars_GET_SESSION:  // try a GET first, and then if it fails, do a SESSION
        $GLOBALS[$key] = isset($_GET["$value[0]"]) ? $_GET["$value[0]"] : (isset($_SESSION["$value[0]"]) ? $_SESSION["$value[0]"] : '');
        break;

      case arr_vars_REQUEST:         // just try a REQUEST
        $GLOBALS[$key] = isset($_REQUEST["$value[0]"]) ? $_REQUEST["$value[0]"] : '';
        break;

      case arr_vars_REQUEST_SESSION:  // try a GET first, and then if it fails, do a SESSION
        $GLOBALS[$key] = isset($_REQUEST["$value[0]"]) ? $_REQUEST["$value[0]"] : (isset($_SESSION["$value[0]"]) ? $_SESSION["$value[0]"] : '');
        break;

      case arr_vars_GET_STRLEN_SESSION:  // try a GET first with length > 0, and then if it fails, do a SESSION
        if ( isset($_GET["$value[0]"]) ) {
          $tmp = isset($_GET["$value[0]"]);
          if ( strlen($tmp) > 0 ) {
            $GLOBALS[$key] = $tmp;
            break;
          }
        }
        $GLOBALS[$key] = isset($_SESSION["$value[0]"]) ? $_SESSION["$value[0]"] : '';
        break;

      default:
        echo "utils-misc.php::arr_vars_session_get: unknown flag = $flag<br>";
        exit;
      }
  }
}

// set all session variables
function arr_vars_session_set ( $ary )
{
  foreach ($ary as $key => $value) {
    $_SESSION["$value[0]"] = $GLOBALS[$key];
  }
}

// get all posted variables
function arr_vars_post_get ( $ary, $allow_none = false )
{
  foreach ($ary as $key => $value) {
    if ( $allow_none ) {
      $GLOBALS[$key] = isset($_POST["$key"]) ? $_POST["$key"] : '';
    } else {
      $GLOBALS[$key] = $_POST["$key"];
    }
  }
}

// get all posted variables with cmd
//
// accept an array of the form:
//
//    array ( 'variable-name' => BEHAVIOR, ... )
//
function arr_vars_post_with_cmd ( $ary )
{
  foreach ($ary as $key => $value) {
    $flag = $value;
    switch ( $flag )
      {
      case arr_vars_POST:
        $GLOBALS[$key] = $_POST["$key"];
        break;

      case arr_vars_POST_UNDEF:
        $GLOBALS[$key] = isset($_POST["$key"]) ? $_POST["$key"] : '';
        break;

      default:
        echo "utils-misc.php::arr_vars_post_get_with_cmd: unknown flag = $flag<br>";
        exit;
      }
  }
}

function arr_vars_get_get ( $ary, $allow_none = false )
{
    foreach ($ary as $key => $value) {
        if ( $allow_none ) {
            $GLOBALS[$key] = isset($_GET["$key"]) ? $_GET["$key"] : '';
        } else {
            $GLOBALS[$key] = $_GET["$key"];
        }
    }
}

function arr_vars_get_request ( $ary, $allow_none = false )
{
    foreach ($ary as $key => $value) {
        if ( $allow_none ) {
            $GLOBALS[$key] = isset($_REQUEST["$key"]) ? $_REQUEST["$key"] : '';
        } else {
            $GLOBALS[$key] = $_REQUEST["$key"];
        }
    }
}

// show program variables
function arr_vars_show_pgm_vars ( $ary )
{
  echo 'arr_vars_show_pgm_vars<br>';

  foreach ($ary as $key => $value) {
    echo '$' . $key . ' = ' . $GLOBALS[$key] . '<br>';
  }
}

// show session variables
function arr_vars_show_ses_vars ( $ary )
{
  echo 'arr_vars_show_ses_vars<br>';

  foreach ($ary as $key => $value) {
    echo '$' . $value[0] . ' = ' . $_SESSION["$value[0]"] . '<br>';
  }
}

/**
 * function SendDownloadHeaders - send file to the browser
 *
 * Original Source: SM core src/download.php
 * moved here to make it available to other code, and separate
 * front end from back end functionality.
 *
 * @param string $type0 first half of mime type
 * @param string $type1 second half of mime type
 * @param string $filename filename to tell the browser for downloaded file
 * @param boolean $force whether to force the download dialog to pop
 * @param optional integer $filesize send the Content-Header and length to the browser
 * @return void
 */
 function SendDownloadHeaders($type0, $type1, $filename, $force, $filesize=0) {
     global $languages, $xrms__language;
     $isIE = $isIE6 = 0;

     $get= 'HTTP_USER_AGENT';
     $HTTP_USER_AGENT = '';
     getGlobalVar($HTTP_USER_AGENT, $get);

     if (strstr($HTTP_USER_AGENT, 'compatible; MSIE ') !== false &&
         strstr($HTTP_USER_AGENT, 'Opera') === false) {
         $isIE = 1;
     }

     if (strstr($HTTP_USER_AGENT, 'compatible; MSIE 6') !== false &&
         strstr($HTTP_USER_AGENT, 'Opera') === false) {
         $isIE6 = 1;
     }

     if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
         function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
         $filename =
         $languages[$squirrelmail_language]['XTRA_CODE']('downloadfilename', $filename, $HTTP_USER_AGENT);
     } else {
         $filename = ereg_replace('[\\/:\*\?"<>\|;]', '_', str_replace('&nbsp;', ' ', $filename));
     }

     // A Pox on Microsoft and it's Internet Explorer!
     //
     // IE has lots of bugs with file downloads.
     // It also has problems with SSL.  Both of these cause problems
     // for us in this function.
     //
     // See this article on Cache Control headers and SSL
     // http://support.microsoft.com/default.aspx?scid=kb;en-us;323308
     //
     // The best thing you can do for IE is to upgrade to the latest
     // version
     //set all the Cache Control Headers for IE
     if ($isIE) {
         header ("Pragma: public");
         header ("Cache-Control: no-store, max-age=0, no-cache, must-revalidate"); # HTTP/1.1
         header ("Cache-Control: post-check=0, pre-check=0", false);
         header ("Cache-control: private");

         //set the inline header for IE, we'll add the attachment header later if we need it
         header ("Content-Disposition: inline; filename=$filename");
     }

     if (!$force) {
         // Try to show in browser window
         header ("Content-Disposition: inline; filename=\"$filename\"");
         header ("Content-Type: $type0/$type1; name=\"$filename\"");
     } else {
         // Try to pop up the "save as" box

         // IE makes this hard.  It pops up 2 save boxes, or none.
         // http://support.microsoft.com/support/kb/articles/Q238/5/88.ASP
         // http://support.microsoft.com/default.aspx?scid=kb;EN-US;260519
         // But, according to Microsoft, it is "RFC compliant but doesn't
         // take into account some deviations that allowed within the
         // specification."  Doesn't that mean RFC non-compliant?
         // http://support.microsoft.com/support/kb/articles/Q258/4/52.ASP

         // all browsers need the application/octet-stream header for this
         header ("Content-Type: application/octet-stream; name=\"$filename\"");

         // http://support.microsoft.com/support/kb/articles/Q182/3/15.asp
         // Do not have quotes around filename, but that applied to
         // "attachment"... does it apply to inline too?
         header ("Content-Disposition: attachment; filename=\"$filename\"");

         if ($isIE && !$isIE6) {
             // This combination seems to work mostly.  IE 5.5 SP 1 has
             // known issues (see the Microsoft Knowledge Base)

             // This works for most types, but doesn't work with Word files
             header ("Content-Type: application/download; name=\"$filename\"");

             // These are spares, just in case.  :-)
             //header("Content-Type: $type0/$type1; name=\"$filename\"");
             //header("Content-Type: application/x-msdownload; name=\"$filename\"");
             //header("Content-Type: application/octet-stream; name=\"$filename\"");
         } else {
             // another application/octet-stream forces download for Netscape
             header ("Content-Type: application/octet-stream; name=\"$filename\"");
         }
     }

     //send the content-length header if the calling function provides it
     if ($filesize > 0) {
         header("Content-Length: $filesize");
     }

}  // end fn SendDownloadHeaders

/**********************************************************************/
/**
 *
 * Creates a where clause for use in a SELECT statement, from criteria in an associative array
 *
 * @param adodbconnection $con handle to the database
 * @param array $criteria_array with array of criteria, keyed by fieldname
 * @param string $tablename optionally providing tablename to prepend to criteria fieldnames
 *
 * @return string which can be used after the WHERE in a sql statement or false if no criteria are found
 */
function make_where_string($con, $criteria_array, $tablename=false, $extra_where=false) {
    $magic_quotes_gpc=get_magic_quotes_gpc();
    if (!$criteria_array) return '';
    $where=array();
    foreach ($criteria_array as $akey=>$aval) {
        if ($aval!==false) {
            if ($tablename) $akey="$tablename.$akey";
            if (is_numeric($aval) OR (substr($aval,0,1)=="'")) {
                $where[]="$akey=$aval";
            } else $where[]="$akey=".$con->qstr($aval, $magic_quotes_gpc);
        }
    }
    if ($extra_where) { $where=array_merge($where,$extra_where); }
    if (count($where)>0) {
        $wherestr=implode(" AND ", $where);
    } else $wherestr=false;
    if ($wherestr) return $wherestr;
    else return false;
}


/**
 * Check if function is cached
 *
 * This returns bool if the function passed is cached or not
 * Can not just return false on function_cache_get because false is a valid cached response
 *
 * @author  Tom Setliff
 *
 * @param string $func_name Name of the Function
 * @param array $params Parameters returned from
 *
 * @return bool if we have the function cached
 */
function function_cache_bool($func_name, $params) {
    global $xrms_function_cache;
    $key = implode('|',$params);
    if (isset($xrms_function_cache[$func_name][$key])) {
        return true;
    }
    if (isset($_SESSION['XRMS_function_cache'][$func_name][$key])) {
        return true;
    }
    return false;
}

/**
 * Get cached value of function
 *
 * Return mixed value of function
 *
 * @author Tom Setliff
 *
 * @param string $func_name Name of the Function
 * @param array $params Parameters  of a function to fetch
 *
 * @return mixed The return of the function cached
 */
function function_cache_get($func_name, $params) {
    global $xrms_function_cache;
    $key = implode('|',$params);
    if (isset($xrms_function_cache[$func_name][$key])) {
        //echo("returning cache for just this page request");
        return $xrms_function_cache[$func_name][$key];
    }
    //echo("returning cache for session");
    return $_SESSION['XRMS_function_cache'][$func_name][$key];
}

/**
 * Save function return value to cache
 *
 * This function will save a cached value to a global var where it will only be avaliable for this page request
 * or by setting $this_request_only to false you can save it for the duration of the session.  This should be
 * useful for values that remain constant to a particular user.  This function should be implemented for database
 * intensive calls that should only be run once per session. To do this add these lines to the start of a function:
 * $func_args = func_get_args();
 * $func_name = 'change_this_to_be_the_name_of_your_function';
 * if (function_cache_bool($func_name, $func_args)) {
 *     return function_cache_get($func_name, $func_args);
 * }
 * and add function_cache_set($func_name, $func_args, $ret,false); right before the return statement
 * The number of lines added to the start of the function are required because of the PHP limitation on func_get_args()
 *
 * @author  Tom Setliff
 *
 * @param string $func_name Name of the Function
 * @param array $params Parameters returned from
 * @param mixed $ret Value from function to save
 * @param bool $this_request_only If this is set then the result is cached just for this page request in a global var
 */
function function_cache_set($func_name, $params, $ret,
$this_request_only = true) {
    global $xrms_function_cache;
    $key = implode('|',$params);
    if ($this_request_only) {
        //echo("setting value for this request<br>\n");
        $xrms_function_cache[$func_name][$key] = $ret;
        //unset($_SESSION['XRMS_function_cache'][$func_name][$key]);
    } else {
        //echo("setting $params value for duration of session<br>\n");
        $_SESSION['XRMS_function_cache'][$func_name][$key] = $ret;
        //unset($xrms_function_cache[$func_name][$key]); 
    }
    //unset key in storage method which is NOT being used now, to avoid confusion between session function cache and global (request) function cache
    function_cache_unset($func_name,$params,!$this_request_only,$key);
}

/**
 * Clear function return value from cache
 *
 * This function will clear the cached value in the global var or session where it was stored.
 * This should be useful for functions which need to clear the cache when the cached value is out of date or invalid
 *
 * @author Aaron van Meerten
 *
 * @param string $func_name Name of the Function
 * @param array $params Parameters returned from
 * @param bool $this_request_only If this is set then the result cleared just for this page request in a global var
 * @param string $key used as key to function cache, if known.  Using this value will ignore $params
 */
function function_cache_unset($func_name,$params,$this_request_only=true,$key=false) {
    global $xrms_function_cache;
    if (!$key) $key= implode('|',$params);
    if ($this_request_only) {
    	unset($xrms_function_cache[$func_name][$key]);
    } else {
    	unset($_SESSION['XRMS_function_cache'][$func_name][$key]);
    }
}


/**
* A function that returns either '?' or '&' for adding arguments to unknown URLs
* @param string url to check
* @return string '?' or '&' based on existance or not of 'php?' in url
*/
function get_url_seperator($url) {

    if(false === strpos($url, 'php?')) {
        return '?';
    } else {
        return '&';
    }
}

 /**
  * Converts all '<br>' & '<p> tags, with attributes, into NEWLINE characters
  *
  * @name brp2nl()
  * @access public
  * @category string_handling
  *
  * @static
  * @final
  *
  * @param  int    $_string          String to convert
  * @return mixed  $_string          Converted string
  *
  */
function brp2nl($_string)
{
    /* Remove x|HTML PARAGRAPH tags, with attributes */
    $_string = preg_replace("=[\n]?<[p][^>]*>[\n]?=i", "\n\n", $_string);

    /* Remove x|HTML linebreak tags, with attributes */
    return preg_replace("=[\n]?<[br][^>]*>[\n]?=i", "\n", $_string);
}


 /**
  * Returns an array containing all the values of $arr1  which
  * have matching keys that are present in $arr2.
  *
  * PHP 5 now has a function named 'array_intersect_key'.
  * The main difference between that and this is that this
  * method will only return kys that have values.
  *
  * @name array_intersect_key_2()
  * @access public
  * @category array_handling
  *
  * @author Silvio Ginter
  * @URL php.net/manual/en/function.array-intersect-key.php
  *
  * @static
  * @final
  *
  * @param  array  $arr1    Primary array
  * @param  array  $arr2    Secondary array, pull data from here
  * @return mixed  $_string          Converted string
  *
  */
function array_intersect_key_2($arr1, $arr2) {
   $res = array();
   foreach($arr1 as $key=>$value) {
       $push = true;
       for ($i = 1; $i < func_num_args(); $i++) {
           $actArray = func_get_arg($i);
           if (gettype($actArray) != 'array') return false;
           if (!array_key_exists($key, $actArray)) $push = false;
       }
       if ($push && $arr2[$key]) $res[$key] = $arr2[$key];
   }
   return $res;
};



/**
 * Include the i18n files, as every file with output will need them
 *
 * @todo sort out a better include strategy to simplify it across
 *       the XRMS code base.
 */
require_once($include_directory . 'i18n.php');


/** Include the database utilities file */
require_once($include_directory . 'utils-database.php');


// ============================================================================

/**
 * $Log: utils-misc.php,v $
 * Revision 1.175  2006/07/12 01:03:22  vanmer
 * - added missing global statement for default language
 *
 * Revision 1.174  2006/04/29 01:44:02  vanmer
 * - added new file for workflow related functions (utils-workflow.php)
 * - moved workflow related functions out of utils-misc into utils-workflow.php
 *
 * Revision 1.173  2006/04/05 00:47:54  vanmer
 * - added function_cache_unset function for clearing function cache
 * - added parameter when adding workflow history, to provide user_id, instead of retrieving from the session
 *
 * Revision 1.172  2006/03/03 21:19:20  vanmer
 * - uncommented the exit statement, should be here for security
 * - Thanks to Diego Ongaro at ETSZONE (diego@etszone.com) for noting it
 *
 * Revision 1.171  2006/02/06 16:16:34  niclowe
 * On DB error, added suggestion to update database structure. This is a common error and a cause of many tickets on the site
 *
 * Revision 1.170  2006/01/25 23:43:36  vanmer
 * - patch to log failed logins and include PHP session_id in audit items
 * - credit to "Diego Ongaro at ETSZONE (diego@etszone.com)" for providing this patch
 *
 * Revision 1.169  2006/01/13 20:03:38  vanmer
 * - wrapped the get_xrms_dbconnection call and require_once for utils_database.php inside session_startup, to allow utils_database functions to be defined/used outside this functions scope
 *
 * Revision 1.168  2006/01/10 15:03:16  braverock
 * - update to use $GLOBALS array for database connection to eliminate scope issue
 *
 * Revision 1.167  2006/01/10 14:48:03  braverock
 * - missing globals in session_check fn mess up the xrms_db_connection fn call
 *
 * Revision 1.166  2006/01/09 23:12:33  vanmer
 * - added check to ensure that if no on_what_table and on_what_id are set, that we will allow access
 * - added options to check user permissions when running session check (will change on_what_table and on_what_id)
 *
 * Revision 1.165  2006/01/02 21:39:57  vanmer
 * - always include utils-database.php from pages which include utils-misc, so that dbconnection function is available
 *
 * Revision 1.164  2005/12/20 07:57:58  jswalter
 *  - added 'get_country_id()' to retrieve XRMS country identifer
 * Bug 779
 *
 * Revision 1.163  2005/12/18 02:55:52  vanmer
 * - changed to use new gmt_offset field instead of offset field, for postgres compatiblity
 *
 * Revision 1.162  2005/12/14 04:29:24  daturaarutad
 * add get_url_seperator() function
 *
 * Revision 1.161  2005/12/08 05:21:47  vanmer
 * - Initial revision of database handling of session data using adodb session functions as an option
 *
 * Revision 1.160  2005/12/07 00:19:36  jswalter
 *  - added 'array_intersect_key_2()' to handle intersection of arrays
 *
 * Revision 1.159  2005/12/06 22:34:39  vanmer
 * - removed system parameter functions, changed get_system_parameter to only be a wrapper function
 *
 * Revision 1.158  2005/12/06 21:49:39  vanmer
 * - added require_once for utils-database before first call to get_xrms_dbconnection
 *
 * Revision 1.157  2005/12/03 20:40:33  vanmer
 * - added needed global declaration
 *
 * Revision 1.156  2005/12/03 00:27:27  vanmer
 * - added ability to handle session data in the XRMS database
 *
 * Revision 1.155  2005/12/02 00:44:25  vanmer
 * - added phpdoc package
 *
 * Revision 1.154  2005/12/02 00:31:32  vanmer
 * - added more PHPDoc to utils-misc
 *
 * Revision 1.153  2005/11/23 17:32:00  jswalter
 *  - moved 'clean_phone_fields()' from "utils-contacts.php"
 *
 * Revision 1.152  2005/10/16 20:13:57  maulani
 * - Fix IP address retrieve for some Apache 2.0 installs
 *
 * Revision 1.151  2005/10/16 19:47:08  maulani
 * - Retrieve correct client IP address if XRMS is accessed thru a proxy
 *
 * Revision 1.150  2005/10/13 17:27:24  jswalter
 *  - added 'brp2nl()' to convert HTML breaks into hard break
 *
 * Revision 1.149  2005/10/03 02:32:49  vanmer
 * - changed to use proper whitespace characters so that PHP does not break
 *
 * Revision 1.148  2005/10/01 22:49:24  braverock
 * - add checks for rows returned in fetch_primary_address and fetch_division_id fns
 *   - patch credit to Holger G. Hahn
 *
 * Revision 1.147  2005/09/29 16:02:22  vanmer
 * - fixed typo in insert record for workflow history, now correctly tracks time and user for status change
 *
 * Revision 1.146  2005/09/29 14:52:54  vanmer
 * - added function to instantiate a new entity from an activity template of type "process"
 * - adds activities attached to both the new and old entity pointing to the other entity
 *
 * Revision 1.145  2005/09/21 20:02:08  vanmer
 * - added function to list addresses by company_id
 * - added function to retrieve formatted address and create an html select widget for a company's addresses
 *
 * Revision 1.144  2005/08/19 02:00:23  daturaarutad
 * call session_name() before session_start() so as to not klobber xrms session on logout
 *
 * Revision 1.143  2005/08/02 22:25:24  ycreddy
 * added code to use the default_country_id if the country_id is not available in get_formatted_phone
 *
 * Revision 1.142  2005/07/12 17:36:47  vanmer
 * - changed to throw noperm message when no authorization is allow for a user, instead of noauth
 *
 * Revision 1.141  2005/07/11 16:00:09  braverock
 * - update session cache unset to resolve hang bug in some environments
 *   credit miguel Gonçalves (mig77) for the problem report and resolution
 *
 * Revision 1.140  2005/07/08 19:25:22  jswalter
 *  - added 'random_string()' for central method to create secure  passwords and file names
 *
 * Revision 1.139  2005/07/07 20:54:25  vanmer
 * - added parameter to where string creator to take a seperate array of criteria
 *
 * Revision 1.138  2005/07/07 18:47:55  vanmer
 * - added function to look up company id from a division id
 *
 * Revision 1.137  2005/07/06 17:17:29  vanmer
 * - added caching to system parameters
 * - added query of system preferences before resorting to system parameters
 *
 * Revision 1.136  2005/06/30 18:00:30  vanmer
 * - fixed getGlobalVar to use proper order when fetching HTTP_USER_AGENT parameter
 *
 * Revision 1.135  2005/06/07 23:48:51  braverock
 * - add optional $show_country option to get_formatted_address fn
 *
 * Revision 1.134  2005/05/18 21:40:27  vanmer
 * - added function for adding entries to workflow history, to track status changes on workflow elements
 *
 * Revision 1.133  2005/05/18 05:45:24  vanmer
 * - removed code that uses role_short_name in session for security
 * - replaced with a check for Update access on the Administration controlled object
 *
 * Revision 1.132  2005/05/16 20:56:24  vanmer
 * - added caching for system parameter function calls
 *
 * Revision 1.131  2005/05/16 20:47:55  vanmer
 * - added caching of database results for address formatting functions
 *
 * Revision 1.130  2005/05/11 16:29:28  braverock
 * - explicitly set contacT_id and company_id in phone format for cti integration
 *
 * Revision 1.129  2005/05/09 22:40:37  vanmer
 * - added functions for caching function results into session or global variables for re-use
 * Based on code from Tom Setliff
 *
 * Revision 1.128  2005/05/07 17:04:04  vanmer
 * - require utils-preferences in utils-misc, to allow user preference lookups
 *
 * Revision 1.127  2005/05/06 15:24:13  braverock
 * - minor code formatting changes
 *
 * Revision 1.126  2005/05/06 00:47:03  vanmer
 * - added check for user language, if exists set user language
 * - changed to pass adodb connection to acl when checking for permissions
 *
 * Revision 1.125  2005/04/28 19:01:17  vanmer
 * - cause system parameters function to not exit entirely, but simply output an error and return false
 *
 * Revision 1.124  2005/04/26 23:15:10  vanmer
 * - added comments to the current_page function, to make it more readable
 *
 * Revision 1.123  2005/04/15 07:36:16  vanmer
 * - added function for creating a where clause for a select statement using an associative array of
 * criteria
 *
 * Revision 1.122  2005/03/18 01:23:56  vanmer
 * - added array and check if count of array is 0, instead of simple if check on variable
 *
 * Revision 1.121  2005/03/15 22:18:05  vanmer
 * -readded exit command to allow ACL to properly restrict access to pages
 *
 * Revision 1.120  2005/03/11 16:58:59  daturaarutad
 * fixed order of operations bug in arr_vars_get_all by adding ()
 *
 * Revision 1.119  2005/03/10 18:09:32  ycreddy
 * Using trim on phone_format to fix the empty string problem with SQL server
 *
 * Revision 1.118  2005/03/07 00:58:44  maulani
 * - Show_test_values will now display arrays and objects
 *
 * Revision 1.117  2005/03/04 23:18:53  vanmer
 * - added changed to current_page to be able to operate on IIS
 *
 * Revision 1.116  2005/01/29 17:20:14  braverock
 * - fixed problem with single line address formatting in get_formatted_address fn
 *
 * Revision 1.115  2005/01/13 17:51:18  vanmer
 * - added ACL requirement to utils-misc.php (MAY BREAK existing installs unless update is run)
 * - added ACL restriction into session_check
 *
 * Revision 1.114  2005/01/12 20:29:57  braverock
 * - altered to not append & to first variable
 *
 * Revision 1.113  2005/01/12 20:08:41  vanmer
 * - added optional parameters to formatted_address function to look up company address, also provides one line output
 *
 * Revision 1.112  2005/01/12 02:46:06  introspectshun
 * - Check to make sure count($parts) > 1 before using $parts[1]
 *
 * Revision 1.111  2005/01/10 19:46:29  neildogg
 * - I remember why I left off the http_site_root
 *
 * Revision 1.110  2005/01/10 17:52:31  neildogg
 * - Allows an anchor name to be used
 *
 * Revision 1.109  2005/01/10 16:55:14  neildogg
 * - Properly replaces variables if duplicated
 *
 * Revision 1.108  2005/01/10 16:42:08  neildogg
 * - Now returns proper context in relation to site root and prevents double variables
 *
 * Revision 1.107  2005/01/10 15:52:08  neildogg
 * - Can now pass variables to tack on to the end of current_page
 *
 * Revision 1.106  2005/01/09 17:02:57  vanmer
 * - added optional action parameter to session check, for ACL permissions
 * - checking in now to allow other ACL-related changes to operate properly before ACL is checked in.
 *
 * Revision 1.105  2005/01/09 02:46:06  braverock
 * - added additional test for $_SERVER['REQUEST_URI'] to handle IIS on XP lack of setting var
 *
 * Revision 1.104  2005/01/09 00:30:33  braverock
 * - change data_format_phone hook to pass an array instead of multiple params
 *   'eval' call in do_hook_function to support multiple parameters
 *    was causing problems w/ sidebar hooks that passed strings containing php
 * - plugins that use data_format_phone will need to use expand fn to separate the array
 *
 * Revision 1.103  2005/01/08 23:32:40  braverock
 * - change current_page fn to use $_SERVER['REQUEST_URI'] instead of getenv('REQUEST_URI')
 *   IIS doesn't allow getenv calls, so you need to use $_SERVER for portability
 *
 * Revision 1.102  2005/01/04 16:27:55  neildogg
 * - Removed force index
 *
 * Revision 1.101  2004/12/24 15:52:58  braverock
 * add function SendDownloadHeaders ported from Squirrelmail
 *
 * Revision 1.100  2004/11/10 15:10:18  maulani
 * - Fix bug introduced when update sql changed to use GetUpdateSQL routine.
 *   Quotes will no longer be added to string system parameters
 *
 * Revision 1.99  2004/10/26 17:29:02  introspectshun
 * - Remove blank lines in address by getting rid of double BRs (fixed)
 *   - couldn't match BR at beginning of string due to nl2br call earlier
 *
 * Revision 1.98  2004/10/22 07:26:03  gpowers
 * - In get_formatted_address(), Removed output lines that contain only a comma and blank lines (fixes SF Bug #1028807)
 *
 * Revision 1.97  2004/10/11 14:20:40  neildogg
 * - Added further checks
 *
 * Revision 1.96  2004/09/24 21:45:16  niclowe
 * added support for whereis plugin (very similiar to mapquest)
 *
 * Revision 1.95  2004/09/02 15:19:13  neildogg
 * - Significantly faster time zone queries
 *
 * Revision 1.94  2004/09/02 14:58:05  neildogg
 * - Errant bracket close
 *
 * Revision 1.93  2004/09/02 14:49:29  neildogg
 * - Significantly speed up calls for US time zones
 *
 * Revision 1.92  2004/09/02 12:05:04  neildogg
 * - Fixed variable typo
 *
 * Revision 1.91  2004/08/30 14:50:08  neildogg
 * - Time zone now functions properly on missing variable
 *
 * Revision 1.90  2004/08/30 14:09:23  neildogg
 * - Rollback errant commit
 *
 * Revision 1.89  2004/08/30 14:01:07  neildogg
 * - Grab sorting values
 *
 * Revision 1.88  2004/08/26 14:40:18  neildogg
 * - Return false if no values for address
 *
 * Revision 1.87  2004/08/16 19:51:06  neildogg
 * - Missing end_position definition
 *
 * Revision 1.86  2004/08/16 16:06:11  neildogg
 * - Quotes city properly
 *
 * Revision 1.85  2004/08/14 00:43:23  gpowers
 * - added code for mapquest plugin support
 * - registered address vards as GLOBALS (needed for plugins)
 *
 * Revision 1.84  2004/08/13 19:35:12  gpowers
 * - added support for CTI/dial plugin (link phone number to dial function)
 *   - added a conditional check to avoid returning an empty link
 *
 * Revision 1.83  2004/08/12 11:01:48  braverock
 * - remove the gettext.php include, because i18n.php will include it if it needs to.
 *
 * Revision 1.82  2004/08/09 19:22:02  neildogg
 * - Fixed spelling error in function name
 *
 * Revision 1.81  2004/08/06 14:47:07  braverock
 * - push in changes to turn on i18n gettext
 *
 * Revision 1.80  2004/08/05 19:54:39  introspectshun
 * - Localized error msgs
 * - time_zone_offset and update_daylight_savings funcs updated for db compatibility
 *
 * Revision 1.79  2004/08/05 14:56:00  braverock
 * - add arr_vars functions for special cases
 *
 * Revision 1.78  2004/08/05 14:47:56  neildogg
 * - Fixed my undefined variable
 *
 * Revision 1.77  2004/08/03 20:21:02  neildogg
 * - No need for starting slashes
 *
 * Revision 1.76  2004/08/03 19:39:35  neildogg
 * - Returns the return_url appropriate current page string
 *
 * Revision 1.75  2004/08/02 20:20:48  neildogg
 * - 3 functions added to manage daylight savings
 *
 * Revision 1.74  2004/08/02 11:40:53  maulani
 * - Force exit since db_error_handler only presents the error and does not exit
 *
 * Revision 1.73  2004/08/02 11:33:48  maulani
 * - Fix logical check bug and expand error messages
 *
 * Revision 1.72  2004/08/02 10:01:39  cpsource
 * - Define default value of my_val as '' for the impared developer
 *
 * Revision 1.71  2004/08/02 08:34:16  maulani
 * - Force get_system_parameter to throw an error if value not found
 *
 * Revision 1.70  2004/07/31 12:08:06  cpsource
 * - Remove errant REQUEST from arr_vars
 *
 * Revision 1.69  2004/07/29 23:50:56  maulani
 * -remove obsolete comment
 *
 * Revision 1.68  2004/07/28 20:43:03  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.67  2004/07/27 10:21:50  cpsource
 * - Fix some undefs
 *
 * Revision 1.66  2004/07/27 10:02:15  cpsource
 * - Add routine arr_vars_post_with_cmd and test.
 *
 * Revision 1.65  2004/07/22 17:15:10  braverock
 * - fixed problem with arr_vars subsystem
 *
 * Revision 1.64  2004/07/22 14:34:07  cpsource
 * - Fixed bug with get_formatted_phone whereby $extra
 *   was sometimes used uninitialized.
 *
 * Revision 1.63  2004/07/22 13:27:59  neildogg
 * - Ignore me. It only strips formatting on a non-output variable
 *
 * Revision 1.62  2004/07/22 13:25:39  neildogg
 * - Won't strip formatting unless an expression exists
 *
 * Revision 1.61  2004/07/22 13:16:00  neildogg
 * - Fixed bug, extension now happens if phone is larger than formatting
 *
 * Revision 1.60  2004/07/22 12:03:37  cpsource
 * - Got rid of undefined variable usage in get_formatted_phone
 *   Documented a bug with get_formatted_phone
 *
 * Revision 1.59  2004/07/22 11:12:55  maulani
 * - Change default characterset to UTF-8
 *
 * Revision 1.58  2004/07/21 13:35:04  cpsource
 * - Document function arr_vars_get_all
 *
 * Revision 1.57  2004/07/21 10:11:58  cpsource
 * - Fixed problems with get_system_parameter whereby it ASS-UMED
 *   the record existed in the table.
 *
 * Revision 1.56  2004/07/21 09:13:22  cpsource
 * - Add display functions to arr_vars
 *
 * Revision 1.55  2004/07/21 06:29:45  maulani
 * - Fix bug 994830 with patch from johnfawcett.  Check that sql is valid
 *   in set_system_parameter.
 *
 * Revision 1.54  2004/07/21 05:54:06  maulani
 * - Update audit functions to use logging level from system parameters
 *
 * Revision 1.53  2004/07/20 14:04:41  cpsource
 * - Deprecated getGlobalVars. It's replaced by the arr_vars sub-system.
 *
 * Revision 1.52  2004/07/20 10:43:16  cpsource
 * - Moved SESSION['role'] to SESSION['role_short_name']
 *   role is now set in login-2.php instead of admin/routing.php
 *   utils-misc.php updated to check session with role_short_name
 *
 * Revision 1.51  2004/07/19 14:24:18  cpsource
 * - Add override of $use_post_vars in arr_vars_get_all
 *
 * Revision 1.50  2004/07/16 16:35:47  cpsource
 * - Add argument to session_check to accept a minimum role.
 *
 * Revision 1.49  2004/07/15 22:35:30  introspectshun
 * - set_system_parameter() now uses GetUpdateSQL
 *
 * Revision 1.48  2004/07/15 13:49:54  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.47  2004/07/15 13:05:09  cpsource
 * - Add arr_vars sub-system for passing variables between code streams.
 *
 * Revision 1.46  2004/07/14 16:21:23  maulani
 * - Fix sql bug (typo) in set_system_parameters routine
 *
 * Revision 1.45  2004/07/14 11:50:50  cpsource
 * - Added security feature IN_XRMS
 *
 * Revision 1.44  2004/07/13 22:57:36  cpsource
 * - Coded noew session_startup() routine to create session
 *   Modified session_check() to make it run faster and
 *     eleminate the recursive calling.
 *   Modified xrms_session_register() and xrms_session_unregister() to call
 *     session_startup()
 *
 * Revision 1.43  2004/07/13 14:52:29  braverock
 * - add additional isset tests to avoid unititialized var Notices
 *
 * Revision 1.42  2004/07/13 11:13:29  braverock
 * - changed REQUEST_URI array index to single quotes, so it won't get passed as a string
 *
 * Revision 1.41  2004/07/07 22:26:41  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
 *
 * Revision 1.40  2004/07/07 14:35:28  maulani
 * - Fix bug in show_test_values.  HTML entities need to be properly formatted for display
 *
 * Revision 1.39  2004/07/06 21:28:40  neildogg
 * - Now supports multiple formatting, based on country
 * - New column in countries specifies format, e.g. (###) ###-####
 * - Hook passes unformatted and formatted phone numbers
 *
 * Revision 1.38  2004/07/02 18:54:34  neildogg
 * - Added get_formatted_phone to misc utils
 * - Supports hook to override default formatting types.
 * - Formatting only works with country code of US and a # length of 10 ATM.
 * - No formatting calls added yet, please advise on this implementation.
 *
 * Revision 1.37  2004/07/01 12:43:26  braverock
 * - add utils-database.php file
 * - move list_db_tables and confirm_no_records fns to utils-database.php file
 *
 * Revision 1.36  2004/06/28 18:53:57  gpowers
 * - commented out null target checking code
 *   - it does not appear to be nessacary
 *
 * Revision 1.35  2004/06/28 11:59:43  braverock
 * - comment out exit in session_check function because it causes problems in some installations
 *   - credit to Nic Lowe for spotting the workaround to problem reported on SF
 *
 * Revision 1.34  2004/06/24 23:08:39  braverock
 * - add patch to prevent recursive call to session_check
 *
 * Revision 1.33  2004/06/21 15:43:01  braverock
 * - modified i18n files to better integrate with XRMS
 *
 * Revision 1.32  2004/06/15 14:04:54  gpowers
 * - removed extra )
 *   - second time is a charm? Arg.
 *
 * Revision 1.31  2004/06/15 14:03:18  gpowers
 * - chagned dbtimestamp() to time()
 *   - b/c the quoted time didn't work with mysql
 *
 * Revision 1.30  2004/06/11 20:26:30  introspectshun
 * - Now use ADODB GetInsertSQL and GetUpdateSQL functions.
 *
 * Revision 1.29  2004/06/08 02:21:56  braverock
 * - add optional $colspan parameter to db_error_handler fn for better formatting
 *
 * Revision 1.28  2004/06/07 18:44:30  maulani
 * - Remove cities check because it invalidates pretty addresses and some
 *   international addresses
 *
 * Revision 1.27  2004/06/07 16:27:11  gpowers
 * - get_formatted_address() will now not return an address if there is
 * no "city". This is to prevent blank addresses from displaying.
 *
 * Revision 1.26  2004/06/03 16:32:38  braverock
 * - fixed typo
 *
 * Revision 1.25  2004/05/21 22:37:30  maulani
 * - remove call-time pass by reference.  Function is already declared for
 *   pass by reference.
 *
 * Revision 1.24  2004/05/21 14:11:28  braverock
 * - add example code to db_error_handler fn
 *
 * Revision 1.23  2004/05/21 14:04:01  braverock
 * - added db_error_handler code to get_formatted_address fn
 *
 * Revision 1.22  2004/05/21 13:06:11  maulani
 * - Create get_formatted_address function which centralizes the address
 *   formatting code into one routine in utils-misc.
 *
 * Revision 1.21  2004/05/13 16:40:06  braverock
 * - modified system prefs functions to pass database conection by reference
 * - modified system prefs functions to use db_error_handler_fn
 * - added generic database error handler fn ( db_error_handler() )
 *
 * Revision 1.20  2004/05/10 13:07:21  maulani
 * - Add level to audit trail
 * - Clean up audit trail text
 *
 * Revision 1.19  2004/05/07 21:30:39  maulani
 * - Add audit-level to allow different levels of audit-logging
 *
 * Revision 1.18  2004/05/04 23:48:02  maulani
 * - Added a system parameters table to the database.  This table can be used
 *   for items that would otherwise be dumped into the vars.php file. These
 *   include config items that are not required for database connectivity nor
 *   have access speed performance implications.  Accessor and setor functions
 *   added to utils-misc.
 * - Still need to create editing screen in admin section
 *
 * Revision 1.17  2004/04/23 17:14:57  gpowers
 * Removed http_user_agent from audit_items table. It is space consuming and
 * redundant, as most httpd servers can be configured to log this information.
 *
 * Revision 1.16  2004/04/23 15:12:35  gpowers
 * added support for remote_add, remote_port, session_id, http_user_agent
 *
 * Revision 1.15  2004/04/16 16:35:33  braverock
 * - add check for url so that we don't get a filesize error
 *   when getting CSV data from a http stream
 *
 * Revision 1.14  2004/04/10 11:52:05  braverock
 * - remove trailing whitespace
 *
 * Revision 1.13  2004/04/09 19:53:11  braverock
 * - add better header munging for csv import fn
 *
 * Revision 1.12  2004/02/24 17:30:15  maulani
 * Repair audit trail SQL and add test function
 *
 * Revision 1.11  2004/02/04 21:56:16  braverock
 * - added comments to munging code in CSVtoArray
 * - updated date functions
 *
 * Revision 1.10  2004/02/04 21:18:12  braverock
 * - add key munging for trim,strtolower, and str_replace of array keys in CSVtoArray fn
 *
 * Revision 1.9  2004/02/04 18:38:11  braverock
 * -minor fixes to CSVtoArray and fetch_division_if fns
 *
 * Revision 1.8  2004/02/01 22:05:48  braverock
 * - clean up CSVtoArray fn
 * - add get_division_id fn
 *
 * Revision 1.7  2004/02/01 00:45:41  braverock
 * added Chris's fetch_company_id and fetch_default_address fns
 *
 * Revision 1.6  2004/02/01 00:36:08  braverock
 * improved sql formatting of update_recent_items fn sql
 *
 * Revision 1.5  2004/01/30 21:09:45  cdwtech
 * update_recent_items was broken
 *
 * Revision 1.4  2004/01/26 19:24:51  braverock
 * - added getGlobalVar fn
 * - added phpdoc
 */
?>
