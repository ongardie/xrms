<?php
/**
 * Sidebar box for serverinfo
 *
 * $Id: sidebar.php,v 1.1 2004/07/06 19:57:02 gpowers Exp $
 */

//$con->debug = 1;

global $server_list;

$this = $_SERVER['REQUEST_URI'];
 
$server_rows = "<div id='note_sidebar'>
  <table class=widget cellspacing=1 width=\"100%\">
  <tr>
  <td class=widget_header colspan=2>$server_list</td>
  </tr>\n
";

#//build the svrinfo sql query
$sql = "SELECT svrinfo.value, svrinfo.server_id FROM svrinfo, svrinfo_servers ";
$sql .= "WHERE svrinfo.server_id=svrinfo_servers.server_id ";
$sql .= "AND svrinfo_servers.company_id=$company_id ";
$sql .= "AND svrinfo.element_id=1";
$rst = $con->execute($sql);
if (!$rst) {
  db_error_handler ($con, $sql);
  exit;
}

while (!$rst->EOF) {
  $server_link = "<a href='$http_site_root/plugins/serverinfo/one.php";
  $server_link .= "?server_id=".$rst->fields['server_id'];
  $server_link .= "&company_id=$company_id&return_url=$this'>";
  $server_link .= $rst->fields['value']."</a>";
  
  $server_rows .= "
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
# Add New button
$server_rows .= "
   <tr>
      <td class=widget_content_form_element colspan=5>
        <input class=button type=button value=\"New\" 
          onclick=\"javascript: location.href='$http_site_root/plugins/serverinfo/edit.php?server_id=0&company_id=$company_id&return_url=$this';\">
        </td>
    </tr>\n;
";

//now close the table, we're done
$server_rows .= "</table>\n</div>";

echo $server_rows;

?>
