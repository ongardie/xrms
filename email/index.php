<?php
/**
 *
 * Email.
 *
 * $Id: index.php,v 1.8 2006/01/02 23:02:14 vanmer Exp $
 */

require_once('include-locations-location.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$array_of_companies = array();
$array_of_companies = unserialize($_SESSION['array_of_companies']);

$con = get_xrms_dbconnection();
    
    //hack to not show continue button if no templates are found
    $show_continue=true; 

$sql = "SELECT * FROM users WHERE user_id = $session_user_id";
$rst = $con->execute($sql);

$rec = array();
$rec['last_hit'] = Time();

$upd = $con->GetUpdateSQL($rst, $rec, false, get_magic_quotes_gpc());
$con->execute($upd);

$sql = "select * from email_templates where email_template_record_status = 'act' order by email_template_title";

$rst = $con->execute($sql);

$counter = 0;
if ($rst) {
	while (!$rst->EOF) {
		$counter ++;
		$checked = ($counter == 1) ? ' checked' : '';
		$tablerows .= '<tr>';
        $tablerows .= "<td class=widget_content_form_element><input type=radio name=email_template_id value=" . $rst->fields['email_template_id'] . $checked . "></td>";
		$tablerows .= '<td class=widget_content><a href=one-template.php?email_template_id=' . $rst->fields['email_template_id'] . '>' . $rst->fields['email_template_title'] . '</a></td>';
		$tablerows .= '</tr>';
		$rst->movenext();
	}
	$rst->close();
}

if (strlen($tablerows) == 0) {
	$tablerows = '<tr><td class=widget_content colspan=20>' . _("No e-mail templates") . '</td></tr>';
        $show_continue=false;
}

$page_title = _("Bulk E-Mail");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=email-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=20><?php echo _("E-Mail Templates"); ?></td>
			</tr>
			<tr>
				<td class=widget_label width=1%>&nbsp;</td>
				<td class=widget_label><?php echo _("Template"); ?></td>
			</tr>
            <?php  echo $tablerows ?>
			<tr>
                        <?php if ($show_continue) { ?> <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Continue"); ?>"></td> <?php } ?>
			</tr>
		</table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

		&nbsp;

    </div>

</div>

<?php

end_page();

/**
 * $Log: index.php,v $
 * Revision 1.8  2006/01/02 23:02:14  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.7  2005/01/09 01:06:52  vanmer
 * - added check to see if templates exist.  If not, do not show continue button
 *
 * Revision 1.6  2004/08/18 00:06:17  niclowe
 * Fixed bug 941839 - Mail Merge not working
 *
 * Revision 1.5  2004/08/04 21:46:42  introspectshun
 * - Localized strings for i18n/l10n support
 * - All paths now relative to include-locations-location.inc
 *
 * Revision 1.4  2004/06/14 16:54:37  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Corrected order of arguments to implode() function.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/17 16:00:36  maulani
 * - Add CSS2 positioning
 *
 *
 */
?>
