<?php
/**
 * Insert item details into the database
 *
 * $Id: inline-edit-2.php,v 1.2 2005/04/01 20:08:21 ycreddy Exp $
 */


# Always retrieve, and pass on, info, contact, and company ID
global $_POST, $display_on, $company_id, $contact_id;

if (!$company_id) {
	$company_id = $_POST['company_id'];
}

if (!$info_type_id) {
	$info_type_id = $_POST['info_type_id'];
}

$info_id = $_POST['info_id'];
$new_info = $_POST['new_info'];
$division_id = $_POST['division_id'];
$return_url = $_POST['return_url'];

if (!$contact_id) {
    $contact_id = 0;
}
    
    // Find the elements to display in sidebar under name
    // (there may be none)
    $sql = "SELECT info_type_id ";
    $sql .= "FROM info_display_map ";
    $sql .= "WHERE display_on = '" . $display_on . "' ";
    $rst = $con->SelectLimit($sql);

    if ($rst) {
        if (!$rst->EOF) {
            $info_type_id = $rst->fields['info_type_id'];
        }
    }
     
// $new_info = false;

# If this is a new info item then $info_id will be zero, so create info item
# Note that we cannot simply add a null array as AutoExecute seems to
# optimise it out
//if (0 == $info_id) {

# If this is a new info item and 
#$info_id is set, then we creating instances of new elements
#$info_id is not set, then we creating instances for the first time.
//if ($new_info==true) {
  if ($info_id == 0 ) {
    $tbl = 'info_map';
    $rec = array();
    $rec['company_id'] = $company_id;
    $rec['contact_id'] = $contact_id;
    $rec['division_id'] = $division_id;
    $rec['info_type_id'] = $info_type_id;
    if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
        db_error_handler ($con, $ins);
    }

    $info_id = $con->insert_id();
  }
//}

# Process edited values passed from user for this server. There are some
# challenges here:
#  - the user may have cleared a previously-set checkbox. By default, no value is
#    return from a form for cleared checkbox (PEAR's 'advchkbox' gets around this).
# -  There is no point in updating fields which have not changed
# -  It is possible that, since this server was last edited (or defined),  a new element
#    has been defined for which there is no value
#    in the database for this server, so an INSERT rather than an UPDATE is required.

# Get existing values for this server into an array if $new_info is not set

if ($new_info != true ) {

	$sql = "SELECT element_id,value FROM info WHERE info_id='" . $info_id . "' ";
	$rst = $con->execute($sql);
	if (!$rst) {
	  db_error_handler ($con, $sql);
	  exit;
	}
	$existing_values = array();
	while (!$rst->EOF) {
  		$existing_values[$rst->fields['element_id']] = $rst->fields['value'];
  		$rst->movenext();
	}

}
# All relevant $_POST variables start "element_", so build an array of
# just them indexed by element_id
$passed_values = array();

foreach ($_POST as $key=>$value) {
  if (strncmp($key, "element_", 8) != 0) {
    continue;
  }
  $passed_values[substr($key, 8)] = $value;
}

# Check all existing values against those passed. Any which were not
# passed must be now-cleared checkboxes, so add them to the passed_values array

# Only if $new_info is not set
if ($new_info != true ) {
   foreach ($existing_values as $element_id=>$existing_value) {
     if (!array_key_exists($element_id, $passed_values)) {
       	$passed_values[$element_id] = 0;
     }
   } 
   # Process all passed variables and generate SQL if they
   # either don't exist or have changed from existing_values
   $tbl = 'info';
   foreach ($passed_values as $element_id=>$value) {
     $rec = array();
     if (array_key_exists($element_id, $existing_values)) {
        # element already exists; update it if it has changed
        $rec['value'] = $value;
        if (!$con->AutoExecute($tbl, $rec, 'UPDATE', 
                    "element_id = $element_id AND info_id = $info_id")) {
            db_error_handler ($con, $upd);
        }
      }
      else {
        # This is a new element for this piece of info
        $rec['info_id'] = $info_id;
        $rec['element_id'] = $element_id;
        $rec['value'] = $value;
	if ( Trim($value) ) {
        	if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
            		db_error_handler ($con, $upd);
		}
        }
      }
    }
}
else {
    foreach ($passed_values as $element_id=>$value) {
        $rec = array();
   	$tbl = 'info';
 	# This is a new element for this piece of info
        $rec['info_id'] = $info_id;
        $rec['element_id'] = $element_id;
        $rec['value'] = $value;
	if ( Trim($value) ) {
        	if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
            		db_error_handler ($con, $upd);
		}
        }
    }
}

/**
 * $Log: inline-edit-2.php,v $
 * Revision 1.2  2005/04/01 20:08:21  ycreddy
 * Used the portable SelectLimit in place of LIMIT
 *
 * Revision 1.1  2005/03/21 15:11:01  gpowers
 * - inline display/edit of info items
 *
 */
?>
