<?php
// $Id: save.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

// Macros for on-save features.

require('parse/html.php');
require('parse/macros.php');

// Define the link table.
function parse_define_links($text)
{
  global $pagestore, $page, $ParseEngine, $Entity, $ParseObject;
  static $called = 0;
  $macros_index = -1;
  $transclude_index = -1;
  $elements_index = -1;
  for($i = 0; $i < count($ParseEngine); $i++)
  {
    if($ParseEngine[$i] == 'parse_macros')
      { $macros_index = $i; }
    if($ParseEngine[$i] == 'parse_transclude')
      { $transclude_index = $i; }
    if($ParseEngine[$i] == 'parse_elements')
      { $elements_index = $i; }
  }
  if($macros_index != -1)
    { $ParseEngine[$macros_index] = 'parse_no_macros'; }
  if($transclude_index != -1)
    { $ParseEngine[$transclude_index] = 'parse_noop'; }
  if($elements_index != -1)
    { $ParseEngine[$elements_index] = 'parse_noop'; }

  if(!$called)
  {
    $pagestore->clear_link($page);
    $called = 1;
  }

  $j = count($Entity);
  parseText($text, $ParseEngine, $ParseObject);

  for(; $j < count($Entity); $j++)
  {
    if($Entity[$j][0] == 'ref')
      { $pagestore->new_link($page, $Entity[$j][1]); }
  }

  if($macros_index != -1)
    { $ParseEngine[$macros_index] = 'parse_macros'; }
  if($transclude_index != -1)
    { $ParseEngine[$transclude_index] = 'parse_transclude'; }
  if($elements_index != -1)
    { $ParseEngine[$elements_index] = 'parse_elements'; }

  return $text;
}

// Define interwiki links.
function parse_define_interwiki($text)
{
  global $pagestore, $page;
  static $called = 0;

  if(!$called)
  {
    $pagestore->clear_interwiki($page);
    $called = 1;
  }

  if(preg_match('/^\*InterWiki:\s+\!?([A-Za-z\xc0-\xfe0-9]+)\s+((?:https?|file):[^\s]+)/',
                $text, $result))
  {
    $pagestore->new_interwiki($page, $result[1], $result[2]);
  }
  return $text;
}

// Define sisterwiki links.
function parse_define_sisterwiki($text)
{
  global $pagestore, $page;
  static $called = 0;

  if(!$called)
  {
    $pagestore->clear_sisterwiki($page);
    $called = 1;
  }

  if(preg_match('/^\\*SisterWiki:\\s+\!?([A-Za-z]+)\s+(https?:[^\\s]+)/',
                $text, $result))
  {
    $pagestore->new_sisterwiki($page, $result[1], $result[2]);
  }

  return $text;
}
?>
