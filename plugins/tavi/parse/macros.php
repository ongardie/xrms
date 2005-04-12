<?php
// $Id: macros.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

// Prepare a category list.
function view_macro_category($args)
{
  global $pagestore, $MinEntries, $DayLimit, $full, $page, $Entity;
  global $FlgChr;

  $text = '';
  if(strstr($args, '*'))                // Category containing all pages.
  {
    $list = $pagestore->allpages();
  }
  else if(strstr($args, '?'))           // New pages.
  {
    $list = $pagestore->newpages();
  }
  else if(strstr($args, '~'))           // Zero-length (deleted) pages.
  {
    $list = $pagestore->emptypages();
  }
  else                                  // Ordinary list of pages.
  {
    $parsed = parseText($args, array('parse_freelink', 'parse_wikiname'), '');
    $pagenames = array();
    preg_replace('/' . $FlgChr . '!?(\\d+)' . $FlgChr . '/e', '$pagenames[]=$Entity[\\1][1]', $parsed);
    $list = $pagestore->givenpages($pagenames);
  }

  if(count($list) == 0)
    { return ''; }

  usort($list, 'catSort');

  $now = time();
 
  for($i = 0; $i < count($list); $i++)
  {
    $editTime = mktime(substr($list[$i][0], 8, 2),  substr($list[$i][0], 10, 2),
                       substr($list[$i][0], 12, 2), substr($list[$i][0], 4, 2),
                       substr($list[$i][0], 6, 2),  substr($list[$i][0], 0, 4));
    if($DayLimit && $i >= $MinEntries
       && !$full && ($now - $editTime) > $DayLimit * 24 * 60 * 60)
      { break; }

    $text = $text . html_category($list[$i][0], $list[$i][1],
                                  $list[$i][2], $list[$i][3],
                                  $list[$i][5]);
    if($i < count($list) - 1)           // Don't put a newline on the last one.
      { $text = $text . html_newline(); }
  }

  if($i < count($list))
    { $text = $text . html_fulllist($page, count($list)); }

  return $text;
}

function catSort($p1, $p2)
  { return strcmp($p2[0], $p1[0]); }

function sizeSort($p1, $p2)
  { return $p2[4] - $p1[4]; }

function nameSort($p1, $p2)
  { return strcmp($p1[1], $p2[1]); }

// Prepare a list of pages sorted by size.
function view_macro_pagesize()
{
  global $pagestore;

  $first = 1;
  $list = $pagestore->allpages();

  usort($list, 'sizeSort');

  $text = '';

  foreach($list as $page)
  {
    if(!$first)                         // Don't prepend newline to first one.
      { $text = $text . "\n"; }
    else
      { $first = 0; }

    $text = $text .
            $page[4] . ' ' . html_ref($page[1], $page[1]);
  }

  return html_code($text);
}

// Prepare a list of pages and those pages they link to.
function view_macro_linktab()
{
  global $pagestore, $LkTbl;

  $lastpage = '';
  $text = '';

  $q1 = $pagestore->dbh->query("SELECT page, link FROM $LkTbl ORDER BY page");
  while(($result = $pagestore->dbh->result($q1)))
  {
    if($lastpage != $result[0])
    {
      if($lastpage != '')
        { $text = $text . "\n"; }

      $text = $text . html_ref($result[0], $result[0]) . ' |';
      $lastpage = $result[0];
    }

    $text = $text . ' ' . html_ref($result[1], $result[1]);
  }

  return html_code($text);
}

