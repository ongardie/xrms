<?php
/**
 * Search and view summary information on multiple companies
 *
 * This is the main way of locating companies in XRMS
 *
 * $Id: some.php,v 1.82 2006/07/13 00:37:48 vanmer Exp $
 */

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'utils-saved-search.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

$con = get_xrms_dbconnection();

//uncomment this line if you suspect a problem with the SQL query
//$con->debug = 1;

$on_what_table='companies';
$session_user_id = session_check();


getGlobalVar($browse,'browse');
getGlobalVar($saved_id, 'saved_id');
getGlobalVar($saved_title, 'saved_title');
getGlobalVar($group_item, 'group_item');
getGlobalVar($delete_saved, 'delete_saved');
    
getGlobalVar($not_city, 'not_city');
getGlobalVar($not_state, 'not_state');
getGlobalVar($not_category, 'not_category');
getGlobalVar($not_user, 'not_user');
getGlobalVar($not_country, 'not_country');
getGlobalVar($not_industry, 'not_industry');
getGlobalVar($not_source, 'not_source');
getGlobalVar($not_crm, 'not_crm');
getGlobalVar($not_name, 'not_name');
getGlobalVar($not_name, 'not_legal_name');
getGlobalVar($not_phone, 'not_phone');
getGlobalVar($not_code, 'not_code');
getGlobalVar($not_custom1, 'not_custom1');
getGlobalVar($not_custom2, 'not_custom2');
getGlobalVar($not_custom3, 'not_custom3');
getGlobalVar($not_custom4, 'not_custom4');
getGlobalVar($not_profile, 'not_profile');
    
/*********** SAVED SEARCH BEGIN **********************/
load_saved_search_vars($con, $on_what_table, $saved_id, $delete_saved);

/*********** SAVED SEARCH END **********************/


// declare passed in variables
$arr_vars = array ( // local var name       // session variable name
                   'company_name'        => array('companies_company_name',arr_vars_SESSION),
                   'company_type_id'     => array('companies_company_type_id',arr_vars_SESSION),
                   'company_category_id' => array('companies_company_category_id',arr_vars_SESSION),
                   'company_code'        => array('companies_company_code',arr_vars_SESSION),
                   'user_id'             => array('companies_user_id',arr_vars_SESSION),
                   'crm_status_id'       => array('companies_crm_status_id',arr_vars_GET_SESSION),
                   'industry_id'         => array('industry_id',arr_vars_SESSION),
                   'company_source_id'   => array('company_source_id',arr_vars_GET_SESSION),
                   'city'                => array('city',arr_vars_SESSION),
                   'state'               => array('state',arr_vars_SESSION),
                    'legal_name'         => array('companies_legal_name',arr_vars_SESSION),
                    'phone_search'              => array ( 'phone_search' , arr_vars_SESSION),
                    'phone'              => array ( 'companies_phone' , arr_vars_SESSION),
                    'phone2'             => array ( 'companies_phone2' , arr_vars_SESSION),
                    'fax'                => array ( 'companies_fax' , arr_vars_SESSION),
                    'url'                => array ( 'companies_url' , arr_vars_SESSION),
                    'employees'          => array ( 'companies_employees' , arr_vars_SESSION),
                    'revenue'            => array ( 'companies_revenue' , arr_vars_SESSION),
                    'custom1'            => array ( 'companies_custom1' , arr_vars_SESSION),
                    'custom2'            => array ( 'companies_custom2' , arr_vars_SESSION),
                    'custom3'            => array ( 'companies_custom3' , arr_vars_SESSION),
                    'custom4'            => array ( 'companies_custom4' , arr_vars_SESSION),
                    'profile'            => array ( 'companies_profile' , arr_vars_SESSION),
                    'address_name'       => array ( 'companies_address_name' , arr_vars_SESSION),
                    'line1'              => array ( 'companies_line1' , arr_vars_SESSION),
                    'line2'              => array ( 'companies_line2' , arr_vars_SESSION),
                    'province'           => array ( 'companies_province' , arr_vars_SESSION),
                    'postal_code'        => array ( 'companies_postal_code' , arr_vars_SESSION),
                    'country_id'         => array ( 'companies_country_id' , arr_vars_SESSION),
                    'address_body'       => array ( 'companies_address_body' , arr_vars_SESSION),
                   );

getGlobalVar($advanced_search, 'advanced_search'); // = (!empty($_REQUEST['advanced_search'])) ? true : false;


// get all passed in variables
arr_vars_get_all ( $arr_vars );

// set all session variables
arr_vars_session_set ( $arr_vars );


$sql = "SELECT "
        . $con->Concat("'<a id=\"'" , "c.company_name", "'\" href=\"one.php?company_id='","c.company_id","'\">'","c.company_name","'</a>'") . ' AS "name",
        c.company_code AS "code" ,
        u.username AS "user",
        industry_pretty_name as "industry",
        crm_status_pretty_name AS "crm_status",
        as1.account_status_display_html AS "account_status",
        r.rating_display_html AS "rating", addr.address_body as "primary_address" ';

$sql   .= ", addr.city\n";
$sql   .= ", addr.province\n";

$criteria_count = 0;

if ($company_category_id > 0) {
    $criteria_count++;
    $from = "from industries i, crm_statuses crm, ratings r, account_statuses as1, users u, entity_category_map ecm, companies c ";
} else {
    $from = "from industries i, crm_statuses crm, ratings r, account_statuses as1, users u, companies c ";
}

$extra_defaults=array();

$from  .= "LEFT JOIN addresses addr on addr.address_id = c.default_primary_address ";
$where = "where c.industry_id = i.industry_id ";
$where .= "and c.crm_status_id = crm.crm_status_id ";
//remove next line because it makes companies without default addr not display
//$where .= "and c.default_primary_address = addr.address_id ";
$where .= "and r.rating_id = c.rating_id ";
$where .= "and as1.account_status_id = c.account_status_id ";
$where .= "and c.user_id = u.user_id ";
$where .= "and company_record_status = 'a' \n";

