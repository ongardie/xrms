<?php
/**
 * Edit info element definitions
 *
 * $Id: edit-definitions-2.php,v 1.4 2004/11/10 07:29:33 gpowers Exp $
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

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

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
    }

    $submitted[$element_id] = $tmp;
}

# Do not allow null name (element 1)
# if (empty($submitted[1])) {
#     $submitted[1] = "DEFAULTNAME";
# }

# Get exising values and check against those submitted
$sql = "SELECT * FROM info_element_definitions WHERE info_type_id=" . $post_info_type_id;
$all_elements = $con->execute($sql);

if ($all_elements) {
    while (!$all_elements->EOF) {

        $do_update = false;
        $sql2 = "UPDATE info_element_definitions SET ";

        $element_id = $all_elements->fields['element_id'];
        foreach($columns as $column) {
            $submitted_value = $submitted[$element_id][$column];
            # If element_enabled is NULL then set to 0
            if ("element_enabled" == $column) {
                if (is_null($submitted_value)) {
                    $submitted_value = 0;
                }
            }
            if ($all_elements->fields[$column] != $submitted_value) {
                # Value has changed; generate sql update clause
                $update = "$column='$submitted_value'";
                # If this is the first change just add to sql...
                if (!$do_update) {
                    $sql2 .= $update;
                    # and flag that an update is required
                    $do_update = true;
                }
                else {
                    # we need to precede sql with a comma
                    $sql2 .= ",".$update;
                }
            }
        }
        if ($do_update) {
            $sql2 .= " WHERE element_id='".$element_id."'";
            $status = $con_write->execute($sql2);
            if (!$status) {
                db_error_handler ($con_write, $sql2);
                exit;
            }
        }
        $all_elements->movenext();
    }
}

# Now check for new element and add it if it exists
if ($new_element) {
    $fields = array();
    $values = array();
    foreach ($submitted[0] as $field=>$value) {
        $fields[] = $field;
        $values[] = $value;
    }

    #echo "<pre>";var_dump($fields);echo "<br>";var_dump($values);echo "</pre>";
    $sql = "INSERT INTO info_element_definitions (";
    $first_time = true;
    foreach ($fields as $field) {
        if ($first_time) {
            $sql .= $field;
            $first_time = false;
        }
        else {
            $sql .= ", $field";
        }
    }
    $sql .= ") VALUES (";
    $first_time = true;
    foreach ($values as $value) {
        if ($first_time) {
            $sql .= "'$value'";
            $first_time = false;
        }
        else {
            $sql .= ", '$value'";
        }
    }
    $sql .= ")";
    $status = $con_write->execute($sql);
    if (!$status) {
        db_error_handler ($con, $sql);
        exit;
    }
}

$con->close();
$con_write->close();

header("Location: " . $http_site_root . $return_url);

?>
