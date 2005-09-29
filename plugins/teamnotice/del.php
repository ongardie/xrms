<?php
/**
 * Manage list of GroupGroups
 *
 * Copyright (c) 2004 Explorer Fund Advisors, LLC
 * All Rights Reserved.
 *
 * @todo
 * $Id: del.php,v 1.1 2005/09/29 19:35:27 niclowe Exp $
 */

require_once('../../include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

require_once($include_directory . 'classes/Pager/GUP_Pager.php');
require_once($include_directory . 'classes/Pager/Pager_Columns.php');

global $http_site_root;

$session_user_id = session_check();

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);

//$button1=$con->Concat($con->qstr("<input type=\"button\" class=\"button\" value=\""._("Delete")."\" onclick=\"javascript: location.href='del.php?teamnotice_id="), 'teamnotice_id', $con->qstr("'\">")) . ";
$sql="UPDATE teamnotices SET status='d' where teamnotice_id=$teamnotice_id";
//echo $sql;
$con->execute($sql);



?>
