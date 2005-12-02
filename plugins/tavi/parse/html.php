<?php
// $Id: html.php,v 1.2 2005/12/02 19:40:00 daturaarutad Exp $

// These functions take wiki entities like 'bold_begin' or 'ref' and return
//   HTML representing these entities.  They are used throught this script
//   to generate appropriate HTML.  Together with the template scripts, they
//   constitue the sole generators of HTML in this script, and are thus the
//   sole means of customizing appearance.
function html_bold_start()
  { return '<strong>'; }
function html_bold_end()
  { return '</strong>'; }
function html_italic_start()
  { return '<em>'; }
function html_italic_end()
  { return '</em>'; }
function html_superscript_start()
  { return '<sup>'; }
function html_superscript_end()
  { return '</sup>'; }
function html_subscript_start()
  { return '<sub>'; }
function html_subscript_end()
  { return '</sub>'; }
function html_del_start()
  { return '<del>'; }
function html_del_end()
  { return '</del>'; }
function html_ins_start()
  { return '<ins>'; }
function html_ins_end()
  { return '</ins>'; }
function html_tt_start()
  { return '<tt>'; }
function html_tt_end()
  { return '</tt>'; }
function html_ul_start($attr='')
  { return "<ul $attr>"; }
function html_ul_end()
  { return "</ul>\n"; }
function html_ol_start()
  { return '<ol>'; }
function html_ol_end()
  { return "</ol>\n"; }
function html_li_start()
  { return '<li>'; }
function html_li_end()
  { return "</li>\n"; }
function html_dl_start()
  { return '<dl>'; }
function html_dl_end()
  { return "</dl>\n"; }
function html_dd_start()
  { return '<dd>'; }
function html_dd_end()
  { return "</dd>\n"; }
function html_dt_start()
  { return '<dt>'; }
function html_dt_end()
  { return '</dt>'; }
function html_hr()
  { return "<hr />\n"; }
function html_newline()
  { return "<br />\n"; }
function html_paragraph_start()
  { return "<p>"; }
function html_paragraph_end()
  { return "</p>\n"; }
function html_head_start($level)
  { return "<h$level>"; }
function html_head_end($level)
  { return "</h$level>"; }
function html_nowiki($text)
  { return $text; }
function html_code($text)
  { return '<pre>' . $text . '</pre>'; }
function html_phpcode($text) 
{ 
  ob_start();
  highlight_string($text);
  $text = ob_get_contents();
  ob_end_clean();

  $text = preg_replace("/^<code>/", "<pre class=\"phpsource\">", $text);
  $text = preg_replace("/<\/code>$/", "</pre>", $text);
  
  // Make it valid xhtml...
  $search[0] = '<font color="'.ini_get('highlight.html').'">';
  $search[1] = '<font color="'.ini_get('highlight.default').'">';
  $search[2] = '<font color="'.ini_get('highlight.keyword').'">';
  $search[3] = '<font color="'.ini_get('highlight.string').'">';
  $search[4] = '<font color="'.ini_get('highlight.comment').'">';
  $search[5] = '</font>';
  $search[6] = '\r';
  $search[7] = '<br />';
  $search[8] = '&nbsp;';
  $replace[0] = '<span class="html">';
  $replace[1] = '<span class="default">';
  $replace[2] = '<span class="keyword">';
  $replace[3] = '<span class="string">';
  $replace[4] = '<span class="comment">';
  $replace[5] = '</span>';
  $replace[6] = '';
  $replace[7] = "\n";
  $replace[8] = ' ';
 
  return str_replace($search,$replace,$text);
}
function html_raw($text)
  { return $text; }
function html_anchor($name)
  { return '<a name="' . $name . '"></a>'; }
function html_diff_old_start()
  { return "<table class=\"diff\"><tr><td class=\"diff-removed\">\n"; }
function html_diff_new_start()
  { return "<table class=\"diff\"><tr><td class=\"diff-added\">\n"; }
function html_diff_end()
  { return '</td></tr></table>'; }
function html_diff_add()
  { return html_bold_start() . PARSE_Added . html_bold_end(); }
function html_diff_change()
  { return html_bold_start() . PARSE_Changed . html_bold_end(); }
function html_diff_delete()
  { return html_bold_start() . PARSE_Deleted . html_bold_end(); }
