<?php
/**
 * utils-misc.php - this file contains non-UI utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Chris Woofter
 *
 * $Id: utils-misc.php,v 1.7 2004/02/01 00:45:41 braverock Exp $
 */

/**
 * Check to see if there is a session initialized, and send to logon if there isn't one.
 *
 * @param string $target the page the user was trying to go to
 * @return integer user_id of the logged in user
 */
function session_check($target='') {

    global $http_site_root;
    global $xrms_system_id;

    if ($target= '') {
        $target=$http_site_root.'/private/home.php';
    } else {
        $target=$http_site_root.$target;
    }
    $target=urlencode($target);

    session_start();

    if ((!$_SESSION['session_user_id'] > 0) || (strcmp($_SESSION['xrms_system_id'], $xrms_system_id) != 0)) {
        header("Location: $http_site_root" . "/login.php?target=$target");
        exit;
    }

    return $_SESSION['session_user_id'];

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

    $sql2 = "insert into recent_items set
             user_id       =  $user_id,
             on_what_table = " . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ",
             on_what_id    = $on_what_id,
             recent_item_timestamp = " . $con->dbtimestamp(time());

    // $con->debug=1;
    $con->execute($sql1);
    $con->execute($sql2);
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
function add_audit_item($con, $user_id, $audit_item_type, $on_what_table, $on_what_id) {
    $sql = "insert into audit_items set
            user_id = $user_id,
            audit_item_type = " . $con->qstr($audit_item_type, get_magic_quotes_gpc()) . ",
            on_what_table = " . $con->qstr($on_what_table, get_magic_quotes_gpc()) . ",
            on_what_id = " . $con->qstr($on_what_id, get_magic_quotes_gpc()) . ",
            audit_item_timestamp = " . $con->dbtimestamp(time()) . ")";

    //$con->debug=1
    $con->execute($sql);
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
 * modified from a function on php.net
 *
 * @param  handle  $file           handle to the uploaded file
 * @param  boolean $hasFieldNames  should we get the filed list off the first line
 * @param  char    $delimiter      delimiter for the CSV file
 * @param  char    $enclosure      are the fields enclosed in anything (like quotes)
 *
 * @return array   $result         array in the form of rows with keys and values
 */
function CSVtoArray($file, $hasFieldNames = false, $delimiter = ',', $enclosure='') {
   $result = Array();

   //@todo There must be a better way of finding out the size of the longest row... until then
   $size = filesize($file) +1;
   $file = fopen($file, 'r');

   if ($hasFieldNames) $keys = fgetcsv($file, $size, $delimiter, $enclosure);
   while ($row = fgetcsv($file, $size, $delimiter, $enclosure)) {
       $n = count($row); $res=array();
       for($i = 0; $i < $n; $i++) {
           $idx = ($hasFieldNames) ? $keys[$i] : $i;
           $res[$idx] = $row[i];
       }
       $result[] = $res;
   }
   fclose($file);
   return $result;
}

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
 * Find company id from company name
 * to see if the company exists before adding it
 *
 * @param  handle  $con database connection
 * @param  string  $company_name to search for
 * @return integer $company_id found ID or 0 (ZERO) if no match
 */
function fetch_company_id($con, $company_name) {

    $sql_fetch_company_id = "select company_id from companies where company_name = " . $con->qstr($company_name, get_magic_quotes_gpc());
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
 * $Log: utils-misc.php,v $
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
 *
 */
?>