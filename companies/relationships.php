<?php
/**
 * Edit company relationships
 *
 * @todo put back in established at date picker in form
 *
 * $Id: relationships.php,v 1.12 2006/01/02 22:56:27 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';
$company_id = $_GET['company_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

$company_name = fetch_company_name($con, $company_id);

$sql = "select rt.from_what_text, rt.to_what_text, r.established_at, r.to_what_id,
    c1.company_name as to_company_name, c2.company_name as from_company_name,
    c1.company_id as to_company_id, c2.company_id as from_company_id,
    r.from_what_id, rt.relationship_type_id
    from relationships r, companies c1, companies c2, relationship_types rt
    where (r.from_what_id = $company_id or r.to_what_id = $company_id)
    and rt.from_what_table = 'companies'
    and rt.to_what_table = 'companies'
    and r.relationship_type_id=rt.relationship_type_id
    and r.from_what_id=c1.company_id
    and r.to_what_id=c2.company_id
    and r.relationship_status = 'a'
    order by r.established_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        if($rst->fields['from_what_id'] == $company_id) {
            $from_or_to = "from";
        }
        else {
            $from_or_to = "to";
        }
        $established_at = $con->userdate($rst->fields['established_at']);
        $relationship_rows .= '<tr>';
        $relationship_rows .= '<td class=widget_content_form_element>' . $rst->fields[$from_or_to . '_what_text'] . '</td>';
        $relationship_rows .= '<td class=widget_content_form_element><a href="one.php?company_id='
            . $rst->fields[$from_or_to . '_company_id'] . '">' . $rst->fields[$from_or_to . '_company_name'] . '</a></td>';
        $relationship_rows .= '<td class=widget_content_form_element>' . $established_at . '</td>';
        $relationship_rows .= '<td class=widget_content_form_element>'
            . '<a href="delete-relationship.php?to_what_id=' . $rst->fields['to_what_id']
            . '&from_what_id=' . $rst->fields['from_what_id']
            . '&relationship_type_id=' . $rst->fields['relationship_type_id']
            . '&return_url=/companies/relationships.php&company_id=' . $company_id . '">(Delete)</a>'
            . '</td>';
        $relationship_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "SELECT " . $con->Concat("company_name", "' '", "company_code") . ", company_id AS company2_id FROM companies WHERE company_record_status = 'a' ORDER BY company_name";
$rst = $con->execute($sql);
$company_menu = $rst->getmenu2('to_what_id', '', false);
$rst->close();

$relation_menu = "<select name=relationship_type_id>";

$sql = "select relationship_type_id, from_what_text, to_what_text
    from relationship_types
    where from_what_table = 'companies'
    and to_what_table = 'companies'
    and relationship_status='a'";

$rst = $con->execute($sql);

if($rst) {
    while(!$rst->EOF) {
        $relation_menu .= "\n<option value='from_" . $rst->fields['relationship_type_id'] . "'>"
            . $rst->fields['from_what_text']
            . "\n<option value='to_" . $rst->fields['relationship_type_id'] . "'>"
            . $rst->fields['to_what_text'];
        $rst->movenext();
    }
}

$relation_menu .= "\n</select>";

$con->close();

$page_title = $company_name . " - " . _("Relationships");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">
        <form action=add-relationship.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4><?php echo _("Relationships"); ?></td>
            </tr>
                        <tr>
				<td class=widget_label><?php echo _("Relationship"); ?></td>
				<td class=widget_label><?php echo _("Company"); ?></td>
				<td class=widget_label><?php echo _("Date"); ?></td>
                                <td class=widget_label></td>
                        </tr>
            <?php  echo $relationship_rows; ?>
            <tr>
                <td><?php echo $relation_menu ?></td>
                <td><?php echo $company_menu ?></td>
                        </tr>
            <tr>
                <td class=widget_content_form_element colspan=4>
                    <input class=button type=submit value="<?php echo _("Add Relationship"); ?>">
                    <input class=button type=button value="<?php echo _("Back to"); ?> <?php echo $company_name; ?>" onclick="javascript:location.href='one.php?company_id=<?php echo $company_id; ?>';">
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
 * $Log: relationships.php,v $
 * Revision 1.12  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.11  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.10  2004/07/21 19:17:57  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.9  2004/07/02 18:04:57  neildogg
 * - Changed ? to & in URL to transfer variable properly.
 *
 * Revision 1.8  2004/07/01 19:48:10  braverock
 * - add new configurable relationships code
 *   - adapted from patches submitted by Neil Roberts
 *
 * Revision 1.7  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.6  2004/06/07 16:23:22  gpowers
 * - Added a "Back to $company_name" button. This is needed to get back
 * to the company page after making edits.
 *
 * Revision 1.5  2004/05/27 15:04:34  gpowers
 * Added a link to company page on company name.
 *
 * Revision 1.4  2004/05/06 13:33:04  gpowers
 * removed "Former Names". This is now a separate screen.
 *
 * Revision 1.3  2004/04/16 22:19:38  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.2  2004/04/08 17:00:59  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 */
?>
