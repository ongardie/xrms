<?php
/**
 * Save changes to divisions
 *
 * $Id: edit-division.php,v 1.10 2005/02/08 17:08:29 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$on_what_table='company_division';
$division_id = $_GET['division_id'];
$on_what_id=$division_id;

$session_user_id = session_check('','Update');
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$company_id = $_GET['company_id'];
$address_id = $_GET['address_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select d.*, c.company_name from companies c, company_division d where c.company_id = d.company_id and d.division_id = $division_id";

$rst = $con->execute($sql);

if ($rst) {
    $division_id = $rst->fields['division_id'];
    $address_id = $rst->fields['address_id'];
    $company_name = $rst->fields['company_name'];
    $division_name = $rst->fields['division_name'];
    $description = $rst->fields['description'];
    $rst->close();
}

$sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_id";
$rst = $con->execute($sql);
$address_menu = $rst->getmenu2('address_id', $address_id, true);
$rst->close();

$sidebar='';
$sidebar=do_hook_function('division_sidebar_bottom',$sidebar);

$edit_division_form_extra='';
$edit_division_form = do_hook_function('edit_division_form', &$edit_division_form_extra);

if (!$sidebar) $sidebar = '&nbsp';

$con->close();

$page_title = $company_name . ' - ' . $division_name . ' - ' . _("Edit Division");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=edit-division-2.php method=post>
        <input type=hidden name=company_id value=<?php echo $company_id; ?>>
        <input type=hidden name=division_id value=<?php echo $division_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Edit Division"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Division Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=30 name=division_name value="<?php echo $division_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address"); ?></td>
                <td class=widget_content_form_element><?php echo $address_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Description"); ?></td>
                <td class=widget_content_form_element><textarea rows=8 cols=80 name=description><?php echo $description; ?></textarea></td>
            </tr>
            <?php echo $edit_division_form_extra; ?>
            <tr>
                <td class=widget_content_form_element colspan=2>
                    <?php echo render_edit_button("Save Changes"); ?>
                    <?php echo render_delete_button("Delete Division",'button',"javascript: location.href='delete-division.php?company_id=$company_id&division_id=$division_id';"); ?>                
                 </td>
            </tr>
        </table>
        </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

       <?php echo $sidebar; ?>

    </div>
</div>

<?php

    end_page();

/**
 * $Log: edit-division.php,v $
 * Revision 1.10  2005/02/08 17:08:29  vanmer
 * - removed second passed parameter (does not work with do_hook_function)
 *
 * Revision 1.9  2005/02/08 17:03:37  vanmer
 * - added hook for division edit page form display
 *
 * Revision 1.8  2005/01/25 00:05:29  vanmer
 * - added hook for a division sidebar
 * - added output for sidebar
 *
 * Revision 1.7  2005/01/13 18:22:23  vanmer
 * - Basic ACL changes to allow display functionality to be restricted
 *
 * Revision 1.6  2005/01/06 21:54:26  vanmer
 * - added address_id load/display to division UI, to specify an address for a division
 *
 * Revision 1.5  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.4  2004/07/21 19:17:56  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.3  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.2  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.1  2004/01/26 19:18:02  braverock
 * - added company division pages and fields
 * - added phpdoc
 *
 */
?>