<?php
/**
 * Edit company relationships
 *
 * $Id: former-names.php,v 1.5 2006/01/02 22:56:27 vanmer Exp $
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

// former names

$sql = "select * from company_former_names where company_id = $company_id order by namechange_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $former_name_rows .= $rst->fields['former_name']
            . '&nbsp;&nbsp;<a href="delete-former-name.php?company_id=' . $company_id
            . '&former_name=' . $rst->fields['former_name'] . '">(Delete)</a><br>';
        $rst->movenext();
    }
    $rst->close();
}

$sql = "SELECT " . $con->Concat("company_code", "' '", "company_name") . ", company_id AS company2_id FROM companies WHERE company_record_status = 'a' ORDER BY company_code";
$rst = $con->execute($sql);
$company_menu = $rst->getmenu2('company2_id', '', false);
$rst->close();

$con->close();

$relation_array = array("Acquired", "Acquired by", "Consultant for", "Retains consultant", "Manufactures for", "Uses manufacturer", "Subsidiary of", "Parent company of", "Alternate address for", "Parent address for");

$relation_menu = "<select name=relation>";

for ($i = 0; $i < sizeof($relation_array); $i++) {
	$relation_menu .= "\n<option value='" . $i . "'";
	if ($relation == $relation_array[$i]) {
		$relation_menu .= " selected";
	}
	$relation_menu .= ">" . $relation_array[$i];
}

$relation_menu .= "\n</select>";


$page_title = $company_name . " - " . _("Former Names");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <!-- former name //-->
        <form action=add-former-name.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2><?php echo _("Former Names"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company"); ?></td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Former Names"); ?></td>
                <td class=widget_content_form_element><?php  echo $former_name_rows; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Former Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=former_name size=30></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Add Former Name"); ?>"></td>
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
 * $Log: former-names.php,v $
 * Revision 1.5  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.4  2004/07/30 11:23:38  cpsource
 * - Do standard msg processing
 *   Default use_pretty_address in new-2.php set to null
 *
 * Revision 1.3  2004/07/21 19:17:57  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.2  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.1  2004/05/06 13:34:30  gpowers
 * This implements a separate screen for editing Former Names.
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
