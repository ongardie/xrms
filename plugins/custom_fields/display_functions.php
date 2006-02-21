<?php

require_once('cf_functions.php');

function do_inline_edit_save ($type_name, $keyvalue, $subkeyvalue=false) {

    # $_POST will have an instance_id field and a fields array,
    # indexed by field_id. If instance_id is zero, we need to
    # create an instance first.

    $values = $_POST['fields'];
    $instance_id = $_POST['instance_id'];
    $object_id = type_name_to_object_id ($type_name);

    save_values ($values, $instance_id, $object_id, $keyvalue, $subkeyvalue);
}

function fetch_contact_name ($contact_id) {

    # Return contact name

    $sql = "SELECT  first_names, last_name
            FROM    contacts
            WHERE   contact_id = $contact_id
            AND     contact_record_Status = 'a'";
    $rst = execute_sql($sql);
    extract($rst->fields);
    return "$first_names $last_name";
}

function get_column ($object_id, $instance_id, $sidebar_only, $mode, $col=0) {

    # Return an HTML table of formatted elements. If
    # $mode=="DATA" then return as display; if $mode=="EDIT"
    # return as editable HTML. If $sidebar_only is true, only
    # return fields that should be displayed in the sidebar.

    assert($object_id);

    $con = connect();

    # If we are editing a new instance then instance_id==0 and
    # there will be no entries in the cf_data table yet, so we need
    # to use default values.

    # Get list of elements for this object
    $sql = "SELECT  field_id, field_label, field_type, default_value,
                    possible_values
            FROM    cf_fields
            WHERE   object_id = $object_id
            AND     record_status = 'a'";
    if ($sidebar_only) {
        $sql .= " AND       cf_fields.display_in_sidebar = 1";
    }
    if ($col) {
        $sql .= " AND   field_column = $col";
    }
    $sql .= " ORDER BY field_order";

    $fields_rst = execute_sql($sql);

    # If instance exists, get values
    if ($instance_id) {
        # We have an instance: retrieve the data
        $sql = "SELECT  field_id, value
                FROM    cf_data
                WHERE   instance_id = $instance_id
                AND     record_status = 'a'";
        $values = $con->GetAssoc($sql);
    }
    else {
        # Empty array (ie, no values) if this is a new instance
        $values = array();
        # Ensure instance_id is zero as opposed to null, etc
        $instance_id = 0;
    }

    # Are we editing?
    if ("DATA" == $mode) {
        $editing = False;
        $label_css = "sublabel";
        $data_css = "widget_content_form_element";
    }
    elseif ("EDIT" == $mode) {
        $editing = True;
        $label_css = "widget_label_right";
        $data_css = "clear";
    }
    else {
        echo "Unknown mode: $mode";
        assert(False);
    }

    # Go through each field and format
    $html = "";
    while (!$fields_rst->EOF) {

        # Make symbols for each value retrieved
        extract($fields_rst->fields);

        $values[$field_id] = stripslashes($values[$field_id]);
        if ($editing) {
            # Get default value if value isn't defined
            if (!array_key_exists($field_id, $values)) {
                $values[$field_id] = $default_value;
            }

            $field_value = get_field_edit_html($field_id, $field_type,
                    $values[$field_id], $possible_values);
        }
        else {
            if ($field_type == "checkbox") {
                $field_value = (1 == $values[$field_id]) ? _("yes") : _("no");
            }
            else {
                $field_value = $values[$field_id];
                               if ($field_type == "textarea") {
                                       $field_value = str_replace("\n","<br>\n",
                                                       htmlspecialchars($field_value));
                               }
                if ($field_value == "") {
                    $field_value = "&nbsp;";
                }
            }
        }

        # Add HTML for this label-value pair
        $html .= "
        <tr>
            <td class='$label_css'>$field_label</td>
            <td class='$data_css'>$field_value</td>
        </tr>
        ";

        # Repeat until done
        $fields_rst->movenext();
    }

    # Add additional information if we are editing
    # (specifically required for inline fields)
    if ($editing) {
        $html .= "
            <input type=hidden name=instance_id value=$instance_id>
        ";
    }
    return $html;
}

function get_data_column ($object_id, $instance_id,
                            $sidebar_only=False, $col=0) {

    return get_column($object_id, $instance_id, $sidebar_only, "DATA", $col);
}