// Prepare a list of pages with no incoming links.
function view_macro_orphans()
{
  global $pagestore, $LkTbl;

  $text = '';
  $first = 1;

  $pages = $pagestore->allpages();
  usort($pages, 'nameSort');

  foreach($pages as $page)
  {
    $esc_page = addslashes($page[1]);
    $q2 = $pagestore->dbh->query("SELECT page FROM $LkTbl " .
                                 "WHERE link='$esc_page' AND page!='$esc_page'");
    if(!($r2 = $pagestore->dbh->result($q2)) || empty($r2[0]))
    {
      if(!$first)                       // Don't prepend newline to first one.
        { $text = $text . "\n"; }
      else
        { $first = 0; }

      $text = $text . html_ref($page[1], $page[1]);
      if ($page[4] == 0 ) {
        $text .= PARSE_EmptyToBeDeleted;
      }
    }
  }

  return html_code($text);
}

// Prepare a list of pages linked to that do not exist.
function view_macro_wanted($args)
{
  global $pagestore, $LkTbl, $PgTbl;

  // Check for CurlyOptions, and split them
  preg_match("/^(?:\s*{([^]]*)})?\s*(.*)$/", $args, $arg);
  $options = $arg[1];
  $search = $arg[2];

  // Defaults
  $displayOrigin = false;

  // Parse options
  foreach (split_curly_options($options) as $name=>$value) {
    $name = strtolower($name);
    if (preg_match("/^or/i", $name)) {  // ORigin  - Displays origin of wanted pages
      $displayOrigin = !($value == 'false');
    }
  }

  $text = '';
  $first = 1;

  $q1 = $pagestore->dbh->query("SELECT l.link, SUM(l.count) AS ct, l.page, p.title " .
                               "FROM $LkTbl AS l LEFT JOIN $PgTbl AS p " .
                               "ON l.link = p.title " .
                               "WHERE p.title IS NULL " .
                               "GROUP BY l.link " .
                               "ORDER BY ct DESC, l.link");


  while(($result = $pagestore->dbh->result($q1)))
  {
    if(!$first)                         // Don't prepend newline to first one.
      { $text = $text . "\n"; }
    else
      { $first = 0; }

    if ($displayOrigin) {
      if ($result[1] > 1) {
         $q2 = $pagestore->dbh->query("SELECT l.page, l.link from $LkTbl as l ".
                                      "WHERE l.link = '". $result[0] ."'");

         $deref = ' ' . PARSE_From . ' ';
         while (($res2 = $pagestore->dbh->result($q2))) {
           $deref .= html_url(editUrl($res2[0]), '?'.$res2[0]) . ', ';
         }

         $deref = preg_replace("/, $/", "", $deref);
      } else {
         $deref = ' ' . PARSE_From . ' ' . 
                  html_url(editURL($result[2]), '?'.$result[2]);
      }
    } else {
      $deref = '';
    }
    $text = $text . '(' .
            html_url(findURL($result[0]), $result[1]) .
            ') ' . html_ref($result[0], $result[0]) . $deref;
  }

  return html_code($text);
}

