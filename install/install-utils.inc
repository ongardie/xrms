<?php
/**
 * install-utils.inc - this file contains utility functions for XRMS installation
 *
 * Functions in this file are only used during installation and update.
 *
 * @author Beth Macknik
 *
 * $Id: install-utils.inc,v 1.1 2004/03/18 01:07:18 maulani Exp $
 */

/**
 * Stop everything and display the error.
 *
 */
function install_fatal_error($error_msg = '') {

    echo <<<EOQ
    <!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
    <html>
    <head>
    <title>Test Results</title>
    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
    </head>
    <body>
$error_msg<BR>
    </body>
    </html>
EOQ;
exit;
} // end install_fatal_error fn



/**
 * Create the array of existing tables.
 *
 */
function list_db_tables($con) {
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
 */
function confirm_no_records($con, $table) {
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
 * $Log: install-utils.inc,v $
 * Revision 1.1  2004/03/18 01:07:18  maulani
 * - Create installation tests to check whether the include location and
 *   vars.php have been configured.
 * - Create PHP-based database installation to replace old SQL scripts
 * - Create PHP-update routine to update users to latest schema/data as
 *   XRMS evolves.
 *
 */
?>