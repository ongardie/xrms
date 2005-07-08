<?php
/**
 * utils-database.php - this file contains database utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Beth Macknik
 *
 * $Id: utils-database.php,v 1.16 2005/07/08 01:29:03 vanmer Exp $
 */

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
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

function table_some_url($table) {
    $singular=make_singular($table);
    $return_url="/$singular/some.php";
}

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
 * function portfolio_gpg
 *
 * Provides an adodbconnection handle for the XRMS database
 *
 * @return adodbconnection $con connected to XRMS database
 */
function get_xrms_dbconnection() {
    global $xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname, $xrms_db_dbtype;
    $xcon = &adonewconnection($xrms_db_dbtype);
    $xcon->nconnect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
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

//make sure the db connection cleanup gets run at the end of the script execution
register_shutdown_function('db_con_cleanup');


/*****************************************************************************/
/**
 * $Log: utils-database.php,v $
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
