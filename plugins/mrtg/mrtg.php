<?php
/**
 * The main page for the MRTG plugin
 *
 * $Id: mrtg.php,v 1.3 2004/07/22 13:10:09 gpowers Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];

$page_title = _("Multi Router Traffic Grapher");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
        <?php include("../../../mrtg/index.php");  // Edit this to point to your MRTG page ?>
    </div>
</div>

<?php

end_page();

/**
 * $Log: mrtg.php,v $
 * Revision 1.3  2004/07/22 13:10:09  gpowers
 * - Removed unused code.
 *
 * Revision 1.2  2004/06/16 21:00:36  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.1  2004/05/06 15:13:58  gpowers
 * This is a simple plugin for including an MRTG page in XRMS.
 *
 */
?>
