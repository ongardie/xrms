<?php

require_once('include-locations.inc');

require_once($include_directory . 'vars.php');

session_start();
session_unset();
session_destroy();

header("Location: {$http_site_root}/login.php");

?>
