<?php
/**
 * Set categories for a case
 *
 * $Id: categories.php,v 1.7 2006/01/02 22:47:25 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'utils-accounting.php');

$session_user_id = session_check();
$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

$case_id = $_GET['case_id'];

$con = get_xrms_dbconnection();
// $con->debug = 1;

// associated with

/*

$sql = "select category_id, category_pretty_name
from categories
where category_record_status = 'a'
and category_id in (select ccsm.category_id from category_category_scope_map ccsm, category_scopes cs where ccsm.category_scope_id = cs.category_scope_id and cs.on_what_table = 'companies')
and category_id in (select category_id from entity_category_map where on_what_table = 'companies' and on_what_id = $company_id)
order by category_pretty_name";

*/

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm, entity_category_map ecm
where ecm.on_what_table = 'cases'
and ecm.on_what_id = $case_id
and ecm.category_id = c.category_id
and cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'cases'
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);
$array_of_categories = array();
array_push($array_of_categories, 0);

if ($rst) {
    while (!$rst->EOF) {
        $associated_with .= "<a href='remove-category.php?case_id=$case_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        array_push($array_of_categories, $rst->fields['category_id']);
        $rst->movenext();
    }
    $rst->close();
}

// not associated with

$sql = "select c.category_id, category_pretty_name
from categories c, category_scopes cs, category_category_scope_map ccsm
where cs.category_scope_id = ccsm.category_scope_id
and c.category_id = ccsm.category_id
and cs.on_what_table = 'cases'
and c.category_id not in (" . implode(',', $array_of_categories) . ")
and category_record_status = 'a'
order by category_pretty_name";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $not_associated_with .= "<a href='add-category.php?case_id=$case_id&category_id=" . $rst->fields['category_id'] . "'>" . $rst->fields['category_pretty_name'] . "</a><br>";
        $rst->movenext();
    }
    $rst->close();
}

$con->close();

$page_title = _("Manage Categories");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Manage Categories"); ?></td>
            </tr>
            <tr>
                <td width=50% class=widget_label><?php echo _("Associated With"); ?></td>
                <td class=widget_label><?php echo _("Not Associated With"); ?></td>
            </tr>
            <tr>
                <td class=widget_content valign=top><?php  echo $associated_with; ?></td>
                <td class=widget_content valign=top><?php  echo $not_associated_with; ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input type=button class=button onclick="javascript: location.href='one.php?case_id=<?php  echo $case_id; ?>';" value="<?php echo _("Finished"); ?>"></td>
            </tr>
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
 * $Log: categories.php,v $
 * Revision 1.7  2006/01/02 22:47:25  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.6  2004/07/30 11:02:14  cpsource
 * - Optionally define msg
 *   set default no_update flag to false in edit-2.php
 *
 * Revision 1.5  2004/07/16 07:11:17  introspectshun
 * - Localized strings for i18n/translation support
 *
 * Revision 1.4  2004/06/12 04:08:06  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.3  2004/04/16 22:21:59  maulani
 * - Add CSS2 positioning
 *
 * Revision 1.2  2004/04/08 16:59:15  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
