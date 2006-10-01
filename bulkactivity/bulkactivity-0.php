<?php
  /**
  *
  * bulkactivity-0.
  *
  * $Id: bulkactivity-0.php,v 1.1 2006/10/01 00:15:06 braverock Exp $
  */

  require_once('include-locations-location.inc');

  require_once($include_directory . 'vars.php');
  require_once($include_directory . 'utils-interface.php');
  require_once($include_directory . 'utils-misc.php');
  require_once($include_directory . 'adodb/adodb.inc.php');
  require_once($include_directory . 'adodb-params.php');

  $session_user_id = session_check();
  $msg = $_GET['msg'];
  $campaign_id = $_GET['campaign_id'];
  $scope = $_GET['scope'];
  $return_url = $_GET['return_url'];
  if (!$return_url) $return_url = "../campaigns/one.php?campaign_id=" . $campaign_id;

  //$scope = "campaigns_company_sidebar";
  //echo $scope; echo "<br>"; exit;
  //echo $campaign_id; echo "<br>"; exit;


  //get the database connection
  if (!$xcon) {
      $con=get_xrms_dbconnection();
  }
  //$con->debug = 1;

  //hack to not show continue button if no templates are found
  $show_continue=true;

  $array_of_contacts = array();
  switch ($scope) {

    case "companies":
      //Nic: DO SOMETHING DIFFERENT FOR COMPANIES BECAUSE YOU HAVE TO GET THE CONTACT IDS FROM WITHIN THE COMPANY
      //I only pass companies the end of the sql, ie the from and the where.
      //I should have really done this in the first place for all of them.
      $from=$_SESSION["search_sql"]["from"];
      //var_dump($_SESSION["search_sql"]);
      $where=$_SESSION["search_sql"]["where"];
      $order_by=$_SESSION["search_sql"]["order"];
      $from.=" LEFT JOIN contacts cont on cont.company_id=c.company_id ";
      $sql="SELECT cont.contact_id ".$from.$where.$order_by;
      $rst = $con->execute($sql);

      if ($rst) {
        while (!$rst->EOF) {
          array_push($array_of_contacts, $rst->fields['contact_id']);
          $rst->movenext();
        }
      } else {
        db_error_handler ($con, $sql);
      }
      //echo "1<br>";
      break;

    case 'contact_list':
      getGlobalVar($contact_list, 'contact_list');
      if ($contact_list) {
        $array_of_contacts=explode(",",$contact_list);
      } else {
        db_error_handler ($con, $sql);
      }
      //echo "2<br>";
      break;

    case 'campaigns_company_sidebar':
      $sql= "SELECT cont.contact_id FROM contacts cont, company_campaign_map ccm
             WHERE cont.company_id = ccm.company_id and ccm.campaign_id = $campaign_id and cont.contact_record_status = 'a'";
      $rst = $con->execute($sql);
      if ($rst) {
        while (!$rst->EOF) {
          //make sure contact_id isn't null, negative, or false before adding it
          if ($rst->fields['contact_id']){
              array_push($array_of_contacts, $rst->fields['contact_id']);
          }
          $rst->movenext();
        }
        array_unique($array_of_contacts);
        //print_r($array_of_contacts);
      } else {
        db_error_handler ($con, $sql);
      }
    //echo "3<br>";
    break;

    default:
      $search_sql=$_SESSION["search_sql"];
      list($select, $from) = spliti("FROM", $search_sql,2);//need limit otherwise from_unixtime functions get captured
      list($from, $orderby) = spliti("order by", $from);
      list($from, $groupby) = spliti("group by", $from);

      $sql= "SELECT cont.contact_id FROM ".$from;
      $sql.=" AND cont.contact_id IS NOT NULL ";
      //look out for group bys...
      if($groupby)$sql.="GROUP BY ".$groupby;
      if($orderby)$sql.=" ORDER BY ".$orderby;
      //echo $sql; exit;

      $rst = $con->execute($sql);

      if ($rst) {
        while (!$rst->EOF) {
          //make sure contact_id isn't null, negative, or false before adding it
          if ($rst->fields['contact_id']){
              array_push($array_of_contacts, $rst->fields['contact_id']);
          }
          $rst->movenext();
        }
        array_unique($array_of_contacts);
        //print_r($array_of_contacts);
      } else {
        db_error_handler ($con, $sql);
      }
      //echo "4<br>";
      break;
  } //END switch/CASE

  $_SESSION['array_of_contacts'] = serialize($array_of_contacts);

  if (is_array($array_of_contacts))
    $imploded_contacts = implode(',', $array_of_contacts);
  else
    echo _("WARNING: No array of contacts!") . "<br>";

  $con = get_xrms_dbconnection();
  //$con->debug = 1;

  // create menus
  //$user_menu = get_user_menu($con, $session_user_id);
  $user_menu = get_user_menu($con, $session_user_id, false, 'user_id', false);
  $activity_type_menu=get_activity_type_menu($con);

  $sql = "select cont.contact_id, cont.email, cont.first_names, cont.last_name, cont.title, c.company_name, u.username
  from contacts cont, companies c, users u
  where c.company_id = cont.company_id
  and c.user_id = u.user_id
  and cont.contact_id in ($imploded_contacts)
  and contact_record_status = 'a' order by c.company_name,cont.last_name asc";

  $_x = 1;

  $rst = $con->execute($sql);
  if ($rst) {
      while (!$rst->EOF) {
        $contact_rows .= '<tr>';
        $contact_rows .= '<td class="widget_content_form_element">';
        $contact_rows .= '<input type="checkbox" name="array_of_contacts[]" id="array_of_contacts_' . $_x++ . '" value="' . $rst->fields['contact_id'] . '" checked="checked"></td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['company_name'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['username'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['title'] . '</td>';
        //$contact_rows .= '<td class="widget_content">' . $rst->fields['email'] . '</td>';
        $contact_rows .= "</tr>\n";
        $rst->movenext();
    }

    $rst->close();
  }

  $con->close();

  $page_title = _("Assegna una attività ai contatti selezionati");
  start_page($page_title, true, $msg);

