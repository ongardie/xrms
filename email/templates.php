<?php
/**
 * /email/templates.php
 *
 * Email templates
 *
 * $Id: templates.php,v 1.6 2006/02/06 15:49:22 niclowe Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];

$con = get_xrms_dbconnection();

$sql = "SELECT * FROM users WHERE user_id = $session_user_id";
$rst = $con->execute($sql);

$rec = array();
$rec['last_hit'] = time();

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$sql = "select * from email_templates";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $tablerows .= '<tr>';
        $tablerows .= '<td class=widget_content><a href=one.php?email_template_id=' . $rst->fields['email_template_id'] . '>' . $rst->fields['email_template_title'] . '</a></td>';
        // $tablerows .= '<td class=widget_content>' . $rst->fields['company_code'] . '</td>';
        $tablerows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($tablerows) == 0) {
    $tablerows = '<tr><td class=widget_content colspan=1>'._("No e-mail templates") . '</td></tr>';
}

$page_title = _("E-Mail Templates");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=20><?php echo  _("E-Mail Templates"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
            </tr>
            <?php  echo $tablerows ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php

end_page();

/**
 * $Log: templates.php,v $
 * Revision 1.6  2006/02/06 15:49:22  niclowe
 * patch submitted by icheb 1385195 ] Parse error /email/templates.php
 *
 * Revision 1.5  2006/01/02 23:02:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/08/04 21:46:42  introspectshun
 * - Localized strings for i18n/l10n support
 * - All paths now relative to include-locations-location.inc
 *
 * Revision 1.3  2004/06/14 16:54:37  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.2  2004/04/16 22:19:58  maulani
 * - Add CSS2 positioning
 *
 *
 */
?>
