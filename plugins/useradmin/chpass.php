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

//set target and see if we are logged in
$session_user_id = session_check();

$msg = $_GET['msg'];

$page_title = "Change Password";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
        <?php include("menu.php"); ?>
        <p><br /><br /></p>
        <form action=chpass-2.php method=get>
            <pre>
            Username:  <input type=text name=username><br>
            Password:  <input type=text name=username><br>
            Confirm:   <input type=text name=username><br>
            <input type=submit value="Change"><br>
            </pre>
        </form>
    </div>
</div>

<?php

end_page();

?>
