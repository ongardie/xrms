<?php
/**
 * Details about one item
 *
 * $Id: one.php,v 1.15 2005/04/01 21:50:14 ycreddy Exp $
 *
 */

//include required files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');

require_once('info.inc');

$session_user_id = session_check();

$msg = $_GET['msg'];

# Always retrieve, and pass on, server and company ID
$info_id      = $_GET['info_id'];
$info_type_id = $_GET['info_type_id'];
$company_id   = $_GET['company_id'];
$contact_id   = $_GET['contact_id'];
$return_url   = $_GET['return_url'];

global $http_site_root;


$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

$sql = "SELECT info_type_id FROM info_map WHERE info_id = $info_id";
$rst = $con->execute($sql);
if ($rst) {
    if (!$rst->EOF) {
        $info_type_id = $rst->fields['info_type_id'];
    }
}

$sql = "SELECT info_type_name FROM info_types WHERE info_type_id = $info_type_id";
$rst = $con->execute($sql);
if ($rst) {
    if (!$rst->EOF) {
        $info_type_name = $rst->fields['info_type_name'];
    }
}

# Get details of all defined elements
$sql  = "SELECT info_element_definitions.* FROM info_element_definitions ";
$sql .= "WHERE info_element_definitions.element_enabled=1 ";
$sql .= "AND info_element_definitions.info_type_id=$info_type_id ";
$sql .= "ORDER BY element_column, element_order";
$all_elements = $con->execute($sql);
if (!$all_elements) {
  db_error_handler ($con, $sql);
  exit;
}

# Get details of all elements for this server
$sql2 = "SELECT value, element_id FROM info WHERE info_id='$info_id'";
$rst = $con->execute($sql2);
if (!$rst) {
  db_error_handler ($con, $sql);
  exit;
}

# Build an array of this server's elements with potential multiple values
# for each element indexed by element_id. The value of this array
# is itself an array containing info values.
$this_info = array();
if(!$rst->EOF) {
    while (!$rst->EOF) {
    	$elem_id = $rst->fields['element_id'];
	$value = $rst->fields['value'];
	// get the values array for this element
	$values = $this_info[$elem_id]; 
	// if it exists, add it at the end
	if ($values) {
		$values[count($values) + 1] = $value;
		$this_info[$elem_id] = $values;
	} else {
		// create a new values array and add the value
		$values = array();
		$values[1] = $value;
		$this_info[$elem_id] = $values;
	}
        $rst->movenext();
    }
}

# Build output, one array per column of display
# Step through each defined element and get the value
# for it for this server
$data = array();
while (!$all_elements->EOF) {
  $element_id = $all_elements->fields['element_id'];
  $column = $all_elements->fields['element_column'];

  # Get the values array for this element id and built html for each
  # instance of an element.
  if (isset($this_info[$element_id])) {
    $values = $this_info[$element_id];
  }
  else {
    // Trick to eliminate the duplication of code when the value is not set
    // Can't use false here and using spaces since these would never be
    // stored as values by XRMS.
    $values[1] = '    ';
  }

  foreach ($values as $value) {
      	#If this element is not defined, use default value
	if ($value == '    ') {
      		$value = $all_elements->fields['element_default_value'];
	}
		
     	# Use words for checkbox status
     	if ($all_elements->fields['element_type'] == "checkbox") {
            $print_value = (1 == $value) ? $checkbox_set : $checkbox_clear;
     	}
        else {
           $print_value = $value;
        }
        // Don't show the Name element 
        if ( $all_elements->fields['element_label'] != 'Name' ) {
   	   $data[$column] .= "<tr>\n";
  	   $data[$column] .= "\t<td class=sublabel>".$all_elements->fields['element_label']."</td>\n";
  	   $data[$column] .= "\t<td class=clear>".$print_value."</td>\n";
  	   $data[$column] .= "</tr>\n";
        } 
     }
  $all_elements->movenext();
}

# Calculate width of each column
$pcent = (count($data)>0) ? round(100/count($data)) : 100;
$column_width = $pcent."%";

# Retrieve the name of the company owning this info item
$sql = "SELECT company_name FROM companies WHERE company_id=$company_id";
$company_info = $con->execute($sql);
if (!$company_info) {
  db_error_handler ($con, $sql);
  exit;
}
if ($company_info) {
    $info_name = $company_info->fields['company_name'];
}
if($contact_id) {
    $sql="SELECT first_names, last_name from contacts where contact_id=$contact_id";
    $rst=$con->execute($sql);
    if ($rst) {
        $last_name = $rst->fields['last_name'];
        $first_names = $rst->fields['first_names'];
        $info_name = $first_names.' '.$last_name;
    } else {
        db_error_handler ($con,$sql);
    }
    
}
$page_title = $info_name . ": " . $info_type_name . ": " . $item_name;
start_page($page_title, true, $msg);


?>

<div id="Main">
  <div id="Content">
    <table class=widget cellspacing=1>
      <tr>
        <td class=widget_header>
          <?php echo _("Details"); ?>
        </td>
      </tr>
      <tr>
        <td class=widget_content>
          <table border=0 cellpadding=0 cellspacing=0 width=100%>
            <tr>
              <?php foreach ($data as $column) { ?>
                <td width=<?php echo $column_width ?> class=clear align=left valign=top>
                  <table border=0 cellpadding=0 cellspacing=0 width=100%>
                    <?php echo $column; ?>
                  </table>
                </td>
              <?php } ?>
            </tr>
          </table>
          <p><?php  echo $profile; ?>
        </td>
      </tr>
      <tr>
        <td class=widget_content_form_element>
          <input class=button type=button value="<?php echo _("Edit"); ?>"
            onclick="javascript: location.href='<?php echo "edit.php?info_id=$info_id&info_type_id=$info_type_id&contact_id=$contact_id&company_id=$company_id&return_url=$return_url"; ?>';">
          <input class=button type=button value="<?php echo _("Delete"); ?>"
            onclick="javascript: location.href='<?php echo "delete-2.php?info_id=$info_id&info_type_id=$info_type_id&contact_id=$contact_id&company_id=$company_id&return_url=$return_url"; ?>';">
          <input class=button type=button value="<?php echo _("New"); ?>"
            onclick="javascript: location.href='<?php echo "edit.php?info_id=$info_id&info_type_id=$info_type_id&new_info=true&contact_id=$contact_id&company_id=$company_id&return_url=$return_url"; ?>';">
          <input class=button type=button value="<?php echo _("Back"); ?>"
            onclick="javascript: location.href='<?php echo $http_site_root.$return_url; ?>';">
        </td>
      </tr>
    </table>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

    </div>

</div>

<?php

end_page();

/**
 * $Log: one.php,v $
 * Revision 1.15  2005/04/01 21:50:14  ycreddy
 * Added changes to handle  multiple instance values for an element type
 *
 * Revision 1.14  2005/03/18 20:54:37  gpowers
 * - added support for inline (custom fields) info
 *
 * Revision 1.13  2005/02/15 15:10:13  ycreddy
 * Included adodb-params.php so that Column name based lookup on a Result Set works properly on SQL Server
 *
 * Revision 1.12  2005/02/11 13:55:14  braverock
 * - fix handling of return_url
 * - remove references to server_info and replace with just info
 *
 * Revision 1.11  2005/02/11 00:49:11  braverock
 * - modified to correctly pass contact_id and return_url
 *
 */
?>
