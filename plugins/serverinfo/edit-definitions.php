<?php
/**
 * Edit server details
 *
 * $Id: edit-definitions.php,v 1.1 2004/07/06 19:57:02 gpowers Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

require_once('serverinfo.inc');

function possible_element_types ($type, $element_id) {
  # Some element types can be changed to others (eg, text to textarea), but it is not
  # sensible to change, eg, textarea to radio. Some types (eg, checkbox) can't really
  # be changed at all. This function returns a selection of radio buttons to all the 
  # passed type to be changed to other "sensible" values or, if there are no alternatives,
  # it returns a text string describing the element type
  
  global $text, $textarea, $select, $radio, $checkbox;
  
  # The element name, element 1, can only be textual
  if (1 == $element_id) {
    return "text";
  }

  switch ($type) {
  
  case "text":
  case "textarea":
    $html = '<SELECT name="element_type['.$element_id.']">\n';
    $html .= '<OPTION';
    if ("text" == $type) {
      $html .= " SELECTED";
    }
    $html .= " VALUE=text>$text</OPTION>\n";
    $html .= '<OPTION';
    if ("textarea" == $type) {
      $html .= " SELECTED";
    }
    $html .= " VALUE=textarea>$textarea</OPTION>\n";
    $html .= "</SELECT>\n";
    break;
  
  case "select":
  case "radio":
    $html = '<SELECT name="element_type['.$element_id.']">\n';
    $html .= '<OPTION';
    if ("select" == $type) {
      $html .= " SELECTED";
    }
    $html .= " VALUE=select>$select</OPTION>\n";
    $html .= '<OPTION';
    if ("radio" == $type) {
      $html .= " SELECTED";
    }
    $html .= " VALUE=radio>$radio</OPTION>\n";
    $html .= "</SELECT>\n";
    break;

  case "checkbox":
    $html = "<INPUT TYPE=hidden NAME=\"element_type[$element_id]\" VALUE=\"checkbox\">\n";
    $html .= "$checkbox";
    break;
  
  default:
    assert(true);
    echo "Unreconised element type ($type)";
    exit;
  }
  return $html;
}

function show_row ($fields) {

  global $text, $textarea, $radio,$checkbox, $select;

  # Special-case element_id=1 as this is the element name (must be a text type and enabled)
  foreach ($fields as $key=>$value) {
    $$key = $value;
  }
  $label_html = "\t<td>\n\t\t<input type=text size=15 ";
  $label_html .= "name=\"element_label[$element_id]\" value=\"$element_label\">";
  $label_html .= "\n\t</td>\n";
  
  $type_html = "\t<td>\n\t\t";
  if (1 == $element_id) {
    $type_html .= "<INPUT TYPE=hidden NAME=\"element_type[$element_id]\" VALUE=\"text\">\n";
    $type_html .= "$text";
  }
  else if (is_array($element_type)) {
    # we are constructing a new element, so list all types in $type
    $type_html .= "<SELECT name=\"element_type[$element_id]\">\n";
    foreach ($element_type as $type) {
      $display_type = (empty($$type)) ? $type : $$type;
      $type_html .= "\t\t\t<OPTION VALUE=$type>$display_type</OPTION>\n";
    }
    $type_html .= "\t\t</SELECT>\n";
  }
  else {
    $type_html .= possible_element_types($element_type, $element_id);
    $type_html .= "\n\t</td>\n";
  }
  
  $column_html = "\t<td>\n\t\t<input type=text size=2 ";
  $column_html .= "name=\"element_column[$element_id]\" value=\"$element_column\">";
  $column_html .= "\n\t</td>\n";
  
  $order_html = "\t<td>\n\t\t<input type=text size=2 ";
  $order_html .= "name=\"element_order[$element_id]\" value=\"$element_order\">";
  $order_html .= "\n\t</td>\n";
  
  $default_value_html = "\t<td>\n\t\t<input type=text size=10 ";
  $default_value_html .= "name=\"element_default_value[$element_id]\" ";
  $default_value_html .= "value=\"$element_default_value\">";
  $default_value_html .= "\n\t</td>\n";
  
  $possible_values_html = "\t<td>\n\t\t<input type=text size=10 ";
  $possible_values_html .= "name=\"element_possible_values[$element_id]\" ";
  $possible_values_html .= "value=\"$element_possible_values\">";
  $possible_values_html .= "\n\t</td>\n";
  
  $enabled_html = "\t<td>\n\t\t";
  if (1 == $element_id) {
    $enabled_html .= "<INPUT TYPE=hidden NAME=\"element_enabled[1]\" VALUE=\"1\">\n";
    $enabled_html .= "Yes";
  }
  else {
    $enabled_html .= "<input type=checkbox value=1 ";
    $enabled_html .= "name=\"element_enabled[$element_id]\"";
    if ($element_enabled) $enabled_html .= " CHECKED";
  }
  $enabled_html .= "\n\t</td>\n";

  return "<tr>\n".$label_html.$type_html.$column_html.$order_html.
    $default_value_html.$possible_values_html.$enabled_html."</tr>\n";
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

$add_new_element = array_key_exists("add_element", $_GET);

# Get all existing element definitions
$sql = "SELECT * FROM svrinfo_element_definitions ORDER BY element_id";
$rst = $con->execute($sql);

# Get a list of supported element types into $options
$element_type = $rst->fields['element_type'];
$sql = 'SHOW COLUMNS FROM svrinfo_element_definitions LIKE \'element_type\'';
$rst2 = $con->execute($sql);
if (!$rst2) {
  db_error_handler ($con, $sql);
  exit;
}
$options = explode("','",preg_replace("/(enum|set)\('(.+?)'\)/","\\2",$rst2->fields['Type']));

# Define prototype of new element
$new_element = array(
  "element_id" => 0,
  "element_label" => "",
  "element_type" => $options,
  "element_column" => "1",
  "element_order" => "",
  "element_default_value" => "",
  "element_possible_values" => "",
  "element_enabled" => 1,
);

$page_title = "Edit element definitions";
start_page($page_title, true);

?>

<div id="Main">
  <div id="Content">
    <form action="edit-definitions-2.php" method="post">
    <input type=hidden name=company_id value="<?php echo $company_id ?>">
    <input type=hidden name=server_id value="<?php echo $server_id ?>">
    <input type=hidden name=return_url value="<?php echo $return_url ?>">
      <table class=widget cellspacing=1>
        <tr>
          <td class=widget_header colspan=7>Edit Server Info</td>
        </tr>
        <tr>
          <th>Label</th><th>Type</th><th>Column</th><th>Order</th><th>Default Value</th><th>Possible Values</th><th>Enabled</th>
        </tr>
        <?php 
          while (!$rst->EOF) { 
            echo show_row($rst->fields);
            $rst->movenext(); 
          }
          if ($add_new_element) {
            echo show_row($new_element);
          }
        ?>
        <tr>
          <td class=widget_content_form_element>
            <input class=button type=submit value="Save Changes">
          </td>
          <?php if (!$add_new_element) { ?>
            <td colspan=3>
              <input class=button type=button
                value="Add new element" 
                onclick="javascript:location.href='edit-definitions.php?add_element=1<?php echo
                "&server_id=$server_id&company_id=$company_id&return_url=$return_url" ?>';" >
            </td>
          <?php } ?>
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
$con->close();
end_page();
?>
