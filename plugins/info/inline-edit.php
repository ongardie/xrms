<?php
/**
 * Edit item details
 *
 * $Id: inline-edit.php,v 1.3 2005/04/01 20:10:00 ycreddy Exp $
 */

# Always retrieve, and pass on, company_id, contact_id, division_id, and info_idd

global $company_id, $contact_id;
// $con->debug = 1;
  if (!$contact_id) {
        $contact_id = 0;
    }
   
$info_id      = $_GET['info_id'];;
$division_id  = $_GET['division_id'];
$return_url   = $_GET['return_url'];
$info_type_id = $_GET['info_type_id'];
$new_info = $_GET['new_info'];

    // Find the elements to display in sidebar under name
    // (there may be none)
    $sql = "SELECT info_type_id
            FROM info_display_map
            WHERE display_on = '" . $display_on . "' 
    	    AND record_status = 'a'";
    $rst = $con->SelectLimit($sql, 1);

    if ($rst) {
        if (!$rst->EOF) {
            $info_type_id = $rst->fields['info_type_id'];
        }
    }

function show_element ($element_id, $element_value, $element_possvals) {
  # returns HTML to edit element
  # the element_type array will be populated before we're called
  # the "name" for each element is "element_" plus the element_id

  global $element_type;

  $name = "element_".$element_id;

  #echo "$element_id=$element_value <br>";
  $html = "<td class=widget_content_form_element>";
  switch ($element_type[$element_id]) {

  case "checkbox":
    $html .= "<input type=checkbox value=1 name=".$name;
    if ($element_value) $html .= " CHECKED";
    $html .= ">";
    break;

  case "select":
    $contenders = explode(",",$element_possvals);
    $html .= "<select name=\"$name\">";
    foreach ($contenders as $possible_value) {
      $html .= "<option ";
      if ($possible_value == $element_value) {
        $html .= "SELECTED ";
      }
      $html .= "value=\"$possible_value\">$possible_value</option>";
    }
    $html .= "</select>";
    break;

  case "radio":
    $contenders = explode(",",$element_possvals);
    foreach ($contenders as $possible_value) {
      $html .= "<label><input type=radio name=".$name;
      $html .= " VALUE=\"$possible_value\"";
      if ($possible_value == $element_value) {
        $html .= " CHECKED";
      }
      $html .= ">".$possible_value."</label>&nbsp;";
    }
    break;

    $html .= "(radio) ".$element_value;
    break;

  case "textarea":
    $html .= "<textarea rows=8 cols=80 name='$name'>";
    $html .= "$element_value</textarea>";
    break;

  case "text":
  default:
    $html .= "<input type=text size=40 name='$name'";
    $html .= " value='$element_value'>";
    break;
  }
  $html .= "</td>";
  return $html;
}


if (!$return_url) {
    $return_url = "/companies/one.php?company_id=$company_id&division_id=$division_id";
};

# Get details of all defined elements
$sql  = "SELECT info_element_definitions.* FROM info_element_definitions ";
$sql .= "WHERE element_enabled=1 ";
$sql .= "AND info_element_definitions.info_type_id= '" . $info_type_id . "' ";
$sql .= "AND element_type!= 'name' ";
$sql .= "ORDER BY element_column, element_order";
$all_elements = $con->execute($sql);

# Populate $this_info array with existing elements
# If this is a new info, every element will be added
# with a default value later
$this_info = array();
  $sql = "SELECT info.info_id
          FROM info_map, info
          WHERE company_id = '" . $company_id . "'
          AND info.info_id = info_map.info_id
          AND info_map.contact_id = '" . $contact_id . "'
          AND info.info_record_status ='a'";

  $rst = $con->execute($sql);

  if ($rst) {
  	if (!$rst->EOF) {
		$info_id = $rst->fields['info_id'];

if (!$new_info) {
  $sql = "SELECT value, element_id FROM info WHERE info_id='$info_id' AND info_record_status ='a'";
  $rst = $con->execute($sql);

  # Build an array indexed by element_id
  if ($rst) {
      while (!$rst->EOF) {
        $this_info[$rst->fields['element_id']] = $rst->fields['value'];
        # Capture name for later
        if ($rst->fields['element_id']) {
          $info_name = $rst->fields['value'];
        }
        $rst->movenext();
      }
  }
}

  	}
  }
  

$element_value = array();
$element_label = array();
$element_type = array();
$element_possvals = array();

# Build display for each element
if ($all_elements) {
    while (!$all_elements->EOF) {
        $element_id = $all_elements->fields['element_id'];
        $column = $all_elements->fields['element_column'];

        # If this server doesn't have this element defined, use default value
        $value = (array_key_exists($element_id, $this_info)) ?
        $this_info[$element_id] : $all_elements->fields['element_default_value'];

        # Populate arrays for later display
        $element_value[$element_id] = $value;
        $element_label[$element_id] = $all_elements->fields['element_label'];
        $element_type[$element_id] = $all_elements->fields['element_type'];
        $element_possvals[$element_id] = $all_elements->fields['element_possible_values'];
        $all_elements->movenext();
    }
}

// Display a table row for each piece of info:
foreach ($element_value as $element_id=>$value) {
    if ( $element_label[$element_id] != "Name") {
        $sidebar_string .= "<tr> <td class=widget_label_right> "
            . $element_label[$element_id]
            . "</td>"
            . show_element($element_id, $element_value[$element_id],$element_possvals[$element_id])
            . "</tr>";
                }
            }
            
            
$sidebar_string .= "
                <input type=hidden name=info_id value=" . $info_id . ">
      			<input type=hidden name=new_info value=" . $new_info . ">
     			<input type=hidden name=company_id value=" . $company_id . ">
    			<input type=hidden name=division_id value=" . $division_id . ">
    			<input type=hidden name=contact_id value=" . $contact_id . ">
    			<input type=hidden name=info_type_id value=" . $info_type_id . ">
    			<input type=hidden name=return_url value=" . $return_url . ">
";
            
return $sidebar_string;

/**
 * $Log: inline-edit.php,v $
 * Revision 1.3  2005/04/01 20:10:00  ycreddy
 * Replaced LIMIT with the portable SelectLimit
 *
 * Revision 1.2  2005/03/24 17:42:08  gpowers
 * - moved admin button to admin screen
 * - fixed bug when removing and adding info types
 *
 * Revision 1.1  2005/03/21 15:11:01  gpowers
 * - inline display/edit of info items
 *
 *
 */
?>