function get_display ($object_type, $keyvalue, $return_url, $subkeyvalue=false) {

    # Dispatch to correct routine
    $con = connect();
    $sql = "SELECT  display
            FROM    cf_types
            WHERE   type_name = '$object_type'
            AND     record_status = 'a'";
    $display = $con->GetOne($sql);

    switch ($display) {

    case 'sidebar':
        return get_sidebar_display ($object_type, $keyvalue, $return_url, $subkeyvalue);
        break;

    case 'inline':
        return get_inline_display ($object_type, $keyvalue, $return_url, $subkeyvalue);
        break;

    case 'section':
        return get_section_display ($object_type, $keyvalue, $return_url, $subkeyvalue);
        break;

    default:

    }
}

function get_edit_column ($object_id, $instance_id, $col=0) {

    return get_column($object_id, $instance_id, False, "EDIT", $col);
}

function get_field_edit_html ($field_id, $field_type, $value,
                                                $possible_values) {

    # returns HTML to edit element

    $name = "fields[$field_id]";
    $html = "";

    switch ($field_type) {

    case "checkbox":
        $html .= "<input type=checkbox value=1 name=".$name;
        if ($value) {
            $html .= " CHECKED";
        }
        $html .= ">";
        break;

    case "select":
        $selections = explode(",",$possible_values);
        $html .= "<select name=\"$name\">";
        foreach ($selections as $selection) {
            $html .= "<option ";
            if ($selection == $value) {
                $html .= "SELECTED ";
            }
            $html .= "value=\"$selection\">$selection</option>";
        }
        $html .= "</select>";
        break;

    case "radio":
        $selections = explode(",",$possible_values);
        foreach ($selections as $selection) {
            $html .= "<label><input type=radio name=".$name;
            $html .= " VALUE=\"$selection\"";
            if ($selection == $value) {
                $html .= " CHECKED";
            }
            $html .= ">$selection</label>&nbsp;";
        }
        break;

    case "textarea":
        $html .= "<textarea rows=8 cols=80 name='$name'>";
        $html .= "$value</textarea>";
        break;

    case "text":
    default:
        $html .= "<input type=text size=40 name='$name'";
        $html .= " value='$value'>";
        break;

    }
    return $html;
}

function get_formatted_data ($field_id, $instance_id) {

    # Return an HTML tablerow of formatted elements

    # Get type information
    $sql = "SELECT  field_label, field_type
            FROM    cf_fields
            WHERE   field_id = $field_id
            AND     record_status = 'a'";
    $rst = exectute_sql($sql);
    assert($rst->fields);
    extract($rst->fields);

    # Get data for object
    $sql = "SELECT  value
            FROM    cf_data
            WHERE   field_id = $field_id
            AND     instance_id = $instance_id
            AND     record_status = 'a'";
    $rst = execute_sql($sql);
    assert($rst->fields);
    extract($rst->fields);

    # Format HTML
    $html = "";

    # Get text for this element value using words for checkbox status
    if ($field_type == "checkbox") {
        $print_value = (1 == $value) ? _("yes") : _("no");
    }
    else {
       $print_value = $value;
    }

    # Add HTML for this label-value pair
    $html .= "
    <tr>
        <td class='sublabel'>$label</td>
        <td class='clear'>$print_value</td>
    </tr>
    ";

    return $html;
}

function get_inline ($type_name, $keyvalue, $mode, $subkeyvalue=false) {

    # Return display or edit string depending upon $mode

    $con = connect();

    # There will only be one object_id for each inline type, so
    # retrieve it now
    $object_id = type_name_to_object_id ($type_name);

    # Get instance_id (may be zero if we're editing a new instance)
    $sql = "SELECT  instance_id
            FROM    cf_instances
            WHERE   object_id = $object_id
            AND     key_id = $keyvalue
            AND     record_status = 'a'";
    if ($subkeyvalue) $sql .= " AND subkey_id = $subkeyvalue";
    $instance_id = $con->GetOne($sql);

    # If in data mode and no instance_id, return empty string
    if ("DATA" == $mode) {
        if (!$instance_id) {
            return "";
        }
        else {
            # return data
            return get_data_column ($object_id, $instance_id);
        }
    }
    elseif ("EDIT" == $mode) {
        return get_edit_column ($object_id, $instance_id);
    }
    else {
        echo "Unknown mode '$mode'";
        assert(False);
        exit;
    }
}

function get_inline_display ($type_name, $keyvalue, $subkeyvalue=false) {

    # Return HTML for inline display

    return get_inline($type_name, $keyvalue, "DATA", $subkeyvalue);
}