function html_table_start($args)
{
  if ($args != '') {
    $extraStr = '';

    // Split and parse CurlyOptions
    foreach (split_curly_options($args) as $name=>$value) {
    // Only use the Table-options
      if ($name[0]=='T') {
        if ($name[1]=='c') { // TClass - Class of <table>
          $extraStr .= ' class="'. $value .'"';
        } else if ($name[1]=='s') {  // TStyle - Style of <table>
          $extraStr .= ' style="'. $value .'"';
        } else if ($name[1]=='b') { // TBorder - Use border with given width
          if (is_numeric($value)) {
            $extraStr .= ' border="'. $value .'"';
          } else {
            $extraStr .= ' border="1"';
          }
        }
      }
    }

    return "<table$extraStr>";
  } else {
    return '<table border="1">';
  }
}
function html_table_end()
  { return '</table>'; }
function html_table_row_start($args)
{
  if ($args != '') {
    $extraStr = '';

    // Split and parse CurlyOptions
    foreach (split_curly_options($args) as $name=>$value) {
      // Only use the Row-options
      if ($name[0]=='R') {
        if ($name[1]=='c') { // RClass - Class of <row>
          $extraStr .= ' class="'. $value .'"';
        } else if ($name[1]=='s') {  // RStyle - Style of <row>
          $extraStr .= ' style="'. $value .'"';
        }
      }
    }

    return "<tr$extraStr>";
  } else {
    return "<tr>";
  }
}
function html_table_row_end()
  { return '</tr>'; }
function html_table_cell_start($span = 1, $args)
{

  $extraStr = ''; 
  if ($args != '') {
    $styleStr = '';

    // Parse CurlyOptions
    foreach (split_curly_options($args) as $name=>$value) {
      if ($name[0]=='T' or $name[0]=='R') {
        continue; //Was either a row or table option
      }
      if ($name[0]=='w') {
        if (is_numeric($value)) {
          $rowspan = $value;
        } else {
          $rowspan=2;
        }
        $extraStr .= ' rowspan="' . $rowspan .'"';
      } else if ($name[0] == 'l') { $styleStr .= ' text-align: left;';
      } else if ($name[0] == 'c') { $styleStr .= ' text-align: center;';
      } else if ($name[0] == 'r') { $styleStr .= ' text-align: right;';
      } else if ($name[0] == 't') { $styleStr .= ' vertical-align: top;';
      } else if ($name[0] == 'b') { $styleStr .= ' vertical-align: bottom;';
      } else if ($name[0] == 'B') { $styleStr .= ' font-weight: bold;';
      } else if ($name[0] == 'I') { $styleStr .= ' font-style: italic;';
      } else if ($name[0] == 's') { $styleStr .= ' '. $value .';';
      } else if ($name[0] == 'C') { $extraStr .= ' class="'. $value .'"';
      }
    }

    if ($styleStr != "") {
      $extraStr .= ' style="'. $styleStr .'"';
    }
  }

  if($span == 1)
    { return '<td'. $extraStr .'>'; }
  else
    { return '<td colspan="'. $span .'"' .$extraStr. '>'; }
}
function html_table_cell_end()
  { return '</td>'; }
