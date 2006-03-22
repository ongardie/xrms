<?php
/**
 * display company divisions
 *
 * @author Brian Peterson
 *
 * $Id: divisions.php,v 1.16 2006/03/22 02:31:12 braverock Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');


$on_what_table='company_division';
$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$company_id = $_GET['company_id'];

$return_url="divisions.php?company_id=$company_id";
$url_return_url=urlencode($return_url);

$con = get_xrms_dbconnection();
// $con->debug = 1;

$company_name = fetch_company_name($con, $company_id);

$sql = "select * from companies c, company_division d
where d.division_record_status = 'a'
and c.company_id = d.company_id
and c.company_id = $company_id";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        if (!strlen($rst->fields['division_name'])){
            $rst->fields['division_name'] = _("[none]");
        }
        $division_html .= '<tr>';
        $division_html .= "<td class=widget_label><a href=edit-division.php?return_url=$url_return_url&company_id=$company_id&division_id=". $rst->fields['division_id'] . '>' . $rst->fields['division_name'] . '</a></td>';
        $division_html .=   '<td class=widget_content>'
                          . $rst->fields['description'] . '</td>';

        $division_html .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "select address_name, address_id from addresses where company_id = $company_id and address_record_status = 'a' order by address_name";
$rst = $con->execute($sql);
$address_menu = $rst->getmenu2('address_id', $address_id, true);
$rst->close();

$con->close();
$new_division_form = do_hook_function('new_division_form', $new_division_form_extra);

$page_title = $company_name . " - " . _("Divisions");
start_page($page_title, true, $msg);

?>

<div id="Main">

    <!-- new division //-->
    <form action=add-division.php method=post>
    <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
    <table class=widget cellspacing=1>
        <tr>
            <td class=widget_header colspan=2><?php echo _("New Division"); ?></td>
        </tr>
        <tr>
            <td class=widget_label><?php echo _("Company"); ?></td>
            <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
        </tr>
        <tr>
            <td class=widget_label><?php echo _("Division Name"); ?></td>
            <td class=widget_content_form_element><input type=text name=division_name size=30></td>
        </tr>
            <tr>
                <td class=widget_label><?php echo _("Address"); ?></td>
                <td class=widget_content_form_element><?php echo $address_menu; ?><?php echo '<a href="addresses.php?company_id=' . $company_id .'">' . _("Add/Edit Addresses") . '</a>'; ?></td>
            </tr>
        <tr>
            <td class=widget_label><?php echo _("Division Description"); ?></td>
            <td class=widget_content_form_element><textarea rows=8 cols=80 name=description></textarea></td>
        </tr>
                <?php echo $new_division_form_extra; ?>
        <tr>
            <td class=widget_content_form_element colspan=2><?php echo render_create_button('Add'); ?></td>
        </tr>
    </table>
    </form>

    <table class=widget cellspacing=1>
        <tr>
            <td class=widget_header colspan=2><?php echo _("Divisions"); ?></td>
        </tr>
        <tr>
            <td class=widget_label><?php echo _("Name"); ?></td>
            <td class=widget_label><?php echo _("Description"); ?></td>
        </tr>
        <?php  echo $division_html; ?>
    </table>

</div>

<?php

end_page();

/**
 * $Log: divisions.php,v $
 * Revision 1.16  2006/03/22 02:31:12  braverock
 * - fix missing return_url
 *   -  patch submitted by MarkoL
 *   -  fixes https://sourceforge.net/tracker/?func=detail&atid=588128&aid=1440968&group_id=88850
 *
 * Revision 1.15  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.14  2005/07/21 22:13:27  braverock
 * - set a default name for division if no name specified
 *
 * Revision 1.13  2005/06/07 21:06:37  braverock
 * - sort address drop-down menu by address_name
 *
 * Revision 1.12  2005/04/07 17:49:14  vanmer
 * - changed second parameter to do_hook_function to pass variable instead of passing reference (reference is now in function definition)
 *
 * Revision 1.11  2005/01/26 22:49:09  gpowers
 * - removed "localhost" from add/edit address URL
 *
 * Revision 1.10  2005/01/25 23:30:57  vanmer
 * - added new hook for addition to form for adding new division
 *
 * Revision 1.9  2005/01/13 18:22:50  vanmer
 * - Basic ACL changes to allow display functionality to be restricted
 *
 * Revision 1.8  2005/01/08 06:23:41  gpowers
 * - added "Add/Edit Addresses" Link
 *
 * Revision 1.7  2005/01/06 21:54:26  vanmer
 * - added address_id load/display to division UI, to specify an address for a division
 *
 * Revision 1.6  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.5  2004/07/21 19:17:56  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.4  2004/07/07 21:53:13  introspectshun
 * - Now passes a table name instead of a recordset into GetInsertSQL
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
 */
?>