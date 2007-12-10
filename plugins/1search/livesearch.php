<?php

// Copyright 2007 Glenn Powers <glenn@net127.com>

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');
require_once($include_directory . 'adodb-params.php');
require_once($include_directory . 'adodb/tohtml.inc.php');

$session_user_id = session_check();
$q = mysql_real_escape_string($_GET['q']);
$q = preg_replace('/^(\s*)(.*)/','\2',$q);
// $q = preg_replace('/[\s*]$/','',$q);

header('Content-Type: text/xml');

$con = get_xrms_dbconnection();
//$con->debug = 1;

$data = "<ul class=\"LSRes\">";

$sql = "select * from users where user_id = '" . $session_user_id . "'";
$rst = $con->execute($sql);
$user_last_name = $rst->fields['last_name'];
$user_first_names = $rst->fields['first_names'];

if ((preg_match('/hello/',$q)) || (preg_match('/help/',$q))) {
    $data .= "
	<li class=\"LSRow\">
Hello, " . $user_first_names .". My Name is Alex. I'll be your search agent today.
        </li>
	<li class=\"LSRow\">
I'm still learning,
but I understand contact names and phone numbers, company names and phone numbers and legal names.
I can also lookup phone numbers in XRMS.
        </li>
";

}

if (preg_match('/www./',$q)) {
    $data .= "
        <li class=\"LSRow\">
<a href=\"http://" . $q . "\" target=\"_new\">" . $q . "</a>
</li>
";
}

if (preg_match('/google/',$q)) {
    $data .= "
        <li class=\"LSRow\">
I love <a href=\"http://google.com\" target=\"_new\">Google</a>.
</li>
";
}

if (preg_match('/lets play/',$q)) {
    $data .= "
        <li class=\"LSRow\">
Wouldn't you prefer a good game of chess?
</li>
";
}

if (preg_match('/glenn powers/',$q)) {
    $data .= "
        <li class=\"LSRow\">
Glenn Powers is my creator.
</li>
";
}

if (preg_match('/why/',$q)) {
    $data .= "
        <li class=\"LSRow\">
I don't know why. I guess that's just the way it is.
</li>
";
}

if (preg_match('/who am i/',$q)) {
    $data .= "
        <li class=\"LSRow\">
You are " . $user_first_names . " " . $user_last_name . ", user id#" . $session_user_id . "
</li>
";
}

if (preg_match('/who are you/',$q)) {
    $data .= "
        <li class=\"LSRow\">
I am Alex.
</li>
";
}

if (preg_match('/love/',$q)) {
    $data .= "
        <li class=\"LSRow\">
Machines need love, too.
</li>
";
}

if (preg_match('/sex/',$q)) {
    $data .= "
        <li class=\"LSRow\">
" . $user_first_names . ", " . $user_first_names . ", " . $user_first_names . ", searching for sex during work? What <a href=\"http://nerve.com\">nerve</a>!
</li>
";
}

if (preg_match('/where is /',$q)) {
$bq = preg_replace('/where is /', '', $q);
    $data .= "
        <li class=\"LSRow\">
Try <a href=\"http://maps.google.com/maps?q=" . urlencode($bq) . "\" target=\"_new\">Google Maps</a>.
</li>
";
}

// COMPANY ID search
if (preg_match('/\d+/',$q)) {
$sql = "SELECT company_name, company_id
FROM companies
WHERE company_id = '" . $q . "'
and company_record_status = 'a'
";

    $rst = $con->execute($sql);

    if (($rst)&&(!$rst->EOF)) {
        $data .= "
	<li class=\"LSRow\">
COPMANY: <a href=\"" . $http_site_root . "/companies/one.php?company_id=" . $rst->fields['company_id'] . "\">" . $rst->fields['company_name'] . "</a>
</li>
";
    }
}

// CONTACT ID search
if (preg_match('/\d+/',$q)) {
$dq = preg_replace('/[^\d*](\d+)[^\d*]/','\1',$q);
$sql = "SELECT CONCAT(first_names, ' ', last_name) as contact_name, contact_id
FROM contacts
WHERE contact_id = '" . $dq . "'
AND contact_record_status = 'a'
";

    $rst = $con->execute($sql);

    if (($rst)&&(!$rst->EOF)) {
        $data .= "
	<li class=\"LSRow\">
CONTACT: <a href=\"" . $http_site_root . "/contacts/one.php?contact_id=" . $rst->fields['contact_id'] . "\">" . $rst->fields['contact_name'] . "</a>
</li>
";
    }
}

// TAX ID seatch
if (preg_match('/^\d\d\d\-\d\d\-\d\d\d\d$/',$q)) {
$sql = "SELECT CONCAT(first_names, ' ', last_name) as contact_name, contact_id
FROM contacts
WHERE tax_id = '" . $q . "'
and contact_record_status = 'a'
";

    $rst = $con->execute($sql);

    if (($rst)&&(!$rst->EOF)) {
        $data .= "
	<li class=\"LSRow\">
TAX ID: <a href=\"" . $http_site_root . "/contacts/one.php?contact_id=" .  $rst->fields['contact_id'] . "\">" . $rst->fields['contact_name'] . "</a>
</li>
";
    }
}

