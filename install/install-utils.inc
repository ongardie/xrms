<?php
/**
 * install-utils.inc - this file contains utility functions for XRMS installation
 *
 * Functions in this file are only used during installation and update.
 *
 * @author Beth Macknik
 *
 * $Id: install-utils.inc,v 1.8 2004/08/21 01:30:56 d2uhlman Exp $
 */

/**
 * Stop everything and display the error.
 *
 * @param string $error_msg
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


// Check to make sure that include files do not have whitespace characters
// added to the end.  In some cases, the whitespace causes php to send 
// headers before it should.
function check_extra_whitespace($path, $filename) {
    $filepath = $path . $filename;
    $contents = file($filepath);
    $endchars = $contents[count($contents) -1];
    //char 10 is a linefeed, this is acceptable at the end of a file
    if(!($endchars == "?>" xor $endchars == ("?>" . chr(10)))) {
        $failuremsg = "The file  ";
        $failuremsg .= $filename;
        $failuremsg .= " does not end with the characters ?>. ";
        $failuremsg .= "You probably have added extra whitespace to the end. ";
        $failuremsg .= "Please remove the extra characters and run install again.";
        install_fatal_error($failuremsg);
    }
} // end check_extra_whitespace fn


/**
 * $Log: install-utils.inc,v $
 * Revision 1.8  2004/08/21 01:30:56  d2uhlman
 * better whitespace check, linefeeds 0A,char(10) are acceptable and common at the end of a php file
 *
 * Revision 1.7  2004/08/02 01:31:19  maulani
 * - Add ending whitespace check to include-locations.inc and vars.php.
 * - Fix bugs involving dependency on general system includes
 *
 * Revision 1.6  2004/07/31 03:17:42  maulani
 * - Add a function to check include files for trailing whitespace
 *
 * Revision 1.5  2004/07/28 17:27:03  braverock
 * - add phpdoc params to install_fatal_error fn
 *
 * Revision 1.4  2004/07/28 17:25:10  braverock
 * - add optional fatal parameter to install_fatal_error fn
 *
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
