<?php
/**
 * install-utils.inc - this file contains utility functions for XRMS installation
 *
 * Functions in this file are only used during installation and update.
 *
 * @author Beth Macknik
 *
 * $Id: install-utils.inc,v 1.3 2004/07/22 11:12:56 maulani Exp $
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
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    </head>
    <body>
$error_msg<BR>
    </body>
    </html>
EOQ;
exit;
} // end install_fatal_error fn


/**
 * $Log: install-utils.inc,v $
 * Revision 1.3  2004/07/22 11:12:56  maulani
 * - Change default characterset to UTF-8
 *
 * Revision 1.2  2004/07/01 12:43:26  braverock
 * - add utils-database.php file
 * - move list_db_tables and confirm_no_records fns to utils-database.php file
 *
 * Revision 1.1  2004/03/18 01:07:18  maulani
 * - Create installation tests to check whether the include location and
 *   vars.php have been configured.
 * - Create PHP-based database installation to replace old SQL scripts
 * - Create PHP-update routine to update users to latest schema/data as
 *   XRMS evolves.
 *
 */
?>