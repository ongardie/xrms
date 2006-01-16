<?php
  /**
  *
  * Email.
  *
  * $Id: snailmail-1.php,v 1.4 2006/01/16 15:10:09 niclowe Exp $
  */

  require_once('include-locations-location.inc');

  require_once($include_directory . 'vars.php');
  require_once($include_directory . 'utils-interface.php');
  require_once($include_directory . 'utils-misc.php');
  require_once($include_directory . 'adodb/adodb.inc.php');
  require_once($include_directory . 'adodb-params.php');
  require_once($include_directory . 'classes/Pager/GUP_Pager.php');
	require_once($include_directory . 'classes/Pager/Pager_Columns.php');

  $session_user_id = session_check();
  $msg = $_GET['msg'];

  $scope = $_GET['scope'];

  //echo $scope;exit;
  $company_id = $_GET['company_id'];

  // opportunities
  $user_id = $_POST['user_id'];
  $contact_id = $_POST['contact_id'];

  //activities
  $activity_id = $_POST['activity_id'];

  $con = get_xrms_dbconnection();
//$con->debug = 1;

    //hack to not show continue button if no templates are found
    $show_continue=true; 

   $array_of_contacts = array();
  switch ($scope) {
    case "company":
      $sql = "select cont.contact_id
      from contacts cont, companies c
      where c.company_id = $company_id
      and c.company_id = cont.company_id
      and cont.contact_record_status = 'a'";
			break;
		case "companies":
		  //Nic: DO SOMETHING DIFFERENT FOR COMPANIES BECAUSE YOU HAVE TO GET THE CONTACT IDS FROM WITHIN THE COMPANY
			//I only pass companies the end of the sql, ie the from and the where.
			//I should have really done this in the first place for all of them.
      $from=$_SESSION["search_sql"]["from"];
			//var_dump($_SESSION["search_sql"]);
			$where=$_SESSION["search_sql"]["where"]." and cont.contact_id<>'NULL'";
			$order_by=$_SESSION["search_sql"]["order"];
			$from.=" LEFT JOIN contacts cont on cont.company_id=c.company_id ";
			$sql="SELECT cont.contact_id ".$from.$where.$order_by;
            $rst = $con->execute($sql);
        
            if ($rst) {
                while (!$rst->EOF) {
                    array_push($array_of_contacts, $rst->fields['contact_id']);
                    $rst->movenext();
                }
            }
   break;
   case 'contact_list':
    getGlobalVar($contact_list, 'contact_list');
    if ($contact_list) {
        $array_of_contacts=explode(",",$contact_list);
    }
   break;
   default:
      $search_sql=$_SESSION["search_sql"];
      list($select, $from) = spliti("FROM", $search_sql,2);//need limit otherwise from_unixtime functions get captured
      list($from, $orderby) = spliti("order by", $from);
      list($from, $groupby) = spliti("group by", $from);

      $sql= "SELECT cont.contact_id FROM ".$from;
      //added the null statement as a null record ruins email-3.php
      $sql.=" AND cont.contact_id IS NOT NULL ";
      //look out for group bys...
      if($groupby)$sql.="GROUP BY ".$groupby;
	  if($orderby)$sql.=" ORDER BY ".$orderby;
        $rst = $con->execute($sql);
    
        if ($rst) {
            while (!$rst->EOF) {
                array_push($array_of_contacts, $rst->fields['contact_id']);
                $rst->movenext();
            }
        }
    break;
    
}

/* THIS CODE IS REDUNDANT AS I HAVE PUT IT INTO ONE PAGE - Nic

$_SESSION['array_of_contacts'] = serialize($array_of_contacts);

$array_of_contacts = unserialize($_SESSION['array_of_contacts']);
*/

if (is_array($array_of_contacts))
    $imploded_contacts = implode(',', $array_of_contacts);
else
    echo _("WARNING: No array of contacts!") . "<br>";


$con = get_xrms_dbconnection();
//$con->debug = 1;

$sql = "select cont.contact_id, addr.address_body, cont.first_names, cont.last_name, "
.  $con->concat("cont.first_names", "' '","cont.last_name") . 
" as contact_name, c.company_name, cont.title,u.username
from contacts cont, companies c, users u, addresses addr
where c.company_id = cont.company_id
and c.user_id = u.user_id
and cont.contact_id in ($imploded_contacts)
and cont.address_id=addr.address_id
and contact_record_status = 'a' ";

$rst = $con->execute($sql);
if ($rst) {
    while (!$rst->EOF) {
        $contact_rows .= "<tr>";
        $contact_rows .= "<td class=widget_content_form_element><input type=checkbox name=array_of_contacts[]] value=" . $rst->fields['contact_id'] . " checked></td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['company_name'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['username'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['first_names'] . ' ' . $rst->fields['last_name'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['title'] . "</td>";
        $contact_rows .= "<td class=widget_content>" . $rst->fields['address_body'] . "</td>";
        $contact_rows .= "</tr>\n";
        $rst->movenext();
    }

    $rst->close();
}


$page_title = _("Confirm Recipients");
start_page($page_title, true, $msg);

?>
<div id="Main">
    <div id="Content">

        <form action=snailmail-2.php method=post>
        <table class=widget cellspacing=1>
            <tr>
                <td class=widget_header colspan=6><?php echo _("Confirm Recipients"); ?></td>
            </tr>
            <tr>
                <td class=widget_label>&nbsp;</td>
                <td class=widget_label><?php echo _("Company"); ?></td>
                <td class=widget_label><?php echo _("Owner"); ?></td>
                <td class=widget_label><?php echo _("Contact"); ?></td>
                <td class=widget_label><?php echo _("Title"); ?></td>
                <td class=widget_label><?php echo _("Address"); ?></td>
            </tr>
            <?php  echo $contact_rows ?>
            <tr>
                <td class=widget_content_form_element colspan=6><input type=submit class=button value="<?php echo _("Continue"); ?>"></td>
            </tr>
        </table>
        </form>

    </div>
Please note this snail mail merge functionality is still under development.<br>
At the moment, snail mail merge simply gives you a (choosable) list of names and addresses of the people in csv format. <br>
Like the Email Merge functionalility, if you choose a number of companies it will list all the contacts within those companies. Its useful for doing specific & targetted mailouts to particular catagories of customers. <br>
However, it is not as advanced as the email merge functionality. It does NOT record activities when you send mail merge to someone.<br><br>
What still needs to be added/solved in snail mail merge functionality?
<ul>
<li>Ability to add an activity to the contact so that you know you have sent him/her a letter. </li>
<li>However, the question is - if you dont store a snail mail merge template, what is the best way to record this activity?</li>
<li>Resolution of compatability issues with Internet Explorer and passing arrays between pages</li>
</ul>
Feel free to contribute your own changes or additions to this code!
<?

$con->close();

end_page();

?>
</form>
</div>

