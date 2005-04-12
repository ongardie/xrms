<?php
// $Id: latex.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

//
//   2002/03/18  Troy D. Straszheim  <troy@resophonic.com>
//

//   These functions take wiki entities like 'bold_begin' or 'ref' and return
//   latex representing these entities.  They are used throught this script
//   to generate appropriate latex.  Together with the template scripts, they
//   constitue the sole generators of latex in this script, and are thus the
//   sole means of customizing appearance.

function latex_bold_start()
  { return '\textbf{'; }
function latex_bold_end()
  { return '}'; }
function latex_italic_start()
  { return '\textit{'; }
function latex_italic_end()
  { return '}'; }
function latex_tt_start()
  { return '\texttt{'; }
function latex_tt_end()
  { return '}'; }
function latex_ul_start()
  { return "\\begin{itemize}\n"; }
function latex_ul_end()
  { return "\\end{itemize}\n"; }
function latex_ol_start()
  { return '\begin{enumerate}'; }
function latex_ol_end()
  { return '\end{enumerate}'; }
function latex_li_start()
  { return '\item '; }
function latex_li_end()
  { return "\n\n"; }

function latex_dl_start()
  { return '\begin{itemize}'; }
function latex_dl_end()
  { return '\end{itemize}'; }
function latex_dd_start()
  { return '\item '; }
function latex_dd_end()
  { return "\n"; }

function latex_hr()
  { return "\vskip0.25cm\hrule\vskip0.25cm"; }
function latex_newline()
  { return "\n"; }
function latex_head_start($level)
  { 
    $lvlmap = array('\chapter', '\section', '\subsection',
	      '\subsubsection', '\paragraph', '\subparagraph');
  
    return $lvlmap[$level] . '{'; 
  }

function latex_head_end($level)
  { return "}\n\n"; }
function sanitize($text) 
  { 
//    $patterns = array ("/([^\\])%/", "/([^\\])#/", "/([^\\])&/");
//    $replacements = array ("$1\%CLEAN", "$1\#CLEAN", "$1\&CLEAN");
//    return preg_replace($patterns, $replacements, $text);
      return "GIMPY:" . $text;
  }
function latex_nowiki($text)
  { 
//  $newtext = sanitize($text);
    return $text; 
  }
function latex_code($text)
  { return '\begin{verbatim}' . $text . '\end{verbatim}'; }
function latex_raw($text)
  { return $text; }

function html_table_start()
  { return '\begin{tabular}{|l|l|l|l|l|l|l|l|l|}'; }
function html_table_end()
  { return '\end{tabular}' . "\n"; }
function html_table_row_start()
  { return ''; }
function html_table_row_end()
  { return '\\'.'\\'."\n"; }
function html_table_cell_start($span = 1)
{
//  if($span == 1)
//    { return '<td>'; }
//  else
//    { return '<td colspan="' . $span . '">'; }

  return '&';    
}
function html_table_cell_end()
  { return '&'; }
//function latex_diff_old_start()
//  { return "<table width=\"95%\" bgcolor=\"ffffaf\"><tr><td>\n"; }
//function latex_diff_end()
//  { return '</td></tr></table>'; }
//function latex_diff_new_start()
//  { return "<table width=\"95%\" bgcolor=\"cfffcf\"><tr><td>\n"; }
//function latex_diff_add()
//  { return latex_bold_start() . 'Added:' . latex_bold_end(); }
//function latex_diff_change()
//  { return latex_bold_start() . 'Changed:' . latex_bold_end(); }
//function latex_diff_delete()
//  { return latex_bold_start() . 'Deleted:' . latex_bold_end(); }

function latex_time($timestamp)
{
  $time = mktime(substr($timestamp, 8, 2),  substr($timestamp, 10, 2),
                 substr($timestamp, 12, 2), substr($timestamp, 4, 2),
                 substr($timestamp, 6, 2),  substr($timestamp, 0, 4));
  return date('D, d M Y H:i:s', $time);
}

function latex_timestamp($timestamp)
{
  return substr($timestamp, 0, 4) . '.' .
         substr($timestamp, 4, 2) . '.' .
         substr($timestamp, 6, 2) . ' ' .
         substr($timestamp, 8, 2) . ':' .
         substr($timestamp, 10, 2);
}

function latex_url($url, $text)
{
  if($url == $text
     && preg_match('/(.jpg|.png|.gif)$/', $text))
  {
    return '\textit{(image: '. $url .')}';
  }
  return '$\underline{\textrm{'. $url . '}}$';
}

function latex_ref($page, $appearance, $hover = '')
{
  global $db;

  if($hover != '')
    { $hover = ' title="' . $hover . '"'; }

  $p = new WikiPage($db, $page);

  if($p->exists())
  {
//    return '<a href="' . viewURL($page) . '"' . $hover . '>' . $page . '</a>';
    return '\textbf{'. $page . '}';
  }
  else
  {
    return '\textbf{' . $appearance . $hover . "}";
  }
}

