<?php
/**
 * utils-database.php - this file contains database utility functions for XRMS
 *
 * Functions in this file may be used throughout XRMS.
 * Almost all files in the system include this file.
 *
 * @author Beth Macknik
 *
 * $Id: utils-database.php,v 1.1 2004/07/01 12:43:26 braverock Exp $
 */

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
        for ($i=0;$i<$number_of_rows;$i++) $table_list[$i] = $my_array[$i][0];
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
 * $Log: utils-database.php,v $
 * Revision 1.1  2004/07/01 12:43:26  braverock
 * - add utils-database.php file
 * - move list_db_tables and confirm_no_records fns to utils-database.php file
 *
 */
?>