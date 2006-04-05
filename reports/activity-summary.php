<?php
/**
 * Create a report of company, contacts, and activity for the requested user
 * and time .
 *
 * @author John Read
 *
 * $Id: activity-summary.php,v 1.2 2006/04/05 03:46:38 ongardie Exp $
 */
require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

$session_user_id = session_check();
$msg = $_GET['msg'];
$starting = $_POST['starting'];
$ending = $_POST['ending'];
$user_id = $_POST['user_id'];
$category_id = $_POST['category_id'];
$line_number = 0;
$NEWLINE = "\r\n";

$only_show_completed = $_POST['only_show_completed'];
//echo ">>$only_show_completed";
if (strlen($only_show_completed) > 0) {
	$checked_only_show_completed = "checked";
	$only_show_completed = true;
}
else $only_show_completed = false;

$company_profile = $_POST['company_profile'];
//echo ">>$company_profile";
if (strlen($company_profile) > 0) {
	$checked_company_profile = "checked";
	$company_profile = true;
}
else $company_profile = false;

$company_employees = $_POST['company_employees'];
//echo ">>$company_employees";
if (strlen($company_employees) > 0) {
	$checked_company_employees = "checked";
	$company_employees = true;
}
else $company_employees = false;

$company_revenue = $_POST['company_revenue'];
//echo ">>$company_revenues";
if (strlen($company_revenue) > 0) {
	$checked_company_revenue = "checked";
	$company_revenue = true;
}
else $company_revenue = false;

if (!strlen($starting) > 0) $starting = date("Y-m-d");
if (!strlen($ending) > 0) $ending = date("Y-m-d");

$page_title = _("Activity Summary Report");

$con = get_xrms_dbconnection();

//uncomment this line if you suspect a problem with the SQL query
//$con->debug = 1;

// get users
$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$rst = $con->execute($sql2);
$user_menu = $rst->getmenu2('user_id', $user_id, true);
$rst->close();
// get activities
$sql2  = "SELECT activity_type_id, activity_type_pretty_name, activity_type_pretty_plural";
$sql2 .= " from activity_types order by activity_type_id";
$rst_act_types = $con->execute($sql2);
$act_types= $rst_act_types->GetAssoc();
$rst_act_types->close();

// get categories
$sql2  = "select category_pretty_name, c.category_id";
$sql2 .= " from categories c, category_scopes cs, category_category_scope_map ccsm";
$sql2 .= " where c.category_id = ccsm.category_id";
$sql2 .= " and cs.on_what_table =  'companies'";
$sql2 .= " and ccsm.category_scope_id = cs.category_scope_id";
$sql2 .= " and category_record_status =  'a'";
$sql2 .= " order by category_pretty_name";
$rst = $con->execute($sql2);
$category_menu = $rst->getmenu2('category_id', $category_id, true);
$rst->close();

if (!$_POST['csv_output']) {
	start_page($page_title, true, $msg);
	echo "<form action='activity-summary.php' method=post>\n"
		,"<table class=widget><tr>"
			,"<td class=widget_label>",_("Start"),"</td>\n"
			,"<td class=widget_label>",_("End"),"</td>\n"
			,"<td class=widget_label>",_("User"),"</td>\n"
			,"<td class=widget_label>",_("Category"),"</td>\n"
			,"<td class=widget_label></td></tr>\n"
		,"<tr><td class=widget_content_form_element>"
			,"<input type=text name=starting value='$starting'></td>\n"
			,"<td class=widget_content_form_element>"
			,"<input type=text name=ending value='$ending'></td>\n"
			,"<td class=widget_content_form_element>$user_menu</td>\n"
			,"<td class=widget_content_form_element>$category_menu</td></tr></table>\n"
	,"<table class=widget><tr><td class=widget_content_form_element>\n"
			,"<input type=checkbox name=only_show_completed" 
			,$checked_only_show_completed,">"
			,_("Only show completed activities"),"</input></td>\n"
		,"<td class=widget_content_form_element>"
			,"<input type=checkbox name=company_profile " 
			,$checked_company_profile, ">\n"
			,_("Include company profile"),"</input></td></tr>\n"
		,"<tr><td class=widget_content_form_element>\n"
			,"<input type=checkbox name=company_employees " 
			,$checked_company_employees,">"
			,_("Include number of employees"),"</input></td>\n"
		,"<td class=widget_content_form_element>"
			,"<input type=checkbox name=company_revenue " 
			,$checked_company_revenue,">"
			,_("Include revenue"),"</input></td></tr></table>\n"
	,"<table class=widget>\n"
		,"<tr><td class=widget_content_form_element>"
			,"<input class=button name=display_report type=submit "
			,"value='",_("Display on screen"),"'></td>\n"
		,"<td class=widget_content_form_element>\n"
			,"<input class=button name=csv_output type=submit "
			,"value='",_("Generate a CSV file"),"'>\n"
		,"</td></table></form><div id='report'>\n";
}

