<?php

//
// interface functions
//

function start_page($page_title = '', $show_navbar = true, $msg = '') {
    
    global $page_title_height;
    global $http_site_root;
    $session_username = $_SESSION['username'];
    $msg = translate_msg($msg);
	$stylesheet = "'$http_site_root/stylesheet.css'";
    
    echo <<<EOQ
<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'>
<html>
<head>
<title>$page_title</title>
<link rel=stylesheet href=$stylesheet>
</head>
<body>
<table class=page_header cellspacing=1 width='100%'>
	<tr>
		<td class=page_title height=$page_title_height valign=bottom colspan=3>$page_title</td>
	</tr>
EOQ;

if ($show_navbar) echo <<<EOQ

	<tr>
        <td>
        <table class=navbar cellspacing=0 width='100%'>
		<td class=navbar width='80%'>
			<a href="$http_site_root/private/home.php">Home</a> &bull; 
			<a href="$http_site_root/companies/some.php">Companies</a> &bull; 
			<a href="$http_site_root/contacts/some.php">Contacts</a> &bull; 
			<a href="$http_site_root/campaigns/some.php">Campaigns</a> &bull; 
			<a href="$http_site_root/opportunities/some.php">Opportunities</a> &bull; 
			<a href="$http_site_root/cases/some.php">Cases</a> &bull; 
			<a href="$http_site_root/reports/">Reports</a> &bull; 
			<a href="$http_site_root/admin/">Administration</a> 
		</td>
		<td class=navbar align=center>&nbsp;</td>
		<td class=navbar align=right>
			Logged in as: $session_username &bull; <a href="$http_site_root/logout.php">Logout</a>
		</td>
        </table>
        </td>
	</tr>
</table>

EOQ;

if (strlen($msg) > 0) echo <<<EOQ
<center><table class=msg border=0 width='80%'><tr><td class=msg>{$msg}</td></tr></table></center>
EOQ;

}

function end_page() {

echo <<<EOQ

</body>
</html>
EOQ;
}

?>