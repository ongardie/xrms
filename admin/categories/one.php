<?php

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$category_id = $_GET['category_id'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from categories where category_id = $category_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$category_short_name = $rst->fields['category_short_name'];
	$category_pretty_name = $rst->fields['category_pretty_name'];
	$category_pretty_plural = $rst->fields['category_pretty_plural'];
	$category_display_html = $rst->fields['category_display_html'];
	
	$rst->close();
}

// associated with

$sql = "select cs.category_scope_id, category_scope_pretty_plural 
from category_scopes cs, category_category_scope_map ccsm 
where cs.category_scope_id = ccsm.category_scope_id 
and ccsm.category_id = $category_id 
and category_scope_record_status = 'a' 
order by category_scope_pretty_name";

$rst = $con->execute($sql);
$array_of_category_scopes = array();
array_push($array_of_category_scopes, 0);

if ($rst) {
	while (!$rst->EOF) {
		$associated_with .= "<a href='remove-scope.php?category_id=$category_id&category_scope_id=" . $rst->fields['category_scope_id'] . "'>" . $rst->fields['category_scope_pretty_plural'] . "</a><br>";
		array_push($array_of_category_scopes, $rst->fields['category_scope_id']);
		$rst->movenext();
	}
	$rst->close();
}

// not associated with

$sql = "select category_scope_id, category_scope_pretty_plural 
from category_scopes cs 
where category_scope_id not in (" . implode($array_of_category_scopes, ',') . ") 
and category_scope_record_status = 'a' 
order by category_scope_pretty_name";

$rst = $con->execute($sql);

if ($rst) {
	while (!$rst->EOF) {
		$not_associated_with .= "<a href='add-scope.php?category_id=$category_id&category_scope_id=" . $rst->fields['category_scope_id'] . "'>" . $rst->fields['category_scope_pretty_plural'] . "</a><br>";
		$rst->movenext();
	}
	$rst->close();
}

$con->close();

$page_title = "One Category : $category_pretty_name";
start_page($page_title);

?>

<table border=0 cellpadding=0 cellspacing=0 width=100%>
	<tr>
		<td class=lcol width=25% valign=top>
		
		<form action=edit-2.php method=post>
		<input type=hidden name=category_id value="<?php  echo $category_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Edit Category Information</td>
			</tr>
			<tr>
				<td class=widget_label_right>Short Name</td>
				<td class=widget_content><input type=text name=category_short_name value="<?php  echo $category_short_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Name</td>
				<td class=widget_content><input type=text name=category_pretty_name value="<?php  echo $category_pretty_name; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Full Plural</td>
				<td class=widget_content><input type=text name=category_pretty_plural value="<?php  echo $category_pretty_plural; ?>"></td>
			</tr>
			<tr>
				<td class=widget_label_right>Display HTML</td>
				<td class=widget_content><input type=text name=category_display_html value="<?php  echo $category_display_html; ?>"></td>
			</tr>
			<tr>
				<td class=widget_content colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

		<form action=delete.php method=post>
		<input type=hidden name=category_id value="<?php  echo $category_id; ?>">
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=4>Delete Category</td>
			</tr>
			<tr>
				<td class=widget_content>
				Click the button below to remove this category from the system.
				<p>Note: This action CANNOT be undone!
				<p><input class=button type=submit value="Delete">
				</td>
			</tr>
		</table>
		</form>
		
		</td>
		
		<!-- gutter //-->
		<td class=gutter width=2%>
		&nbsp;
		</td>
		
		<!-- right column //-->
		
		<td class=rcol width=73% valign=top>

		<!-- category scopes //-->
		<table class=widget cellspacing=1 width=100%>
			<tr>
				<td class=widget_header colspan=2>Category Scopes</td>
			</tr>
			<tr>
				<td class=widget_label>Associated With</td>
				<td class=widget_label>Not Associated With</td>
			</tr>
			<tr>
				<td class=widget_content><?php  echo $associated_with; ?></td>
				<td class=widget_content><?php  echo $not_associated_with; ?></td>
			</tr>
		</table>

		</td>
		
	</tr>
</table>

<?php end_page();; ?>
