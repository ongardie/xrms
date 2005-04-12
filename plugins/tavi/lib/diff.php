<?php
// $Id: diff.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

// Compute the difference between two sets of text.
function diff_compute($text1, $text2)
{
  global $TempDir, $DiffCmd;

  $num = function_exists('posix_getpid') ? posix_getpid() : rand();

  $temp1 = $TempDir . '/wiki_' . $num . '_1.txt';
  $temp2 = $TempDir . '/wiki_' . $num . '_2.txt';

  if(!($h1 = fopen($temp1, 'w')) || !($h2 = fopen($temp2, 'w')))
    { die(LIB_ErrorCreatingTemp); }

  if(fwrite($h1, $text1) < 0 || fwrite($h2, $text2) < 0)
    { die(LIB_ErrorWritingTemp); }

  fclose($h1);
  fclose($h2);

  if (ini_get('safe_mode') and
     (ini_get('safe_mode_exec_dir') != dirname($DiffCmd))) 
    { $diff = LIB_NoDiffAvailableSafeMode; }
  else if (!file_exists($DiffCmd) or !is_readable($DiffCmd)) 
    { $diff = LIB_NoDiffAvailable; }
  else {
    $output = array();
    exec("$DiffCmd $temp1 $temp2", $output);
    $diff = join("\n", $output);
  }

  unlink($temp1);
  unlink($temp2);

  return $diff;
}

// Parse diff output into nice HTML.
function diff_parse($text)
{
  global $DiffEngine;

  return parseText($text, $DiffEngine, '');
}

?>