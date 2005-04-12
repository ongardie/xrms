<?php
// $Id: style.php,v 1.1 2005/04/12 20:45:10 gpowers Exp $

// This function emits the current template's stylesheet.

function action_style()
{
  header("Content-type: text/css");

  ob_start();

  require(TemplateDir . '/wiki.css');

  $size = ob_get_length();
  header("Content-Length: $size");
  ob_end_flush();
}
?>
