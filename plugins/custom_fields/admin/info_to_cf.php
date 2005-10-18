<?php

# Script to migrate from info to custom_fields plugin
#
# Keith Edmunds, September 2005

require_once('../../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('../cf_functions.php');

$session_user_id = session_check( 'Admin' );

$con = connect();

# Functions for this script
function info_type_id_to_info_type_name ($info_type_id) {
	global $con;

	$sql = "SELECT info_type_name
			FROM info_types
			WHERE info_type_record_status = 'a'
			AND info_type_id = $info_type_id";
	$info_type_name = $con->GetOne($sql);
	if (False === $info_type_name) {
		echo "Error getting info_type_name for info_type_id $info_type_id<br>";
		$info_type_name = "UNKNOWN";
	}
	return $info_type_name;
}

# Check we have info and cf tables
$info_tables = array(
	"info", 
	"info_display_map", 
	"info_element_definitions",
	"info_map",
	"info_types",
);
$cf_tables = array(
	"cf_types",
	"cf_objects",
	"cf_instances",
	"cf_fields",
	"cf_data",
);
$tables=$con->MetaTables('TABLES');
foreach ($info_tables as $info_table) {
	if (!in_array($info_table,$tables)) {
		echo "<h3>Table $info_table from info plugin cannot be found</h3>";
		exit;
	}
}
foreach ($cf_tables as $cf_table) {
	if (!in_array($cf_table,$tables)) {
		echo "<h3>Table $cf_table from custom_fields plugin cannot be found</h3>";
		exit;
	}
}

# Check cf_fields is empty (this procedure should only be run on empty tables)
$sql = "SELECT field_id
		FROM cf_fields
		WHERE field_id = 1";
$result = $con->GetOne($sql);
if ($result) {
	echo "<h3>cf_fields table is not empty. This procedure should only ";
	echo "be run on a new installation of the custom_fields plugin</h3>";
	exit;
}

# Get list of defined info objects
$sql = "SELECT info_type_id, display_on
		FROM info_display_map
		WHERE record_status = 'a'";
$defined_info_objects = $con->GetAssoc($sql);

# Interate through defined info objects
foreach ($defined_info_objects as $info_type_id=>$display_on) {
	$object_name = info_type_id_to_info_type_name($info_type_id);
	
	# Create cf_object
	echo "Creating $object_name...<br>";
	$rec = array();
	$rec['object_name'] = $object_name;
	$rec['type_name'] = $display_on;
	$tbl = "cf_objects";
        $sql = $con->getInsertSQL($tbl, $rec);
	if (!$con->Execute($sql)) {
            db_error_handler ($con, $sql);
            assert(False);
            exit;
	}
	$object_id = $con->Insert_ID();
	assert($object_id);
	
	# Copy element_definitions to cf_fields
	echo "\tCopying object definition...<br>";
		
	$sql = "SELECT *
			FROM info_element_definitions
			WHERE info_type_id = $info_type_id
			AND	element_enabled = 1";
	$el_defs_rst = execute_sql($sql);

	# Create an array to map element_ids to field_ids
	$mapping = array();
	
	while (!$el_defs_rst->EOF) {
		extract($el_defs_rst->fields);

		# Convert 'name' types to 'text' for sidebar-type objects
		$label_element_id = 0;
		if ('name' == $element_type) {
			$element_type = 'text';
			$label_element_id = $element_id;
		}

		$rec = array();
		$rec['object_id'] = $object_id;
		$rec['field_label'] = $element_label;
		$rec['field_type'] = $element_type;
		$rec['field_column'] = $element_column;
		$rec['field_order'] = $element_order;
		$rec['default_value'] = $element_default_value;
		$rec['possible_values'] = $element_possible_values;
		$rec['display_in_sidebar'] = $element_display_in_sidebar;
		$tbl = "cf_fields";
		if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
			db_error_handler ($con, "Error copying fields");
			assert(False);
			exit;
		}
		$field_id = $con->Insert_ID();
		$mapping[$element_id] = $field_id;
		
		# If we've seen a 'name' element, set new object label_field_id
		if ($label_element_id) {
			$rec = array();
			$rec['label_field_id'] = $field_id;
			$tbl = 'cf_objects';
			if (!$con->AutoExecute($tbl, $rec, 'UPDATE', 
						"object_id = $object_id")) {
				db_error_handler ($con, "Error updating object - safe to ignore");
			}
		}

		$el_defs_rst->movenext();
	}

	# Get list of instances of this $info_type_id
	$sql = "SELECT *
			FROM info_map
			WHERE info_type_id = $info_type_id";
	$instances_rst = execute_sql($sql);
	
	# Iterate through instances
	while (!$instances_rst->EOF) {
		extract($instances_rst->fields);
		
		# Get key_id
		$key_id = (0 == $contact_id) ? $company_id : $contact_id;
		 if ($company_id) $subkey_id = $division_id;
		 else $subkey_id=false;
		
		# Create instance
		echo "\t\tCreating instance...<br>";
		$rec = array();
		$rec['object_id'] = $object_id;
		$rec['key_id'] = $key_id;
		if ($subkey_id) $rec['subkey_id']=$subkey_id;

		$tbl = "cf_instances";
		if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
			db_error_handler ($con, "Error creating new instance");
			assert(False);
			exit;
		}
		$instance_id = $con->Insert_ID();
		
		# Copy data and element definitions for this instance
		$sql = "SELECT *
				FROM info
				WHERE info_record_status = 'a'
				AND info_id = $info_id";
		$info_values_rst = execute_sql($sql);
		
		while (!$info_values_rst->EOF) {
			extract ($info_values_rst->fields);
			
			# Now copy data for this element
			echo "\t\t\tCopying data for info_id $info_id...<br>";
			
			$rec = array();
			$rec['field_id'] = $mapping[$element_id];
			$rec['value'] = $value;
			$rec['instance_id'] = $instance_id;
			$tbl = "cf_data";
                        $sql = $con->getInsertSQL($tbl, $rec);
			if (!$con->execute($sql)) {
				db_error_handler ($con, $sql);
				assert(False);
				exit;
			}
			$info_values_rst->movenext();
		}
	$instances_rst->movenext();
	}
}
		
echo "Conversion successful";
