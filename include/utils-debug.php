<?php

if ( !defined('IN_XRMS') )
{
  die('Hacking attempt');
  exit;
}

/* $Id: utils-debug.php,v 1.1 2004/07/21 14:50:12 cpsource Exp $ */

//
// utils-debug.php
//
//  require_once($include_directory . 'utils-debug.php');
//  to be used for debugging.
//
// You have two choices:
//
// A. from adodb (shows sql commands)
//
//    function xrms_utils_backtrace($printOrArr=true,$levels=9999)
//
// B. from www.php.net (shows just php file and line number)
//
//    function xrms_utils_debug()
//

/*
  Perform a stack-crawl and pretty print it.
  
  @param printOrArr  Pass in a boolean to indicate print, or an $exception->trace array (assumes that print is true then).
  @param levels Number of levels to display
*/
// from adodb
function xrms_utils_backtrace($printOrArr=true,$levels=9999)
{
  $s = '';
  if (PHPVERSION() < 4.3) return;
		 
  $html =  (isset($_SERVER['HTTP_USER_AGENT']));
  $fmt =  ($html) ? "</font><font color=#808080 size=-1> %% line %4d, file: <a href=\"file:/%s\">%s</a></font>" : "%% line %4d, file: %s";

  $MAXSTRLEN = 64;
	
  $s = ($html) ? '<pre align=left>' : '';
		
  if (is_array($printOrArr)) $traceArr = $printOrArr;
  else $traceArr = debug_backtrace();
  array_shift($traceArr);
  $tabs = sizeof($traceArr)-1;
		
  foreach ($traceArr as $arr) {
    $levels -= 1;
    if ($levels < 0) break;
			
    $args = array();
    for ($i=0; $i < $tabs; $i++) $s .=  ($html) ? ' &nbsp; ' : "\t";
    $tabs -= 1;
    if ($html) $s .= '<font face="Courier New,Courier">';
    if (isset($arr['class'])) $s .= $arr['class'].'.';
    if (isset($arr['args']))
      foreach($arr['args'] as $v) {
	if (is_null($v)) $args[] = 'null';
	else if (is_array($v)) $args[] = 'Array['.sizeof($v).']';
	else if (is_object($v)) $args[] = 'Object:'.get_class($v);
	else if (is_bool($v)) $args[] = $v ? 'true' : 'false';
	else {
	  $v = (string) @$v;
	  $str = htmlspecialchars(substr($v,0,$MAXSTRLEN));
	  if (strlen($v) > $MAXSTRLEN) $str .= '...';
	  $args[] = $str;
	}
      }
    $s .= $arr['function'].'('.implode(', ',$args).')';
			
			
    $s .= @sprintf($fmt, $arr['line'],$arr['file'],basename($arr['file']));
				
    $s .= "\n";
  }	
  if ($html) $s .= '</pre>';
  if ($printOrArr) print $s;
		
  return $s;
}

// from www.php.net
function xrms_utils_debug()
{
  // skip for non-functional versions of php
  if (PHPVERSION() < 4.3) return;

  // get the backtrace
  $debug_array = debug_backtrace();

  // get number of elements, limit to 9999
  $counter = count($debug_array);
  if ( $counter > 9999 ) {
    $counter = 9999;
  }

  // display all
  for($tmp_counter = 0; $tmp_counter != $counter; ++$tmp_counter)
    {
  ?>
 <table width="558" height="116" border="1" cellpadding="0" cellspacing="0" bordercolor="#000000">
    <tr>
    <td height="38" bgcolor="#D6D7FC"><font color="#000000">function <font color="#FF3300"><?
    echo($debug_array[$tmp_counter]["function"]);?>(</font> <font color="#2020F0"><?
    //count how many args a there
    $args_counter = count($debug_array[$tmp_counter]["args"]);
    //print them
    for($tmp_args_counter = 0; $tmp_args_counter != $args_counter; ++$tmp_args_counter)
    {
      echo($debug_array[$tmp_counter]["args"][$tmp_args_counter]);
      
      if(($tmp_args_counter + 1) != $args_counter)
	{
	  echo(", ");
	}
      else
	{
	  echo(" ");
	}
    }
    ?></font><font color="#FF3300">)</font></font></td>
      </tr>
           <tr>
             <td bgcolor="#5F72FA"><font color="#FFFFFF">{</font><br>
               <font color="#FFFFFF">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;file: <?
               echo($debug_array[$tmp_counter]["file"]);?></font><br>
               <font color="#FFFFFF">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;line: <?
               echo($debug_array[$tmp_counter]["line"]);?></font><br>
               <font color="#FFFFFF">}</font></td>
           </tr>
         </table>
         <?
	 if(($tmp_counter + 1) != $counter)
         {
           echo("<br>was called by:<br>");
         }
       }
  //exit();
}

/**
 * $Log: utils-debug.php,v $
 * Revision 1.1  2004/07/21 14:50:12  cpsource
 * - Define a couple of functions to generate stack
 *   traces useful for debugging.
 *
 */

?>
