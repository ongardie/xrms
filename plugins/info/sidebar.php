<?php
/**
 * Sidebar box for info
 *
 * $Id: sidebar.php,v 1.2 2004/07/14 19:03:27 gpowers Exp $
 */

//$con->debug = 1;

global $display_on;

# set a reasonable default
if (!$display_on) {
    $display_on = "company_sidebar";
}

$sql = "SELECT info_types.info_type_id, info_types.info_type_name FROM info_types, info_display_map ";
$sql .= "WHERE info_types.info_type_status = 'a' ";
if ($display_on != "all") {
    $sql .= "AND info_display_map.display_on = '" . $display_on . "' ";
}
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

        $info_rows = "<div id='note_sidebar'>
            <table class=widget cellspacing=1 width=\"100%\">
            <tr>
            <td class=widget_header colspan=2>$info_type_name</td>
            </tr>\n
        ";

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


        #//build the info sql query
        $sql = "SELECT info.value, info.info_id FROM info, info_map ";
        $sql .= "WHERE info.info_id=info_map.info_id ";
if ($company_id) {
        $sql .= "AND info_map.company_id=$company_id ";
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
                $server_link = "<a href='$http_site_root/plugins/info/one.php";
                $server_link .= "?info_id=".$rst->fields['info_id'];
                $server_link .= "&company_id=$company_id'>";
                $server_link .= $rst->fields['value']."</a>";

                $info_rows .= "
                  <tr>
                    <td class=widget_content>
                      <font class=note_label>
                        $server_link
                      </font>
                    </td>
                  </tr>
                ";
                $rst->movenext();
            }
        }

        # Add New button
        $info_rows .= "
             <tr>
                <td class=widget_content_form_element colspan=5>
                  <input class=button type=button value=\"New\"
                    onclick=\"javascript: location.href='$http_site_root/plugins/info/edit.php?info_id=0&company_id=$company_id&info_type_id=$info_type_id';\">
                  </td>
              </tr>\n;
        ";

        //now close the table, we're done
        $info_rows .= "</table>\n</div>";

        echo $info_rows;
        $toprst->movenext();
    }
}
# End Info Type Loop
?>