if ($user_id) {
     $sql = "select username from users where user_id = $user_id limit 1";
    $rst = $con->execute($sql);
    $username = $rst->fields['username'];
    $rst->close();
	$userselect= " and user_id = $user_id ";
}
else
	$userselect = "";


if ($starting) {
    $start_date = "$starting 00:00:00";
    $end_date =  "$ending 23:23:59";

    $sql2 = "SELECT company_id from activities
    		where activity_record_status = 'a' $userselect
    		and scheduled_at between " . $con->qstr($start_date, get_magic_quotes_gpc()) . "
    		and " . $con->qstr($end_date, get_magic_quotes_gpc());
    if ($only_show_completed) $sql2 .= " and activity_status <> 'o' ";
    $sql2 .= " order by scheduled_at ";
    $rst = $con->execute($sql2);
	if ($rst) {
		while ($id = $rst->fields['company_id']) {
			$cmpid[] = $id;
			$rst->movenext();
		}
		if (count($cmpid) > 0)
			$companyids= array_unique($cmpid);
	}
	$rst->close();

	if ($companyids)	{
		if ($_POST['display_report'])
			echo "<table class=widget>\n";
		if ($_POST['csv_output']) {
			$csvfp = fopen($xrms_file_root . '/tmp/activity_summary.csv', 'w');
			if (!$csvfp) {
			   echo '<br><h1>'._("Unable to Open file for writing.").'</h1>';
			   exit;
			}
		}
		$titleout = '';
		foreach ($companyids as $my_cmpid) {
			// check if category legal
			$process_this = 1;
			if ($category_id) {
				$sqlcat = "SELECT category_id from entity_category_map ";
				$sqlcat .= "where category_id=$category_id and ";
				$sqlcat .= "on_what_id=$my_cmpid and on_what_table='companies'";
				$rst = $con->execute($sqlcat);
				if (!$rst->fields['category_id'])
					$process_this = 0;
			}
			if ($process_this) {
				$sql3  = "SELECT company_name, default_primary_address,profile,employees, revenue";
				$sql3 .= " from companies where company_id=$my_cmpid";
				$rst = $con->execute($sql3);
				if ($rst) {
					$output['company_name']=$rst->fields['company_name'];
					if ($line_number == 0)
						$titleout ="Company_Name";
					if ($company_profile) {
						$output['profile'] =str_replace("\n","<br>",htmlentities($rst->fields['profile']));
						if ($line_number == 0)
							$titleout .=",Profile";
					}
					if ($company_employees) {
						$output['employees'] =$rst->fields['employees'];
						if ($line_number == 0)
							$titleout .=",Employees";
					}
					if ($company_revenue) {
						$output['revenue'] =$rst->fields['revenue'];
						if ($line_number == 0)
							$titleout .=",Revenue";
					}

					// get address
					$sql4 = "SELECT  line1, line2, city, province, postal_code, country_id";
					$sql4 .=" from addresses where address_id = ";
					$sql4 .= $rst->fields['default_primary_address'];
					$rst = $con->execute($sql4);
					$output['line1']=$rst->fields['line1'];
					$output['line2']=$rst->fields['line2'];
					$output['postal_code'] = $rst->fields['postal_code'];
					$output['city'] = $rst->fields['city'];
					$output['province'] = $rst->fields['province'];

					$sqlctry = "SELECT country_name from countries where ";
					$sqlctry .= "country_id=".$rst->fields['country_id'];
					$rst = $con->execute($sqlctry);
					$output['country_name']=$rst->fields['country_name'];
					if ($line_number == 0)
						$titleout .=",Line1,Line2,Postal_Code,City,Province,Country";
					
					// activity loop for each company and activity type
					foreach ($act_types as $myid=>$myact_type)  {
						$sql4 = "SELECT * from activities where company_id=$my_cmpid
							and scheduled_at between " . $con->qstr($start_date, get_magic_quotes_gpc()) . "
							and " . $con->qstr($end_date, get_magic_quotes_gpc())." and activity_type_id="
							.$myid;
						if ($only_show_completed)
							$sql4 .= " and activity_status <> 'o' ";
						$sql4 .= $userselect." order by scheduled_at ";
						$rst = $con->execute($sql4);
						$activities[$myid]['activity_name']=$act_types[$myid]['activity_type_pretty_plural'];
						if ($line_number == 0)
							$titleout .= ",".$act_types[$myid]['activity_type_pretty_plural'];
						if ($rst)
							$activities[$myid]['count']=$rst->NumRows();
						else
							$activities[$myid]['count']=0;
					}

					// show we display one company?
					if ($_POST['display_report']) {
						echo "<tr></tr><tr><td class=widget_label>"._("Company name")."</td>
							<td class=widget_contents>",$output['company_name'],"</td></tr>\n";
						echo "<tr><td class=widget_label>"._("Address"),":</td>
							<td class=widget_contents>",$output['line1'],"</td></tr>";
						if (strlen($output['line2']) > 0) {
							echo "<tr><td class=widget_label></td>
								<td class=widget_contents>",$output['line2'],"</td></tr>";
						}
						echo "<tr><td class=widget_label></td>
							<td class=widget_contents>",$output['postal_code'],
							" ",$output['city'],"</td></tr>";
						if (strlen($output['province']) > 0) {
							echo "<tr><td class=widget_label></td>
								<td class=widget_contents>",$output['province'],"</td></tr>";
						}
						echo "<tr><td class=widget_label></td>
							<td class=widget_contents>".$output['country_name'];
						if (strlen($output['profile']) > 0) {
							echo "<tr><td class=widget_label>"._("Profile")."</td>
								<td class=widget_contents>",$output['profile'],"</td></tr>";
						}
						if (strlen($output['employees']) > 0) {
						echo "<tr><td class=widget_label>"._("Employees")."</td>
							<td class=widget_contents>".$output['employees'];
						}
						if (strlen($output['revenue']) > 0) {
						echo "<tr><td class=widget_label>"._("Revenue")."</td>
							<td class=widget_contents>".$output['revenue'];
						}

						// now scan the activities
						foreach ($activities as $myactid) {
							if ($myactid['count']) {
								echo "<tr><td class=widget_label>".$myactid['activity_name']
								."</td><td>".$myactid['count']."</td></tr>\n";
							}
						}
						$count++;
						echo "<tr><td class=widget_label></td></tr>\n";
					} // end of display

					// output into csv
					if ($_POST['csv_output']) {
						if ($line_number == 0) {
							$titleout .= $NEWLINE;
							fwrite($csvfp, $titleout);
							$line_number++;
						}
						
						$csvout = '';
						$elements = array();
						reset($elements);
						foreach($output as $v) {
							if ($escquote)
								$v = str_replace('"','""',trim($v));
							$v = strip_tags(
								str_replace("\n", ' ',
								str_replace("\r", ' ',
								str_replace(',',',',$v))));
							if (strpos($v,',') !== false 
								|| strpos($v,'"') !== false)
								 $elements[] = '"'.$v.'"';
							else $elements[] = $v;
							//echo "<br>v=$v";
						}
						foreach ($activities as $myid)  {
							$elements[] = $myid['count'];
						}
						$csvout .= implode(',', $elements).$NEWLINE;
						fwrite($csvfp, $csvout);
					}	// end of CSV
					$rst->movenext();
				}
			}
		}
	}
	if (($companyids) && ($_POST['display_report'])) {
		echo "</table></div>\n";
		end_page();
	}
	if (($companyids) && ($_POST['csv_output'])) {
		fclose($csvfp);
		header("Location: {$http_site_root}/tmp/activity_summary.csv");
	}
	$rst->close();
}
$con->close();
?>
