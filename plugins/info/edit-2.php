<?php
/**
 * Insert item details into the database
 *
 * $Id: edit-2.php,v 1.9 2005/03/18 20:54:37 gpowers Exp $
 */
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

require_once('info.inc');

$session_user_id = session_check();

$msg = $_POST['msg'];

# Always retrieve, and pass on, info, contact, and company ID
$info_id = $_POST['info_id'];
$new_info = $_POST['new_info'];
$company_id = $_POST['company_id'];
$division_id = $_POST['division_id'];
$contact_id = $_POST['contact_id'];
$info_type_id = $_POST['info_type_id'];
$return_url = $_POST['return_url'];

global $http_site_root;

if (!$info_type_id) {
	$info_type_id = 0;
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

# If this is a new info item then $info_id will be zero, so create info item
# Note that we cannot simply add a null array as AutoExecute seems to
# optimise it out
//if (0 == $info_id) {

# If this is a new info item and 
#$info_id is set, then we creating instances of new elements
#$info_id is not set, then we creating instances for the first time.
if ($new_info==true) {
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
    $k=$info_id;
    $info_id = $con->insert_id();
  }
}

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

	$sql = "SELECT element_id,value FROM info WHERE info_id='$info_id'";
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

$con->close();

header("Location: " .$http_site_root.$return_url);

/**
 * $Log: edit-2.php,v $
 * Revision 1.9  2005/03/18 20:54:37  gpowers
 * - added support for inline (custom fields) info
 *
 * Revision 1.8  2005/02/11 00:49:11  braverock
 * - modified to correctly pass contact_id and return_url
 *
 */
?>
