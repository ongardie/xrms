<?php

require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

session_start();

$msg = $_GET['msg'];
$target = $_GET['target'];

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

$page_title = $app_title;
start_page($page_title, false, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width="100%" height="80%">
    <tr>
        <td width="33%" valign=top>

        &nbsp;

        </td>

        <!-- gutter //-->
        <td align=center valign=middle>

        <form action=login-2.php method=post>
        <input type=hidden name=target value="<?php echo $target; ?>" >
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Login</td>
            </tr>
            <tr>
                <td class=widget_label_right>Username</td>
                <td class=widget_content_form_element><input type=text name=username></td>
            </tr>
            <tr>
                <td class=widget_label_right>Password</td>
                <td class=widget_content_form_element><input type=password name=password></td>
            </tr>
            <tr>
                <td class=widget_content_form_element_center colspan=2><input class=button type=submit value="Login"></td>
            </tr>
        </table>
        </form>

        </td>

        <!-- right column //-->
        <td width="33%" valign=top>

        &nbsp;

        </td>
    </tr>
</table>

<script type="text/javascript">
<!--

function initialize() {
    document.forms[0].username.focus();
}

initialize();
//-->
</script>


<?php end_page(); ?>