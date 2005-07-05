<?php
/**
 * Sidebar box for info
 *
 * $Id: sidebar.php,v 1.18 2005/07/05 16:04:43 gpowers Exp $
 */

//$con->debug = 1;

global $display_on;

// Set company_accounting
if ($display_on == "company_accounting") {
    $company_accounting = 1;
}

# For each info type (eg, "servers") we need to display a header. Within that
# section we need to display the "name" element_type for each instance of that
# section (eg "mail server"), which is a link to the full details of that 
# instance. Finally, within each name section we display any details marked
# to be shown in the side bar.

# Get a list of the info types we should be showing
$sql = "SELECT info_types.info_type_id, info_types.info_type_name ";
$sql .= "FROM info_types, info_display_map ";
$sql .= "WHERE info_types.info_type_record_status = 'a' ";
$sql .= "AND info_display_map.display_on = '" . $display_on . "' ";
$sql .= "AND info_types.info_type_id = info_display_map.info_type_id ";
$sql .= "ORDER BY info_types.info_type_order ";

$toprst = $con->execute($sql);

if (!$toprst) {
  db_error_handler ($con, $sql);
  exit;
}

// Loop through each type of info
while (!$toprst->EOF) {
    $info_type_id = $toprst->fields['info_type_id'];
    $info_type_name = $toprst->fields['info_type_name'];

    if (!$company_accounting) {
        $info_rows .= "<div id='info_item'>
            <table class=widget cellspacing=1 width=\"100%\">
                <tr>
                    <td class=widget_header colspan=2>$info_type_name</td>
                </tr>\n";
    };
    
    // Find the elements to display in sidebar under name
    // (there may be none)
    $sql = "SELECT element_id,  element_label, element_type ";
    $sql .= "FROM info_element_definitions ";
    $sql .= "WHERE element_display_in_sidebar = 1 ";
    $sql .= "AND info_type_id = $info_type_id ";
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
    $sql = "SELECT distinct info.info_id FROM info, info_map ";
    $sql .= "WHERE info.info_id=info_map.info_id ";
    $sql .= "AND info_map.info_type_id = $info_type_id ";
    //$sql .= "AND info.element_id = $name_element_id ";
    
    if ($company_id) {
        $sql .= "AND info_map.company_id = $company_id ";
    } else {
        $company_id = 0;
    }

    if ($division_id) {
        $sql .= "AND info_map.division_id = '" . $division_id . "' ";
    }

    if ($contact_id) {
        $sql .= "AND info_map.contact_id = $contact_id ";
    } else {
        $contact_id = 0;
    }
    
    if (($company_accounting) && (!$division_id)) {
        $sql .= "AND info_map.division_id = '' ";
    }

    $sql .= "AND info.info_record_status='a'";
    $rst = $con->execute($sql);

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

      $info_rows .= $info_link;
      */

      # If we should show fields under link, generate them now
      #echo "count=".count($el
        if (!$division_id) {
	    $sqldiv = "select division_name from company_division LEFT JOIN info_map on company_division.division_id = info_map.division_id where info_id= '" . $info_id  . "' ";
	    $rstdiv = $con->execute($sqldiv);
	    if (!$rstdiv->EOF) {
                $info_rows .= '<tr><td class="sublabel"><strong>' . _("Division") . '</strong></td>';
                $info_rows .= '<td><strong>' . $rstdiv->fields['division_name'] . '</strong></td></tr>';
	    }
        }



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
                  	$info_rows .= "<tr><td class=sublabel>" . $field . "</td><td class=widget_content>";
		  	if ($element_types[$value] == 'checkbox' && $rst2->fields['value'] == '1' ) {
		  		$info_rows = $info_rows . "yes</td></tr>";
			} else {
                        	$info_rows = $info_rows . nl2br($rst2->fields['value']) . "</td></tr>";
		  	}
		  }
		  $rst2->movenext();
                }
              }
          }
      }
      $rst->movenext();
    }

    // Add New button
    if (!$company_accounting) {
        $info_rows .= "<tr> <td class=widget_content_form_element colspan=2>";
    }


    if (((!$empty) && ($company_accounting)) || (!$company_accounting)) {
        if ($company_accounting) {
             $info_rows .= "<tr><td colspan=2>";
        }
	if ($empty) {
        	$info_rows .= "<br /> <input class=button type=button value=\"" . _("New");
         	$info_rows .= "\" onclick=\"javascript: location.href='$http_site_root/plugins/info/edit.php?info_id=$info_id&new_info=true&company_id=$company_id&contact_id=$contact_id&division_id=$division_id&info_type_id=$info_type_id&return_url=$return_url';\">";
	}
	else {
        	$info_rows .= "<br /> <input class=button type=button value=\"" . _("View");
         	$info_rows .= "\" onclick=\"javascript: location.href='$http_site_root/plugins/info/one.php?info_id=$info_id&company_id=$company_id&contact_id=$contact_id&division_id=$division_id&info_type_id=$info_type_id&return_url=$return_url';\">";
	};


        if ($company_accounting) {
            $info_rows .= " " . $info_type_name . " Info";
        }

    }

    if (!$company_accounting) {
        $info_rows .= "</td> </tr>\n";

        $info_rows .= "</table>\n</div>";
    }

   $toprst->movenext();
};

echo $info_rows;

?>
