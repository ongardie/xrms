<?php
/**
 * The main page for the info plugin
 *
 * $Id: info.php,v 1.1 2004/07/14 16:50:16 gpowers Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once('info.inc');

//set target and see if we are logged in
$this = $_SERVER['REQUEST_URI'];
$session_user_id = session_check( $this );

$msg = $_GET['msg'];

//connect to the database
$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment the debug line to see what's going on with the query
//$con->debug = 1;

# Create a list of all servers

$sql  = "SELECT info.info_id,info.value,companies.company_name,companies.company_id ";
$sql .= "FROM info, info_map, companies ";
$sql .= "WHERE info.element_id='1' ";
$sql .= "AND info.info_id=info_map.info_id ";
$sql .= "AND info_map.company_id=companies.company_id ";
$sql .= "ORDER BY ";
$sql .= ($sort_by_companyname) ? "companies.company_name" : "info.value";

$rst = $con->execute($sql);

start_page($server_info_heading);
?>

<div id="Main">
  <div id="Content">
    <table class=widget cellspacing=1 width="100%">
      <tr>
         <td class=widget_header colspan=2><?php echo $server_list; ?></td>
      </tr>
      <tr>
        <?php if ($sort_by_companyname) { ?>
          <td class=widget_label><?php echo $company_heading; ?></td>
          <td class=widget_label><?php echo $server_heading; ?></td>
        <?php } else { ?>
          <td class=widget_label><?php echo $server_heading; ?></td>
          <td class=widget_label><?php echo $company_heading; ?></td>
        <?php } ?>
      </tr>
      <?php
      if ($rst) {
            while (!$rst->EOF) {
              $server_name = $rst->fields['value'];
              $company_name = $rst->fields['company_name'];
              $company_id = $rst->fields['company_id'];
              $info_id = $rst->fields['info_id'];

              $link = "one.php?info_id=$info_id&company_id=$company_id&return_url=$this";
              $server_link = "<a href='$link'>$server_name</a>";

              echo "<tr><td class=widget_content>";
              if ($sort_by_companyname) {
                echo "$company_name</td><td class=widget_content>$server_link";
              }
              else {
                echo "$server_link</td><td class=widget_content>$company_name";
              }
              echo "</td></tr>\n";
              $rst->movenext();
              }
      }
      ?>
  </div>

</div>

<?php

//close the database connection
$con->close();

end_page();

?>
