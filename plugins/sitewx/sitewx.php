<?php
/*
*
* Site Weather XRMS Plugin v0.1
* uses wx200 from:
* http://wx200d.sourceforge.net/
*
* copyright 2004 Glenn Powers <glenn@net127.com>
* Licensed Under the Open Software License v. 2.0
*
*/


// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];

$page_title = "Site Weather";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
        <pre>
        <?php system('/usr/local/bin/wx200');  // Edit this if needed ?>
        </pre>
    </div>
</div>

<?php

end_page();

?>
