<?php
/**
 * install-utils.inc - this file contains utility functions for XRMS installation
 *
 * Functions in this file are only used during installation and update.
 *
 * @author Beth Macknik
 *
 * $Id: install-utils.inc,v 1.5 2004/07/28 17:27:03 braverock Exp $
 */

/**
 * Stop everything and display the error.
 *
 * @param string $error_msg
 * @param boolean $fatal If fatal is true, stops installation.
 */
function install_fatal_error($error_msg = '', $fatal=true ) {

    echo <<<EOQ
    <table>
        <tr>
            <td class=widget_header>
EOQ;
    echo _("Test Results");
    echo <<<EOQ
            </td>
        </tr>
        <tr>
            <td class=widget_error>
                $error_msg
            </td>
        </tr>
    </table>
EOQ;

    if ($fatal) { exit; };

} // end install_fatal_error fn


/**
 * $Log: install-utils.inc,v $
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