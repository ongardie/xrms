<?php

// receives POST's from your web site and imports the company into XRMS, then redirects back to your web site

require_once('../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'utils-accounting.php');

$company_type_id = $_POST['company_type_id'];
$crm_status_id = $_POST['crm_status_id'];
$industry_id = $_POST['industry_id'];
$company_source_id = $_POST['company_source_id'];
//when this is finished, make sure to work out sort_order

header("Location: $after_adding_new_companies_from_your_web_site_redirect_to_this_page");