function latex_interwiki($base, $ref)
{
  global $pagestore;

  if(($url = $pagestore->interwiki($base)) != '')
    { return 'INTERWIKI-W-URL: ' . $url . ' ' . $base . ':' . $ref ; }

  return 'INTERWIKI-NO-URL: ' . $base . ':' . $ref;
}

function latex_twin($base, $ref)
{
  global $pagestore;

//  return '<a href="' . $pagestore->interwiki($base) . $ref . '">' .
//         '<font size="-1"><em>[' . $base . ']</em></font></a>';
}
function latex_category($time, $page, $host, $user, $comment)
{
  global $pagestore;

  $text = '(' . latex_timestamp($time) . ') (' .
          '<a href="' . historyURL($page) . '"><font size="-1">changes</font></a>) ' .
          latex_ref($page, $page);

  if(count($twin = $pagestore->twinpages($page)))
  {
    foreach($twin as $site)
      { $text = $text . ' ' . latex_twin($site[0], $site[1]); }
  }

  $text = $text . ' . . . . . <em>' .
          ($user == '' ? $host : latex_ref($user, $user, $host)) . '</em>';

  if($comment != '')
  {
    $text = $text . ' ' . latex_bold_start() . '[' .
            str_replace('<', '&lt;', str_replace('&', '&amp;', $comment)) .
            ']' . latex_bold_end();
  }

  return $text . latex_newline();
}
function latex_fulllist($page, $count)
{
  return '<strong><a href="' . viewURL($page, '', 1) . '">' .
         PARSE_CompleteListStart . $count . PARSE_CompleteListEnd .
         '</a></strong>';
}
function latex_fullhistory($page, $count)
{
  return '<tr><td colspan="3"><strong><a href="' . historyURL($page, 1) .'">'. 
         PARSE_CompleteListStart . $count . PARSE_CompleteListEnd .
         '</a></strong></td></tr>';
}
function latex_toolbar_top()
{
  global $HomePage, $PrefsScript;
  return latex_ref($HomePage, $HomePage) . ' | ' .
         latex_ref(PARSE_RecentChanges, PARSE_RecentChanges) . ' | ' .
         '<a href="' . $PrefsScript . '">' . PARSE_Preferences . '</a>';
}
function latex_history_entry($page, $version, $time, $host, $user, $c1, $c2,
                             $comment)
{
  return "<tr><td>" .
         "<input type=\"radio\" name=\"ver1\" value=\"$version\"" .
         ($c1 ? ' checked="checked"' : '') . " /></td>\n" .
         "    <td>" .
         "<input type=\"radio\" name=\"ver2\" value=\"$version\"" .
         ($c2 ? ' checked="checked"' : '') . " /></td>\n" .
         "<td><a href=\"" . viewURL($page, $version) . "\">" .
         latex_time($time) . "</a> . . . . " .
         ($user == '' ? $host : latex_ref($user, $user, $host)) .
         ($comment == '' ? '' :
           (' ' . latex_bold_start() . '[' .
            str_replace('<', '&lt;', str_replace('&', '&amp;', $comment)) .
            ']' . latex_bold_end())) .
         "</td></tr>\n";
}
function latex_lock_start()
{
  global $AdminScript;

  return '<form method="post" action="' . $AdminScript . "\">\n" .
         '<input type="hidden" name="locking" value="1" />' . "\n" .
         latex_bold_start() . PARSE_Locked . latex_bold_end() . latex_newline();
}
function latex_lock_end($count)
{
  return '<input type="hidden" name="count" value="' . $count . '" />' . "\n" .
         '<input type="submit" name="Save" value="'.PARSE_ButtonSave.'" />' . 
         "\n" . '</form>' . "\n";
}
function latex_lock_page($page, $mutable)
{
  static $count = 0;
  $count++;
  return '<input type="hidden" name="name' . $count .
         '" value="' . urlencode($page) . '" />' . "\n" .
         '<input type="checkbox" name="lock' . $count . '" value="1"' .
         ($mutable ? '' : ' checked="checked"') . ' />' . "\n" .
         "\n" . $page . latex_newline();
}
function latex_rate_start()
{
  return '<br /><strong>' . PARSE_BlockedRange. "</strong>\n<dl>\n";
}
function latex_rate_end()
{
  global $AdminScript;

  return "</dl>\n" .
         '<form method="post" action="' . $AdminScript . "\">\n" .
         '<input type="hidden" name="blocking" value="1" />' . "\n" .
         PARSE_EnterIpRange . "<br />\n" .
         '<input type="text" name="address" value="" size="40" /><br />' .
         "\n" .
         '<input type="submit" name="Block" value="'. PARSE_ButtonBlock . 
         '" />' . "\n" .
         '<input type="submit" name="Unblock" value="'. PARSE_ButtonUnblock . 
         '" />' . "\n" .
         '</form>' . "\n";
}
function latex_rate_entry($address)
{
  return '<dd>' . $address . "</dd>\n";
}
?>
