<?php
/**
 * install/data_clean.php - Moved to XRMS administration
 *
 * Do not add anything to this file
 *
 * @author Beth Macknik
 * $Id: data_clean.php,v 1.5 2004/04/13 12:29:20 maulani Exp $
 */

// where do we include from
require_once('../include-locations.inc');

// get required common files
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

// vars.php sets all of the installation-specific variables
require_once($include_directory . 'vars.php');

$page_title = "Database Cleanup Complete";
start_page($page_title, false, $msg);

?>

<BR>
This function has been moved to the administration section of XRMS.  Please log in to run it.


<?php

end_page();

/**
 * $Log: data_clean.php,v $
 * Revision 1.5  2004/04/13 12:29:20  maulani
 * - Move the data clean and update files into the admin section of XRMS
 *
 * Revision 1.4  2004/04/12 16:21:58  maulani
 * - Add check to insure that all companies have at least one contact
 *
 * Revision 1.3  2004/04/12 14:35:19  maulani
 * - move structure change to update.php
 *
 * Revision 1.2  2004/04/09 17:13:28  braverock
 * - added alter table command to change company_legal_name to legal_name
 *   (only relevant for upgraded old installations)
 *
 * Revision 1.1  2004/04/07 20:16:21  maulani
 * - Set of routines to cleanup common data problems
 *
 *
 */
?>
