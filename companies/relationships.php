<?php
/**
 * Edit company relationships
 *
 * $Id: relationships.php,v 1.4 2004/05/06 13:33:04 gpowers Exp $
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

$sql = "select r.relationship_type, r.established_at, r.company_to_id, c.company_name, r.company_from_id
from company_relationship r, companies c 
where r.company_from_id = $company_id 
and r.company_to_id=c.company_id 
order by r.established_at desc";

$rst = $con->execute($sql);

if ($rst) {
    while (!$rst->EOF) {
        $established_at = $con->userdate($rst->fields['established_at']);
        $relationship_rows .= '<tr>';
        $relationship_rows .= '<td class=widget_content_form_element>' . $rst->fields['relationship_type'] . '</td>';
        $relationship_rows .= '<td class=widget_content_form_element>' . $rst->fields['company_name'] . '</td>';
        $relationship_rows .= '<td class=widget_content_form_element>' . $established_at . '</td>';
        $relationship_rows .= '<td class=widget_content_form_element>' 
            . '<a href="delete-relationship.php?company_to_id=' . $rst->fields['company_to_id']
            . '&company_from_id=' . $rst->fields['company_from_id']
            . '&relationship_type=' . $rst->fields['relationship_type'] . '">(Delete)</a>'
            . '</td>';
        $relationship_rows .= '</tr>';
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


$page_title = $company_name . " - Relationships";
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">
        <form action=add-relationship.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=4>Relationships</td>
            </tr>
			<tr>
				<td class=widget_label>Relationship</td>
				<td class=widget_label>Company</td>
				<td class=widget_label>Date</td>
				<td class=widget_label></td>
			</tr>
            <?php  echo $relationship_rows; ?>
            <tr>
            	<td><?php echo $relation_menu ?></td>
            	<td><?php echo $company_menu ?></td>
			</tr>
            <tr>
                <td class=widget_content_form_element colspan=4><input class=button type=submit value="Add Relationship"></td>
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
