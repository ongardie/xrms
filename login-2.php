<?php

require_once('include-locations.inc');

require_once($include_directory . 'vars.php');
require_once($include_directory . 'utils-interface.php');
require_once($include_directory . 'utils-misc.php');
require_once($include_directory . 'adodb/adodb.inc.php');

$username = $_POST['username'];
$password = $_POST['password'];
$target   = $_POST['target'];
    if ($target== '') {
        $target=$http_site_root.'/private/home.php';
    }

$password = md5($password);

$con = &adonewconnection($xrms_db_dbtype);
$con->connect($xrms_db_server, $xrms_db_username, $xrms_db_password, $xrms_db_dbname);
// $con->debug = 1;

$sql = "select * from users where username = " . $con->qstr($username, get_magic_quotes_gpc()) . " AND password = " . $con->qstr($password, get_magic_quotes_gpc()) . " AND user_record_status = 'a'";

$rst = $con->execute($sql);

if ($rst && !$rst->EOF) {
    $session_user_id = $rst->fields['user_id'];
    $user_type_id = $rst->fields['user_type_id'];
    $username = $rst->fields['username'];
    $language = $rst->fields['language'];
    $gmt_offset = $rst->fields['gmt_offset'];
    $rst->close();
    session_start();
    $_SESSION['session_user_id'] = $session_user_id;
    $_SESSION['xrms_system_id'] = $xrms_system_id;
    $_SESSION['user_type_id'] = $user_type_id;
    $_SESSION['username'] = $username;
    $_SESSION['language'] = $language;
    $_SESSION['gmt_offset'] = $gmt_offset;
    header("Location: $target");
} else {
    header("Location: $http_site_root/login.php?msg=noauth");
}

?>