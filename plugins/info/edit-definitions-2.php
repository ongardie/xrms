<?php
/**
 * Edit info element definitions
 *
 * $Id: edit-definitions-2.php,v 1.6 2005/03/18 20:54:37 gpowers Exp $
 */

require_once('../../include-locations.inc');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-accounting.php');

require_once('info.inc');

$session_user_id = session_check();

$msg = $_POST['msg'];

# Always retrieve, and pass on, server and company ID
$info_id = $_POST['info_id'];
$post_info_type_id = $_POST['post_info_type_id'];
$company_id = $_POST['company_id'];
$return_url = urldecode($_POST['return_url']);

if (!$info_type_id) {
	$info_type_id = "0";
}

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$con_write = &adonewconnection($xrms_db_dbtype);
$con_write->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con_write->debug = 1;

# The challenge here is that the user may have cleared a previously-set
# checkbox. By default, no value is return from a form for cleared checkbox
# (PEAR's 'advchkbox' gets around this). Also, there is no point in updating
# fields which have not changed. Finally, it is possible that a new element
# has been added for which there is no value in the database for this server,
# so an INSERT rather than an UPDATE is required.

$columns = array(
  'element_label',
  'element_type',
  'element_column',
  'element_order',
  'element_default_value',
  'element_possible_values',
  'element_display_in_sidebar',
  'element_enabled',
  'info_type_id',
);

# The submitted $label array will have an entry for every element_id (because it is
# guaranteed to be a text type rather than, say, a checkbox). Use that to build an
# array indexed by element_id, each array element contaning the details for that element

if (!$_POST['element_label']) {
    $_POST['element_label'] = "Name";
}

$new_element = false;
foreach ($_POST['element_label'] as $element_id=>$dummy) {
    if (0 == $element_id) {
        $new_element = true;
    }
    $tmp = array();
    foreach($columns as $column) {
        $tmp[$column] = $_POST[$column][$element_id];
        if (($column == 'info_type_id') && (!$tmp[$column])) {
            $tmp[$column] = $info_type_id[$element_id];
        }
        # Always enable "name" type and don't allow empty string for label
        if (($column ==  'element_type') && 
            ($tmp[$column] == 'name') &&
            (!$tmp['element_label'])) {
                $tmp[$column] = _("Name");
        }
    }

    $submitted[$element_id] = $tmp;
}

# Get exising values and check against those submitted
$sql = "SELECT * FROM info_element_definitions WHERE info_type_id=" . $post_info_type_id;
$all_elements = $con->execute($sql);

if ($all_elements) {
    while (!$all_elements->EOF) {
        $element_id = $all_elements->fields['element_id'];
        $rec = array();
        foreach($columns as $column) {
            $submitted_value = $submitted[$element_id][$column];
            # If element_enabled or element_display_in_sidebar is NULL
            # then set to 0
            if (("element_enabled" == $column) || 
                            ('element_display_in_sidebar' == $column)) {
                if (is_null($submitted_value)) {
                    $submitted_value = 0;
                }
            }
            $rec[$column] = $submitted_value;
        }
        $tbl = 'info_element_definitions';
        if (!$con->AutoExecute($tbl, $rec, 'UPDATE',
                    "element_id = $element_id")) {
            db_error_handler ($con, $upd);
        }
        $all_elements->movenext();
    }
}

# Now check for new element and add it if it exists
if ($new_element) {
#    $fields = array();
#    $values = array();
    $rec = array();
    foreach ($submitted[0] as $field=>$value) {
        $rec[$field] = $value;
        # If element_enabled or element_display_in_sidebar is NULL
        # then set to 0
        if (("element_enabled" == $field) || 
                        ('element_display_in_sidebar' == $field)) {
            if (is_null($value)) {
                $rec[$field] = 0;
            }
        }
    }


    $tbl = 'info_element_definitions';
    if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
        db_error_handler ($con, $ins);
    }
}

$con->close();
$con_write->close();

header("Location: " . $http_site_root . $return_url);

?>