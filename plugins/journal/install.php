<?php
/**
 * INSTALL
 *
 * $Id: install.php,v 1.1 2004/11/09 03:41:19 gpowers Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');

$session_user_id = session_check();

global $xrms_db_name, $xrms_db_host, $xrms_db_username, $xrms_db_password;

$msg = $_GET['msg'];

$page_title = _("Personal Journal - INSTALL ");
start_page($page_title);

echo "<pre>";
system("cat weblog.sql | `which mysql` -h$xrms_db_server -u$xrms_db_username -p$xrms_db_password $xrms_db_dbname");
echo "</pre>";

echo "Done."

end_page();

/**
 * $Log: install.php,v $
 * Revision 1.1  2004/11/09 03:41:19  gpowers
 * - Journal Plugin v0.1
 *
 */
?>
