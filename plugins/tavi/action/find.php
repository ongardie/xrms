<?php
// $Id: find.php,v 1.1 2005/04/12 20:45:09 gpowers Exp $

require('parse/html.php');
require(TemplateDir . '/find.php');

// Find a string in the database.
function action_find()
{
  global $pagestore, $find, $style, $SeparateLinkWords;

  $list = $pagestore->find($find);

  switch ($style) {
    case 'meta':
      $SeparateLinkWords = 0;
      break;
  }

  $text = '';
  foreach($list as $page)
    { $text = $text . html_ref($page, $page) . html_newline(); }

  template_find(array('find'  => $find,
                      'pages' => $text));
}
?>
