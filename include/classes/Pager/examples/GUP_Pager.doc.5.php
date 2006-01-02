<?php

/**

Simple example of basic pager usage with Data


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
Simple example of basic pager usage with Array (not SQL)
</h3>



<?php


if(check_user_role(false, $session_user_id, 'Administrator')) {

global $con;

$con = get_xrms_dbconnection();

//Let's assume that we have a query like:
$data = array();
$data[] = array('jane', 2, 2);
$data[] = array('bob', 4, 8);
$data[] = array('steve', 8, 13);
$data[] = array('ted', 9, 3);
$data[] = array('sally', 2, 23);
$data[] = array('margaret',12, 93);
$data[] = array('chandra', 9, 83);
$data[] = array('esmee', 32, 43);
$data[] = array('yukiko', 52, 53);
$data[] = array('salam', 23, 31);
$data[] = array('paul', 22, 32);
$data[] = array('paulette', 42, 33);

// Set up the column_info array describing the data
$colums = array();
$columns[] = array('name' => 'Name', 'index_data' => 0);
$columns[] = array('name' => 'Number of Pets', 'index_data' => 1);
$columns[] = array('name' => 'Cost per day', 'index_data' => 2);

echo "Note: you may need to hit the pager's refresh button if you are wondering why your callback isn't being called when it seems that it should be<br/>";


// constructor: GUP_Pager(&$db, $sql, $data, $caption, $form_id, $pager_id='gup_pager', $column_info, $use_cached = true)
$pager = new GUP_Pager($con, '', $data, 'List of Activities', 'activities_form', 'example5_ActivitiesPager', $columns, true);

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

