<?php
/**
 * Sidebar box for info
 *
 * $Id: sidebar.php,v 1.9 2005/01/08 21:43:06 gpowers Exp $
 */

// $con->debug = 1;

global $display_on;

// Set imbedded
if ($display_on != "company_accounting") {
    $company_accounting = 1;
}

$sql = "SELECT info_types.info_type_id, info_types.info_type_name FROM info_types, info_display_map ";
$sql .= "WHERE info_types.info_type_record_status = 'a' ";
$sql .= "AND info_display_map.display_on = '" . $display_on . "' ";
$sql .= "AND info_types.info_type_id = info_display_map.info_type_id ";
$sql .= "ORDER BY info_types.info_type_order ";

$toprst = $con->execute($sql);

if (!$toprst) {
  db_error_handler ($con, $sql);
  exit;
}

# Loop through each type of info
if ($toprst) {
    while (!$toprst->EOF) {
        $info_type_id = $toprst->fields['info_type_id'];
        $info_type_name = $toprst->fields['info_type_name'];

        if ($company_accounting) {
            $info_rows .= "<div id='note_sidebar'>
                <table class=widget cellspacing=1 width=\"100%\">
                    <tr>
                        <td class=widget_header colspan=2>$info_type_name</td>
                    </tr>\n";
        };

        #//Find which element_id contains the "Name"
        $sql = "SELECT element_id ";
        $sql .= "FROM info_element_definitions ";
        $sql .= "WHERE element_label ";
        $sql .= "LIKE 'Name' AND info_type_id = $info_type_id ";
        $sql .= "LIMIT 1 ";
        $rst = $con->execute($sql);
        if ($rst) {
            $name_element_id = $rst->fields['element_id'];
        }


        $sql = "SELECT element_id,  element_label ";
        $sql .= "FROM info_element_definitions ";
        $sql .= "WHERE element_display_in_sidebar = 1 ";
        $sql .= "AND info_type_id = $info_type_id ";
        $sql .= "AND element_label NOT LIKE 'Name' ";
        $sql .= "ORDER BY element_order";
        $rst = $con->execute($sql);
        if ($rst) {
            while (!$rst->EOF) {
                $element[$rst->fields['element_label']]
                    = $rst->fields['element_id'];
                $rst->movenext();
            }
        }


        #//build the info sql query
        $sql = "SELECT info.value, info.info_id FROM info, info_map ";
        $sql .= "WHERE info.info_id=info_map.info_id ";
        if ($company_id) {
            $sql .= "AND info_map.company_id=$company_id ";
        } else {
            $company_id = 0;
        }
        if ($division_id) {
            $sql .= "AND info_map.division_id = '" . $division_id . "' ";
        }
        if ((!$company_accounting) && (!$division_id)) {
            $sql .= "AND info_map.division_id = '' ";
        }
        $sql .= "AND info_map.info_type_id=$info_type_id  ";
        if ($name_element_id) {
            $sql .= "AND info.element_id=$name_element_id ";
        }
        $sql .= "AND info.info_record_status='a'";
        $rst = $con->execute($sql);
        if (!$rst) {
            db_error_handler ($con, $sql);
            exit;
        }

        if ($rst) {
          while (!$rst->EOF) {
            $server_link = "<tr><td class=sublabel><a href='$http_site_root/plugins/info/one.php";
            $server_link .= "?info_id=".$rst->fields['info_id'];
            $server_link .= "&company_id=$company_id'>";
            $server_link .= $rst->fields['value']."</a></td><td></td></tr>";

    $fields = array();
    $values = array();
    foreach ($element as $field=>$value) {
        $fields[] = $field;
        $values[] = $value;
    }

    foreach ($fields as $field) {

     $value = $element[$field];
                $sql2 = "SELECT info.value, info.info_id FROM info, info_map ";
                $sql2 .= "WHERE info.info_id = " . $rst->fields['info_id'];
                $sql2 .= " AND info.element_id=$value ";
                $rst2 = $con->execute($sql2);
                if ($rst2) {
                  if (!$rst2->EOF) {
                    $server_link .= "<tr><td class=sublabel>" . $field . "</td><td class=widget_content>"
                        . $rst2->fields['value'] . "</td></tr>";
                  }
                }
             }
        if ($company_accounting) {
                $info_rows .= "
                  <tr>
                    <td class=widget_content>";
        };
                $info_rows .= "
                      <font class=note_label>
                        $server_link
                      </font>";
        if ($company_accounting) {
                $info_rows .= "
                    </td>
                  </tr>";
        };
                $rst->movenext();
            }
        }

       # Add New button

        if ($company_accounting) {
           $info_rows .= "<tr>
               <td class=widget_content_form_element colspan=5>";
        };
        if (!$server_link) {
            if (!$company_accounting) {
                 $info_rows .= "<tr><td colspan=2>";
            };
        $info_rows .= "<br />
          <input class=button type=button value=\"" . _("New") . " " . $info_type_name . " Info\"
            onclick=\"javascript: location.href='$http_site_root/plugins/info/edit.php?info_id=0&company_id=$company_id&contact_id=$contact_id&division_id=$division_id&info_type_id=$info_type_id';\">";
        };
        if (!$company_accounting) {
             $info_rows .= "<tr><td colspan=2>";
        };
        if ($company_accounting) {
           $info_rows .= "</td>
             </tr>\n";

       //now close the table, we're done
            $info_rows .= "</table>\n</div>";
        };

       $toprst->movenext();
    }
}

echo $info_rows;

# End Info Type Loop
?>
