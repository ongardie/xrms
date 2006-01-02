<?php
/**
 * View the system parameters
 *
 * $Id: some.php,v 1.5 2006/01/02 22:07:25 vanmer Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$con = get_xrms_dbconnection();

$sql = "select param_id from system_parameters order by param_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $theparameter = urlencode ($rst->fields['param_id']);
        $theparameter_str = _($rst->fields['param_id']);
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?param_id=' . $theparameter . '>' .$theparameter_str. '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage System Parameters");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("System Parameters"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Parameter"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.5  2006/01/02 22:07:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2005/05/10 13:32:21  braverock
 * - localized string patches provided by Alan Baghumian (alanbach)
 *
 * Revision 1.3  2004/12/30 19:04:20  braverock
 * - localize strings
 * - patch provided by Ozgur Cayci
 *
 * Revision 1.2  2004/07/14 16:46:03  maulani
 * - Fix URL encode bug
 *
 * Revision 1.1  2004/07/14 16:23:37  maulani
 * - Add administrator capability to modify system parameters
 */
?>