?>

<div id="Main">
     <form action="bulkactivity-1.php" method="post">
       <div id="Content">
        <input type=hidden name=array_of_contacts value="<?php  echo $array_of_contacts; ?>">
        <table class="widget" cellspacing="1">
            <tr>
                <td class=widget_header colspan=5><?php echo _("Conferma Selezione"); ?></td>
            </tr>
            <tr>
                <td class="widget_label">&nbsp;</td>
                <td class="widget_label"><?php echo _("Company"); ?></td>
                <td class="widget_label"><?php echo _("Owner"); ?></td>
                <td class="widget_label"><?php echo _("Contact"); ?></td>
                <td class="widget_label"><?php echo _("Title"); ?></td>
                <!-- <td class="widget_label"><?php echo _("E-Mail"); ?></td>//-->
            </tr>
            <?php  echo $contact_rows ?>
        </table>
     </div>
     <!-- right column //-->
     <div id="Sidebar">

        <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
        <input type=hidden name=campaign_id value="<?php  echo $campaign_id; ?>">

        <table class=widget cellspacing=1>
            <tr>
            <td class=widget_header colspan=2><?php echo _("New Activity"); ?></td>
            </tr>
            <tr>
            <td class=widget_label><?php echo _("Summary"); ?></td>
            <td class=widget_content_form_element><input type=text size=60 name=activity_title></td>
            </tr>
            <tr>
            <td class=widget_label><?php echo _("User"); ?></td>
            <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
            <td class=widget_label><?php echo _("Type"); ?></td>
            <td class=widget_content_form_element><?php  echo $activity_type_menu; ?></td>
            </tr>
            <!--
            <tr>
            <td class=widget_label><?php echo _("Scheduled Start"); ?></td>
            <td class=widget_content_form_element>
                    <input type=text ID="f_date_1" name=scheduled_at value="<?php  echo $scheduled_at; ?>">
                    <img ID="f_trigger_1" style="CURSOR: hand" border=0 src="../img/cal.gif">
            </td>
            </tr>
            //-->
            <tr>
            <td class=widget_label><?php echo _("Scheduled End"); ?></td>
            <td class=widget_content_form_element>
                    <input type=text ID="f_date_2" name=ends_at value="<?php  echo $ends_at; ?>">
                    <img ID="f_trigger_2" style="CURSOR: hand" border=0 src="../img/cal.gif">
            </td>
            </tr>
            <tr>
            <td class=widget_label><?php echo _("Activity description"); ?></td>
            <td class=widget_content_form_element><textarea rows=5 cols=40 name=activity_description></textarea></td>
            </tr>
            <tr>
                <td class="widget_content_form_element" colspan="2">
                    <input type="submit" class="button" value="<?php echo _("Continue"); ?>">
                </td>
            </tr>
        </table>
      </div>
   </form>
</div>

<!--
<script type="text/javascript">
Calendar.setup({
        inputField     :    "f_date_1",      // id of the input field
        ifFormat       :    "%Y-%m-%d",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "f_trigger_1",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });
</script>
//-->

<script type="text/javascript">
    Calendar.setup({
        inputField     :    "f_date_2",      // id of the input field
        ifFormat       :    "%Y-%m-%d",       // format of the input field
        showsTime      :    false,            // will display a time selector
        button         :    "f_trigger_2",   // trigger for the calendar (button ID)
        singleClick    :    false,           // double-click mode
        step           :    1,                // show all years in drop-down boxes (instead of every other year as default)
        align          :    "Bl"           // alignment (defaults to "Bl")
    });
</script>



<?php

end_page();

 /**
  * $Log: bulkactivity-0.php,v $
  * Revision 1.1  2006/10/01 00:15:06  braverock
  * - Initial Revision of Bulk Activity and Bulk Assignment contributed by Danielle Baudone
  *
  * Revision 1.0  2006/01/15 01:18:00  dbaudone
  */
?>