// COMPANY NAME and LEGAL NAME search
if (preg_match('/^\w+$/',$q)) {
$sql = "SELECT company_name, company_id
FROM companies
WHERE (company_name like '%" . $q . "%'
OR legal_name like '%" . $q . "%')
AND company_record_status = 'a'
";

    $rst = $con->execute($sql);

    if (($rst)&&(!$rst->EOF)) {
        $data .= "
	<li class=\"LSRow\">
COMPANY: <a href=\"" . $http_site_root . "/companies/one.php?company_id=" . $rst->fields['company_id'] . "\">" . $rst->fields['company_name'] . "</a>
</li>
";
    }
}

// CONTACT NAME seatch
if (preg_match('/^\w+$/',$q)) {
$sql = "SELECT CONCAT(first_names, ' ', last_name) as contact_name, contact_id
FROM contacts
WHERE last_name like '%" . $q . "%'
and contact_record_status = 'a'
LIMIT 25
";

    $rst = $con->execute($sql);

    if (($rst)&&(!$rst->EOF)) {
        while(!$rst->EOF) {
            $data .= "
	<li class=\"LSRow\">
CONTACT: <a href=\"" . $http_site_root . "/contacts/one.php?contact_id=" .  $rst->fields['contact_id'] . "\">" . $rst->fields['contact_name'] . "</a>
</li>
";
            $rst->movenext();
        }
    }
}

// COMPANY PHONE NUMBER search
if ((preg_match('/\d\d\d\d\d\d\d\d\d\d/',$q)) ||
(preg_match('/\d\d\d\-\d\d\d\-\d\d\d\d/',$q)) ||
(preg_match('/\d\d\d \d\d\d \d\d\d\d/',$q)) ||
(preg_match('/\(\d\d\d\) \d\d\d\-\d\d\d\d/',$q)) ||
(preg_match('/\(\d\d\d\)\d\d\d\-\d\d\d\d/',$q))) {
$nsq = preg_replace("/[^\d]/", '', $q);
$sql = "SELECT company_name, company_id
FROM companies
WHERE phone like '" . $nsq . "%'
OR  phone2 like '" . $nsq . "%'
OR  fax like '" . $nsq . "%')
and contact_record_status = 'a'
LIMIT 10
";

    $rst = $con->execute($sql);

    if (($rst)&&(!$rst->EOF)) {
        while(!$rst->EOF) {
            $data .= "
	<li class=\"LSRow\">
COMPANY: <a href=\"" . $http_site_root . "/companies/one.php?company_id=" . $rst->fields['company_id'] . "\">" . $rst->fields['company_name'] . "</a>
</li>
";
            $rst->movenext();
        }
    }
}

// CONTACT PHONE NUMBER search
if ((preg_match('/\d\d\d\d\d\d\d\d\d\d/',$q)) ||
(preg_match('/\d\d\d\-\d\d\d\-\d\d\d\d/',$q)) ||
(preg_match('/\d\d\d \d\d\d \d\d\d\d$/',$q)) ||
(preg_match('/\(\d\d\d\) \d\d\d\-\d\d\d\d/',$q)) ||
(preg_match('/\(\d\d\d\)\d\d\d\-\d\d\d\d/',$q))) {
$nsq = preg_replace("/[^\d]/", '', $q);
$sql = "SELECT CONCAT(first_names, ' ', last_name) as contact_name, contact_id
FROM contacts
WHERE (work_phone like '" . $nsq . "%'
OR  cell_phone like '" . $nsq . "%'
OR  home_phone like '" . $nsq . "%'
OR  fax like '" . $nsq . "%')
and contact_record_status = 'a'
LIMIT 10
";

    $rst = $con->execute($sql);

    if (($rst)&&(!$rst->EOF)) {
        while(!$rst->EOF) {
            $data .= "
	<li class=\"LSRow\">
CONTACT: <a href=\"" . $http_site_root . "/contacts/one.php?contact_id=" .  $rst->fields['contact_id'] . "\">" . $rst->fields['contact_name'] . "</a>
</li>
";
            $rst->movenext();
        }
    }
}

// WHERE AM I
if (preg_match('/where am i/',$q)) {

require_once "Net/GeoIP.php";
        
        $geoip = Net_GeoIP::getInstance("/usr/local/share/GeoIP/GeoIPCity.dat");
        $location = $geoip->lookupLocation($_SERVER['REMOTE_ADDR']);

            $data .= '
        <li class="LSRow"> ' . _("You are in") . ' ' . '
<a href="http://maps.google.com/maps?f=q&hl=en&q=' . $location->latitude . '+' . $location->longitude .
    '&ie=UTF8&spn=0.700835,1.73584&t=h" target="_new">' . $location->city . ", " . $location->region . '</a>       
</li>
';
            
}

// DATE
if (preg_match('/date/',$q)) {

            $data .= "
        <li class=\"LSRow\">
Today is " . date('l, \t\h\e dS \of F Y') . ".
</li>
";
}

// TIME
if (preg_match('/time/',$q)) {

            $data .= "
        <li class=\"LSRow\">
The time is " . date('g:i a (T)') . ".
</li>
";
}

if ($data == "<ul class=\"LSRes\">") {
    $data .= "
        <li class=\"LSRow\">
No records about " . $q . " found.
        </li>
";
}

$data .= "</ul>";

echo $data;

?>