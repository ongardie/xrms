<?php
/**
 * utils-database.php - this file contains database utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Beth Macknik
 *
 * $Id: utils-database.php,v 1.9 2005/01/25 05:59:59 vanmer Exp $
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
    $sql = "show tables";

    //execute
    $rst = $con->execute($sql);

    $number_of_rows = $rst->RecordCount();
    if ($number_of_rows > 0) {
        $my_array = $rst->GetRows($number_of_rows);
        $table_list = array();
        for ($i=0;$i<$number_of_rows;$i++) $table_list[$i] = current($my_array[$i]);
        return ($table_list);
    } else {
        $table_list = array();
        return ($table_list);
    }
} // end list_db_tables fn


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
            return array("division_id");
        break;    
        default:
            return array(make_singular($table) . "_name");
        break;
    }
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


/**
 * $Log: utils-database.php,v $
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
