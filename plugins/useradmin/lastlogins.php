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

require_once('useradmin.inc');

//set target and see if we are logged in
$session_user_id = session_check();

$msg = $_GET['msg'];
$username = $_GET['username'];

$page_title = "Last Logins";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">
        <?php include("menu.php"); ?>
        <p><br /><br /></p>
        <form action=lastlogins.php method=get>
            <pre>
            Username:  <input type=text name="username" value="<?php echo $username; ?>"><br>
            <input type=submit value="Last Logins"><br>

<?php if ($username) {system("/usr/bin/ssh $ssh_user /usr/local/bin/radlast -100 $username");} ?>

            </pre>
        </form>
    </div>
</div>

<?php

end_page();

?>
