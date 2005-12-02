<?php
// $Id: view.php,v 1.2 2005/12/02 19:40:00 daturaarutad Exp $

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
                      'html'      => $pg->text,
                      'editable'  => $pg->mutable,
                      'timestamp' => $pg->time,
                      'archive'   => $version != '',
                      'version'   => $pg->version));
}
?>
