<?php
/**
 * View all Case Types
 *
 * $Id: some.php,v 1.9 2007/10/17 15:11:00 randym56 Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from case_types where case_type_record_status = 'a' order by case_type_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['case_type_id'] . '</td>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['case_type_short_name'] . '</td>';
        $table_rows .= '<td class=widget_content><a href=one.php?case_type_id=' . $rst->fields['case_type_id'] . '>' . $rst->fields['case_type_pretty_name'] . '</a></td>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['case_type_pretty_plural'] . '</td>';
        $table_rows .= '<td class=widget_content>' . $rst->fields['case_type_display_html'] . '</td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage Case Types");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=5><?php echo _("Case Types"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("ID"); ?></td>
                <td class=widget_label><?php echo _("Short Name"); ?></td>
                <td class=widget_label><?php echo _("Full Name"); ?></td>
                <td class=widget_label><?php echo _("Full Plural Name"); ?></td>
                <td class=widget_label><?php echo _("Display HTML"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=new-2.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Add New Case Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_type_short_name size=10></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_type_pretty_name size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_type_pretty_plural size=20></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=case_type_display_html size=30></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.9  2007/10/17 15:11:00  randym56
 * Show ID field to make ACL mods for group members easier and match new docs
 *
 * Revision 1.8  2006/12/05 19:43:27  jnhayart
 * Add cosmetics display, and control localisation
 *
 * Revision 1.7  2006/01/02 21:41:51  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.6  2004/07/16 23:51:35  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:56  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 21:48:25  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:24  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/03/21 23:55:51  braverock
 * - fix SF bug 906413
 * - add phpdoc
 *
 */
?>