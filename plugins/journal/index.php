<?php
/**
 * The main page for the MRTG plugin
 *
 * $Id: index.php,v 1.1 2004/11/09 03:41:19 gpowers Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require "weblog.inc";

$mode=$_GET['wl_mode'];

global $xrms_db_name, $xrms_db_server, $xrms_db_username, $xrms_db_password;
$w = new Weblog($xrms_db_dbname, $xrms_db_server, $xrms_db_username, $xrms_db_password, '', 'journal_');

$session_user_id = session_check();

$msg = $_GET['msg'];

$page_title = _("Personal Journal");
start_page($page_title);

echo '<div id="Main">';

if (($mode == "entry_search") or ($mode == "")) {
    echo '<div id="Content">';
    $w->entry_edit();
    echo '</div>';
    echo '<div id="Sidebar">';
    $w->insert();
    echo '</div>';
} else {
    echo '<div id="Content">';
    $w->insert();
    echo '</div>';
}
echo '</div>';

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.1  2004/11/09 03:41:19  gpowers
 * - Journal Plugin v0.1
 *
 */
?>
