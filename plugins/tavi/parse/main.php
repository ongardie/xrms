<?php
// $Id: main.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

// Master parser for 'Tavi.
function parseText($text, $parsers, $object_name)
{
  global $Entity, $ParseObject;

  $old_parse_object = $ParseObject;
  $ParseObject = $object_name;          // So parsers know what they're parsing.

  $count  = count($parsers);
  $result = '';

  $text = parse_elem_flag($text);    // Escape $FlgChr before pre-parsing
  $text = pre_parser($text);  // Fix line continuation/breaks and code-sections 

  // Run each parse element in turn on each line of text.

  foreach(explode("\n", $text) as $line)
  {
    $line = $line . "\n";
    for($i = 0; $i < $count; $i++)
      { $line = $parsers[$i]($line); }

    $result = $result . $line;
  }

  // Some stateful parsers need to perform final processing.

  $line = '';
  for($i = 0; $i < $count; $i++)
    { $line = $parsers[$i]($line); }

  $ParseObject = $old_parse_object;

  return $result . $line;
}

?>
