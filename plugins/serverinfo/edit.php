<?php
/**
 * Edit server details
 *
 * $Id: edit.php,v 1.1 2004/07/06 19:57:02 gpowers Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

require_once('serverinfo.inc');

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

$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );

$msg = $_GET['msg'];

# Always retrieve, and pass on, server and company ID
$server_id = $_GET['server_id'];
$company_id = $_GET['company_id'];
$return_url = $_GET['return_url'];

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$new_server = (0 == $server_id);

# Get details of all defined elements
$sql  = "SELECT svrinfo_element_definitions.* FROM svrinfo_element_definitions ";
$sql .= "WHERE element_enabled=1 ";
$sql .= "ORDER BY element_column, element_order";
$all_elements = $con->execute($sql);
if (!$all_elements) {
  db_error_handler ($con, $sql);
  exit;
}

# Populate $this_server array with existing elements
# If this is a new server, every element will be added
# with a default value later
$this_server = array();
if ($server_id) {
  $sql = "SELECT value, element_id FROM svrinfo WHERE server_id='$server_id'";
  $rst = $con->execute($sql);
  if (!$rst) {
    db_error_handler ($con, $sql);
    exit;
  }

  # Build an array indexed by element_id
  while (!$rst->EOF) {
    $this_server[$rst->fields['element_id']] = $rst->fields['value'];
    # Capture name for later
    if (1 == $rst->fields['element_id']) {
      $server_name = $rst->fields['value'];
    }
    $rst->movenext();
  }
}

$element_value = array();
$element_label = array();
$element_type = array();
$element_possvals = array();

# Build display for each element
while (!$all_elements->EOF) {
  $element_id = $all_elements->fields['element_id'];
  $column = $all_elements->fields['element_column'];

  # If this server doesn't have this element defined, use default value
  $value = (array_key_exists($element_id, $this_server)) ? 
    $this_server[$element_id] : $all_elements->fields['element_default_value'];

  # Populate arrays for later display
  $element_value[$element_id] = $value;
  $element_label[$element_id] = $all_elements->fields['element_label'];
  $element_type[$element_id] = $all_elements->fields['element_type'];
  $element_possvals[$element_id] = $all_elements->fields['element_possible_values'];
  $all_elements->movenext();
}

if ($new_server) {
  $page_title = "$new_server_details";
}
else {
  $page_title = $server_name . " - " . $edit_server_details;
}
start_page($page_title, true, $msg);
$con->close();

?>

<div id="Main">
  <div id="Content">
    <form action=edit-2.php method=post>
      <input type=hidden name=server_id value=<?php  echo $server_id; ?>>
      <input type=hidden name=company_id value=<?php  echo $company_id; ?>>
      <input type=hidden name=return_url value=<?php  echo $return_url; ?>>
      <table class=widget cellspacing=1>
        <tr>
          <td class=widget_header colspan=2>
            <?php echo $edit_server_details; ?>
          </td>
        </tr>
        <?php foreach ($element_value as $element_id=>$value) { ?>
          <tr>
            <td class=widget_label_right><?php echo "$element_label[$element_id]"; ?></td>
            <?php echo show_element($element_id,
              $element_value[$element_id],$element_possvals[$element_id]); ?>
          </tr>
        <?php } ?>
        <tr>
          <td class=widget_content_form_element colspan=2>
            <input class=button type=submit value="Save Changes">&nbsp;
            <input class=button type=button
              value="Edit element definitions" onclick="javascript:
              location.href='<?php echo
              "edit-definitions.php?server_id=$server_id&company_id=$company_id&return_url=$return_url"; ?>';"
          </td>
        </tr>
      </table>
    </form>

    </div>

        <!-- right column //-->
    <div id="Sidebar">

        &nbsp;

    </div>
</div>

<?php

end_page();

?>
