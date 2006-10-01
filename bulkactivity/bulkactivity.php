<?php
  /**
  *
  * bulkactivity.
  *
  * $Id: bulkactivity.php,v 1.1 2006/10/01 00:15:06 braverock Exp $
  */

  require_once('include-locations-location.inc');

  require_once($include_directory . 'vars.php');
  require_once($include_directory . 'utils-interface.php');
  require_once($include_directory . 'utils-misc.php');
  require_once($include_directory . 'adodb/adodb.inc.php');
  require_once($include_directory . 'adodb-params.php');

  $session_user_id = session_check();
  $msg = $_GET['msg'];
  $scope = $_POST['scope'];
  //echo $scope;exit;
  $user_id = $_POST['user_id'];
  $activity_type_id = $_POST['activity_type_id'];
  $activity_title = $_POST['activity_title'];
  $ends_at = $_POST['ends_at'];
  $campaign_id = $_POST['campaign_id'];
  $return_url = $_POST['return_url'];


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
      //echo "1";
      break;

    case 'contact_list':
      getGlobalVar($contact_list, 'contact_list');
      if ($contact_list) {
        $array_of_contacts=explode(",",$contact_list);
      } else {
        db_error_handler ($con, $sql);
      }
      //echo "2";
      break;

    case 'campaigns_company_sidebar':
      $sql= "SELECT cont.contact_id FROM contacts cont, company_campaign_map ccm
             WHERE cont.company_id = ccm.company_id";
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
      //echo "3";
      break;
  } //END switch/CASE

  $_SESSION['array_of_contacts'] = serialize($array_of_contacts);
  //$_SESSION['return_url'] = $return_url;  non serve, anzi assegna il return_url come variabile di sessione e da poi noia in alcuni casi (ad es inserzione nuovo contatto)
  $_SESSION['activity_type_id'] = $acctivity_type_id;
  $_SESSION['activity_title'] = $activity_title;
  $_SESSION['scheduled_at'] = $scheduled_at;
  $_SESSION['ends_at'] = $ends_at;
  $_SESSION['user_id'] = $user_id;
  $_SESSION['campaign_id'] = $campaign_id;

  if (is_array($array_of_contacts))
    $imploded_contacts = implode(',', $array_of_contacts);
  else
    echo _("WARNING: No array of contacts!") . "<br>";

  $con = get_xrms_dbconnection();
  //$con->debug = 1;

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
        $contact_rows .= '<td class="widget_content">' . $rst->fields['email'] . '</td>';
        $contact_rows .= "</tr>\n";
        $rst->movenext();
    }

    $rst->close();
  }

  $con->close();

  $page_title = _("Confirm Recipients");
  start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action="bulkactivity-1.php" method="post">
        <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
        <input type=hidden name=user_id value="<?php  echo $user_id; ?>">
        <input type=hidden name=activity_type_id value="<?php  echo $activity_type_id; ?>">
        <input type=hidden name=activity_title value="<?php  echo $activity_title; ?>">
        <input type=hidden name=scheduled_at value="<?php  echo $scheduled_at; ?>">
        <input type=hidden name=ends_at value="<?php  echo $ends_at; ?>">
        <input type=hidden name=campaign_id value="<?php  echo $campaign_id; ?>">

        <table class="widget" cellspacing="1">
            <tr>
                <td class=widget_header colspan=1><?php echo _("Activity description"); ?></td>
            </tr>
            <tr>
                <td class=widget_content_form_element><input type=text size=155 name=activity_description></td>
            </tr>
        </table>

        <table class="widget" cellspacing="1">
            <tr>
                <td class=widget_header colspan=6><?php echo _("Confirm Recipients"); ?></td>
            </tr>
            <tr>
                <td class="widget_label">&nbsp;</td>
                <td class="widget_label"><?php echo _("Company"); ?></td>
                <td class="widget_label"><?php echo _("Owner"); ?></td>
                <td class="widget_label"><?php echo _("Contact"); ?></td>
                <td class="widget_label"><?php echo _("Title"); ?></td>
                <td class="widget_label"><?php echo _("E-Mail"); ?></td>
            </tr>
            <?php  echo $contact_rows ?>
            <tr>
                <td class="widget_content_form_element" colspan="6">
                    <input type="submit" class="button" value="<?php echo _("Continue"); ?>">
                </td>
            </tr>
        </table>
        </form>

    </div>


</div>

<?php

end_page();

 /**
  * $Log: bulkactivity.php,v $
  * Revision 1.1  2006/10/01 00:15:06  braverock
  * - Initial Revision of Bulk Activity and Bulk Assignment contributed by Danielle Baudone
  *
  * Revision 1.0  2006/01/15 01:18:00  dbaudone
  */
?>