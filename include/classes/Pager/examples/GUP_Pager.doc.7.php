<?php

/**
Another pager example showing Calculated Columns and callback usage

As you'll notice, the callback is only called for the rows that are being displayed.
However, if the user clicks to sort on a column that is calculated, the entire data
set will be fetched.

*/

require_once('../../../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Session_Var_Watcher.php');
require_once($include_directory . 'adodb/adodb.inc.php');


$session_user_id = session_check();

start_page();

?>

<p>
The thing to notice in this example is the use of the SessionVarWatcher class, which will
watch a CGI variable and flush the cache if the value has changed since the last page draw.
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
$columns[] = array('name' => 'User Ticket', 'index_calc' => 'user_ticket');

$columns[] = array('name' => 'Email', 'index_sql' => 'email');
$columns[] = array('name' => 'Activity Title', 'index_sql' => 'activity_title');
$columns[] = array('name' => 'Activity Description', 'index_sql' => 'activity_description');


// constructor: GUP_Pager(&$db, $sql, $data, $caption, $form_id, $pager_id='gup_pager', $column_info, $use_cached = true)
$pager = new GUP_Pager($con, $sql, 'get_calculated_row', 'List of Activities', 'activities_form', 'example7_ActivitiesPager', $columns, true);

$pager->SetDebug();


$var_watcher = new SessionVarWatcher('Pager_Example_7');
$var_watcher->RegisterCGIVars(array('user_input','user_input_2'));

if($var_watcher->VarsChanged()) {
    $pager->FlushCache();
}



echo '<form name="activities_form" method=post>';
echo 'User Input:<input type="text" name="user_input" value="23"><br>';
echo 'User Input 2:<input type="text" name="user_input_2" value="42"><br>';
echo '<input type="submit" name="sub" value="Re-Submit Form"><br>';



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

	if($row) {
		$row['user_ticket'] = strrev($row['username']) .'-'. md5($row['email'] . $row['activity_title']);
	}

	return $row;
}


?>

