<?php
/**
 * Sidebar box for info
 *
 * $Id: inline-display.php,v 1.3 2005/04/01 20:07:31 ycreddy Exp $
 */

global $company_id, $contact_id;

    if (!$contact_id) {
        $contact_id = 0;
    }
    
    if (!$company_id) {
    	    $sql = "SELECT company_id
            FROM contacts
            WHERE contact_id = '" . $contact_id . "'";
    $rst = $con->SelectLimit($sql, 1);
    $company_id = $rst->fields['company_id'];
    }
    
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

    // Find the elements to display in sidebar under name
    // (there may be none)
    $sql = "SELECT element_id,  element_label, element_type ";
    $sql .= "FROM info_element_definitions ";
    $sql .= "WHERE element_display_in_sidebar = 1 ";
    $sql .= "AND info_type_id = '" . $info_type_id . "' ";
    $sql .= "AND element_type != 'name' ";
    $sql .= "ORDER BY element_order";
    $rst = $con->execute($sql);

    if ($rst) {
        while (!$rst->EOF) {
            $element[$rst->fields['element_label']]
                = $rst->fields['element_id'];
	    $element_types[$rst->fields['element_id']]
                = $rst->fields['element_type'];
            $rst->movenext();
        }
    }

    // Generate list of instances of this info type
    $sql = "SELECT info.info_id FROM info, info_map ";
    $sql .= "WHERE info.info_id=info_map.info_id ";
    //$sql .= "AND info_map.info_type_id = '" . $info_type_id . "' ";
    //$sql .= "AND info.element_id = $name_element_id ";
    
    //if ($company_id) {
        $sql .= "AND info_map.company_id = '" . $company_id ."' ";
    //}

    if ($division_id) {
        $sql .= "AND info_map.division_id = '" . $division_id . "' ";
    }

    //if ($contact_id) {
        $sql .= "AND info_map.contact_id = '" . $contact_id . "' ";
    //}
    
    if  (!$division_id) {
        $sql .= "AND info_map.division_id = '' ";
    }

    $sql .= "AND info.info_record_status='a'";
    $rst = $con->SelectLimit($sql,1);

    if (!$rst) {
        db_error_handler ($con, $sql);
        exit;
    }

    $empty = 1;
    $info_id=0;
    # Loop through each instance type and get info to display
    while (!$rst->EOF) {

      // Save the info_id
      $info_id = $rst->fields['info_id'];
      $empty = 0;

      /*
      $info_link = "<tr><td colspan=2 class=widget_content><a href='$http_site_root/plugins/info/one.php";
      $info_link .= "?info_id=" . $rst->fields['info_id'];
      $info_link .= "&company_id=$company_id";
      if ($contact_id) {
            $info_link .="&contact_id=$contact_id";
      }
      if ($return_url) {
            $info_link .="&return_url=$return_url";
      }
      $info_link .= "'>".$rst->fields['value'] . "</a></td></tr>";

      $sidebar_string .= $info_link;
      */

      # If we should show fields under link, generate them now
      #echo "count=".count($el
      if (!empty($element)) {
          $fields = array();
          $values = array();

          foreach ($element as $field=>$value) {
              $fields[] = $field;
              $values[] = $value;
          }

          foreach ($fields as $field) {
              $value = $element[$field];
              $sql2 = "SELECT info.value, info.info_id FROM info ";
              $sql2 .= "WHERE info.info_id = " . $rst->fields['info_id'];
              $sql2 .= " AND info.element_id=$value ";
              $rst2 = $con->execute($sql2);

              if ($rst2) {
                while (!$rst2->EOF) {
		  if (Trim($rst2->fields['value']) ) {
                  	$sidebar_string .= "<tr><td class=sublabel>" . $field . "</td><td class=widget_content>";
		  	if ($element_types[$value] == 'checkbox' && $rst2->fields['value'] == '1' ) {
		  		$sidebar_string = $sidebar_string . "yes</td></tr>";
			} else {
                        	$sidebar_string = $sidebar_string . $rst2->fields['value'] . "</td></tr>";
		  	}
		  }
		  $rst2->movenext();
                }
              }
          }
      }
      $rst->movenext();
    }

return $sidebar_string;

?>
