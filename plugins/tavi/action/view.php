<?php
// $Id: view.php,v 1.1 2005/04/12 20:45:10 gpowers Exp $

require('parse/main.php');
require('parse/macros.php');
require('parse/html.php');
require(TemplateDir . '/view.php');
require('lib/headers.php');

// Parse and display a page.
function action_view()
{
  global $page, $pagestore, $ParseEngine, $version;

  $pg = $pagestore->page($page);
  if($version != '')
    { $pg->version = $version; }
  $pg->read();

  gen_headers($pg->time);

  template_view(array('page'      => $page,
                      'html'      => parseText($pg->text, $ParseEngine, $page),
                      'editable'  => $pg->mutable,
                      'timestamp' => $pg->time,
                      'archive'   => $version != '',
                      'version'   => $pg->version));
}
?>
