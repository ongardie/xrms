<?php
// $Id: latex.php,v 1.1 2005/04/12 20:45:09 gpowers Exp $

//
// 2002/03/18  Troy D. Straszheim  <troy@resophonic.com>
//
require('parse/main.php');
require('parse/macros.php');
require('parse/latex.php');
require(TemplateDir . '/latex.php');
require('lib/headers.php');

function backslashit($text) 
  { 
    $patterns = array ("/&lt;/", "/%/", "/#/", "/&/", "/>/", "/&amp;/");
    $replacements = array ("$<$", "\%", "\#", "\&", "$>$", "\&");
    return preg_replace($patterns, $replacements, $text);
  }

// Parse and display a page.
function action_latex()
{
  global $page, $pagestore, $ParseEngine, $DisplayEngine, $HTTP_IF_MODIFIED_SINCE;
  global $version;

  $pg = $pagestore->page($page);
  if($version != '')
    { $pg->version = $version; }
  $pg->read();

//  if(!empty($HTTP_IF_MODIFIED_SINCE))
//    { if_modified($pg->time); }
//  gen_headers($pg->time);

// $pg->text is the raw stuff from the database

//  print $pg->text;

// $DisplayEngine indicates what functions will be used to translate wiki
//   markup elements into actual HTML.  See parse/html.php

$DisplayEngine = array(
                   'bold_start'   => 'latex_bold_start',
                   'bold_end'     => 'latex_bold_end',
                   'italic_start' => 'latex_italic_start',
                   'italic_end'   => 'latex_italic_end',
                   'tt_start'     => 'latex_tt_start',
                   'tt_end'       => 'latex_tt_end',
                   'head_start'   => 'latex_head_start',
                   'head_end'     => 'latex_head_end',
                   'newline'      => 'latex_newline',
                   'ref'          => 'latex_ref',
                   'url'          => 'latex_url',
                   'interwiki'    => 'latex_interwiki',
                   'raw'          => 'latex_raw',
                   'code'         => 'latex_code',
                   'hr'           => 'latex_hr',
                   'nowiki'       => 'latex_nowiki',
                   'bullet_list_start'   => 'latex_ul_start',
                   'bullet_list_end'     => 'latex_ul_end',
                   'bullet_item_start'   => 'latex_li_start',
                   'bullet_item_end'     => 'latex_li_end',
                   'indent_list_start'   => 'latex_dl_start',
                   'indent_list_end'     => 'latex_dl_end',
                   'indent_item_start'   => 'latex_dd_start',
                   'indent_item_end'     => 'latex_dd_end',
                   'numbered_list_start' => 'latex_ol_start',
                   'numbered_list_end'   => 'latex_ol_end',
                   'numbered_item_start' => 'latex_li_start',
                   'numbered_item_end'   => 'latex_li_end',
                   'diff_old_start'      => 'latex_diff_old_start',
                   'diff_old_end'        => 'latex_diff_end',
                   'diff_new_start'      => 'latex_diff_new_start',
                   'diff_new_end'        => 'latex_diff_end',
                   'diff_change'         => 'latex_diff_change',
                   'diff_add'            => 'latex_diff_add',
                   'diff_delete'         => 'latex_diff_delete'
                 );

  $rawtext = $pg->text;
  $parseText = parseText($rawtext, $ParseEngine, "OBJECTNAMEHERE");
  $newtext = backslashit($parseText);
  template_view($page, $newtext);
}
?>