if ($company_category_id > 0) {
  
    $where .= " and ecm.on_what_table = 'companies' and ecm.on_what_id = c.company_id and ecm.category_id = $company_category_id ";
}

if (strlen($company_name) > 0) {
    $criteria_count++;
  if ($not_name)
    $where .= " and c.company_name not like " . $con->qstr(company_search_string($company_name), get_magic_quotes_gpc()) ." \n";
  else
    $where .= " and c.company_name like " . $con->qstr(company_search_string($company_name), get_magic_quotes_gpc()) ." \n";
}

if (strlen($user_id) > 0) {
    $criteria_count++;
  if ($not_user)
    $where .= " and c.user_id <> $user_id \n";
  else
    $where .= " and c.user_id = $user_id \n";
}

if (strlen($company_code) > 0) {
    $criteria_count++;
  if ($not_code)
    $where .= " and c.company_code not like " . $con->qstr($company_code, get_magic_quotes_gpc())." \n";
  else
    $where .= " and c.company_code = " . $con->qstr($company_code, get_magic_quotes_gpc())." \n";
}

if (strlen($city) > 0) {
    $criteria_count++;
  if ($not_city) 
    $where .= " and addr.city NOT LIKE " . $con->qstr($city . '%' , get_magic_quotes_gpc())." \n" ;
  else 
    $where .= " and addr.city LIKE " . $con->qstr($city . '%' , get_magic_quotes_gpc())." \n" ;
    $extra_defaults[]='city';
}

if (strlen($state) > 0) {
    $criteria_count++;
 if ($not_state) 
    $where .= " and addr.province NOT LIKE " . $con->qstr($state, get_magic_quotes_gpc());
 else
    $where .= " and addr.province LIKE " . $con->qstr($state, get_magic_quotes_gpc());
    $extra_defaults[]='province';
}

if (strlen($crm_status_id) > 0) {
    $criteria_count++;
  if ($not_crm)
    $where .= " and c.crm_status_id <> $crm_status_id \n";
  else
    $where .= " and c.crm_status_id = $crm_status_id \n";
}

if (strlen($industry_id) > 0) {
    $criteria_count++;
  if ($not_industry)  
    $where .= " and c.industry_id <> $industry_id \n";
  else
    $where .= " and c.industry_id = $industry_id \n";
}

if (strlen($company_source_id) > 0) {
    $criteria_count++;
  if ($not_source)
    $where .= " and c.company_source_id <> $company_source_id \n";
  else
    $where .= " and c.company_source_id = $company_source_id \n";
}

// begin advanced-search query items
$advanced_search_columns = array();

