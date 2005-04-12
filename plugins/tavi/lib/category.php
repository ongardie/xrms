<?php
// $Id: category.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

require('parse/main.php');

// Add a page to a list of categories.
function add_to_category($page, $catlist)
{
  global $pagestore, $Entity, $UserName, $REMOTE_ADDR, $FlgChr;

  // Parse the category list for category names.
  $parsed = parseText($catlist, array('parse_freelink', 'parse_wikiname'), '');
  $pagenames = array();
  preg_replace('/' . $FlgChr . '!?(\\d+)' . $FlgChr . '/e',
               '$pagenames[]=$Entity[\\1][1]', $parsed);

  // Add it to each category.
  foreach($pagenames as $category)
  {
    $pg = $pagestore->page($category);

    $pg->read();
    if($pg->exists)
    {
      if(preg_match('/\\[\\[!(.*)\\]\\]/', $pg->text, $match))
      {
        $parsed = parseText($match[1], array('parse_freelink', 
                                             'parse_wikiname'), '');
        $categorypages = array();
        preg_replace('/' . $FlgChr . '!?(\\d+)' . $FlgChr . '/e',
               '$categorypages[$Entity[\\1][1]]=1', $parsed);

        if (!$categorypages[$page] ) 
        {
          if(validate_page($page) == 2)
            { $page = '((' . $page . '))'; }
                  
          $pg->text = preg_replace('/(\\[\\[!.*)\\]\\]/',
                                   "\\1 $page]]", $pg->text);
        }
        else
          { continue; }
      }
      else 
      {  
        if(validate_page($page) == 2)
          { $page = '((' . $page . '))'; }
        $pg->text = $pg->text . "\n[[! $page]]\n"; 
      }

      $pg->text = str_replace("\\", "\\\\", $pg->text);
      $pg->text = str_replace("'", "\\'", $pg->text);
     
      $pg->version++;
      $pg->comment  = '';
      $pg->hostname = gethostbyaddr($REMOTE_ADDR);
      $pg->username = $UserName;

      $pg->write();
    }
  }
}
?>
