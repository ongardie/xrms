<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

// Force Indexing By Name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

// Force Lower-case Keys
$ADODB_ASSOC_CASE = 0;

?>
