<?php
/**
 * Edit the information for a system parameter
 *
 * $Id: one.php,v 1.2 2005/01/24 00:17:19 maulani Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$param_id = $_GET['param_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$my_val = get_system_parameter($con, $param_id);

//get case details
$sql = "select description from system_parameters where param_id = '$param_id'";

$rst = $con->execute($sql);

if ($rst) {
    $description = $rst->fields['description'];
    $rst->close();
} else {
    db_error_handler ($con, $sql);
}

$con->close();

$page_title = "System Parameter : $param_id";
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=param_id value="<?php  echo $param_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Edit System Parameter</td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $param_id; ?></td>
                <td class=widget_content_form_element><input type=text size=40 name=param_value value="<?php echo $my_val; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><?php echo $description; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Save Changes"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.2  2005/01/24 00:17:19  maulani
 * - Add description to system parameters
 *
 * Revision 1.1  2004/07/14 16:23:37  maulani
 * - Add administrator capability to modify system parameters
 *
 *
 */
?>