function get_inline_edit ($type_name, $keyvalue, $subkeyvalue=false) {

    # Return HTML for inline editing

    return get_inline($type_name, $keyvalue, "EDIT", $subkeyvalue);
}

function get_instance_buttons ($instance_id, $column_count, $return_url,
                $back_button) {

    # Return HTML to provide instance buttons

    global $http_site_root;

    $location = "$http_site_root/plugins/custom_fields/";
    $html = "
      <tr>
        <td class=widget_content_form_element colspan=$column_count>
          <input class=button type=button value="._("Edit")."
            onclick=\"javascript: location.href='$location/edit.php?instance_id=$instance_id&return_url=$return_url';\">
          <input class=button type=button value="._("Delete")."
            onclick=\"javascript: location.href='$location/delete-2.php?instance_id=$instance_id&return_url=$return_url';\">
    ";
    # Provide a Back button?
    if ($back_button) {
        $html .= "
          <input class=button type=button value="._("Back")."
            onclick=\"javascript: location.href='$return_url';\">
        ";
    }
    $html .= "
        </td>
      </tr>
    ";

    return $html;
}

function get_instance_detail ($instance_id, $object_id, $return_url,
            $back_button=True) {

    $con = connect();

    # Get a list of columns
    $sql = "SELECT  field_column
            FROM    cf_fields
            WHERE   record_status = 'a'
            AND     object_id = $object_id
            ORDER BY field_column";
    $columns = array_unique($con->GetCol($sql));
    if (!$columns) {
        db_error_handler($con, "Error retrieving columns info");
        assert(False);
    }
    $column_count = count($columns);

    $html .= "
    <tr>
      <td>
        <table class=widget cellspacing=0 width='100%'>";

    # Get data for each column in turn
    reset($columns);
    foreach ($columns as $column) {
        $data[$column] = get_data_column($object_id, $instance_id,
                False, $column);
    }

    # Display data
    $html .= get_multicolumn_display($data);

    # Show instance buttons
    $html .= get_instance_buttons ($instance_id, $column_count, $return_url,
                $back_button);

    $html .= "
        </table>
      </tr>
    ";

    return $html;
}



function get_label_data ($instance_id) {

   # Return value of data element for this object, or
   # empty string if none defined

   $value = "";
   $sql = "SELECT  label_field_id
           FROM    cf_objects, cf_instances
           WHERE   cf_instances.instance_id = $instance_id
           AND     cf_instances.object_id = cf_objects.object_id
           AND     cf_instances.record_status = 'a'
           AND     cf_objects.record_status = 'a'";
   $rst = execute_sql($sql);
   $label_field_id = $rst->fields['label_field_id'];

   if ($label_field_id) {
       # There is a label_field_id defined: get its value
       $sql = "SELECT  value
               FROM    cf_data
               WHERE   instance_id = $instance_id
               AND     field_id = $label_field_id
               AND     record_status = 'a'";
       $rst = execute_sql($sql);

       if ($rst->fields) {
           $value = $rst->fields['value'];
       }
   }
   return $value;
}

function get_multicolumn_display ($data) {

    # Returns HTML for a multicolumn display (as used by the detailed
    # view of a sidebar or a section display). $data is a an array of
    # columns of data.

    $html = "
      <tr>
    ";
    foreach ($data as $column) { $html .= "
        <td class=clear align=left valign=top>
          <table border=0 cellpadding=0 cellspacing=0>
            $column
          </table>
        </td>";
    } $html .= "
      </tr>
    ";

    return $html;
}

function get_object_type_select () {

    # Return HTML SELECT box of all object types except inline types

    $sql = "SELECT  type_name
            FROM    cf_types
            WHERE   display != 'inline'";

    $rst = execute_sql($sql);
    return $rst->GetMenu("type_name", $current, False);
}

function get_new_button ($object_id, $keyvalue, $return_url, $subkeyvalue=false) {

    # Return code to create button for a new instance of object

    global $http_site_root;

    $btn_label = _("New");
    $html .= "
        <input class=button type=button value='$btn_label'
            onclick=\"javascript:";
            $html .= "location.href='";
            $html .= "$http_site_root/plugins/custom_fields/edit.php?";
            $html .= "object_id=$object_id&instance_id=0";
            $html .= "&key_id=$keyvalue";
            $html .= "&return_url=$return_url";
            $html .= "&subkey_id=$subkeyvalue";
            $html .= "'\">
    ";

    return $html;
}

