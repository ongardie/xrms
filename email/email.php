<?php
  /**
  *
  * Email.
  *
  * $Id: email.php,v 1.10 2005/01/09 01:05:02 vanmer Exp $
  */

  require_once('include-locations-location.inc');

  require_once($include_directory . 'vars.php');
  require_once($include_directory . 'utils-interface.php');
  require_once($include_directory . 'utils-misc.php');
  require_once($include_directory . 'adodb/adodb.inc.php');
  require_once($include_directory . 'adodb-params.php');

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

  $con = &adonewconnection($xrms_db_dbtype);
  $con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
//$con->debug = 1;

    //hack to not show continue button if no templates are found
    $show_continue=true; 



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
			$where=$_SESSION["search_sql"]["where"];
			$order_by=$_SESSION["search_sql"]["order"];
			$from.=" LEFT JOIN contacts cont on cont.company_id=c.company_id ";
			$sql="SELECT cont.contact_id ".$from.$where.$order_by;
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
			$sql.=" ORDER BY ".$orderby;
			break;
    }

    $rst = $con->execute($sql);

    $array_of_contacts = array();
    if ($rst) {
      while (!$rst->EOF) {
        array_push($array_of_contacts, $rst->fields['contact_id']);
        $rst->movenext();
      }
    }
    
    $_SESSION['array_of_contacts'] = serialize($array_of_contacts);

    $sql = "select * from email_templates where email_template_record_status = 'a' order by email_template_title";

    $rst = $con->execute($sql);

    $counter = 0;
    if ($rst) {
      while (!$rst->EOF) {
        $counter ++;
        $checked = ($counter == 1) ? ' checked' : '';
        $tablerows .= '<tr>';
        $tablerows .= "<td class=widget_content_form_element><input type=radio name=email_template_id value=" . $rst->fields['email_template_id'] . $checked . "></td>";
        $tablerows .= '<td class=widget_content><a href=one-template.php?email_template_id=' . $rst->fields['email_template_id'] . '>' . $rst->fields['email_template_title'] . '</a></td>';
        $tablerows .= '</tr>';
        $rst->movenext();
      }
      $rst->close();
    }
    
    if (strlen($tablerows) == 0) {
      $tablerows = '<tr><td class=widget_content colspan=20>' . _("No e-mail templates") . '</td></tr>';
      $show_continue=false;
    }

    $con->close();

    $page_title = _("Bulk E-Mail");
    start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="Content">

        <form action=email-2.php method=post>
		<table class=widget cellspacing=1>
			<tr>
				<td class=widget_header colspan=20><?php echo _("E-Mail Templates"); ?></td>
			</tr>
			<tr>
				<td class=widget_label width=1%>&nbsp;</td>
				<td class=widget_label><?php echo _("Template"); ?></td>
			</tr>
            <?php  echo $tablerows ?>
			<tr>
<?php if ($show_continue) { ?> <td class=widget_content_form_element colspan=2><input class=button type=submit value="<?php echo _("Continue"); ?>"></td> <?php } ?>
    </tr>
    </table>
    </form>

    </div>

    <!-- right column //-->
    <div id="Sidebar">

    &nbsp;

    </div>

    </div>

<?php

    end_page();

    /**
    * $Log: email.php,v $
    * Revision 1.10  2005/01/09 01:05:02  vanmer
    * - added check to see if templates exist.  If not, do not show continue button
    *
    * Revision 1.9  2004/08/26 22:55:26  niclowe
    * Enabled mail merge functionality for companies/some.php
    * Sorted pre-sending email checkbox page by company then contact lastname
    * Enabled mail merge for advanced-search companies
    *
    * Revision 1.8  2004/08/18 00:06:17  niclowe
    * Fixed bug 941839 - Mail Merge not working
    *
    * Revision 1.7  2004/08/04 21:46:42  introspectshun
    * - Localized strings for i18n/l10n support
    * - All paths now relative to include-locations-location.inc
    *
    * Revision 1.6  2004/07/03 14:48:52  metamedia
    * Minor bug fixes so that the "mail merge" from a company work.
    *
    * Revision 1.5  2004/06/14 16:54:37  introspectshun
    * - Add adodb-params.php include for multi-db compatibility.
    * - Corrected order of arguments to implode() function.
    * - Now use ADODB GetInsertSQL, GetUpdateSQL functions.
    *
    * Revision 1.4  2004/04/17 16:00:36  maulani
    * - Add CSS2 positioning
    *
    *
    */
?>
