<?php

// Copyright 2007 Glenn Powers <glenn@net127.com>
require_once('../../include-locations.inc');
require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'adodb/tohtml.inc.php');

/* BOF: Setup Search */

// setup what to search for, change from true to false or vice versa
// to enable disable seaches
$contact_search = true;
$contact_first_names_search = false;
$company_search = true;
$opportunity_search = true;
$case_search = true;
$campaign_search = true;

// i got trouble with the preg_match expression '/^\w+$/'
// and german umlaut, so i defined characters explitily,
// modify to your needs e.g. add 'è'
$input_regex='/^[a-z\A-Z\0-9\ä\Ä\ö\Ö\ü\Ü\-\ß]+$/';

/* EOF: setup seach */

$session_user_id = session_check();
$q = mysql_real_escape_string($_GET['q']);
$q = preg_replace('/^(\s*)(.*)/','\2',$q);
// $q = preg_replace('/[\s*]$/','',$q);


$con = get_xrms_dbconnection();
//$con->debug = 1;

$data = "";

$sql = "select * from users where user_id = '" . $session_user_id . "'";
$rst = $con->execute($sql);
$user_last_name = $rst->fields['last_name'];
$user_first_names = $rst->fields['first_names'];

// COMPANY NAME and LEGAL NAME search
if (preg_match('/^\w+$/',$q) && $company_search) {
  $sql = "SELECT company_name, company_id
  FROM companies
  WHERE (company_name like '%" . $q . "%'
  OR legal_name like '%" . $q . "%')
  AND company_record_status = 'a'
  ";

  $rst = $con->execute($sql);

  if (($rst)&&(!$rst->EOF)) {
    while(!$rst->EOF) {
      $data .= strtoupper(_("Company")).": ".$rst->fields['company_name']. "|". $http_site_root;
      $data .= "/companies/one.php?company_id=". $rst->fields['company_id']. "\n";
      $rst->movenext();
      }
    }
}

// CONTACT NAME seatch
if (preg_match('/^[a-z\A-Z\ä\Ä\ö\Ö\ü\Ü\-\ß]+$/',$q) && $contact_search) {
  $sql_first_names = ($contact_first_names_search) ? " OR first_names like '%" . $q . "%' " : "";
  $sql = "SELECT CONCAT(first_names, ' ', last_name) as contact_name, contact_id
  FROM contacts
  WHERE (last_name like '%" . $q . "%'
  " . $sql_first_names . ")
  and contact_record_status = 'a'
  LIMIT 25
  ";

  $rst = $con->execute($sql);

  if (($rst)&&(!$rst->EOF)) {
    while(!$rst->EOF) {
      $data .= strtoupper(_("Contact")).": ".$rst->fields['contact_name'];
      $data .= "|".$http_site_root;
      $data .= "/contacts/one.php?contact_id=". $rst->fields['contact_id']. "\n";
      $rst->movenext();
    }
  }
}

// Opportunity TITLE search with company_name
if (preg_match($input_regex,$q) && $opportunity_search) {
  $sql = "SELECT CONCAT(opportunity_title,' (', company_name, ')') AS opp_concat_name, opportunity_id
  FROM opportunities
  inner join companies on opportunities.company_id=companies.company_id
  WHERE opportunity_title like '%" . $q . "%'
  and opportunity_record_status = 'a'
  LIMIT 25
  ";
 
  $rst = $con->execute($sql);

  if (($rst)&&(!$rst->EOF)) {
    while(!$rst->EOF) {
      $data .= strtoupper(_("Opportunity")).": ".$rst->fields['opp_concat_name'];
      $data .= "|".$http_site_root;
      $data .= "/opportunities/one.php?opportunity_id=". $rst->fields['opportunity_id']. "\n";
      $rst->movenext();
    }
  }
}

// Case TITLE search with company_name
if (preg_match($input_regex,$q) && $case_search ) {
  $sql = "SELECT CONCAT(case_title,' (', company_name, ')') AS case_concat_name, case_id
  FROM cases
  inner join companies on cases.company_id=companies.company_id
  WHERE case_title like '%" . $q . "%'
  and case_record_status = 'a'
  LIMIT 25
  ";
 
  $rst = $con->execute($sql);

  if (($rst)&&(!$rst->EOF)) {
    while(!$rst->EOF) {
      $data .= strtoupper(_("Case")).": ".$rst->fields['case_concat_name'];
      $data .= "|".$http_site_root;
      $data .= "/cases/one.php?case_id=". $rst->fields['case_id']. "\n";
      $rst->movenext();
    }
  }
}

// Campaign TITLE search 
if (preg_match($input_regex,$q) && $campaign_search ) {
  $sql = "SELECT campaign_title, campaign_id
  FROM campaigns
  WHERE campaign_title like '%" . $q . "%'
  and campaign_record_status = 'a'
  LIMIT 25
  ";

  $rst = $con->execute($sql);

  if (($rst)&&(!$rst->EOF)) {
    while(!$rst->EOF) {
      $data .= strtoupper(_("Campaign")).": ".$rst->fields['campaign_title'];
      $data .= "|".$http_site_root;
      $data .= "/campaigns/one.php?campaign_id=". $rst->fields['campaign_id']. "\n";
      $rst->movenext();
    }
  }
}


echo $data;

?>