function get_parent_name ($object_id, $keyvalue, $subkeyvalue=false) {

    # Return name of object's parent, formatted on a per-object-type basis

    assert($object_id);
    assert($keyvalue);

    $con = connect();

    # Get object type
    $sql = "SELECT  type_name
            FROM    cf_objects
            WHERE   object_id = $object_id
            AND     record_status = 'a'";
    $type_name = $con->GetOne($sql);

    switch ($type_name) {

    case 'company_sidebar_bottom':
    case 'company_content_bottom':
    case 'company_accounting':
        $con = connect();
        return fetch_company_name($con, $keyvalue);
        break;

    case 'contact_sidebar_top':
    case 'contact_sidebar_bottom':
    case 'contact_accounting':
        return fetch_contact_name($keyvalue);
        break;

    case 'private_sidebar_bottom':
        return $_SESSION['username'];
        break;

    default:
        assert(False);
        return "No name defined for $object_type in get_parent_name()";
    }
}

function get_section_display ($type_name, $keyvalue, $return_url, $subkeyvalue=false) {

    $con = connect();

    # Variable to store HTML in
    $html = "";

    # Get a list of object_ids for this $object_type
    $sql = "SELECT  object_id
            FROM    cf_objects
            WHERE   type_name = '$type_name'
            AND     record_status = 'a'";
    $object_list = $con->GetCol($sql);


    # For each object_id...
    foreach ($object_list as $object_id) {

        # Get object name
        $sql = "SELECT  object_name
                FROM    cf_objects
                WHERE   object_id = $object_id
                AND     record_status = 'a'";
        $object_name = $con->GetOne($sql);

        # Create a header row
        $html .= "
          <table class=widget cellspacing=0>
            <tr>
              <td class=widget_header align=left>
                $object_name
              </td>
            </tr>
        ";

        # Get a list of instances
        $sql = "SELECT  instance_id
                FROM    cf_instances
                WHERE   object_id = $object_id
                AND     key_id = $keyvalue
                AND     record_status = 'a'";
        if ($subkeyvalue) $sql.= " AND subkey_id = $subkeyvalue";
        $instances_list = $con->GetCol($sql);

        # Now create row for each instance, each row of which contains
        # instance data as a table
        foreach ($instances_list as $instance_id) {
            $html .= get_instance_detail ($instance_id, $object_id,
                    $return_url, False);
        }
        # Now the object buttons row and finish table
        $html .= "
          <tr>
            <td>
        ";
         $html .= get_new_button ($object_id, $keyvalue, $return_url, $subkeyvalue);
         $html .= "
            </td>
          </tr>
        </table>
        ";
    }

    return $html;
}

function get_sidebar_display ($object_type, $keyvalue, $return_url, $subkeyvalue=false) {


    # Returns HTML to display sidebars for objects of $object_type.
    # $keyvalue is the value of key item for this object. For example,
    # contact_sidebar_bottom is keyed on "contact_id" so this would
    # be the current value of $contact_id.

    global $http_site_root;

    # Symbol to build HTML to return
    $html = "";

    # Every defined object of $object_type needs to be shown, even
    # if there is no instance defined, as we need to show a "New" button.

    # Get list of objects of this $object_type
    $sql = "SELECT  object_id, object_name
            FROM    cf_objects
            WHERE   type_name = '$object_type'
            AND     record_status = 'a'";
    $object_rst = execute_sql($sql);

    # Iterate through the list
    while (!$object_rst->EOF) {
        # Get field values
        extract($object_rst->fields);
        # Generate header for this object
        $html .= "
            <div id='info_item'>
                <table class=widget cellspacing=1 width=\"100%\">
                    <tr>
                        <td class=widget_header colspan=2>$object_name</td>
                    </tr>\n
        ";
        # Are any instance of this object defined?
        $sql = "SELECT  instance_id
                FROM    cf_instances
                WHERE   object_id = $object_id
                AND key_id = $keyvalue
                AND     record_status = 'a'";
        if ($subkeyvalue) $sql .= " AND subkey_id = $subkeyvalue";

        $instances_rst = execute_sql($sql);
        while (!$instances_rst->EOF) {

            # Extract instance_id
            $instance_id = $instances_rst->fields['instance_id'];

            # Get link text
            $label = get_label_data($instance_id);
            if (empty($label)) {
               $label = _("View");
            }

            # Now generate link
            $html .= "<tr><td colspan=2 class=widget_content>";
            $html .= "<a href='$http_site_root/plugins/custom_fields/one.php";
            $html .= "?instance_id=" . $instance_id;
            $html .= "&return_url=$return_url";
            $html .= "'>$label</a>";
            $html .= "</td></tr>";

            # Show data
            $html .= get_data_column ($object_id, $instance_id, True);

            # Repeat
            $instances_rst->movenext();
        }

        # Insert New button
        $btn_label = _("New");
        $html .= "
                <tr>
                    <td class=widget_content_form_element colspan=2>
                        <br />
                        <input class=button type=button value='$btn_label'
                            onclick=\"javascript:";
                            $html .= "location.href='";
                            $html .= "$http_site_root/plugins/custom_fields/edit.php?";
                            $html .= "object_id=$object_id&instance_id=0";
                            $html .= "&key_id=$keyvalue";
                            $html .= "&subkey_id=$subkeyvalue";
                            $html .= "&return_url=$return_url";
                            $html .= "'\">
                    </td>
                </tr>
            </table>
        </div>";
        $object_rst->movenext();
    }
    return $html;
}

