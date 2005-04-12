<?php
// $Id: tavidoc.php,v 1.1 2005/04/12 20:45:13 gpowers Exp $

?><html>
 <head>
  <title>TaviDoc</title>	
 </head>
 <body><?php

//////////////////////////////////////////////////////////////////////////
//
//	S t a t i c
//

// Required for database connectivity
require_once('config.php');

// Required for rendering
require_once('lib/defaults.php');
require_once('parse/main.php');
require_once('parse/html.php');
require_once('parse/transforms.php');

// TaviDoc defaults
static	$TDOutputPath		= 'tavidoc/';
static	$TDTemplateFile		= 'tavidoc.template.html';
$TDPageQueue			= array();

// Tavi defaults
static 	$CustomParseEngine 	= array('parse_elem_flag',
					'parse_raw_html',
					'custom_parse_hyperlink_description',
					'parse_hyperlink',
					'custom_parse_freelink',
					'parse_bold',
					'parse_italic',
					'parse_teletype',
					'parse_heading',
					'parse_table',
					'parse_horiz',
					'parse_indents',
					'parse_newline',
					'parse_elements');
$DisplayEngine['hr']		= 'custom_display_html_hr';
$DisplayEngine['ref']		= 'custom_display_html_ref';
$FlgChr						= chr(255);

//////////////////////////////////////////////////////////////////////////
//
//	M a i n l i n e
//

// Report
echo '<h1>TaviDoc</h1>';

// Check if the user has properly set a configuration page name
parse_str($QUERY_STRING);
if (!isset($page))
  die('No page specified');

// Load the template
$TDTemplate = join('', file($TDTemplateFile));

// Open the database
mysql_connect($DBServer, $DBUser, $DBPasswd);
mysql_select_db($DBName);

// Report
echo 'Processing configuration page <a href="',
  "index.php?page=$page", '">', $page, "</a>.<br />\n";
echo 'Using template <a href="', $TDTemplateFile, '">',
  $TDTemplateFile, "</a>.<br />\n";
echo 'Output into path <a href="', $TDOutputPath, '">',
  $TDOutputPath, "</a>.<br />\n",
  '<ol>';

$TDPageQueue[] = $page;
$i = 0;
while ($i < count($TDPageQueue))
{
  $Title	= $TDPageQueue[$i];
  $Messages	= array();
  $PageCount	= count($TDPageQueue);

  // Render using Tavi
  $Entity	= array();
  $body		= parseText(query_page_body($Title),
			    $CustomParseEngine, '');

  // Macros
  $body		= str_replace(array('[TITLE]',
				    '[BODY]',
				    '[TIMESTAMP]',
				    '[YEAR]'),
			      array($Title,
				    $body,
				    date('w, F jS Y, G:i'),
				    date('Y')),
			      $TDTemplate);

  // Write file
  $filename	= $TDOutputPath.filename($Title);
  $fid 		= fopen($filename, 'w');
  fwrite($fid, $body);
  fclose($fid);

  // Report
  $dif = (count($TDPageQueue) - $PageCount);
  echo '<li>',
    'Writing page <a href="index.php?page=', $Title, '">', $Title, '</a>',
    ' to file <a href="'.$filename.'">', $filename, '</a>',
    ($dif > 0 ? ", $dif new pages found" : '');

  if (count($Messages) > 0)
  {
    echo '<ol>';
    foreach ($Messages as $Message)
      echo '<li>'.$Message.'</li>';
    echo '</ol>';
  }

  echo '</li>';
  flush();
  ++$i;
}

// Close the database
mysql_close();

echo "</ol>\n", "Succesfully completed.\n";

//////////////////////////////////////////////////////////////////////////
//
//	F u n c t i o n s
//

//------------------------------------------------------------------------
// Create the filename from a page title
function filename($Title)
{
  return strtolower(urlencode(str_replace(' ', '_', $Title))).'.html';
}

//------------------------------------------------------------------------
// Die with a nice error
function query_error($sql)
{
  die('<big>MySQL Error '.mysql_errno().': '.mysql_error()
    .'</big><br /><code>'.nl2br($sql).'</code>');
}

//------------------------------------------------------------------------
// Extract the body from a page
function query_page_body($Title)
{
  global $DBTablePrefix;

  $Title = mysql_escape_string($Title);

  $sql	= 'select body
	   from '.$DBTablePrefix."pages
	   where title = '$Title'
	   order by version desc";
  $qid	= mysql_query($sql)	or query_error($sql);
  $row	= mysql_fetch_row($qid)	or query_error($sql);
  mysql_free_result($qid)	or query_error($sql);

  return $row[0];
}

//------------------------------------------------------------------------
// Query the Wiki whether a page exists with given title
function query_page_exists($Title)
{
  global $DBTablePrefix;

  $Title = mysql_escape_string($Title);

  $sql	= 'select count(*)
	   from '.$DBTablePrefix."pages
	   where title = '$Title'";
  $qid	= mysql_query($sql)	or query_error($sql);
  $row	= mysql_fetch_row($qid) or query_error($sql);
  mysql_free_result($qid)	or query_error($sql);

  return($row[0] > 0? TRUE : FALSE);
}

//------------------------------------------------------------------------
// Custom renderer for horizontal rulers
function custom_display_html_hr()
{
  return "<hr noshade size=\"2\" />\n";
}

//------------------------------------------------------------------------
// Custom parser which strips of the braces from links
function custom_parse_hyperlink_description($text)
{
  global $UrlPtn;

  return preg_replace("/\\[($UrlPtn) ([^]]+)]/e",
    "url_token(q1('\\1'), q1('\\4'))", $text, -1);
}

//------------------------------------------------------------------------
// Custom display engine which nullifies non-existing pages
function custom_display_html_ref($page, $appearance, $hover = '',
				 $anchor = '', $anchor_appearance = '')
{
  global $SeparateLinkWords, $TDPageQueue, $Messages;

  if ($hover != '')
    $hover = ' title="' . $hover . '"';

  if (query_page_exists($page)) {
    $found = FALSE;
    foreach($TDPageQueue as $t)
      if ($t == $page)
	$found = TRUE;

    if (!$found)
      $TDPageQueue[] = $page;

    if ($SeparateLinkWords && $page == $appearance)
      $appearance = html_split_name($page);

    return '<a href="'.viewURL($page).$anchor.'"'.$hover.'>'
      .$appearance.$anchor_appearance.'</a>';
  } else {
    $Messages[] = 'Page <a href="index.php?action=edit&page='.$page.'">'
      .$page.'</a> missing from Wiki.';

    return $appearance;
  }
}

//------------------------------------------------------------------------
// Custom parser which strips of the braces from links
function custom_parse_freelink($text)
{
  return preg_replace(
    "/\\(\\(([-A-Za-z0-9 _+\\/.,;:!?'\"\\[\\]\\{\\}&\xc0-\xff]+)()()\\)\\)/e",
    "freelink_token(q1('\\1'), q1('\\2'), '\\3', '')",
    $text, -1);
}

//------------------------------------------------------------------------
// Overwrite the normal viewURL code to return the filename
function viewURL($page, $version = '', $full = '')
{
  return filename($page);
}

?> </body>
</html>
