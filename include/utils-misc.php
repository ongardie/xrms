<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/**
 * utils-misc.php - this file contains non-UI utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Chris Woofter
 * @author Brian Peterson
 *
 * $Id: utils-misc.php,v 1.52 2004/07/20 10:43:16 cpsource Exp $
 */

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
  $sessid = session_id();
  if ( empty( $sessid ) ) {
    // only call session_start once
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
 * @return integer user_id of the logged in user
 */
function session_check($c_role='') {

    global $http_site_root;
    global $xrms_system_id;

    // get our eventual target
    if (isset($_SERVER['REQUEST_URI'])) {
        $target = urlencode($_SERVER['REQUEST_URI']);
    } else {
        $target = urlencode($_SERVER['PHP_SELF']);
    }

    // make sure the session has started
    session_startup();

    // make sure we have a role to do this
    $role_ok = true;
    if ( $c_role ) {
      $s_role = isset($_SESSION['role_short_name']) ? $_SESSION['role_short_name'] : '';
      if ( 0 == strcmp($s_role, $c_role) ) {
	// yes
	$role_ok = true;
      } else {
	// no
	$role_ok = false;
      }
    }

    // make sure we've logged in
    if ( isset($_SESSION['session_user_id']) && 0 == strcmp($_SESSION['xrms_system_id'], $xrms_system_id) ) {
      // we are logged in
      if ( !$role_ok ) {
	// we are logged in, go straight to logout.php
	header("Location: $http_site_root" . "/logout.php?msg=noauth");
	//exit;
      }
      // just return our current session id
      return $_SESSION['session_user_id'];
    }

    // we are not logged in, go straight to login.php
    header("Location: $http_site_root" . "/login.php?target=$target");
    //exit;
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
function update_recent_items($con, $user_id, $on_what_table, $on_what_id) {

    $sql1 = "delete from recent_items where
             user_id = $user_id
             and on_what_table = " . $con->qstr($on_what_table, get_magic_quotes_gpc())  ."
             and on_what_id = $on_what_id";

    $con->execute($sql1);

    //save to database
    $rec = array();
    $rec['user_id'] = $user_id;
    $rec['on_what_table'] = $on_what_table;
    $rec['on_what_id'] = $on_what_id;
    $rec['recent_item_timestamp'] = time();

    $tbl = 'recent_items';
    $ins = $con->GetInsertSQL($tbl, $rec, get_magic_quotes_gpc());
    $con->execute($ins);
}

/**
 * Get the current system audit level
 *
 * @todo  Currently a stub that logs everything.  Will be data or vars.php based
 *   0 - no logging
 *   1 - inserts & updates
 *   2 - and login/logout
 *   3 - and views
 *   4 - and searches
 *
 * @return the audit level
 */
function current_audit_level() {
    return 4;  // 0 is the most restrictive level, 4 logs everything
}

/**
 * Add current item to the XRMS audit table
 *
 * @param  handle  $con database connection
 * @param  integer $user_id
 * @param  string  $audit_item_type
 * @param  string  $on_what_table   where are we in the interface?
 * @param  integer $on_what_id what record are we viewing
 * @return void
 */
function add_audit_item($con, $user_id, $audit_item_type, $on_what_table, $on_what_id, $level=4) {
    $log_level = current_audit_level();
    if ($level <= $log_level) {
        //save to database
        $rec = array();
        $rec['user_id'] = $user_id;
        $rec['audit_item_type'] = $audit_item_type;
        $rec['on_what_table'] = $on_what_table;
        $rec['on_what_id'] = $on_what_id;
        if (isset($_SERVER['REMOTE_ADDR'])) {
            $rec['remote_addr'] = $_SERVER['REMOTE_ADDR'];
        }
        if (isset($_SERVER['REMOTE_PORT'])) {
            $rec['remote_port'] = $_SERVER['REMOTE_PORT'];
        }
        $rec['session_id'] = $_COOKIE['PHPSESSID'];
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
        echo "Unable to open file: $file \n";
        echo "Please correct this error.";
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
    $rst_address_id = $con->execute($sql_fetch_address_id);
    if ($rst_address_id) {
        $address_id = $rst_address_id->fields['default_primary_address'];
        $rst_address_id->close();
    } else {
        $address_id = 1;
    }

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

    $sql_fetch_division_id = 'select division_id from company_division where
                             division_name = ' . $con->qstr($company_name, get_magic_quotes_gpc()) . "
                             and company_id = $company_id";

    $rst_division_id = $con->execute($sql_fetch_division_id);

    if ($rst_division_id) {
        $division_id = $rst_division_id->fields['division_id'];
        $rst_division_id->close();
    } else {
        $division_id = 0;
    }

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
    $p1 = htmlentities ($param1, ENT_QUOTES );
    $p2 = htmlentities ($param2, ENT_QUOTES );
    $p3 = htmlentities ($param3, ENT_QUOTES );
    $p4 = htmlentities ($param4, ENT_QUOTES );
    $p5 = htmlentities ($param5, ENT_QUOTES );

    echo <<<EOQ
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
    <html>
    <head>
    <title>Test Results</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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
 *
 * @param handle &$con handle to the database connection
 * @param string $param System Parameter to be retrieved
 */
function get_system_parameter(&$con, $param) {

    $sql ="select string_val, int_val, float_val, datetime_val from system_parameters where param_id='$param'";
    $sysst = $con->execute($sql);
    if ($sysst){
        $string_val = $sysst->fields['string_val'];
        $int_val = $sysst->fields['int_val'];
        $float_val = $sysst->fields['float_val'];
        $datetime_val = $sysst->fields['datetime_val'];
        if (!is_null($string_val)) {
            $my_val=$string_val;
        } elseif (!is_null($int_val)) {
            $my_val=$int_val;
        } elseif (!is_null($float_val)) {
            $my_val=$float_val;
        } elseif (!is_null($datetime_val)) {
            $my_val=$datetime_val;
        }
        $sysst->close();
    } else {
        //there was a problem, notify the user
        db_error_handler ($con, $sql);
    }

    return $my_val;
} //end fn get_system_parameter

/**
 * Set a value in the system_parameters.
 *
 * @param handle &$con    handle to the database connection
 * @param string $param   System Parameter to be changed
 * @param mixed  $new_val value to change the parameter to
 */
function set_system_parameter(&$con, $param, $new_val) {

    // First, determine which field is appropriate for the set.
    $sql ="select string_val, int_val, float_val, datetime_val from system_parameters where param_id='$param'";
    $sysst = $con->execute($sql);
    if ($sysst){
        $string_val = $sysst->fields['string_val'];
        $int_val = $sysst->fields['int_val'];
        $float_val = $sysst->fields['float_val'];
        $datetime_val = $sysst->fields['datetime_val'];
        if (!is_null($string_val)) {
            $my_field='string_val';
            $set_val = "'" . $new_val . "'";
        } elseif (!is_null($int_val)) {
            $my_field='int_val';
            $set_val = $new_val;
        } elseif (!is_null($float_val)) {
            $my_field='float_val';
            $set_val = $new_val;
        } elseif (!is_null($datetime_val)) {
            $my_field='datetime_val';
            $set_val = $new_val;
        }
        $sysst->close();
    } else {
        //there was a problem, notify the user
        db_error_handler ($con, $sql);
    }
    $sql ="SELECT * FROM system_parameters WHERE param_id='$param'";
    $rst = $con->execute($sql);
    
    $rec = array();
    $rec[$my_field] = $set_val;
    
    $upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
    $sysst = $con->execute($upd);
    if (!$sysst){
        //there was a problem, notify the user
        db_error_handler ($con, $upd);
    }
} //end fn set_system_parameter

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
        // figure out where to print this out.
        if ($error) {
            echo "\n<tr>\n\t<td class=widget_error colspan=$colspan>"
                 ."\t<br>"
                 ._("Unable to execute your query.").' '
                 ._("Please correct this error.")
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
function get_formatted_phone ($con, $address_id, $phone) {
    $phone_to_display = $phone;
    $sql = "select
                c.phone_format
            from
                addresses a,
                countries c
            where
                a.address_id='$address_id'
            and
                a.country_id=c.country_id";
    $rst = $con->execute($sql);
    $expression = $rst->fields['phone_format'];
    $rst->close();

    $pos = 0;
    $number_length = 0;
    list($phone, $extra) = split(" ", $phone, 2);

    if(strlen($expression)) {
        preg_match_all("|[#]+|", $expression, $matched);
        $matched = $matched[0];
        foreach($matched as $match) {
            $number_length += strlen($match);
        }
        if(strlen($phone) == $number_length) {
            foreach($matched as $match) {
                $expression = substr_replace($expression, substr($phone, $pos, strlen($match)), strpos($expression, $match), strlen($match));
                $pos += strlen($match);
            }
            $phone_to_display = $expression;
        }
    }
    $temp_phone = do_hook_function("data_format_phone", $phone, $phone_to_display);
    if($temp_phone) {
        $phone_to_display = $temp_phone;
    }
    return $phone_to_display . " " . $extra;
}

/**
 * function get_formatted_address : get the address and format it
 *
 * @author Beth Macknik
 *
 * @param handle &$con handle to the database connection
 * @param int $address_id id of the address to be retrieved
 *
 * @return string $address_to_display
 */
function get_formatted_address (&$con,$address_id) {
    $sql = "select a.address_body, a.line1, a.line2, a.city, a.province, a.postal_code, a.use_pretty_address, ";
    $sql .= 'afs.address_format_string, c.country_name ';
    $sql .= 'from addresses a, address_format_strings afs, countries c ';
    $sql .= "where a.address_id=$address_id ";
    $sql .= 'and a.country_id=c.country_id ';
    $sql .= 'and c.address_format_string_id=afs.address_format_string_id';
    $rst = $con->execute($sql);

    if ($rst) {
        $address_body = $rst->fields['address_body'];
        $line1 = $rst->fields['line1'];
        $line2 = $rst->fields['line2'];
        $city = $rst->fields['city'];
        $province = $rst->fields['province'];
        $postal_code = $rst->fields['postal_code'];
        $use_pretty_address = $rst->fields['use_pretty_address'];
        $address_format_string = $rst->fields['address_format_string'];
        $country = $rst->fields['country_name'];

        if ($use_pretty_address == 't') {
            $address_to_display = nl2br($address_body);
        } else {
            $lines = (strlen($line2) > 0) ? "$line1<br>$line2" : $line1;
            eval("\$address_to_display = \"$address_format_string\";");
            // eval ("\$str = \"$str\";");
        }
    } else {
        // database error, return some useful information.
        ob_start();
        db_error_handler ($con,$sql);
        $address_to_display = ob_get_contents();
        ob_end_clean();
    }
    return $address_to_display;
} //end fn get_formatted_address

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

// get all variables
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
	$GLOBALS[$key] = isset($_GET["$value[0]"]) ? $_GET["$value[0]"] : isset($_SESSION["$value[0]"]) ? $_SESSION["$value[0]"] : '';
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

/**
 * Include the i18n files, as every file with output will need them
 *
 * @todo sort out a better include strategy to simplify it across
 *       the XRMS code base.
 */
require_once($include_directory . 'i18n.php');

/** Include the database utilities file */
require_once($include_directory . 'utils-database.php');


/**
 * $Log: utils-misc.php,v $
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
