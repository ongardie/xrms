<?php
/**
 * Search and view summary information on multiple companies
 *
 * This is the advanced screen that allows many more search fields
 *
 * $Id: advanced-search.php,v 1.8 2004/08/30 13:20:17 neildogg Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb/adodb-pager.inc.php');
require_once($include_directory . 'adodb-params.php');

//set the language
//$_SESSION['language'] = 'english';

$msg = isset($_GET['msg']) ? $_GET['msg'] : '';

// a helper routine to retrieve one field from a table
//
// Call:
//
// $con - db connection
// $sql - the sql statement to execute
// $nam - the option to highlight - if it's '', then first option is
//        the default and it is blank.
//
// Return:
//
// a string of the html menu
//

function check_and_get ( $con, $sql, $nam, $default = false )
{
  $rst = $con->execute($sql);

  if ( !$rst ) {
    db_error_handler($con, $sql);
  }
  if ( !$rst->EOF && $nam ) {
    $GLOBALS[$nam] = $rst->fields[$nam];
  }
  $tmp = $rst->getmenu2($nam, $default, true);	

  $rst->close();

  return $tmp;
}

$session_user_id = session_check();

// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                    'company_name'        => array('companies_company_name',arr_vars_SESSION),
                    'legal_name'          => array('companies_legal_name',arr_vars_SESSION),
                    'company_code'        => array('companies_company_code',arr_vars_SESSION),
                    'crm_status_id'       => array('companies_crm_status_id',arr_vars_SESSION),
                    'company_source_id'   => array('companies_company_source_id',arr_vars_SESSION),
                    'industry_id'         => array('companies_industry_id',arr_vars_SESSION),
                    'user_id'             => array('companies_user_id',arr_vars_SESSION),
                    'phone' => array ( 'companies_phone' , arr_vars_SESSION),
                    'phone2' => array ( 'companies_phone2' , arr_vars_SESSION),
                    'fax' => array ( 'companies_fax' , arr_vars_SESSION),
                    'url' => array ( 'companies_url' , arr_vars_SESSION),
                    'employees' => array ( 'companies_employees' , arr_vars_SESSION),
                    'revenue' => array ( 'companies_revenue' , arr_vars_SESSION),
                    'custom1' => array ( 'companies_custom1' , arr_vars_SESSION),
                    'custom2' => array ( 'companies_custom2' , arr_vars_SESSION),
                    'custom3' => array ( 'companies_custom3' , arr_vars_SESSION),
             		   'custom4' => array ( 'companies_custom4' , arr_vars_SESSION),
             		   'profile' => array ( 'companies_profile' , arr_vars_SESSION),
             		   'address_name' => array ( 'companies_address_name' , arr_vars_SESSION),
             		   'line1' => array ( 'companies_line1' , arr_vars_SESSION),
	             	   'line2' => array ( 'companies_line2' , arr_vars_SESSION),
	             	   'city' => array ( 'companies_city' , arr_vars_SESSION),
	             	   'province' => array ( 'companies_province' , arr_vars_SESSION),
	             	   'postal_code' => array ( 'companies_postal_code' , arr_vars_SESSION),
	             	   'country_id' => array ( 'companies_country_id' , arr_vars_SESSION),
	             	   'address_body' => array ( 'companies_address_body' , arr_vars_SESSION),
                   );

// get all passed in variables
arr_vars_get_all ( $arr_vars );

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//uncomment this line if you suspect a problem with the SQL query
// $con->debug = 1;

$sql2 = "select username, user_id from users where user_record_status = 'a' order by username";
$user_menu = check_and_get($con,$sql2,'user_id',$user_id);

//This is not used
//$sql2 = "select category_pretty_name, c.category_id
//from categories c, category_scopes cs, category_category_scope_map ccsm
//where c.category_id = ccsm.category_id
//and cs.on_what_table =  'companies'
//and ccsm.category_scope_id = cs.category_scope_id
//and category_record_status =  'a'
//order by category_pretty_name";
//$company_category_menu = check_and_get($con,$sql2,'category_id');

//Also not used
//$sql2 = "select company_type_pretty_name, company_type_id from company_types where company_type_record_status = 'a' order by company_type_id";
//$company_type_menu = check_and_get($con,$sql2,'company_type_id');
//$company_type_menu = check_and_get($con,$sql2,'');

$sql2 = "select crm_status_pretty_name, crm_status_id from crm_statuses where crm_status_record_status = 'a' order by crm_status_id";
$crm_status_menu = check_and_get($con,$sql2,'crm_status_id',$crm_status_id);
//$crm_status_menu = check_and_get($con,$sql2,'');

$sql2 = "select company_source_pretty_name, company_source_id from company_sources where company_source_record_status = 'a' order by company_source_pretty_name";
$company_source_menu = check_and_get($con,$sql2,'company_source_id',$company_source_id);
//$company_source_menu = check_and_get($con,$sql2,'');

$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_id";
$industry_menu = check_and_get($con,$sql2,'industry_id',$industry_id);
//$industry_menu = check_and_get($con,$sql2,'');

$sql2 = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$country_menu = check_and_get($con,$sql2,'country_id',$country_id);
//$country_menu = check_and_get($con,$sql2,'');

$page_title = _("Companies");
start_page($page_title, true, $msg);

?>

<div id="Main">
    <div id="ContentFullWidth">

        <form action=some-advanced.php method=post>
        <input type=hidden name=use_post_vars value=1>

<table border=0 cellpadding=0 cellspacing=0 width="100%">
    <tr>
        <td class=lcol width="55%" valign=top>

        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Company Information"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=50 name=company_name value="<?php  echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Legal Name"); ?></td>
                <td class=widget_content_form_element><input type=text size=50 name=legal_name value="<?php echo $legal_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Code"); ?></td>
                <td class=widget_content_form_element><input type=text size=10 name=company_code value="<?php echo $company_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("CRM Status"); ?></td>
                <td class=widget_content_form_element><?php  echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Source"); ?></td>
                <td class=widget_content_form_element><?php  echo $company_source_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Industry"); ?></td>
                <td class=widget_content_form_element><?php  echo $industry_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element><?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=phone value="<?php echo $phone; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Alt. Phone"); ?></td>
                <td class=widget_content_form_element><input type=text name=phone2 value="<?php echo $phone2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Fax"); ?></td>
                <td class=widget_content_form_element><input type=text name=fax value="<?php echo $fax; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("URL"); ?></td>
                <td class=widget_content_form_element><input type=text name=url size=50 value="<?php echo $url; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Employees"); ?></td>
                <td class=widget_content_form_element><input type=text name=employees size=10 value="<?php echo $employees; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Revenue"); ?></td>
                <td class=widget_content_form_element><input type=text name=revenue size=10 value="<?php echo $revenue; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom1_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom1 size=30 value="<?php echo $custom1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom2_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom2 size=30 value="<?php echo $custom2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom3_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom3 size=30 value="<?php echo $custom3; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo $company_custom4_label ?></td>
                <td class=widget_content_form_element><input type=text name=custom4 size=30 value="<?php echo $custom4; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right_166px><?php echo _("Profile"); ?></td>
                <td class=widget_content_form_element><textarea rows=10 cols=70 name=profile><?php echo $profile; ?></textarea></td>
            </tr>
        </table>

        </td>
        <!-- gutter //-->
        <td class=gutter width="1%">
        &nbsp;
        </td>
        <!-- right column //-->
        <td class=rcol width="44%" valign=top>

        <!-- Address Entry //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Address"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=address_name size=30 value="<?php echo $address_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 1"); ?></td>
                <td class=widget_content_form_element><input type=text name=line1 size=30 value="<?php echo $line1; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Line 2"); ?></td>
                <td class=widget_content_form_element><input type=text name=line2 size=30 value="<?php echo $line2; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("City"); ?></td>
                <td class=widget_content_form_element><input type=text name=city size=30 value="<?php echo $city; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("State/Province"); ?></td>
                <td class=widget_content_form_element><input type=text name=province size=20 value="<?php echo $province; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Postal Code"); ?></td>
                <td class=widget_content_form_element><input type=text name=postal_code size=10 value="<?php echo $postal_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Country"); ?></td>
                <td class=widget_content_form_element><?php echo $country_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right_91px><?php echo _("Override Address"); ?></td>
                <td class=widget_content_form_element><textarea rows=5 cols=40 name=address_body value="<?php echo $address_body; ?>"></textarea></td>
            </tr>
             <tr>
                <td class=widget_content_form_element colspan=2>
                    <input class=button type=submit value="<?php echo _("Search"); ?>">
                    <input name="clear_search" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                </td>
            </tr>
        </table>

        </td>
    </tr>
</table>
</form>

<?php

$con->close();

?>

    </div>

</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].company_name.focus();
}

initialize();

function bulkEmail() {
    document.forms[0].action = "../email/email.php";
    document.forms[0].submit();
}

function clearSearchCriteria() {
    location.href = "advanced-search.php?clear=1";
}

//-->
</script>

<?php

end_page();

/**
 * $Log: advanced-search.php,v $
 * Revision 1.8  2004/08/30 13:20:17  neildogg
 * - Robustified search
 *
 * Revision 1.7  2004/08/12 20:45:59  niclowe
 * fixed bug 1008238 - advanced company search not displaying first record of menu drop downs, and not searching properly for drop down items.
 *
 * Revision 1.6  2004/07/31 16:23:09  cpsource
 * - Make default menu items blank
 *
 * Revision 1.5  2004/07/31 12:11:04  cpsource
 * - Fixed multiple undefines and subsequent hidden bugs
 *   Used arr_vars for retrieving POST'ed variables
 *   Code cleanup and simplification.
 *   Removed setting session variables as they were unused
 *   Set use_post_vars as needed.
 *
 * Revision 1.4  2004/07/31 11:10:02  cpsource
 * - Fix lots and lots of errors that were masked by using undefined
 *     variables.
 *   Fix HTML syntax error
 *   Define msg properly
 *
 * Revision 1.3  2004/07/21 19:17:56  introspectshun
 * - Localized strings for i18n/l10n support
 *
 * Revision 1.2  2004/06/29 13:19:59  maulani
 * - Additional fields for advanced search
 *
 * Revision 1.1  2004/06/28 23:08:39  maulani
 * - Advanced search allows searching with a lot more fields
 *
 */
?>
