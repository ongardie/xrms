<?php

/**

Another pager example showing Calculated Columns and callback usage


As you'll notice, the callback is only called for the rows that are being displayed.
However, if the user clicks to sort on a column that is calculated, the entire data
set will be run.


*/

require_once('../../../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'adodb/adodb.inc.php');


$session_user_id = session_check();

start_page();

?>

<h3>
Another pager example showing Calculated Columns and callback usage
</h3>

<p>
As you'll notice, the callback is only called for the rows that are being displayed.
However, if the user clicks to sort on a column that is calculated, the entire data
set will be run.
</p>


<?php


if(check_user_role(false, $session_user_id, 'Administrator')) {

global $con;

$con = get_xrms_dbconnection();


//Let's assume that we have a query like:
$sql = 'SELECT u.user_id, u.username, u.email, a.activity_title, a.activity_description FROM users u, activities a WHERE u.user_id = a.user_id';

// Set up the column_info array describing the data
$colums = array();
$columns[] = array('name' => 'User Name', 'index_sql' => 'username');
$columns[] = array('name' => 'User ID', 'index_sql' => 'user_id');

// This column doesn't exist in the SQL, we will be setting it in get_calculated_row
$columns[] = array('name' => 'User hash', 'index_calc' => 'user_hash');

$columns[] = array('name' => 'Email', 'index_sql' => 'email');
$columns[] = array('name' => 'Activity Title', 'index_sql' => 'activity_title');
$columns[] = array('name' => 'Activity Description', 'index_sql' => 'activity_description');

echo "Note: you may need to hit the pager's refresh button if you are wondering why your callback isn't being called when it seems that it should be<br/>";


// constructor: GUP_Pager(&$db, $sql, $data, $caption, $form_id, $pager_id='gup_pager', $column_info, $use_cached = true)
$pager = new GUP_Pager($con, $sql, 'get_calculated_row', 'List of Activities', 'activities_form', 'example4_ActivitiesPager', $columns, true);

echo '<form name="activities_form" method=post>';
// output the html that is the pager.
$pager->Render(10);
echo '</form>';

} else {
    echo _("Examples are viewable by Administrators only");
}


end_page();

global $i;
$i=0;

function get_calculated_row($row) {
	global $i;
	$i++;

	echo "row $i ";
	if($row) {
		$row['user_hash'] = md5($row['email'] . $row['activity_title']);
	}

	return $row;
}


?>

