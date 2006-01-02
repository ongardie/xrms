<?php
/**
 * Show all the ratings for editing
 *
 * @todo add sort order for display purposes
 *
 * $Id: some.php,v 1.7 2006/01/02 22:03:16 vanmer Exp $
 */


require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$con = get_xrms_dbconnection();

$sql = "select * from ratings where rating_record_status = 'a' order by rating_id";
$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $table_rows .= '<tr>';
        $table_rows .= '<td class=widget_content><a href=one.php?rating_id=' . $rst->fields['rating_id'] . '>' . $rst->fields['rating_display_html'] . '</a></td>';
        $table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage Ratings");
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Ratings"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php echo _("Name"); ?></td>
            </tr>
            <?php  echo $table_rows; ?>
        </table>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <form action=add-2.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Add New Rating"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=rating_short_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=rating_pretty_name size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=rating_pretty_plural size=30></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=rating_display_html size=30></td>
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
 * Revision 1.7  2006/01/02 22:03:16  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.6  2004/07/16 23:51:37  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.5  2004/07/16 13:51:59  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.4  2004/06/14 22:38:46  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.3  2004/04/16 22:18:26  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/02/14 15:40:18  braverock
 * - change table to rating_display_html per a SF bug
 * - add phpdoc
 *
 */
?>