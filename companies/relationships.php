<?php

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
        $former_name_rows .= $rst->fields['former_name'] . '<br>';
        $rst->movenext();
    }
    $rst->close();
}


$sql = "select r.relationship_type, r.established_at, r.company_to_id, c.company_name 
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


$page_title = $company_name . " - Names and Relationships";
start_page($page_title, true, $msg);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
    <tr>
        <td class=lcol width=65% valign=top>

        <!-- former name //-->
        <form action=add-former-name.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1 width=100%>
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

        <form action=add-relationship.php method=post>
        <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
        <table class=widget cellspacing=1 width=100%>
            <tr>
                <td class=widget_header colspan=3>Relationships</td>
            </tr>
			<tr>
				<td class=widget_label>Relationship</td>
				<td class=widget_label>Company</td>
				<td class=widget_label>Date</td>
			</tr>
            <?php  echo $relationship_rows; ?>
            <tr>
            	<td><?php echo $relation_menu ?></td>
            	<td><?php echo $company_menu ?></td>
			</tr>
            <tr>
                <td class=widget_content_form_element colspan=3><input class=button type=submit value="Add Relationship"></td>
            </tr>
        </table>
        </form>

        </td>
        <!-- gutter //-->
        <td class=gutter width=1%>
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width=34% valign=top>

        </td>
    </tr>
</table>

<?php end_page();; ?>