if ( $legal_name ) {
    $criteria_count++;
    $sql .= ', c.legal_name ';
  if($not_legal_name)
    $where .= " and c.legal_name not like " . $con->qstr($legal_name, get_magic_quotes_gpc())." \n";
  else
    $where .= " and c.legal_name like " . $con->qstr($legal_name, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Legal Name"), 'index_sql' => 'legal_name');
    $extra_defaults[]='legal_name';
}

if ( $phone ) {
    $sql_phone=preg_replace("/[^\d]/", '', $phone);
    $criteria_count++;
    $sql .= ', c.phone ';
  if ($not_phone)
    $where .= " and c.phone not like " . $con->qstr('%'.$sql_phone.'%', get_magic_quotes_gpc())." \n";
  else 
    $where .= " and c.phone like " . $con->qstr('%'.$sql_phone.'%', get_magic_quotes_gpc())." \n";
    $extra_defaults[]='phone';
    $advanced_search_columns[] = array('name' => _("Phone"), 'index_sql' => 'phone');
}

if ( $phone2 ) {
    $sql_phone2=preg_replace("/[^\d]/", '', $phone2);
    $criteria_count++;
    $sql .= ', c.phone2 ';
    $where .= " and c.phone2 like " . $con->qstr('%'.$sql_phone2.'%', get_magic_quotes_gpc())." \n";
    $extra_defaults[]='phone2';
    $advanced_search_columns[] = array('name' => _("Phone 2"), 'index_sql' => 'phone2');
}

if ( $fax ) {
    $sql_fax=preg_replace("/[^\d]/", '', $fax);
    $criteria_count++;
    $sql .= ', c.fax ';
    $where .= " and c.fax like " . $con->qstr('%'.$sql_fax.'%', get_magic_quotes_gpc())." \n";
    $extra_defaults[]='fax';
    $advanced_search_columns[] = array('name' => _("Fax"), 'index_sql' => 'fax');
}

$phone_fields=array('phone'=>_("Phone"),'phone2'=>_("Phone 2"),'fax'=>_("Fax"));
if ($phone_search) {
    $sql_phone_search=preg_replace("/[^\d]/", '', $phone_search);
    $phonewhere=array();
    foreach ($phone_fields as $phonefield => $phonelabel) {
        $criteria_count++;
        $sql .= ", $phonefield ";
      if ($not_phone)
        $phonewhere[] = "($phonefield NOT LIKE " . $con->qstr('%'.$sql_phone_search.'%'). ")";
      else
        $phonewhere[] = "($phonefield LIKE " . $con->qstr('%'.$sql_phone_search.'%'). ")";
        $extra_defaults[]=$phonefield;
        $advanced_search_columns[] = array('name' => $phonelabel, 'index_sql' => $phonefield);
    }
    if (count($phonewhere)>0) {
        $where .= " AND (" . implode(' OR ', $phonewhere) . ")";
    }
}

if (strlen($url) > 0) {
    $criteria_count++;
    $sql .= ', c.url ';
    $where .= " and c.url like " . $con->qstr($url, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("URL"), 'index_sql' => 'url');
}

if ( $employees ) {
    $criteria_count++;
    $sql .= ', c.employees ';
    $where .= " and c.employees like " . $con->qstr($employees, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("employees"), 'index_sql' => 'employees');
}

if ( $revenue ) {
    $criteria_count++;
    $sql .= ', c.revenue ';
    $where .= " and c.revenue like " . $con->qstr($revenue, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Revenue"), 'index_sql' => 'revenue');
}

if ( $custom1 ) {
    $criteria_count++;
    $sql .= ', c.custom1 ';
  if ($not_custom1)
    $where .= " and c.custom1 not like " . $con->qstr($custom1, get_magic_quotes_gpc())." \n";
  else
    $where .= " and c.custom1 like " . $con->qstr($custom1, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Custom 1"), 'index_sql' => 'custom1');
}

if ( $custom2 ) {
    $criteria_count++;
    $sql .= ', c.custom2 ';
  if ($not_custom2)
    $where .= " and c.custom2 not like " . $con->qstr($custom2, get_magic_quotes_gpc())." \n";
  else
    $where .= " and c.custom2 like " . $con->qstr($custom2, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Custom 2"), 'index_sql' => 'custom2');
}

if ( $custom3 ) {
    $criteria_count++;
    $sql .= ', c.custom3 ';
  if ($not_custom3)
    $where .= " and c.custom3 not like " . $con->qstr($custom3, get_magic_quotes_gpc())." \n";
  else
    $where .= " and c.custom3 like " . $con->qstr($custom3, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Custom 3"), 'index_sql' => 'custom3');
}

if ( $custom4 ) {
    $criteria_count++;
    $sql .= ', c.custom4 ';
  if ($not_custom4)
    $where .= " and c.custom4 not like " . $con->qstr($custom4, get_magic_quotes_gpc())." \n";
  else
    $where .= " and c.custom4 like " . $con->qstr($custom4, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Custom 4"), 'index_sql' => 'custom4');
}

if ( $profile ) {
    $criteria_count++;
    $sql .= ', c.profile ';
  if ($not_profile)
    $where .= " and c.profile not like " . $con->qstr($profile, get_magic_quotes_gpc())." \n";
  else
    $where .= " and c.profile like " . $con->qstr($profile, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Profile"), 'index_sql' => 'profile');
}

if ( $address_name ) {
    $criteria_count++;
    $sql .= ', addr.address_name ';
    $where .= " and addr.address_name like " . $con->qstr($address_name, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Address"), 'index_sql' => 'address_name');
}

if ( $line1 ) {
    $criteria_count++;
    $sql .= ', addr.line1 ';
    $where .= " and addr.line1 like " . $con->qstr($line1, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Line 1"), 'index_sql' => 'line1');
}

if ( $line2 ) {
    $criteria_count++;
    $sql .= ', addr.line2 ';
    $where .= " and addr.line2 like " . $con->qstr($line2, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Line 2"), 'index_sql' => 'line2');
}

if ( $province ) {
    $criteria_count++;
    $sql .= ', addr.province ';
    $where .= " and addr.province like " . $con->qstr($province, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Province"), 'index_sql' => 'province');
}

if ( $postal_code ) {
    $criteria_count++;
    $sql .= ', addr.postal_code ';
    $where .= " and addr.postal_code like " . $con->qstr($postal_code, get_magic_quotes_gpc())." \n";
    $advanced_search_columns[] = array('name' => _("Postal Code"), 'index_sql' => 'postal_code');
}

if ( $country_id and is_numeric($country_id)) {
    $criteria_count++;
    $from .= ', countries country ';
    $sql .= ', country.country_name ';
  if ($not_country)
    $where .= " and addr.country_id <> $country_id and country.country_id = addr.country_id \n";
  else
    $where .= " and addr.country_id = $country_id and country.country_id = addr.country_id \n";
    $advanced_search_columns[] = array('name' => _("Country"), 'index_sql' => 'country_name');
    $extra_defaults[]='country_name';
}
// end advanced-search query items

if (!$use_post_vars && (!$criteria_count > 0)) {
    $where .= " and 1 = 2";
} else {
    $list=acl_get_list($session_user_id, 'Read', false, $on_what_table);
    if ($list) {
        if ($list!==true) {
            $list=implode(",",$list);
            $where .= " and c.company_id IN ($list) \n";
        }
    } else { $where .= ' AND 1 = 2 '; }
}

$sql .= $from . $where;

$sql_recently_viewed = "select
 c.company_id,
 c.company_name,
 max(r.recent_item_timestamp) as lasttime
 from recent_items r, companies c
 where r.user_id = $session_user_id
 and r.on_what_table = 'companies'
 and r.recent_action = ''
 and r.on_what_id = c.company_id
 and c.company_record_status = 'a'
 group by company_id,
 c.company_name
 order by lasttime desc";

$rst = $con->selectlimit($sql_recently_viewed, $recent_items_limit);


$recently_viewed_table_rows = '';

if ($rst) {
    while (!$rst->EOF) {
        $recently_viewed_table_rows .= '<tr>';
        $recently_viewed_table_rows .= '<td class=widget_content><a href="one.php?company_id=' . $rst->fields['company_id'] . '">' . $rst->fields['company_name'] . '</a></td>';
        $recently_viewed_table_rows .= '</tr>';
        $rst->movenext();
    }
    $rst->close();
}

if (strlen($recently_viewed_table_rows) == 0) {
    $recently_viewed_table_rows = "<tr><td class=widget_content colspan=3>"._("No recently viewed companies")."</td></tr>";
}

/******* SAVED SEARCH BEGINS *****/
    if (!isset($day_diff)) $day_diff=0;
    $saved_data = $_POST;
    $saved_data["sql"] = $sql;
    $saved_data["day_diff"] = $day_diff;
    
    if(!$saved_title) {
        $saved_title = "Current";
        $group_item = 0;
    }
    if ($saved_title OR $browse) {
//        echo "adding saved search";
        $saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);
//        echo "$saved_id=add_saved_search_item($con, $saved_title, $group_item, $on_what_table, $saved_data);";
    }    

//get saved searches
$rst=get_saved_search_item($con, $on_what_table, $session_user_id, false,  false, true,'search', true);
if( $rst AND $rst->RowCount() ) {
    $saved_menu = $rst->getmenu2('saved_id', 0, true) . ' <input name="delete_saved" type=submit class=button value="' . _("Delete") . '">';
} else {
  $saved_menu = '';
}

/********** SAVED SEARCH ENDS ****/



$user_menu = get_user_menu($con, $user_id, true);

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


// secondary queries and menus for basic-search
$sql2 = "select industry_pretty_name, industry_id from industries where industry_record_status = 'a' order by industry_pretty_name";
$rst = $con->execute($sql2);
$industry_menu = translate_menu($rst->getmenu2('industry_id', $industry_id, true));
$rst->close();


// secondary queries and menus for advanced-search
if($advanced_search) {

    // crm_status_menu
    $crm_status_menu = build_crm_status_menu($con, $crm_status_id, true);

    // company_source_menu
    $sql2 = "select company_source_pretty_name, company_source_id from company_sources where company_source_record_status = 'a' order by company_source_pretty_name";
    $company_source_menu = check_and_get($con,$sql2,'company_source_id',$company_source_id);
    //$company_source_menu = check_and_get($con,$sql2,'');


}
// country_menu
$sql2 = "select country_name, country_id from countries where country_record_status = 'a' order by country_name";
$country_menu = check_and_get($con,$sql2,'country_id',$country_id);
//$country_menu = check_and_get($con,$sql2,'');


if ($criteria_count > 0) {
    add_audit_item($con, $session_user_id, 'searched', 'companies', '', 4);
}

$page_title = _("Search Companies");
start_page($page_title, true, $msg);

if ($advanced_search) $field_columns=1;
else ($field_columns=2);
$search_colspan=$field_columns+1;
$total_colspan=2*$search_colspan

?>

<div id="Main">
    <div id="Content">

        <form action=some.php class="print" method=post name="CompanyForm">
        <input type=hidden name=use_post_vars value=1>
        <input type=hidden name=advanced_search value="<?php echo $advanced_search; ?>">
        <input type=hidden name=company_type_id value="<?php  echo $company_type_id; ?>">
        <input type=hidden name=company_code value="<?php  echo $company_code; ?>">
        <input type=hidden name=crm_status_id value="<?php  echo $crm_status_id; ?>">

        <input type=hidden name=companies_next_page>
        <table class=widget cellspacing=1 width="100%">
        <tr>
          <td class=widget_header colspan="<?php echo $total_colspan; ?>">
            <?php  echo _("Search Criteria"); ?>
          </td>
        </tr>

<?php

if (!$advanced_search) {
//BASIC SEARCH
?>
        <tr>
          <td class=widget_label>
            <?php  echo _("Company Name"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("Owner"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("Category"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("Industry"); ?>
          </td>
        </tr>
        <tr>
            <td class=widget_content_form_element>
                <?php if ($advanced_search) {
                    echo _("!"); echo ' <input type=checkbox name="not_name" value=1';
                    if ($not_name) { echo ' checked'; } 
                    echo '>';
                 } ?>
                <input type=text name="company_name" size=15 value="<?php  echo $company_name; ?>">
            </td>
            <td class=widget_content_form_element>
                <?php if ($advanced_search) {
                    echo _("!"); echo ' <input type=checkbox name="not_user" value=1';
                    if ($not_user) { echo ' checked'; } 
                    echo '>';
                 } ?>
                <?php  echo $user_menu; ?>
            </td>
            <td class=widget_content_form_element>
                <?php  echo $company_category_menu; ?>
            </td>
            <td class=widget_content_form_element>
                <?php if ($advanced_search) {
                    echo _("!"); echo ' <input type=checkbox name="not_industry" value=1';
                    if ($not_industry) { echo ' checked'; } 
                    echo '>';
                 } ?>
                <?php  echo $industry_menu; ?>
            </td>
        </tr>
        <tr>
          <td class=widget_label>
            <?php  echo _("Phone"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("City"); ?>
          </td>
          <td class=widget_label>
            <?php  echo _("State"); ?>
          </td>
                <td class=widget_label><?php echo _("Country"); ?></td>
                
        </tr>
        <tr>
            <td class=widget_content_form_element>
                <?php if ($advanced_search) {
                    echo _("!"); echo ' <input type=checkbox name="not_phone" value=1';
                    if ($not_phone) { echo ' checked'; } 
                    echo '>';
                 } ?>
                <input type=text name="phone_search" size=10 value="<?php  echo $phone_search; ?>">
            </td>
            <td class=widget_content_form_element>
                <?php if ($advanced_search) {
                    echo _("!"); echo ' <input type=checkbox name="not_city" value=1';
                    if ($not_city) { echo ' checked'; } 
                    echo '>';
                 } ?>
                <input type=text name="city" size=10 value="<?php  echo $city; ?>">
            </td>
            <td class=widget_content_form_element>
                <?php if ($advanced_search) {
                    echo _("!"); echo ' <input type=checkbox name="not_state" value=1';
                    if ($not_state) { echo ' checked'; } 
                    echo '>';
                 } ?>
                <input type=text name="state" size=5 value="<?php echo $state; ?>">
            </td>
            <td class=widget_content_form_element>
                <?php if ($advanced_search) {
                    echo _("!"); echo ' <input type=checkbox name="not_country" value=1';
                    if ($not_country) { echo ' checked'; } 
                    echo '>';
                 } ?>
                <?php echo $country_menu ?>
            </td>
        </tr>

        <?php
            //END BASIC SEARCH
            } else {
            //ADVANCED SEARCH
        ?>
    <tr>
        <td class=lcol width="55%" valign=top>


        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Company Information")." - "._("Advanced Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Name"); ?></td>
		    <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_name) { echo ' <input type=checkbox name="not_name" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_name" value=1>';}?>
                <input type=text size=50 name=company_name value="<?php echo $company_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Legal Name"); ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_legal_name) { echo ' <input type=checkbox name="not_legal_name" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_legal_name" value=1>';}?>
                <input type=text size=50 name=legal_name value="<?php echo $legal_name; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Company Code"); ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_code) { echo ' <input type=checkbox name="not_code" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_code" value=1>';}?>
                <input type=text size=10 name=company_code value ="<?php echo $company_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Category"); ?></td>
                <td class=widget_content_form_element>
                <?php  echo $company_category_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("CRM Status"); ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_crm) { echo ' <input type=checkbox name="not_crm" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_crm" value=1>';}?>
                <?php  echo $crm_status_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right>
                <?php echo _("Company Source"); ?></td>
          <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_source) { echo ' <input type=checkbox name="not_source" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_source" value=1>';}?>
                <?php  echo $company_source_menu; ?> </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Industry"); ?></td>
                <td class=widget_content_form_element width=80%>
                <?php echo _("!")?>
                <?php if ($not_industry) { echo ' <input type=checkbox name="not_industry" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_industry" value=1>';}?>
                <?php  echo $industry_menu; ?> </td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Owner"); ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_user) { echo ' <input type=checkbox name="not_user" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_user" value=1>';}?>
                <?php  echo $user_menu; ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Phone"); ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_phone) { echo ' <input type=checkbox name="not_phone" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_phone" value=1>';}?>
                <input type=text name=phone value="<?php echo $phone; ?>"></td>
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
            <?php if ($company_custom1_label!='(Custom 1)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom1_label ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_custom1) { echo ' <input type=checkbox name="not_custom1" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_custom1" value=1>';}?>
                <input type=text name=custom1 size=30 value="<?php echo $custom1; ?>"></td>
            </tr>
            <?php } if ($company_custom2_label!='(Custom 2)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom2_label ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_custom2) { echo ' <input type=checkbox name="not_custom2" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_custom2" value=1>';}?>
                <input type=text name=custom2 size=30  value="<?php echo $custom2; ?>"></td>
            </tr>
            <?php } if ($company_custom3_label!='(Custom 3)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom3_label ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_custom3) { echo ' <input type=checkbox name="not_custom3" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_custom3" value=1>';}?>
                <input type=text name=custom3 size=30  value="<?php echo $custom3; ?>"></td>
            </tr>
            <?php } if ($company_custom4_label!='(Custom 4)') { ?>
            <tr>
                <td class=widget_label_right><?php echo $company_custom4_label ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_custom4) { echo ' <input type=checkbox name="not_custom4" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_custom4" value=1>';}?>
                <input type=text name=custom4 size=30  value="<?php echo $custom4; ?>"></td>
            </tr>
            <?php } ?>
            <tr>
                <td class=widget_label_right><?php echo _("Profile"); ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_profile) { echo ' <input type=checkbox name="not_profile" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_profile" value=1>';}?>
                <input type=text size=50 name=profile_name value="<?php echo $profile; ?>"></td>
                
            </tr>
             <tr>
                
          <td class=widget_content_form_element colspan=2>&nbsp;</td>
            </tr>
        </table>

        </td>
        <!-- gutter //-->
        <td class=gutter width="1%">&nbsp;
        
        </td>
        <!-- right column //-->
        <td class=rcol width="44%" valign=top>

        <!-- Address Entry //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Address")." - "._("Advanced Search Criteria"); ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Address Name"); ?></td>
                <td class=widget_content_form_element><input type=text name=address_name size=30 value="<?php echo $address_name; ?>">
          </td>
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
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_city) { echo ' <input type=checkbox name="not_city" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_city" value=1>';}?>
                <input type=text name=city size=30 value="<?php echo $city; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("State/Province"); ?></td>
                <td class=widget_content_form_element>
                <?php echo _("!")?>
                <?php if ($not_state) { echo ' <input type=checkbox name="not_state" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_state" value=1>';}?>
                <input type=text name=province size=20 value="<?php echo $province; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Postal Code"); ?></td>
                <td class=widget_content_form_element><input type=text name=postal_code size=10 value="<?php echo $postal_code; ?>"></td>
            </tr>
            <tr>
                <td class=widget_label_right>
                <?php echo _("Country"); ?>
                <?php echo _("!")?>
                <?php if ($not_country) { echo ' <input type=checkbox name="not_country" value=1 checked>'; } 
                      else   { echo ' <input type=checkbox name="not_country" value=1>';}?>
          </td>
                <td class=widget_content_form_element>
                <?php echo $country_menu ?></td>
            </tr>
            <tr>
                <td class=widget_label_right><?php echo _("Override Address"); ?></td>
                <td class=widget_content_form_element><textarea rows=5 cols=40 name=address_body><?php echo $address_body; ?></textarea><br>
          </td>
            </tr>
        </table>

    </td>
    </tr>
<?php
    //END ADVANCED SEARCH
            }
    //START SAVED SEARCH MENU
        ?>

        <tr>
            <td class=widget_label colspan="<?php echo $search_colspan-1; ?>"><?php echo _("Saved Searches"); ?></td>
            <td class=widget_label colspan="<?php echo $search_colspan+1; ?>"><?php echo _("Search Title"); ?></td>
        </tr>
        <tr>
            <td class=widget_content_form_element colspan="<?php echo $search_colspan-1; ?>">
                <?php echo ($saved_menu) ? $saved_menu : _("No Saved Searches"); ?>
            </td>
            <td class=widget_content_form_element colspan="<?php echo $search_colspan+1; ?>">
                <input type=text name="saved_title" size=24>
                <?php
                    if(check_user_role(false, $_SESSION['session_user_id'], 'Administrator')) {
                        echo _("Add to Everyone").' <input type=checkbox name="group_item" value=1>';
                    }
                ?>
            </td>
        </tr>
        <tr>
            <td class=widget_content_form_element colspan=<?php echo $total_colspan; ?>>
                <input name="submit_form" type=submit class=button value="<?php echo _("Search"); ?>">
                <input name="clear_search" type=button class=button onClick="javascript: clearSearchCriteria();" value="<?php echo _("Clear Search"); ?>">
                    <?php
                        if(!$advanced_search) {
                            echo '<input name="advanced_search" type=button class=button onclick="javascript: location.href=\'some.php?advanced_search=true\';" value="' . _("Advanced Search") . '">';
                        }
                    ?>
            </td>
        </tr>
      </table>

<?php
//Nic - I did this different than the other some.phps because it is a more complex sql you have to write to retrieve company email records
$searchsql = array();
$searchsql['from'] = $from;
$searchsql['where'] = $where;
$_SESSION['search_sql'] = $searchsql;

$columns = array();
$columns[] = array('name' => _("Company Name"), 'index_sql' => 'name', 'type' => 'url');
$columns[] = array('name' => _("Company Code"), 'index_sql' => 'code');
$columns[] = array('name' => _("User"), 'index_sql' => 'user');
$columns[] = array('name' => _("Industry"), 'index_sql' => 'industry');
$columns[] = array('name' => _("CRM Status"), 'index_sql' => 'crm_status');
$columns[] = array('name' => _("Account Status"), 'index_sql' => 'account_status', 'type' => 'html');
$columns[] = array('name' => _("Rating"), 'index_sql' => 'rating', 'type' => 'html');
$columns[] = array('name' => _("Primary Address"), 'index_sql' => 'primary_address');

$advanced_search_columns[] = array('name' => _("City"), 'index_sql' => 'city');
$advanced_search_columns[] = array('name' => _("State"), 'index_sql' => 'province');

$columns = array_merge($columns, $advanced_search_columns);

// selects the columns this user is interested in
// no reason to set this if you don't want all by default
$default_columns = null;
$default_columns = array('name','code','user','industry','crm_status','account_status','rating','primary_address');
$default_columns=array_merge($default_columns, $extra_defaults);

// $default_columns =  array("name","code","user","industry","crm_status","account_status","rating");
$pager_id='CompanyPager';
$form_id='CompanyForm';
$pager_columns = new Pager_Columns($pager_id, $columns, $default_columns, $form_id);
$pager_columns_button = $pager_columns->GetSelectableColumnsButton();
$pager_columns_selects = $pager_columns->GetSelectableColumnsWidget();

$columns = $pager_columns->GetUserColumns();

echo $pager_columns_selects;

$pager = new GUP_Pager($con, $sql, null, _('Search Results'), $form_id, $pager_id, $columns);

$endrows = "<tr><td class=widget_content_form_element colspan=10>
            $pager_columns_button
            " . $pager->GetAndUseExportButton() .  "
            <input type=button class=button onclick=\"javascript: bulkEmail();\" value=\""._("Mail Merge")."\">
	    <input type=button class=button onclick=\"javascript: bulkSnailMail();\" value=\""._("Snail Mail Merge")."\">
</td></tr>";

$pager->AddEndRows($endrows);
$pager->Render($system_rows_per_page);

$con->close();

?>

    </form>
    </div>

        <!-- right column //-->
    <div id="Sidebar">

        <!-- new company //-->
        <div class="noprint">
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header colspan=2><?php echo _("Company Options"); ?></td>
            </tr>
            <tr>
                <td class=widget_content><?php echo render_create_button(_("New Company"), 'button', "javascript: location.href='new.php';", false, false, 'companies'); ?></td>
            </tr>
        </table>
        </div>

        <!-- recently viewed companies //-->
        <table class=widget cellspacing=1 width="100%">
            <tr>
                <td class=widget_header><?php echo _("Recently Viewed"); ?></td>
            </tr>
            <tr>
                <td class=widget_label><?php  echo _("Company Name"); ?></td>
            </tr>
            <?php  echo $recently_viewed_table_rows; ?>
        </table>

    </div>
</div>

<script language="JavaScript" type="text/javascript">
<!--

function initialize() {
    document.forms[0].company_name.focus();
}

initialize();

function submitForm(companiesNextPage) {
    document.forms[0].companies_next_page.value = companiesNextPage;
    document.forms[0].submit();
}

function exportIt() {
    //document.forms[0].action = "export.php";
    //document.forms[0].submit();
    // reset the form so that post-export searches work
    //document.forms[0].action = "some.php";
                alert('Export functionality hasnt been implemented yet for multiple companies')
}

function bulkEmail() {
    document.forms[0].action = "../email/email.php?scope=companies";
    document.forms[0].submit();
    //alert('Mail Merge functionality hasnt been implemented yet for multiple companies')
}
function bulkSnailMail() {
    document.forms[0].action = "../snailmail/snailmail-1.php?scope=companies";
    document.forms[0].submit();
}
function clearSearchCriteria() {
    location.href = "some.php?clear=1";
}


//-->
</script>

<?php

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

end_page();

/**
 * $Log: some.php,v $
 * Revision 1.82  2006/07/13 00:37:48  vanmer
 * - changed to ensure that company pager name matches columns name
 *
 * Revision 1.81  2006/01/02 22:56:27  vanmer
 * - changed to use centralized dbconnection function
 *
 * Revision 1.80  2005/12/06 21:39:18  vanmer
 * - updated negative criteria to only appear in advanced search
 *
 * Revision 1.79  2005/12/06 21:26:06  vanmer
 * - patch from dbaudone for companies advanced search
 * - adds ability to specify negative criterion
 * - adds search on company categories
 * - removes snailmail button
 *
 * Revision 1.78  2005/10/06 04:30:06  vanmer
 * - updated log entries to reflect addition of code by Diego Ongaro at ETSZONE
 *
 * Revision 1.77  2005/10/04 23:21:44  vanmer
 * Patch to allow sort_order on the company CRM status field, thanks to Diego Ongaro at ETSZONE
 *
 * Revision 1.76  2005/08/16 00:15:21  vanmer
 * - changed all phone searches to be contains instead of starts with
 * - added code to strip all formatting off phone searches
 * - added work extension to fields searched on a contact
 *
 * Revision 1.75  2005/08/13 22:57:01  vanmer
 * - altered to hide custom company fields unless their labels have been changed in vars.php
 *
 * Revision 1.74  2005/08/13 22:43:51  vanmer
 * - added phone search to basic company search
 * - added searched for fields into outputted results by default
 * - added country search to basic company search
 *
 * Revision 1.73  2005/08/12 20:42:52  vanmer
 * - added Advanced Search interface (patch from niclowe)
 * - added advanced search as parameter instead of hardcoded into _REQUEST
 * - changed column counts in table to reflect change between advanced/basic search
 *
 * Revision 1.72  2005/08/12 16:31:42  ycreddy
 * Moved the code for populating Recently Viewed Companies before the query for Saved Search - fixes the bug around not showing Recently Viewed Companies
 *
 * Revision 1.71  2005/08/05 21:39:09  vanmer
 * - changed to use centralized company search name function
 *
 * Revision 1.70  2005/08/05 01:33:21  vanmer
 * - added saved search functionality to companies search page
 *
 * Revision 1.69  2005/08/02 22:05:52  vanmer
 * - changed to use new company button instead of company link, wrapped in ACL check
 *
 * Revision 1.68  2005/06/20 18:48:09  niclowe
 * added snail mail merge functionality
 *
 * Revision 1.67  2005/06/11 12:59:13  braverock
 * - clean up SQL formatting (add spaces) to fix adodb pager 'single page' bugs
 *
 * Revision 1.66  2005/05/09 05:01:53  daturaarutad
 * fixed missing space after query
 *
 * Revision 1.65  2005/05/06 21:54:37  daturaarutad
 * merged advanced-search fields into query and pager
 *
 * Revision 1.64  2005/04/29 17:54:22  daturaarutad
 * fixed printing of form/search results
 *
 * Revision 1.63  2005/04/29 16:22:46  daturaarutad
 * updated to use GUP_Pager for export
 *
 * Revision 1.62  2005/03/30 17:29:21  daturaarutad
 * s/Rating/rating/ in $columns
 *
 * Revision 1.61  2005/03/21 13:40:55  maulani
 * - Remove redundant code by centralizing common user menu call
 *
 * Revision 1.60  2005/03/20 01:50:35  maulani
 * - Remove reference to company_company_type_map
 *
 * Revision 1.59  2005/03/15 22:48:04  daturaarutad
 * pager tuning sql_sort_column
 *
 * Revision 1.58  2005/03/11 20:22:08  daturaarutad
 * added company_source_id as a search criteria for linking with reports/graphs
 *
 * Revision 1.57  2005/03/11 17:28:39  daturaarutad
 * added GET to cases_case_status_id for linking to reports/graphs
 *
 * Revision 1.56  2005/03/02 15:18:34  daturaarutad
 * removed redundant mail merge button and corresponding query
 *
 * Revision 1.55  2005/02/28 22:41:49  daturaarutad
 * changed columns to be index_sql so that the pager knows it doesnt have to get the whole data set
 *
 * Revision 1.54  2005/02/25 03:43:43  daturaarutad
 * fixed search_sql for email, updated to use GUP_Pager
 *
 * Revision 1.53  2005/02/14 21:43:45  vanmer
 * - updated to reflect speed changes in ACL operation
 *
 * Revision 1.52  2005/02/09 23:58:14  braverock
 * - quote the Mail MErge button so both words show
 *
 * Revision 1.51  2005/02/09 23:54:53  braverock
 * - fix missing . concatenate operator in Mail Merge button
 *
 * Revision 1.50  2005/02/09 22:25:49  braverock
 * - localized pager column headers
 * - de-localized AS clauses in SQL
 *
 * Revision 1.49  2005/01/25 22:01:54  daturaarutad
 * updated to use new XRMS_Pager and Pager_Columns to implement selectable columns
 *
 * Revision 1.48  2005/01/13 18:28:06  vanmer
 * - ACL restriction on search
 *
 * Revision 1.47  2004/12/31 21:14:30  braverock
 * - sort select lists in search
 *
 * Revision 1.46  2004/10/28 22:17:02  gpowers
 * - removed company_code from Recently Viewed Sidebar
 *   - If anyone wants to keep it, let me know and I'll revert this patch and create a new patch with a $show_company_code_in_sidebars var.
 *
 * Revision 1.45  2004/08/26 23:16:28  niclowe
 * Enabled mail merge functionality for companies/some.php
 * Sorted pre-sending email checkbox page by company then contact lastname
 * Enabled mail merge for advanced-search companies
 *
 * Revision 1.44  2004/08/26 22:35:28  niclowe
 * Enabled mail merge functionality for companies
 *
 * Revision 1.43  2004/08/20 17:14:40  braverock
 * - add translate_menu for additional menus in search
 *
 * Revision 1.42  2004/08/19 13:14:05  maulani
 * - Add specific type pager to ease overriding of layout function
 *
 * Revision 1.41  2004/08/19 12:01:53  braverock
 * - added space after Rating so that the $from clause wouldn't collide
 *   - fixes SF bug 996549 using suggestion from Roberto Durrer (durrer)
 *
 * Revision 1.40  2004/08/18 00:06:16  niclowe
 * Fixed bug 941839 - Mail Merge not working
 *
 * Revision 1.39  2004/08/17 10:56:44  johnfawcett
 * - added translate_menu call to Industries select menu
 *
 * Revision 1.38  2004/08/13 12:29:57  maulani
 * - Fix errant copy and paste
 *
 * Revision 1.37  2004/08/03 14:36:54  maulani
 * - Fix recent items sql to only list each company once and to optimize the sql
 * - Fix advanced search button to remove erroneous comment
 *
 * Revision 1.36  2004/07/31 12:23:19  cpsource
 * - Reactivate advanced search feature
 *
 * Revision 1.35  2004/07/31 12:14:59  cpsource
 * - Stub advanced search as it doesn't seem to work.
 *
 * Revision 1.34  2004/07/28 20:41:30  neildogg
 * - Added field recent_action to recent_items
 *  - Same function works transparently
 *  - Current items have recent_action=''
 *  - update_recent_items has new optional parameter
 *
 * Revision 1.33  2004/07/25 12:43:25  braverock
 * - remove lang file require_once, as it is no longer used
 *
 * Revision 1.32  2004/07/19 02:21:56  braverock
 * - localize all strings for i18n
 *
 * Revision 1.31  2004/07/16 11:31:42  cpsource
 * - Removed hard-coded english language construct
 *   Removed advanced-search.php button as advanced-search had problems
 *
 * Revision 1.30  2004/07/15 13:49:53  cpsource
 * - Added arr_vars sub-system.
 *
 * Revision 1.29  2004/07/15 13:05:08  cpsource
 * - Add arr_vars sub-system for passing variables between code streams.
 *
 * Revision 1.28  2004/07/14 20:19:50  cpsource
 * - Resolved $company_count not being set properly
 *   opportunities/some.php tried to set $this which can't be done in PHP V5
 *
 * Revision 1.27  2004/07/14 16:06:55  cpsource
 * - Fix numerous undefined variable usages, including a database
 *   loop to determine $company_count.
 *
 * Revision 1.26  2004/07/10 12:56:06  braverock
 * - fix $SESSION sort order variables
 *   - applies patch suggest by cpsource in SF bug 976223
 *
 * Revision 1.25  2004/07/09 18:42:13  introspectshun
 * - Removed CAST(x AS CHAR) for wider database compatibility
 * - The modified MSSQL driver overrides the default Concat function to cast all datatypes as strings
 * - Removed 'as' in JOIN as it fails on Oracle and isn't needed with other db engines
 *
 * Revision 1.24  2004/07/05 21:30:54  introspectshun
 * - Moved 'companies c' to the end of 'from' clause.
 * - Query fails on MSSQL otherwise (column 'c' not recognized during 'LEFT JOIN' operation)
 *
 * Revision 1.23  2004/07/02 17:38:18  maulani
 * - Fix addresses reference in sql in patch submitted by Nic Lowe (niclowe)
 * - Patch # 981927
 *
 * Revision 1.22  2004/07/01 15:50:25  maulani
 * - Fix bug 976220 reported by cpsource ($where used before defined)
 *
 * Revision 1.21  2004/07/01 13:37:25  braverock
 * - compress quick search back onto one line now that advanced search exists
 *   - @todo add Category search to Advanced search
 *
 * Revision 1.20  2004/07/01 13:21:06  braverock
 * - change name of submit to submit_form to not conflict with js
 *   - patch supplied by David Uhlman
 *
 * Revision 1.19  2004/06/29 14:43:21  maulani
 * - Full implementation of advanced companies search
 *
 * Revision 1.18  2004/06/26 15:36:03  braverock
 * - change search layout to two rows to improve CSS positioning
 *   - applied modified version of SF patch #971474 submitted by s-t
 *
 * Revision 1.17  2004/06/23 21:50:53  braverock
 * - use join to find address so that even companies without addr will display
 *   - patch submitted by David Uhlman
 *
 * Revision 1.16  2004/06/21 20:56:29  introspectshun
 * - Now use CAST AS CHAR to convert integers to strings in Concat function calls.
 *
 * Revision 1.15  2004/06/16 20:42:02  gpowers
 * - removed $this from session_check()
 *   - it is incompatible with PHP5
 *
 * Revision 1.14  2004/06/12 16:15:06  braverock
 * - remove CAST on CONCAT - databases should implicitly convert numeric to string and VARCHAR is not universally supported
 *
 * Revision 1.13  2004/06/12 05:03:16  introspectshun
 * - Now use ADODB GetInsertSQL, GetUpdateSQL, date and Concat functions.
 * - Corrected order of arguments to implode() function.
 *
 * Revision 1.12  2004/05/10 13:09:14  maulani
 * - add level to audit trail
 *
 * Revision 1.11  2004/05/06 13:55:49  braverock
 * -add industry search to Companies
 *  - modified form of SF patch 949147 submitted by frenchman
 *
 * Revision 1.10  2004/04/15 22:04:39  maulani
 * - Change to CSS2 positioning
 * - Clean HTML to achieve validation
 *
 * Revision 1.9  2004/04/07 19:38:25  maulani
 * - Add CSS2 positioning
 * - Repair HTML to meet validation
 *
 * Revision 1.8  2004/03/09 13:47:42  braverock
 * - fixed duplicate city,state display when both are in search terms
 *
 * Revision 1.7  2004/03/09 13:39:39  braverock
 * - fixed broken city and state search
 * - add phpdoc
 *
 */
?>