<?php
/**
 * The main page for the info plugin
 *
 * $Id: info.php,v 1.5 2005/02/11 13:53:01 braverock Exp $
 */

// include the common files
require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once('info.inc');

//set target and see if we are logged in
$session_user_id = session_check();

$msg = $_GET['msg'];

$return_url = "info.php";

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

start_page($info_heading);
?>

<div id="Main">
  <div id="Content">
    <table class=widget cellspacing=1 width="100%">
      <tr>
         <td class=widget_header colspan=2><?php echo $info_list; ?></td>
      </tr>
      <tr>
        <?php if ($sort_by_companyname) { ?>
          <td class=widget_label><?php echo $company_heading; ?></td>
          <td class=widget_label><?php echo $info_heading; ?></td>
        <?php } else { ?>
          <td class=widget_label><?php echo $info_heading; ?></td>
          <td class=widget_label><?php echo $company_heading; ?></td>
        <?php } ?>
      </tr>
      <?php
      if ($rst) {
            while (!$rst->EOF) {
              $info_name = $rst->fields['value'];
              $company_name = $rst->fields['company_name'];
              $company_id = $rst->fields['company_id'];
              $info_id = $rst->fields['info_id'];

              $link = "one.php?info_id=$info_id&company_id=$company_id&return_url=$return_url";
              $info_link = "<a href='$link'>$info_name</a>";

              echo "<tr><td class=widget_content>";
              if ($sort_by_companyname) {
                echo "$company_name</td><td class=widget_content>$info_link";
              }
              else {
                echo "$info_link</td><td class=widget_content>$company_name";
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

/**
 * $Log: info.php,v $
 * Revision 1.5  2005/02/11 13:53:01  braverock
 * - add phpdoc
 * - remove references to server_info and replace with just info
 *
 */
?>