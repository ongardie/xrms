<?php
/*
*
* Radius Test (radtest) XRMS Plugin v0.1
* uses radtest from:
* http://www.freeradius.org/
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the Open Software License v. 2.0
*
*/

$radtest = "/usr/local/bin/radtest";
$radius_server = "auth.example.com";
$radius_port = "1645";
# nas_port is the port number that will appear in the RADIUS logs
# it has no effect on the plugin, but should not be null
$nas_port = "99";
# you will need to configure your radius server to accept connections
# from the server running XRMS. shared secrets must match and should
# be different than other NASes.
$shared_secret = "testing123";

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];
$username = urldecode($_GET['username']);
$password = urldecode($_GET['password']);
$contact_id = $_GET['contact_id'];

$page_title = _("Radius Test");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
        <pre>
<?php system("$radtest $username $password $radius_server:$radius_port $nas_port $shared_secret"); ?>
        </pre>
        <a href="../../contacts/one.php?contact_id=<?php echo $contact_id; ?>"><?php echo _("Back"); ?></a>
    </div>
</div>

<?php

end_page();

/**
 * $Log: radtest.php,v $
 * Revision 1.3  2004/07/22 13:43:50  gpowers
 * - i18n'ed "Back" link
 *
 * Revision 1.2  2004/07/22 13:32:41  gpowers
 * - put server vars and comment at head of file
 * - i18n'ed page title
 * - added phpdoc log
 *
 */
?>
