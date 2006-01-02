<?php


/**

GUP basic Pager example 

*/

require_once('../../../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'adodb/adodb.inc.php');


$session_user_id = session_check();

start_page();
?>

<h3> 
GUP basic Pager example 
</h3>

<?php


if(check_user_role(false, $session_user_id, 'Administrator')) {
	$con = get_xrms_dbconnection();
	
	//Let's assume that we have a query like:
	$sql = 'SELECT u.username, u.email, a.activity_title, a.activity_description FROM users u, activities a WHERE u.user_id = a.user_id';
	
	// Set up the column_info array describing the data
	$colums = array();
	$columns[] = array('name' => 'User Name', 'index_sql' => 'username');
	$columns[] = array('name' => 'Email', 'index_sql' => 'email', 'default_sort' => 'asc');
	$columns[] = array('name' => 'Activity Title', 'index_sql' => 'activity_title');
	$columns[] = array('name' => 'Activity Description', 'index_sql' => 'activity_description');
	
	
	// constructor: GUP_Pager(&$db, $sql, $data, $caption, $form_id, $pager_id='gup_pager', $column_info, $use_cached = true)
	$pager = new GUP_Pager($con, $sql, null, 'List of Activities', 'activities_form', 'example1_ActivitiesPager', $columns, true);
	
	echo '<form name="activities_form" method=post>';
	// output the html that is the pager.
	$pager->Render(10);
	echo '</form>';

} else {
	echo _("Examples are viewable by Administrators only");
}

end_page();


?>

