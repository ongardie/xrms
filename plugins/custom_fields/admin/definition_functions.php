<?php

function do_delete_element ($field_id) {

	# Mark passed element as deleted
	
	$con = connect();

	# Mark as deleted all cf_data table lines using this field_id
	# Create the delete part of record
	$rec = array();
	$rec['record_status'] = 'd';
	$tbl = 'cf_data';
	if (!$con->AutoExecute($tbl, $rec, 'UPDATE', 
			"field_id = $field_id")) {
		db_error_handler ($con, "AutoExecute delete_element");
	}

	# Mark the element definition as deleted
	$tbl = 'cf_fields';
	if (!$con->AutoExecute($tbl, $rec, 'UPDATE', 
			"field_id = $field_id")) {
		db_error_handler ($con, "AutoExecute delete_element");
	}
}

function get_row_html ($fields, $in_sidebar, $inline) {

	# Takes an array of fields (a single row) to display. Returns
	# an array of formatted HTML elements.
	
	# Convert the array into $key variables
	extract($fields);

	# Are we constructing a blank row for a new element?
	$new_element = (is_array($field_type));
	
	# Build array of HTML
	# Start with empty array
	$result = array();
	
	# Label
	$label_html  = "<input type=text size=15 ";
	$label_html .= "name='field_label[$field_id]' ";
	$label_html .= "value='$field_label'>\n";
	$result[] = $label_html;
	
	# Type
	# If new element then list all types in $type
	if ($new_element) {
		$type_html .= "<SELECT name='field_type[$field_id]'>\n";
		foreach ($field_type as $t) {
			# Translate name if it exists
			$display_type = (empty($$t)) ? $t : $$t;
			$type_html .= "<OPTION VALUE=$t>$display_type</OPTION>";
		}
		$type_html .= "</SELECT>\n";
	}
	else {
		$type_html  = possible_field_types($field_type, $field_id);
	}
	$result[] = $type_html;
	
	# Default value
	$default_value_html  = "<input type=text size=10 ";
	$default_value_html .= "name='default_value[$field_id]' ";
	$default_value_html .= "value='$default_value'>\n";
	$result[] = $default_value_html;

	# Possible values
	$possible_values_html  = "<input type=text size=10 ";
	$possible_values_html .= "name='possible_values[$field_id]' ";
	$possible_values_html .= "value='$possible_values'>\n";
	$result[] = $possible_values_html;

	# Order
	$order_html = "<input type=text size=2 ";
	$order_html .= "name='field_order[$field_id]' ";
	$order_html .= "value='$field_order'>\n";
	$result[] = $order_html;

	# Column, but not if an inline element
	if (!$inline) {
		$column_html  = "<input type=text size=2 ";
		$column_html .= "name='field_column[$field_id]' ";
		$column_html .= "value='$field_column'>\n";
		$result[] = $column_html;
	}

	# Display in sidebar checkbox, but only for sidebar displays
	if ($in_sidebar) {
		$sidebar_html  = "<input type=checkbox value=1 ";
		$sidebar_html .= "name='display_in_sidebar[$field_id]'";
		if ($display_in_sidebar) {
			$sidebar_html .= " CHECKED";
		}
		$result[] = $sidebar_html.'>';
	}

	# Delete button - only add if this is not a new element
	if ($new_element) {
		$result[] = "&nbsp;";
	}
	else {
		$delete_html  = "<input class=button type=submit ";
		$delete_html .= "name=btnDelete$field_id value='Delete'>\n";
		$result[] = $delete_html;
	}
	
	return $result;
}

function possible_field_types ($type, $field_id) {
	# Some element types can be changed to others (eg, text to textarea), but it is not
	# sensible to change, eg, textarea to radio. Some types (eg, checkbox) can't really
	# be changed at all. This function returns a selection of radio buttons to allow the 
	# user to select which alternative type they want or, if there are no alternatives,
	# it returns a text string describing the element type

	switch ($type) {

	case "text":
	case "textarea":
		$html = '<SELECT name="field_type['.$field_id.']">\n';
		$html .= '<OPTION';
		if (("text" == $type) || ("name" == $type)) {
		  $html .= " SELECTED";
		}
		$html .= " VALUE=text>"._("text")."</OPTION>\n";
		$html .= '<OPTION';
		if ("textarea" == $type) {
		  $html .= " SELECTED";
		}
		$html .= " VALUE=textarea>"._("textarea")."</OPTION>\n";
		$html .= "</SELECT>\n";
		break;

	case "select":
	case "radio":
		$html = '<SELECT name="field_type['.$field_id.']">\n';
		$html .= '<OPTION';
		if ("select" == $type) {
		   $html .= " SELECTED";
		}
		$html .= " VALUE=select>"._("select")."</OPTION>\n";
		$html .= '<OPTION';
		if ("radio" == $type) {
		   $html .= " SELECTED";
		}
		$html .= " VALUE=radio>"._("radio")."</OPTION>\n";
		$html .= "</SELECT>\n";
		break;

	case "checkbox":
		$html = "<INPUT TYPE=hidden NAME=\"field_type[$field_id]\" VALUE=\"checkbox\">\n";
		$html .= _("checkbox");
	break;

	case "name":
		$html = "<INPUT TYPE=hidden NAME=\"field_type[$field_id]\" VALUE=\"name\">\n";
		$html .= _("text");
	break;

	default:
		assert(true);
		echo _("Error: Unreconised Element Type") .  "(" . $type . ")";
		assert(False);
		exit;
	}
	return $html;
}

function save_changes () {

	# Save any changes on the edit definitions form
	
	# Establish a database connection (used later)
	$con = connect();

	# Consider each row of elements one by one. We need to account for the fact that
	# no value is returned by the form for a cleared checkbox, so we cannot simply
	# iterate through all returned values (because that will miss cleared checkboxes).
	# We DO know that every row will have a 'label' element, so we loop through all
	# elements of $label array to process each line.

	# List all the columns used to define an element
	$columns = array(
	  'field_label',
	  'field_type',
	  'field_column',
	  'field_order',
	  'default_value',
	  'possible_values',
	  'display_in_sidebar',
	);

	# Iterate through each id on the form
	$object_id = $_GET["object_id"];

	foreach ($_GET["field_label"] as $id=>$label) {

		# Don't allow null labels
		if (empty($label)) {
			continue;
		}
		
		# Build an associatve array of column=>values with which to 
		# insert/update database; set any nulls to zero.
		$rec = array();
		foreach ($columns as $column) {
			$value = @$_GET[$column][$id];
			$rec[$column] = is_null($value) ? 0 : $value;
		}

		# Add the object_id
		$rec["object_id"] = $object_id;
		# INSERT or UPDATE the record
		$tbl = "cf_fields";
		if (0 == $id) {
			# This is a new element, so commit to database
			if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
				db_error_handler ($con, $ins);
			}
		}
		else {
			# This is an existing element
			if (!$con->AutoExecute($tbl, $rec, 'UPDATE', "field_id = $id")) {
				db_error_handler ($con, $upd);
			}
		}
	}
	
	# Set label_element if given
	if (array_key_exists("label_field_id", $_GET)) {
		$rec = array();
		$rec['label_field_id'] = $_GET['label_field_id'];
		$tbl = "cf_objects";
		if (!$con->AutoExecute($tbl, $rec, 'UPDATE', "object_id = $object_id")) {
			db_error_handler ($con, $upd);
		}
	}
}

?>
