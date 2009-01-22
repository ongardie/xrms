<?php
  /**
  *
  * bulkassignment.
  *
  * $Id: bulkassignment.php,v 1.6 2009/01/22 23:48:28 randym56 Exp $
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
  $campaign_id_param = $_GET['campaign_id'];
  $user_id = $_POST['user_id'];
  $return_url = $_GET['return_url'];
  if (!$return_url) $return_url = '/' . $scope . '/some.php';
  //echo $scope; echo "<br>"; echo $return_url; exit;

  //get the database connection
  if (!$xcon) {
      $con=get_xrms_dbconnection();
  }
  //$con->debug = 1;
  //get the menus
  $crm_status_menu = build_crm_status_menu($con, $crm_status_id, true);
  $company_type_menu = build_company_type_menu($con, $company_type_id, true);


  $sql2 = "select company_source_pretty_name, company_source_id from company_sources where company_source_record_status = 'a' order by company_source_pretty_name";
  $rst = $con->execute($sql2);
  $company_source_menu = $rst->getmenu2('company_source_id', $company_source_id, true);
  $rst->close();

  $sql2 = "select category_pretty_name, c.category_id
  from categories c, category_scopes cs, category_category_scope_map ccsm
  where c.category_id = ccsm.category_id
  and cs.on_what_table =  'companies'
  and ccsm.category_scope_id = cs.category_scope_id
  and category_record_status =  'a'
  order by category_pretty_name";
  $rst = $con->execute($sql2);
  $company_category_menu = $rst->getmenu2('company_category_id', $company_category_id, true);
  $rst->close();

  $sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_pretty_name";
  $rst = $con->execute($sql2);
  $industry_menu = translate_menu($rst->getmenu2('industry_id', $industry_id, true, false, 0, 'style="font-size: x-small; width: 150px; height: 20px"'));
  $rst->close();

  $sql2 = "select rating_pretty_name, rating_id from ratings where rating_record_status = 'a' order by rating_pretty_name";
  $rst = $con->execute($sql2);
  $rating_menu = $rst->getmenu2('rating_id', $rating_id, true);
  $rst->close();

  $sql2 = "select account_status_pretty_name, account_status_id from account_statuses where account_status_record_status = 'a'";
  $rst = $con->execute($sql2);
  $account_status_menu = $rst->getmenu2('account_status_id', $account_status_id, true);
  $rst->close();

  $user_menu = get_user_menu($con, $user_id, true, 'user_id', false);

  $sql2 = "select campaign_title, campaign_id from campaigns, campaign_statuses
         where campaign_record_status = 'a' and
         campaign_statuses.campaign_status_id = campaigns.campaign_status_id and
         campaign_statuses.status_open_indicator = 'o'
         order by campaign_title";
  $rst = $con->execute($sql2);
  if ( $rst && !$rst->EOF ) {
   $campaign_id = $rst->fields['campaign_id'];
  } else {
   $campaign_id = '';
  }
  $campaign_menu = $rst->getmenu('campaign_id', $campaign_id, true);
  $rst->close();


  $array_of_companies = array();

  $from=$_SESSION["search_sql"]["from"];
  //var_dump($_SESSION["search_sql"]);   exit;
  $where=$_SESSION["search_sql"]["where"];
  $order_by=$_SESSION["search_sql"]["order"];
  //$from.=" LEFT JOIN contacts cont on cont.company_id=c.company_id ";
  $sql="SELECT c.company_id ".$from.$where.$order_by;
  //echo $sql; exit;
  $rst = $con->execute($sql);

  if ($rst) {
    while (!$rst->EOF) {
      array_push($array_of_companies, $rst->fields['company_id']);
      $rst->movenext();
    }
  } else {
    db_error_handler ($con, $sql);
  }

  $_SESSION['array_of_companies'] = serialize($array_of_companies);

  if (is_array($array_of_companies))
    $imploded_companies = implode(',', $array_of_companies);
  else
    echo _("WARNING: No array of companies!") . "<br>";

  $con = get_xrms_dbconnection();
  //$con->debug = 1;


  $sql = "SELECT "
        . $con->Concat("'<a id=\"'" , "c.company_name", "'\" href=\"../companies/one.php?company_id='","c.company_id","'\">'","c.company_name","'</a>'") . ' AS "name",
        c.company_id, u.username, i.industry_pretty_name,
        addr.province as "province", addr.city as "city", co.country_name as "country"';

  $sql .= " from companies c
  LEFT JOIN users u ON c.user_id = u.user_id
  LEFT JOIN industries i ON c.industry_id = i.industry_id
  LEFT JOIN addresses addr ON addr.address_id = c.default_primary_address
  LEFT JOIN countries co ON co.country_id = addr.country_id
  LEFT JOIN company_types ct ON c.company_type_id = ct.company_type_id
  where c.company_id in ($imploded_companies)
  and company_record_status = 'a' order by c.company_name asc";

  //echo $sql; exit;

  $_x = 1;

  $rst = $con->execute($sql);
  if ($rst) {
      while (!$rst->EOF) {
        $contact_rows .= '<tr>';
        $contact_rows .= '<td class="widget_content_form_element"></td><td>';
        $contact_rows .= '<input type="checkbox" name="array_of_companies[]" id="array_of_companies_' . $_x++ . '" value="' . $rst->fields['company_id'] . '" checked="checked"></td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['name'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['industry_pretty_name'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['city'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['province'] . '</td>';
        $contact_rows .= '<td class="widget_content">' . $rst->fields['country'] . '</td>';
        $contact_rows .= "</tr>\n";
        $rst->movenext();
    }

    $rst->close();
  }

  $con->close();

  $page_title = _("Set specified values to displayed fields for selected companies");

  start_page($page_title, true, $msg);

?>


        <form action="bulkassignment-1.php" method="post">
        <input type=hidden name=return_url value="<?php  echo $return_url; ?>">
        <input type=hidden name=user_id value="<?php  echo $user_id; ?>">
        <table class="widget" cellspacing="1">
        <tr>
        </tr>
        </table>
        <table class="widget" cellspacing="1">
        <tr>
            <td class=widget_label_right><?php echo _("CRM Status"); ?></td>
            <td class=widget_content_form_element><?php echo $crm_status_menu; ?></td>
            <td class=widget_label_right><?php echo _("Company Type"); ?></td>
            <td class=widget_content_form_element><?php echo $company_type_menu; ?></td>
            <td class=widget_label_right><?php echo _("Company Source"); ?></td>
            <td class=widget_content_form_element><?php echo $company_source_menu; ?></td>
            <td class=widget_label_right><?php echo _("Credit limit"); ?></td>
            <td class=widget_content_form_element><input type=text size=10 name=credit_limit value="<?php echo $credit_limit; ?>"></td>
        </tr>
        <tr>
            <td class=widget_label_right><?php echo _("Industry"); ?></td>
            <td class=widget_content_form_element><?php echo $industry_menu; ?></td>
            <td class=widget_label_right><?php echo _("Account Status"); ?></td>
            <td class=widget_content_form_element><?php echo $account_status_menu; ?></td>
            <td class=widget_label_right><?php echo _("Owner"); ?></td>
            <td class=widget_content_form_element><?php echo $user_menu; ?></td>
            <td class=widget_label_right><?php echo _("Rating"); ?></td>
            <td class=widget_content_form_element><?php echo $rating_menu; ?></td>
        </tr>
        <!---
        <tr>
            <?php /* if ($company_custom1_label!='(Custom 1)') { ?>
                <td class=widget_label_right><?php echo $company_custom1_label ?></td><td>
                <?php echo ' <input type=text name=custom1 size=20 value=' . $custom1 . '>';} ?></td>
            <?php } else {echo '<td class=clear>'; echo '</td><td>';}  ?></td>

	      <?php if ($company_custom2_label!='(Custom 2)') { ?>
                <td class=widget_label_right><?php echo $company_custom2_label ?></td><td>
                <?php echo ' <input type=text name=custom2 size=20 value=' . $custom2 . '>';} ?></td>
            <?php } else {echo '<td class=clear>'; echo '</td><td>';}  ?></td>

            <?php  if ($company_custom3_label!='(Custom 3)') { ?>
                <td class=widget_label_right><?php echo $company_custom3_label ?></td><td>
                <?php echo ' <input type=text name=custom3 size=20 value=' . $custom3 . '>';} ?></td>
            <?php } else {echo '<td class=clear>'; echo '</td><td>';}  ?></td>

            <?php  if ($company_custom4_label!='(Custom 4)') { ?>
                <td class=widget_label_right><?php echo $company_custom4_label ?></td><td>
                <?php echo ' <input type=text name=custom4 size=20 value=' . $custom4 . '>';} ?></td>
            <?php } else {echo '<td class=clear>'; echo '</td><td>';} */ ?></td>

        </tr>
        --->
        </table>
        <table class="widget" cellspacing="1">
        <tr>
               <td class=widget_label><?php echo _("Set category"); ?></td>
               <td class=widget_content_form_element><?php echo $company_category_menu; ?>
               <class=widget_label_right><?php echo _("Select check box to unlink selected Companies from the Category"); ?>
               <class=widget_content_form_element><input type=checkbox id=unlink_category name=unlink_category value=1> </td>
        </tr>
        </table>

        <table class="widget" cellspacing="1">
        <tr>
               <td class=widget_label><?php echo _("Set campaign"); ?></td>
               <td class=widget_content_form_element><?php echo $campaign_menu; ?>
               <class=widget_label_right><?php echo _("Select check box to unlink selected Companies from the Campaign"); ?>
               <class=widget_content_form_element><input type=checkbox id=unlink_campaign name=unlink_campaign value=1> </td>
        </tr>
        </table>
        <table class="widget" cellspacing="1">
            <tr>
                <td class=widget_header colspan=7><?php echo _("Confirm Selection"); ?></td>
            </tr>
            <tr>
                <td class="widget_label">&nbsp;</td><td></td>
                <td class="widget_label"><?php echo _("Company"); ?></td>
                <td class="widget_label"><?php echo _("Industry"); ?></td>
                <td class="widget_label"><?php echo _("City"); ?></td>
                <td class="widget_label"><?php echo _("State"); ?></td>
                <td class="widget_label"><?php echo _("Country"); ?></td>
            </tr>
            <?php  echo $contact_rows ?>
            <tr>
                <td class="widget_content_form_element" colspan="6">
                    <input type="submit" class="button" value="<?php echo _("Continue"); ?>">
                </td>
            </tr>
        </table>
        </form>


<?php

end_page();

 /**
  * $Log: bulkassignment.php,v $
  * Revision 1.6  2009/01/22 23:48:28  randym56
  * Rewrite of SQL statement starting line 115. Used LEFT JOIN statements rather than WHERE statements so that all company records that matched in /companies/some.php will appear.
  *
  * Revision 1.5  2007/10/19 18:31:34  randym56
  * - Fixed bugs preventing bulk updates to companies
  *
  * Revision 1.4  2006/12/03 20:17:46  braverock
  * - fix mistranslated strings
  *
  * Revision 1.3  2006/11/14 19:32:45  braverock
  * - comment out the custom1-4 stuff entirely until someone
  *   has a chance to work on it and make it work
  *
  * Revision 1.2  2006/10/01 10:48:42  braverock
  * - remove custom1-4 menu functions, company custom1-4 will stay simple strings
  *   -- use custom_fields plugin if you want select lists
  *
  * Revision 1.1  2006/10/01 00:15:06  braverock
  * - Initial Revision of Bulk Activity and Bulk Assignment contributed by Danielle Baudone
  *
  * Revision 1.0  2006/02/01 18:30:00  dbaudone
  */
?>