// Do a textual search in list of page titles
function view_macro_titlesearch($args)
{
  // Description of TitleSearch macro:
  //   [[TitleSearch {options} search-pattern]]
  // This macro searches for page-titles matching the searchpattern,
  // and presents it according to options. The pattern may include
  // ^ or $ to lock it against start or end, and otherwise it must 
  // only contain alpha-characters (or '/'). The special pattern '*'
  // matches every title.
  //
  // Legal options, capitalized unique prefix:
  //   Class      : Sets the class of the list used for results
  //   STyle      : Sets the style attribute
  //   Delimiter  : Choose delimiter between text entries
  //   Index      : Divides the list according to first character,
  //                or first character after value of option
  //   Oneline/List : Indicates to use line/list-markup
  //
  // Examples:  [[TitleSearch Pages$]]
  //            [[TitleSearch *]]
  //            [[TitleSearch {c=prelist} ^Tavi]]
  //            [[TitleSearch {i=5} ^Tavi]]

  global $pagestore, $AlphaPtn;

  // Check for CurlyOptions, and split them
  preg_match("/^(?:\s*{([^]]*)})?\s*(.*)$/", $args, $arg);
  $options = $arg[1];
  $search = $arg[2];

  // Some defaults
  $useDelim = ''; // Empty delimiter at the start, changed by options
  $showIndex = false;
  $text = '';

  // Parse options
  foreach (split_curly_options($options) as $name=>$value) {
    $name=strtolower($name);
    if (preg_match("/^st/", $name)) { // STyle - Adds a style-attribute
      $style = $value;
      $listAttr = "style=\"$value\" ";
    } else if ($name[0]=='c') {   // Class - Adds a class-attribute
      $listAttr = "class=\"$value\" ";
      if ($value == "prelist") { $useDelim = ''; }
    } else if ($name[0]=='d') {   // Delimiter - Changes the delimiter used
      $useDelim = $value;
    } else if ($name[0]=='o') {   // Oneline - use line-markup
      $useList = false;
    } else if ($name[0]=='l') {   // List - use list-markup
      $useList = true;
    } else if ($name[0]=='h') {   // headinglevel - gives headinglevel on index
      if (is_numeric($value)) {
        $level=$value;
      } else {
        $level=2; // Default value
      }
    } else if ($name[0]=='i') {  // Index - Use heading to divide index
      $showIndex = true; 
      if (empty($level)) {$level=2;} // default to level 2
      if (is_numeric($value)) {
        $indexCharNo = $value -1;
      } else {
        // Default to first character displayed of result
        $indexCharNo = strpos($search, '(');
        if ($indexCharNo < 0 )
          { $indexCharNo =0; }
      }
    }
  }

  // Check for illegal characters to make search pattern safer against exploits
  if ($search == '*') {  // Match every title 
    $pattern = "."; 
  } else if ( !preg_match("/^\^?(\/|$AlphaPtn|[-_0-9:;\*\(\)])+\\$?$/", $search)) {
     // Search can be locked at ^start and/or end$, contain alphanumeric
     // characters, or characters: :;-_
     // In addition the characters: (*)  have special syntactic meanings
     return "[[TitleSearch $args]]";
  } else {
    // $search validates, replace special characters
    $pattern=preg_replace("|\*|", ".*", $search);
    $useAlternateLinknames = (preg_match("|\(.*\)|", $pattern));
  }

  if (!isset($useList) or !$useList) {
    $useList = false;
    $useDelim = ($useDelim) ? $useDelim : ', ';
  }

  if ($showIndex) {
    $lastIndexChar ='';
  } else {
    if ($useList) { $text = entity_list("*", 'start', $listAttr); };
  }

  // Loop through all pagetitles
  $list = $pagestore->allpages();
  foreach($list as $page)
  {
    if (preg_match("|$pattern|", $page[1], $match)) {
      if ($showIndex && ($lastIndexChar != $page[1][$indexCharNo])) {
        if ($lastIndexChar != '') {  // End previous list
          if ($useDelim) {
           $text = preg_replace("/" . preg_quote($useDelim) . "$/",
                                "\n", $text);
          }
          if ($useList) { $text .= entity_list("*", "end"); };
        }

        // Add index-header
        $text .= new_entity(array('head_start', $level)) .
           substr($page[1], 0, $indexCharNo+1) .
           new_entity(array('head_end', $level));

        if ($useList) {
          // Start list again
          $text .= entity_list("*", 'start', $listAttr);
        }
        $lastIndexChar = $page[1][$indexCharNo];
      }

      if ($useList) { $text .= entity_listitem("*", "start"); };

      // Produce link, with or without alternate name on link       
      $text .= sprintf("%s".$useDelim, html_ref($page[1], 
                          ($useAlternateLinknames) ? $match[1] : $page[1]));
    }
  }

  if ($useDelim) {
    $text = preg_replace("/" . preg_quote($useDelim) . "$/", "\n", $text);
  }
  if ($useList) { $text .= entity_list("*", "end"); };

  return parse_elements($text);
}

