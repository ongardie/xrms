<?php
/**
 * Description: This file re-sorts the statuses when the user clicks
 *   the 'up' or 'down' button.
 *
 * @author Brad Marshall
 *
 * $Id: sort.php,v 1.8 2006/05/29 06:16:58 ongardie Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$direction = $_GET['direction'];
$sort_order = $_GET['sort_order'];
$resort_id = $_GET['resort_id'];
$table_name = $_GET['table_name'];
$on_what_id = $_GET['on_what_id'];
$return_url = $_GET['return_url'];
$activity_template = $_GET['activity_template'];
$allowMultiple=$_GET['allowMultiple'];
$short_fields=$_GET['short_fields']; //allows un-prefixed database columns

$con = get_xrms_dbconnection();
//$con->debug=1;

//if it's sorting activity template links, set the vars
if ($activity_template == 1) {
    $table_name_plural = "activity_templates";
    $on_what_table = $table_name . "es";
    $table_name = "activity_template";
} else {
    if(substr($table_name, -1, 1) == "e") {
        $table_name_plural = $table_name . "s";
    }
    else {
        $table_name_plural = $table_name . "es";
    }
    $on_what_table = $table_name;
}

//handle incoming data
if ($direction == 'down') {
    $swap = $sort_order + 1;
} else if ($direction == 'up') {
    $swap = $sort_order - 1;
}

//refers to database columns
$fields = array('id' => 'id', 'name' => 'name', 'record_status' => 'record_status');
if(!$short_fields){
	//add prefixes
	foreach($fields as $key => $field_name)
		$fields[$key] = $table_name.'_'.$field_name;
}
$fields['sort_order'] = 'sort_order'; //never pre-fixed

//special cases for $fields
if ($table_name == 'activity_resolution_type')
    $fields['record_status'] = 'resolution_type_record_status';

$type_id = false;
if($on_what_table == 'case_status'){
	$fields['type'] = 'case_type_id';
	$type_id = $_GET['case_type_id'];
}

//retrieve the sort_order and id value in the two rows to be swapped
$currentsql = 'SELECT '.$fields['sort_order'].', '.$fields['id'].
		' FROM '.$table_name_plural.
		' WHERE '.$fields['sort_order'].'='.$sort_order;
if ($resort_id)
    $currentsql .= ' AND '.$fields['id'].'='.$resort_id;

$swapsql = 'SELECT '.$fields['sort_order'].', '.$fields['id'].
		' FROM '.$table_name_plural.
		' WHERE '.$fields['sort_order'].'='.$swap;

$sql = '';
if ($activity_template == 1) {
        $sql .= ' AND on_what_table='.$con->qstr($on_what_table).' AND on_what_id='.$on_what_id;
}
if ($type_id) {
    $sql .= ' AND '.$fields['type'].'='.$type_id;
}

$sql .= ' AND '.$fields['record_status'].'="a"';

//echo $sql;
$rst = $con->execute($currentsql.$sql);
if (!$rst) { db_error_handler($con, $currentsql); }

    //get field data for the first row
    $source_id = $rst->fields[$fields['id']];
    $dest_sort_order = $rst->fields[$fields['sort_order']];

if (!$dest_sort_order) $dest_sort_order=$sort_order;

$rst->close();


$rst = $con->execute($swapsql.$sql);;

if ($rst->numRows()==1) {
    //get field data for the second row
    $dest_id = $rst->fields[$fields['id']];
    $source_sort_order = $rst->fields[$fields['sort_order']];
}

if (!$source_sort_order) { $source_sort_order=$swap; }

$rst->close();

if ($allowMultiple) $dest_id=false;


//echo "dest_id: $dest_id<br> sso: $source_sort_order s_id: $source_id dest: $dest_sort_order<br>";

if ($source_id) {
    //swap sort_order and insert into the table
    $sql = 'SELECT * FROM ' . $table_name_plural . ' WHERE ' . $fields['id'] .' = '.$source_id;
    $rst = $con->execute($sql);
    $rec = array();
    $rec['sort_order'] = $source_sort_order;
    
    $upd = $con->GetUpdateSQL($rst, $rec, $forceUpdate=false, $magicq=get_magic_quotes_gpc());
    $rst = $con->execute($upd);
}

if ($dest_id) {
    $sql = 'SELECT * FROM ' . $table_name_plural . ' WHERE ' . $fields['id']. ' = '.$dest_id;
    $rst = $con->execute($sql);

    $rec = array();
    $rec['sort_order'] = $dest_sort_order;
    
    $upd = $con->GetUpdateSQL($rst, $rec, $forceUpdate=false, $magicq=get_magic_quotes_gpc());
    $rst = $con->execute($upd);
}
//reload the page to see the new order
header ('Location: ' . $http_site_root . $return_url);

/**
 *$Log: sort.php,v $
 *Revision 1.8  2006/05/29 06:16:58  ongardie
 *- Allows for non-prefixed database column names when given $_GET['short_fields'].
 *
 *Revision 1.7  2006/01/02 22:38:16  vanmer
 *- changed to use centralized dbconnection function
 *
 *Revision 1.6  2005/06/30 04:41:54  vanmer
 *- added special handling for shortened record_status name on activity resolutions
 *
 *Revision 1.5  2005/01/11 22:22:26  vanmer
 *- altered sort to properly sort lists with multiple entities with the same sort_order
 *- added flag for allowMultiple to engage this functionality
 *- added resort_id parameter to specify which element is to be altered (instead of just sort_order)
 *
 *Revision 1.4  2004/07/16 18:52:43  cpsource
 *- Add role check inside of session_check
 *
 *Revision 1.3  2004/06/24 20:02:53  braverock
 *- minor enhancements to sort functionality
 *- add phpdoc
 *
 */
?>
