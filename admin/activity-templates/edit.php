<?php
/**
 * Manage activity templates
 *
 * $Id: edit.php,v 1.2 2004/06/14 20:50:11 introspectshun Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();

$activity_template_id = $_GET['activity_template_id'];
$on_what_table = $_GET['on_what_table'];
$on_what_id = $_GET['on_what_id'];
$return_url = $_GET['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

$sql = "select * from activity_templates where activity_template_id = $activity_template_id";

$rst = $con->execute($sql);

if ($rst) {
	
	$activity_title = $rst->fields['activity_title'];
	$activity_description = $rst->fields['activity_description'];
	$activity_type_id = $rst->fields['activity_type_id'];
	$duration = $rst->fields['duration'];	
	
	$rst->close();
}

//get activity type menu
$sql = "select activity_type_pretty_name, activity_type_id from activity_types where activity_type_record_status = 'a'";
$rst = $con->execute($sql);
$activity_type_menu = $rst->getmenu2('activity_type_id', $activity_type_id, false);
$rst->close();

$con->close();


$page_title = "Activity Template : " . ucwords($activity_title);
start_page($page_title);

?>

<div id="Main">
    <div id="Content">

		<form action=edit-2.php method=post>
		<input type=hidden name=activity_template_id value="<?php  echo $activity_template_id; ?>">
		<input type=hidden name=return_url value="<?php echo $return_url; ?>">
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=4>Edit Activity Template Information</td>
			</tr>
            		<tr>
                		<td class=widget_label_right>Title</td>
				<td class=widget_content_form_element><input type=text size=40 name="activity_title" value='<?php echo $activity_title; ?>'></td>
			</tr>
			<tr>
                		<td class=widget_label_right>Duration</td>
				<td class=widget_content_form_element><input type=text size=10 name="duration" value='<?php echo $duration; ?>'></td>
			</tr>
			<tr>
				<td class=widget_label_right>Type</td>
				<td class=widget_content_form_element><?php echo $activity_type_menu; ?></td>
			</tr>
                        <tr>
                                <td class=widget_label_right_166px>Description</td>
				<td class=widget_content_form_element><textarea rows=8 cols=100 name="activity_description"><?php echo $activity_description ?></textarea></td>
                        </tr>
			<tr>
				<td class=widget_content colspan=2><input class=button type=submit value="Save Changes"></td>
			</tr>
		</table>
		</form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

	<form action=delete.php method=post>
        <input type=hidden name=activity_template_id value="<?php  echo $activity_template_id; ?>" onsubmit="javascript: return confirm('Delete Activity Template?');">
	<input type=hidden name=on_what_table value="<?php echo $on_what_table; ?>">
	<input type=hidden name=on_what_id value="<?php echo $on_what_id; ?>">
	<input type=hidden name=return_url value="<?php echo $return_url; ?>">
        <table class=widget cellspacing=1>
             <tr>
                   <td class=widget_header colspan=4>Delete Activity Template</td>
             </tr>
             <tr>
                   <td class=widget_content>
                   Click the button below to remove this<br> activity template from the system.
                   <p>Note: This action CANNOT be undone!
                   <p><input class=button type=submit value="Delete Activity Template">
                   </td>
             </tr>
        </table>
        </form>


    </div>
</div>

<?php

end_page();

/**
 * $Log: edit.php,v $
 * Revision 1.2  2004/06/14 20:50:11  introspectshun
 * - Add adodb-params.php include for multi-db compatibility.
 * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
 *
 * Revision 1.1  2004/06/03 16:11:53  braverock
 * - add functionality to support workflow and activity templates
 *   - functionality contributed by Brad Marshall
 *
 * Revision 1.3  2004/04/16 22:18:24  maulani
 * - Add CSS2 Positioning
 *
 * Revision 1.2  2004/04/08 16:56:47  maulani
 * - Update javascript declaration
 * - Add phpdoc
 *
 *
 */
?>