function save_values ($values, $instance_id, $object_id, $key_id, $subkey_id) {

    # Saves the values passed in the $values array (as $field_id=>$value
    # pairs). Creates new instance_id if necessary.

    # If $instance_id is zero then we need to create a new instance.
    # We may not have been passed name-value pairs for every field because
    # checkboxes, for example, don't return a name-value pair from a form if
    # the checkbox has been cleared. Therefore we start with a list of object
    # fields as defined in cf_objects. We may have fields that do not currently
    # exist in cf_data because the object definition may have been updated
    # since this instance was created, meaning an INSERT rather than UPDATE
    # is required.

    $con = connect();

    # Ensure we have an instance_id
    if (!$instance_id) {
        if (!$key_id) return false;
        assert($key_id);
        $tbl = "cf_instances";
        $rec = array();
        $rec['object_id'] = $object_id;
        $rec['key_id'] = $key_id;
        if ($subkey_id) $rec['subkey_id']=$subkey_id;

                $sql = $con->getInsertSQL($tbl, $rec);
        if (!$sql OR !execute_sql($sql)) {
            db_error_handler ($con, "Error creating new instance");
            assert(False);
            exit;
        }
        $instance_id = $con->Insert_ID();
    }

    # Get a lit of all defined fields for this object
    $sql = "SELECT  field_id
            FROM    cf_fields
            WHERE   object_id = $object_id
            AND     record_status = 'a'";
    $object_fields = $con->GetCol($sql);

    # Get a list of existing data values for this instance
    $sql = "SELECT  field_id, value
            FROM    cf_data
            WHERE   instance_id = $instance_id
            AND     record_status = 'a'";
    $existing_data = $con->GetAssoc($sql);

    # Step through each object-defined value and update/insert as appropriate
    $tbl = "cf_data";
    foreach ($object_fields as $field_id) {
        $value = $values[$field_id];

        # If field is an unchecked checkbox, $value will be null, so set to 0
        if (is_null($value)) {
            $value = 0;
        }

        # Does this field already exist?
        if (array_key_exists($field_id, $existing_data)) {
            # Yes: has it changed?
            if ($existing_data[$field_id] != $value) {
                # Yes, it's changed: update it
                $rec = array();
                $rec['value'] = $value;
                if (!$con->AutoExecute($tbl, $rec, 'UPDATE',
                        "field_id = $field_id AND instance_id = $instance_id")) {
                    db_error_handler ($con);
                }
            }
        }
        else {
            # This is a new field: insert it
            $rec = array();
            $rec['field_id'] = $field_id;
            $rec['value'] = $value;
            $rec['instance_id'] = $instance_id;
            if (!$con->AutoExecute($tbl, $rec, 'INSERT')) {
                db_error_handler ($con, "Failed to insert new record");
            }
        }
    }

    connect($con);
}

function type_name_to_object_id ($type_name) {

    # Return object_id for given type name
    # Should only be used by inline functions because they
    # can only have one object_id per type name. This is NOT
    # checked here for speed of execution...

    $con = connect();

    $sql = "SELECT  object_id
            FROM    cf_objects
            WHERE   type_name = '$type_name'
            AND     record_status = 'a'";
    $object_id = $con->GetOne($sql);
    assert($object_id);

    return $object_id;
}
