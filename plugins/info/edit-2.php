<?php
/**
 * Insert item details into the database
 *
 * $Id: edit-2.php,v 1.6 2005/01/08 06:25:14 gpowers Exp $
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

# Always retrieve, and pass on, server and company ID
$info_id = $_POST['info_id'];
$company_id = $_POST['company_id'];
$division_id = $_POST['division_id'];
$contact_id = $_POST['contact_id'];
$info_type_id = $_POST['info_type_id'];
$return_url = $_POST['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

# If this is a new server then $info_id will be zero, so create server
if (0 == $info_id) {
  $sql = "INSERT INTO info_map (company_id,division_id,contact_id,info_type_id) VALUES ('$company_id','$division_id','$contact_id','$info_type_id')";
  $status = $con->execute($sql);
  if (!$status) {
    db_error_handler ($con, $sql);
    exit;
  }
  $info_id = $con->Insert_ID();
  if (0 == $info_id) {
    echo "Error inserting new server into tables";
    exit;
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

# Get existing values for this server into an array
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
# All relevant $_POST variables start "element_", so build an array of
# just them indexed by element_id
$passed_values = array();
foreach ($_POST as $key=>$value) {
  if (strncmp($key, "element_", 8) != 0) {
    continue;
  }
  else {
    $passed_values[substr($key, 8)] = $value;
  }
}

# Check all existing values against those passed. Any which were not
# passed must be now-cleared checkboxes, so add them to the passed_values array
foreach ($existing_values as $element_id=>$exising_value) {
  if (!array_key_exists($element_id, $passed_values)) {
    $passed_values[$element_id] = 0;
  }
}

# Process all passed variables and generate SQL if they
# either don't exist or have changed from existing_values
foreach ($passed_values as $element_id=>$value) {
  if (array_key_exists($element_id, $existing_values)) {
    # element already exists; update it if it has changed
    if ($value != $existing_values[$element_id]) {
      $sql = "UPDATE info SET value = '$value'";
      $sql .= " WHERE element_id = '$element_id'";
      $sql .= " AND info_id = '$info_id'";
      $status = $con->execute($sql);
      if (!$status) {
        db_error_handler ($con, $sql);
        exit;
      }
    }
  }
  else {
    # This is a new element for this server
    $sql = "INSERT INTO info (info_id, element_id, value) ";
    $sql .= "VALUES ('$info_id', '$element_id', '$value')";
    $status = $con->execute($sql);
    if (!$status) {
      db_error_handler ($con, $sql);
      exit;
    }
  }
}

$con->close();

header("Location: " . $return_url);

?>