function html_time($timestamp)
{
  global $TimeZoneOff;
  if($timestamp == '') { return PARSE_Never; }
  $time = mktime(substr($timestamp, 8, 2),  substr($timestamp, 10, 2),
                 substr($timestamp, 12, 2), substr($timestamp, 4, 2),
                 substr($timestamp, 6, 2),  substr($timestamp, 0, 4));
  return date('D, d M Y H:i:s', $time + $TimeZoneOff * 60);
}
function html_gmtime($timestamp)
{
  $time = mktime(substr($timestamp, 8, 2),  substr($timestamp, 10, 2),
                 substr($timestamp, 12, 2), substr($timestamp, 4, 2),
                 substr($timestamp, 6, 2),  substr($timestamp, 0, 4));
  return gmdate('Y-m-d', $time) . 'T' . gmdate('H:i:s', $time) . 'Z';
}
function html_timestamp($timestamp)
{
  global $TimeZoneOff;
  $mysqlVer = mysql_get_server_info();
  if (!preg_match("/^4\./", $mysqlVer)) 
  {
    $time = mktime(substr($timestamp, 8, 2),  substr($timestamp, 10, 2),
                   substr($timestamp, 12, 2), substr($timestamp, 4, 2),
                   substr($timestamp, 6, 2),  substr($timestamp, 0, 4));
    return date('Y.m.d H:i:s', $time + $TimeZoneOff * 60);
  }
  else 
  {
    // This need more tuning according to sql_mode: MAXDB-setting, DATETIME vs TIMESTAMP
    return "4: " . $timestamp; // NOT corrected according to $TimeZoneOFf?!
  }
}
function html_url($url, $text)
{
  global $ImgPtn;
  if($url == $text
     && preg_match("/($ImgPtn)$/i", $text))
  {
    return "<img src=\"$url\" alt=\"" . basename($url) . "\" />";
  }
  if (preg_match("/(.*)\?(.*)/", $url, $match)) 
  {
    $match[2] = preg_replace("/&(amp;amp;|!?amp;)/", '&amp;', $match[2]);
    $url = $match[1] . '?'. $match[2];
  }
  return "<a href=\"$url\">$text</a>";
}
function html_ref($page, $appearance, $hover = '', $anchor = '', $anchor_appearance = '')
{
  global $db, $SeparateLinkWords;

  if($hover != '')
    { $hover = ' title="' . $hover . '"'; }

  $p = new WikiPage($db, $page);

  if($p->exists())
  {
    if($SeparateLinkWords && $page == $appearance)
      { $appearance = html_split_name($page); }
    return '<a href="' . viewURL($page) . $anchor . '"' . $hover . '>'
           . $appearance . $anchor_appearance . '</a>';
  }
  else
  {
    if(validate_page($page) == 1        // Normal WikiName
       && $appearance == $page)         // ... and is what it appears
      { return $page . '<a href="' . editURL($page) . '"' . $hover . '>?</a>'; }
    else                                // Free link.
      { return '(' . $appearance . ')<a href="' . editURL($page) . '"' . $hover . '>?</a>'; }
  }
}
function html_interwiki($url, $text)
{
  return '<a href="' . $url . '">' . $text . '</a>';
}
function html_twin($base, $ref)
{
  global $pagestore;

  return '<a href="' . $pagestore->interwiki($base) . $ref . '">' .
         '<span class="twin"><em>[' . $base . ']</em></span></a>';
}
function html_category($time, $page, $host, $user, $comment)
{
  global $pagestore;

  $text = '(' . html_timestamp($time) . ') (' .
          '<a href="' . historyURL($page) . '">' .
          PARSE_History . '</a>) ' .
          html_ref($page, $page);

  if(count($twin = $pagestore->twinpages($page)))
  {
    foreach($twin as $site)
      { $text = $text . ' ' . html_twin($site[0], $site[1]); }
  }

  $text = $text . ' . . . . ' .
          ($user == '' ? $host : html_ref($user, $user, $host));

  if($comment != '')
  {
    $text = $text . ' ' . html_bold_start() . '[' .
            str_replace('<', '&lt;', str_replace('&', '&amp;', $comment)) .
            ']' . html_bold_end();
  }

  return $text;
}
function html_fulllist($page, $count)
{
  return '<strong><a href="' . viewURL($page, '', 1) . '">' .
         PARSE_CompleteListStart . $count . PARSE_CompleteListEnd .
         '</a></strong>';
}
function html_fullhistory($page, $count)
{
  return '<tr><td colspan="3"><strong><a href="' . historyURL($page, 1) . '">'.
         PARSE_CompleteListStart . $count . PARSE_CompleteListEnd .
         '</a></strong></td></tr>';
}
function html_parents_top($path)
{
  $ret = ''; $topDir='';
  foreach (split('/', $path) as $subDir) {
    $ret .= html_ref($topDir . $subDir, $subDir) . '/ ';
    $topDir .= $subDir . '/';
  };
  return $ret;
}
function html_toolbar_top($path)
{
  global $HomePage, $PrefsScript;
  return '<tr><td class=widget_content>' .
         (($path!="")? html_parents_top($path)
	 . '</td></tr><tr><td class=widget_content>' : '') .
         '<a href="' . $PrefsScript . '">'. PARSE_Preferences
         . '</a></td></tr>';
}
function html_history_entry($page, $version, $time, $host, $user, $c1, $c2,
                            $comment)
{
  return "<tr><td>" .
         "<input type=\"radio\" name=\"ver1\" value=\"$version\"" .
         ($c1 ? ' checked="checked"' : '') . " /></td>\n" .
         "    <td>" .
         "<input type=\"radio\" name=\"ver2\" value=\"$version\"" .
         ($c2 ? ' checked="checked"' : '') . " /></td>\n" .
         "<td><a href=\"" . viewURL($page, $version) . "\">" .
         html_time($time) . "</a> . . . . " .
         ($user == '' ? $host : html_ref($user, $user, $host)) .
         ($comment == '' ? '' :
           (' ' . html_bold_start() . '[' .
            str_replace('<', '&lt;', str_replace('&', '&amp;', $comment)) .
            ']' . html_bold_end())) .
         "</td></tr>\n";
}
function html_lock_start()
{
  global $AdminScript;

  return '<form method="post" action="' . $AdminScript . "\">\n" .
         '<div class="form">' . "\n" .
         '<input type="hidden" name="locking" value="1" />' . "\n" .
         html_bold_start() . PARSE_Locked . html_bold_end() . html_newline();
}
function html_lock_end($count)
{
  return '<input type="hidden" name="count" value="' . $count . '" />' . "\n" .
         '<input type="submit" name="Save" value="'. PARSE_ButtonSave .'" />'.
         "\n" . '</div>' . "\n" .
         '</form>' . "\n";
}
function html_lock_page($page, $mutable)
{
  static $count = 0;
  $count++;
  return '<input type="hidden" name="name' . $count .
         '" value="' . urlencode($page) . '" />' . "\n" .
         '<input type="checkbox" name="lock' . $count . '" value="1"' .
         ($mutable ? '' : ' checked="checked"') . ' />' . "\n" .
         "\n" . $page . html_newline();
}
function html_rate_start()
{
  return '<br /><strong>'. PARSE_BlockedRange. "</strong>\n<dl>\n";
}
function html_rate_end()
{
  global $AdminScript;

  return "</dl>\n" .
         '<form method="post" action="' . $AdminScript . "\">\n" .
         '<div class="form">' . "\n" .
         '<input type="hidden" name="blocking" value="1" />' . "\n" .
         PARSE_EnterIpRange . "<br />\n" . 
         '<input type="text" name="address" value="" size="40" /><br />' .
         "\n" .
         '<input type="submit" name="Block" value="'.PARSE_ButtonBlock.'" />'.
         "\n" .
         '<input type="submit" name="Unblock" value="'. PARSE_ButtonUnblock.
         '" />' . "\n" .
         '</div>' . "\n";
         '</form>' . "\n";
}
function html_rate_entry($address)
{
  return '<dd>' . $address . "</dd>\n";
}

