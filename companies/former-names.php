<?php
/**
 * Edit company relationships
 *
 * $Id: former-names.php,v 1.1 2004/05/06 13:34:30 gpowers Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$session_user_id = session_check();

$msg = $_GET['msg'];
$company_id = $_GET['company_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
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

$sql = "select concat(company_code, ' ', company_name), company_id as company2_id from companies where company_record_status = 'a' order by company_code";
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


$page_title = $company_name . " - Former Names";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <!-- former name //-->
        <form action=add-former-name.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=2>Former Names</td>
            </tr>
            <tr>
                <td class=widget_label_right>Company</td>
                <td class=widget_content><a href="../companies/one.php?company_id=<?php echo $company_id; ?>"><?php  echo $company_name; ?></a></td>
            </tr>
            <tr>
                <td class=widget_label_right>Former Names</td>
                <td class=widget_content_form_element><?php  echo $former_name_rows; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>Former Name</td>
                <td class=widget_content_form_element><input type=text name=former_name size=30></td>
            </tr>
            <tr>
                <td class=widget_content_form_element colspan=2><input class=button type=submit value="Add Former Name"></td>
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
