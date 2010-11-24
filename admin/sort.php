<?php
/**
 * Description: This file re-sorts the statuses when the user clicks
 *   the 'up' or 'down' button.
 *
 * @author Brad Marshall
 *
 * $Id: sort.php,v 1.9 2010/11/24 21:53:00 gopherit Exp $
 */


require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check( 'Admin' );

$table_name         = $_GET['table_name'];
$sort_order         = (int)$_GET['sort_order'];
$direction          = $_GET['direction'];
$on_what_id         = (int)$_GET['on_what_id'];
$resort_id          = (int)$_GET['resort_id'];
$activity_template  = $_GET['activity_template'];
$short_fields       = $_GET['short_fields']; //allows un-prefixed database columns
$return_url         = $_GET['return_url'];

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
    $swap_check = '>=';
} else if ($direction == 'up') {
    $swap = $sort_order - 1;
    $swap_check = '<=';
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

// Retrieve a record set which contains the two rows to be swapped.
$sql = 'SELECT '. $fields['sort_order'] .', '. $fields['id'].
		' FROM '. $table_name_plural.
		' WHERE '. $fields['sort_order'] .' '. $swap_check .' '. $sort_order;
if ($activity_template == 1) {
    $sql .= ' AND on_what_table = '. $con->qstr($on_what_table). ' AND on_what_id = '. $on_what_id;
}
if ($type_id) {
    $sql .= ' AND '. $fields['type'] .' = '. $type_id;
}
$sql .= ' AND '. $fields['record_status'] .' = "a"'.
        ' ORDER BY '. $fields['sort_order'];


$rst = $con->execute($sql);
if (!$rst) { db_error_handler($con, $currentsql); }

// Scan the recordset for the two rows to be swapped
if ($rst->numRows() > 1) {

    if ($direction == 'down') {
        // For swapping down, find the first suitable record
        // When we have multiple records with the same sort_order value this may
        // not necessarily be the very first record.  The while() loop here IS necessary!
        while ( (!$rst->EOF) AND ($resort_id) AND ($rst->fields[$fields['id']] != $resort_id) ) {
            $rst->MoveNext();
        }
        $source_id = $rst->fields[$fields['id']];
        $dest_sort_order = $rst->fields[$fields['sort_order']];
        // And grab the record immediately after it
        $rst->MoveNext();
        if (!$rst->EOF) {
            $dest_id = $rst->fields[$fields['id']];
            $source_sort_order = $rst->fields[$fields['sort_order']];
        }
    }

    if ($direction == 'up') {
        // For swapping up
        // Ordering the record set with DESC does not quite give the expected
        // results in cases when there are multiple records with the same
        // sort_order values so we'll just have to traverse the record set to
        // get to the last two rows
        while ( ( (!$rst->EOF) AND (!$resort_id) ) OR
                ( (!$rst->EOF) AND ($resort_id) AND ($rst->fields[$fields['id']] != $resort_id) ) ) {
            // First store the previous record
            $tmp_id = $rst->fields[$fields['id']];
            $tmp_sort_order = $rst->fields[$fields['sort_order']];
            $rst->MoveNext();
            // Now find the last suitable record and swap with the previous
            if (!$rst->EOF) {
                $dest_id = $tmp_id;
                $source_sort_order = $tmp_sort_order;
                $source_id = $rst->fields[$fields['id']];
                $dest_sort_order = $rst->fields[$fields['sort_order']];
            }
        }
    }
}
$rst->close();

// One last quirk if the two records have the same sort_order value; we'll push
// the record we are swapping by 1.  We have no choice or it won't swap.
//
// @TODO: We would get better results if we resort the rest of the record set
// just to clear it all up but this works good enough for now.  In addition, do
// we really want to force the user to use sequential sort_order values throughout?  Maybe
// they have a reason to use duplicates even though it escapes me why they would.
if ($source_sort_order == $dest_sort_order)
    $source_sort_order = $swap;


if ($source_id AND $source_sort_order) {
    //swap sort_order and insert into the table
    $sql = 'SELECT * FROM ' . $table_name_plural . ' WHERE ' . $fields['id'] .' = '.$source_id;
    $rst = $con->execute($sql);
    $rec = array();
    $rec['sort_order'] = $source_sort_order;
    
    $upd = $con->GetUpdateSQL($rst, $rec, $forceUpdate=false, $magicq=get_magic_quotes_gpc());
    $rst = $con->execute($upd);
}

if ($dest_id AND $dest_sort_order) {
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
 *Revision 1.9  2010/11/24 21:53:00  gopherit
 *FIXED Bug ID 3117854
 ** the script now assumes that there may always be multiple records with the same sort_order value.  The allowMultiple parameter has been eliminated.
 ** the script now accurately 'swaps' records even if there are gaps in the sort_order numbering between them
 ** if there are multiple records with the same sort_order value the script tries to make as accurate of a 'swap' as possible without forcing a complete renumbering of the entire record set.
 *
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