// Prepare a list of pages sorted by how many links they contain.
function view_macro_outlinks()
{
  global $pagestore, $LkTbl;

  $text = '';
  $first = 1;

  $q1 = $pagestore->dbh->query("SELECT page, SUM(count) AS ct FROM $LkTbl " .
                               "GROUP BY page ORDER BY ct DESC, page");
  while(($result = $pagestore->dbh->result($q1)))
  {
    if(!$first)                         // Don't prepend newline to first one.
      { $text = $text . "\n"; }
    else
      { $first = 0; }

    $text = $text .
            '(' . $result[1] . ') ' . html_ref($result[0], $result[0]);
  }

  return html_code($text);
}

// Prepare a list of pages sorted by how many links to them exist.
function view_macro_refs()
{
  global $pagestore, $LkTbl, $PgTbl;

  $text = '';
  $first = 1;

// It's not quite as straightforward as one would imagine to turn the
// following code into a JOIN, since we want to avoid multiplying the
// number of links to a page by the number of versions of that page that
// exist.  If anyone has some efficient suggestions, I'd be welcome to
// entertain them.  -- ScottMoonen

  $q1 = $pagestore->dbh->query("SELECT link, SUM(count) AS ct FROM $LkTbl " .
                               "GROUP BY link ORDER BY ct DESC, link");
  while(($result = $pagestore->dbh->result($q1)))
  {
    $esc_page = addslashes($result[0]);
    $q2 = $pagestore->dbh->query("SELECT MAX(version) FROM $PgTbl " .
                                 "WHERE title='$esc_page'");
    if(($r2 = $pagestore->dbh->result($q2)) && !empty($r2[0]))
    {
      if(!$first)                       // Don't prepend newline to first one.
        { $text = $text . "\n"; }
      else
        { $first = 0; }

      $text = $text . '(' .
              html_url(findURL($result[0]), $result[1]) . ') ' .
              html_ref($result[0], $result[0]);
    }
  }

  return html_code($text);
}

// This macro inserts an HTML anchor into the text.
function view_macro_anchor($args)
{
  preg_match('/^([A-Za-z][-A-Za-z0-9_:.]*)$/', $args, $result);

  if($result[1] != '')
    { return html_anchor($result[1]); }
  else
    { return ''; }
}

// This macro transcludes another page into a wiki page.
function view_macro_transclude($args)
{
  global $pagestore, $ParseEngine, $ParseObject, $HeadingOffset;
  static $visited_array = array();
  static $visited_count = 0;
  
  $previousHeadingOffset = $HeadingOffset;  // Backup previous version
  
  // Check for CurlyOptions, and split them
  preg_match("/^(?:\s*{([^]]*)})?\s*(.*)$/", $args, $arg);
  $options = $arg[1];
  $page = $arg[2];
  
  if(!validate_page($page))
    { return '[[Transclude ' . $args . ']]'; }

  $visited_array[$visited_count++] = $ParseObject;
  for($i = 0; $i < $visited_count; $i++)
  {
    if($visited_array[$i] == $page)
    {
      $visited_count--;
      return '[[Transclude ' . $args . ']]';
    }
  }

  $pg = $pagestore->page($page);
  $pg->read();
  if(!$pg->exists)
  {
    $visited_count--;
    return '[[Transclude ' . $args . ']]';
  }

  // Check for CurlyOptions affecting transclusion 
  // Parse options
  foreach (split_curly_options($options) as $name=>$value) {
    $name=strtolower($name);
    if ($name[0]=='o') { // Offset - Adds to header levels in transcluded docs
      $HeadingOffset = $previousHeadingOffset + (($value=='') ? 1 : $value);
    }
  }
  
  $result = parseText($pg->text, $ParseEngine, $page);
  $visited_count--;
  $HeadingOffset = $previousHeadingOffset; // Restore offset
  return $result;
}
function view_macro_reflist($args) 
{
  return parse_elements(new_entity(array("reflist", $args)));
}
?>
