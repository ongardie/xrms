<?php
/**
 * Manage Activity Types
 *
 * $Id: some.php,v 1.2 2004/11/12 06:36:37 gpowers Exp $
 */

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

// $con->debug = 1;

$sql = "select * from info_types where info_type_record_status = 'a'";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?info_type_id=' . $rst->fields['info_type_id'] . '>' . $rst->fields['info_type_name'] . '</a></td>';
        $table_rows .= '<td class=widget_content>';

        $sql2 = "select * from info_display_map
                 where info_type_id = " . $rst->fields['info_type_id'];
        $rst2 = $con->execute($sql2);
        if ($rst2) {
            while (!$rst2->EOF) {
                $table_rows .= $rst2->fields['display_on'] . "<br />\n";
                $rst2->movenext();
            }
        }

        $table_rows .= '</td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage Info Types");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header><?php echo _("Info Type"); ?></td>
                <td class=widget_header><?php echo _("Display On"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

    <!-- right column //-->
    <div id="Sidebar">

        <form action="add-2.php" method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>
                    <?php echo _("Add New Info Type"); ?>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    <?php echo _("Name"); ?>
                </td>
                <td class=widget_content_form_element>
                    <input type=text name=info_type_name size=30>
                </td>
            </tr>
            <tr>
                <td class=widget_label_right>
                    <?php echo _("Display On"); ?>
                </td>
                <td class=widget_content_form_element>
                    <?php echo display_on_menu(); ?>
                </td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit value="<?php echo _("Add"); ?>">
                </td>
            </tr>
        </table>
        </form>
    </div>
</div>

<?php

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.2  2004/11/12 06:36:37  gpowers
 * - added support for single display_on add/edit/delete/show
 *
 * Revision 1.1  2004/11/10 07:27:49  gpowers
 * - added admin screens for info types
 *
 * Revision 1.10  2004/07/19 21:31:09  introspectshun
 * - Added i18n string for $page_title
 *
 * Revision 1.9  2004/07/16 23:51:34  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.8  2004/07/16 13:51:53  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.7  2004/07/15 21:11:58  introspectshun
 * - Minor tweaks for consistency
 *
 * Revision 1.6  2004/06/24 20:09:25  braverock
 * - use sort order when displaying activity types
 *   - patch provided by Neil Roberts
 *
 * Revision 1.5  2004/06/14 21:06:33  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.4  2004/06/13 09:13:57  braverock
 * - add sort_order to activity_types
 *
 * Revision 1.3  2004/04/16 22:18:23  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:46  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 */
?>
