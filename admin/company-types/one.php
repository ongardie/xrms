<?php
/**
 * Manage company types
 *
 * $Id: one.php,v 1.8 2004/07/16 23:51:36 cpsource Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$company_type_id = $_GET['company_type_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from company_types where company_type_id = $company_type_id";

$rst = $con->execute($sql);

if ($rst) {

    $company_type_short_name = $rst->fields['company_type_short_name'];
    $company_type_pretty_name = $rst->fields['company_type_pretty_name'];
    $company_type_pretty_plural = $rst->fields['company_type_pretty_plural'];
    $company_type_display_html = $rst->fields['company_type_display_html'];

    $rst->close();
}

$page_title = $company_type_pretty_name;
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-2.php method=post>
        <input type=hidden name=company_type_id value="<?php  echo $company_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Edit Company Type Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Short Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_type_short_name value="<?php  echo $company_type_short_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_type_pretty_name value="<?php  echo $company_type_pretty_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Full Plural"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_type_pretty_plural value="<?php  echo $company_type_pretty_plural; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Display HTML"); ?></td>
                <td class=widget_content_form_element><input type=text name=company_type_display_html value="<?php  echo $company_type_display_html; ?>"></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Save Changes"); ?>"></td>
            </tr>
        </table>
        </form>

        <form action=delete.php method=post>
        <input type=hidden name=company_type_id value="<?php  echo $company_type_id; ?>">
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Delete Company Type"); ?></td>
            </tr>
            <tr>
                <td class=widget_content>
                <?php echo _("Click the button below to remove this company type from the system."); ?>
                <p><?php echo _("Note: This action CANNOT be undone!"); ?>
                <p><input class=button type=submit value="<?php echo _("Delete Company Type"); ?>">
                </td>
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
 * $Log: one.php,v $
 * Revision 1.8  2004/07/16 23:51:36  cpsource
 * - require session_check ( 'Admin' )
 *
 * Revision 1.7  2004/07/16 13:51:57  braverock
 * - localize strings for i18n translation support
 *   - applies modified patches from Sebastian Becker (hyperpac)
 *
 * Revision 1.6  2004/06/14 22:08:04  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.5  2004/04/16 22:18:25  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.4  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>