// This function splits up a traditional WikiName so that individual
// words are separated by spaces.

function html_split_name($page)
{
  global $UpperPtn, $LowerPtn;

  if(validate_page($page) != 1)
    { return $page; }
  $page = preg_replace("/(?<=$UpperPtn|$LowerPtn)($UpperPtn$LowerPtn)/",
                       ' \\1', $page, -1);
  $page = preg_replace("/($LowerPtn)($UpperPtn)/",
                       '\\1 \\2', $page, -1);
  return $page;
}
function html_reflist() 
{
  global $RefList;
  if (!empty($RefList)) {
    $str = '<hr style="width:30%; margin-bottom: 0px; float:left" />'; 
    $str .= '<ol style="font-size: 80%; clear:left; line-height:1" class="reflist" style="font-size:80%">'; 
    foreach ($RefList as $ref) {
      $str .=  '<li><a href="'. $ref. '">'. $ref .'</a></li>';
    }
    $str .= '</ol>';
    return $str;
  }
  else 
    { return ""; }

}

function html_captcha($phrase, $formTxt) 
{
  // Input is ascii art images, which needs to be combined
  // horisontally. Easiest way is by using a table, and so it is...
  $output = "<table class=\"codephr\">\n  <tr>\n";
  $output .= "    <td class=\"codehdr\">$formTxt</td>\n";
  $output .= "    <td class=\"codephr\"><pre>$phrase</pre></td>";
  $output .= "  </tr>\n</table>\n";
  
  return $output;
}
?>
