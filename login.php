<?php
/**
 * Display login screen
 *
 * $Id: login.php,v 1.11 2004/12/30 18:49:52 braverock Exp $
 */
require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
if ( isset($_GET['target']) ) {
  $target = urldecode($_GET['target']);
} else {
  $target = '';
}

/* This code does not work and should not be nessacary
    // add check here to make sure that the $target is inside our file tree
    if ($target !='' ) {
        if ((target != $http_site_root) and (!file_exists('.'.$target))){
            $target = '';
        }
    }

    // now set it correctly
    if ($target== '' or $target==$http_site_root) {
        $target=$http_site_root.'/private/home.php';
    } else {
        $target=$http_site_root.$target;
    }
*/

$page_title = $app_title;
start_page($page_title, false, $msg);

?>

<div style="position: absolute; width: 240px; height: 140px; left: 50%; top: 50%; margin-left: -120px; margin-top: -70px;">
<form action="login-2.php" method=post>
<input type=hidden name=target value="<?php echo $target; ?>" >
<table class=widget cellspacing=1>
        <tr>
                <td class=widget_header colspan=2><?php echo _("Login"); ?></td>
        </tr>
        <tr>
                <td class=widget_label_right><?php echo _("Username"); ?></td>
                <td class=widget_content_form_element><input type=text name=username></td>
        </tr>
        <tr>
                <td class=widget_label_right><?php echo _("Password"); ?></td>
                <td class=widget_content_form_element><input type=password name=password></td>
        </tr>
        <tr>
                <td class=widget_content_form_element_center colspan=2><input class=button type=submit value="<?php echo _("Login"); ?>"></td>
        </tr>
</table>
</form>
</div>

<script type="text/javascript">
<!--

function initialize() {
    document.forms[0].username.focus();
}

initialize();
//-->
</script>


<?php

end_page();

/**
 * $Log: login.php,v $
 * Revision 1.11  2004/12/30 18:49:52  braverock
 * - localize strings
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.10  2004/07/14 12:12:50  cpsource
 * - Fix uninitialized variables $msg and $target
 *
 * Revision 1.9  2004/07/10 12:49:21  braverock
 * - removed session_start for security reasons
 *   - applies patch suggested by cpsource in SF bug 977376
 *
 * Revision 1.8  2004/06/28 18:45:36  gpowers
 * - commented out file checking code. it did not work
 *   - login should now redirect to requested page
 *
 * Revision 1.7  2004/04/07 22:53:18  maulani
 * - Update layout to use CSS2
 * - Make HTML validate
 *
 * Revision 1.6  2004/04/07 19:38:25  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.5  2004/04/06 21:59:12  maulani
 * - Begin conversion of positioning tables to CSS
 *   - Remove tables from all page headers
 *   - Position login with CSS
 *
 *
 */
?>
