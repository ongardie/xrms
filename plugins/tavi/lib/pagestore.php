<?php
// $Id: pagestore.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

require('lib/db.php');
require('lib/page.php');

// Abstractor for the page database.  Note that page.php contains the actual
//   code to read/write pages; this serves more general query functions.
class PageStore
{
  var $dbh;

  function PageStore()
  {
    global $DBPersist, $DBServer, $DBUser, $DBPasswd, $DBName;

    $this->dbh = new WikiDB($DBPersist, $DBServer, $DBUser, $DBPasswd, $DBName);
  }

  // Create a page object.
  function page($name = '')
  {
    return new WikiPage($this->dbh, $name);
  }

  // Find text in the database.
  function find($text)
  {
    global $PgTbl;

    $qid = $this->dbh->query("SELECT t1.title, t1.body, t1.version, " .
                             "MAX(t2.version) " .
                             "FROM $PgTbl AS t1, $PgTbl AS t2 " .
                             "WHERE t1.title = t2.title " .
                             "GROUP BY t2.title, t1.version " .
                             "HAVING t1.version = MAX(t2.version) " .
                             "AND (body LIKE '%$text%' OR title LIKE '%$text%')");


    $list = array();
    while(($result = $this->dbh->result($qid)))
    {
      $list[] = $result[0];
    }

    return $list;
  }

  // Retrieve a page's edit history.
  function history($page)
  {
    global $PgTbl;

    $page = addslashes($page);
    $qid = $this->dbh->query("SELECT time, author, version, username, " .
                             "comment " .
                             "FROM $PgTbl WHERE title='$page' " .
                             "ORDER BY version DESC");

    $list = array();
    while(($result = $this->dbh->result($qid)))
    {
      $list[] = array($result[0], $result[1], $result[2], $result[3],
                      $result[4]);
    }

    return $list;
  }

  // Look up an interwiki prefix.
  function interwiki($name)
  {
    global $IwTbl;

    $name = addslashes($name);
    $qid = $this->dbh->query("SELECT url FROM $IwTbl WHERE prefix='$name'");
    if(($result = $this->dbh->result($qid)))
      { return $result[0]; }
    return '';
  }

  // Clear all the links cached for a particular page.
  function clear_link($page)
  {
    global $LkTbl;

    $page = addslashes($page);
    $this->dbh->query("DELETE FROM $LkTbl WHERE page='$page'");
  }

  // Clear all the interwiki definitions for a particular page.
  function clear_interwiki($page)
  {
    global $IwTbl;

    $page = addslashes($page);
    $this->dbh->query("DELETE FROM $IwTbl WHERE where_defined='$page'");
  }

  // Clear all the sisterwiki definitions for a particular page.
  function clear_sisterwiki($page)
  {
    global $SwTbl;

    $page = addslashes($page);
    $this->dbh->query("DELETE FROM $SwTbl WHERE where_defined='$page'");
  }

  // Add a link for a given page to the link table.
  function new_link($page, $link)
  {
    // Assumption: this will only ever be called with one page per
    //   script invocation.  If this assumption should change, $links should
    //   be made a 2-dimensional array.

    global $LkTbl;
    static $links = array();

    $page = addslashes($page);
    $link = addslashes($link);

    if(empty($links[$link]))
    {
      $this->dbh->query("INSERT INTO $LkTbl VALUES('$page', '$link', 1)");
      $links[$link] = 1;
    }
    else
    {
      $links[$link]++;
      $this->dbh->query("UPDATE $LkTbl SET count=" . $links[$link] .
                        " WHERE page='$page' AND link='$link'");
    }
  }

  // Add an interwiki definition for a particular page.
  function new_interwiki($where_defined, $prefix, $url)
  {
    global $IwTbl;

    $url = str_replace("'", "\\'", $url);
    if (preg_match("/(.*)\?(.*)/", $url, $match)) 
    {
      $match[2] = preg_replace("/&(?!amp;)/", '&amp;', $match[2]);
      $url = $match[1] . '?'. $match[2];
    }
    
    $where_defined = addslashes($where_defined);

    $qid = $this->dbh->query("SELECT where_defined FROM $IwTbl " .
                             "WHERE prefix='$prefix'");
    if($this->dbh->result($qid))
    {
      $this->dbh->query("UPDATE $IwTbl SET where_defined='$where_defined', " .
                        "url='$url' WHERE prefix='$prefix'");
    }
    else
    {
      $this->dbh->query("INSERT INTO $IwTbl(prefix, where_defined, url) " .
                        "VALUES('$prefix', '$where_defined', '$url')");
    }
  }

  // Add a sisterwiki definition for a particular page.
  function new_sisterwiki($where_defined, $prefix, $url)
  {
    global $SwTbl;

    $url = str_replace("'", "\\'", $url);
    if (preg_match("/(.*)\?(.*)/", $url, $match)) 
    {
      $match[2] = preg_replace("/&(?!amp;)/", '&amp;', $match[2]);
      $url = $match[1] . '?'. $match[2];
    }

    $where_defined = addslashes($where_defined);

    $qid = $this->dbh->query("SELECT where_defined FROM $SwTbl " .
                             "WHERE prefix='$prefix'");
    if($this->dbh->result($qid))
    {
      $this->dbh->query("UPDATE $SwTbl SET where_defined='$where_defined', " .
                        "url='$url' WHERE prefix='$prefix'");
    }
    else
    {
      $this->dbh->query("INSERT INTO $SwTbl(prefix, where_defined, url) " .
                        "VALUES('$prefix', '$where_defined', '$url')");
    }
  }

  // Find all twins of a page at sisterwiki sites.
  function twinpages($page)
  {
    global $RemTbl;

    $list = array();
    $page = addslashes($page);
    $q2 = $this->dbh->query("SELECT site, page FROM $RemTbl " .
                            "WHERE page LIKE '$page'");
    while(($twin = $this->dbh->result($q2)))
      { $list[] = array($twin[0], $twin[1]); }

    return $list;
  }

  // Lock the database tables.
  function lock()
  {
    global $PgTbl, $IwTbl, $SwTbl, $LkTbl;

    $this->dbh->query("LOCK TABLES $PgTbl WRITE, $IwTbl WRITE, $SwTbl WRITE, " .
                      "$LkTbl WRITE");
  }

  // Unlock the database tables.
  function unlock()
  {
    $this->dbh->query("UNLOCK TABLES");
  }

  // Retrieve a list of all of the pages in the wiki.
  function allpages()
  {
    global $PgTbl;

    $qid = $this->dbh->query("SELECT t1.title, t1.version, t1.author, t1.time, " .
                             "t1.username, LENGTH(t1.body), t1.comment, " .
                             "t1.mutable, MAX(t2.version) " .
                             "FROM $PgTbl AS t1, $PgTbl AS t2 " .
                             "WHERE t1.title = t2.title " .
                             "GROUP BY t2.title, t1.version " .
                             "HAVING t1.version = MAX(t2.version)");

    $list = array();
    while(($result = $this->dbh->result($qid)))
    {
      $list[] = array($result[3], $result[0], $result[2], $result[4],
                      $result[5], $result[6], $result[7] == 'on', $result[1]);
    }

    return $list;
  }

  // Retrieve a list of all new pages in the wiki.
  function newpages()
  {
    global $PgTbl;

    $qid = $this->dbh->query("SELECT title, author, time, username, " .
                             "LENGTH(body), comment " .
                             "FROM $PgTbl WHERE version=1");

    $list = array();
    while(($result = $this->dbh->result($qid)))
    {
      $list[] = array($result[2], $result[0], $result[1], $result[3],
                      $result[4], $result[5]);
    }

    return $list;
  }

  // Return a list of all empty (deleted) pages in the wiki.
  function emptypages()
  {
    global $PgTbl;

    $qid = $this->dbh->query("SELECT t1.title, t1.version, t1.author, " .
                             "t1.time, t1.username, t1.comment, t1.body, " .
                             "MAX(t2.version) " .
                             "FROM $PgTbl AS t1, $PgTbl AS t2 " .
                             "WHERE t1.title = t2.title " .
                             "GROUP BY t2.title, t1.version " .
                             "HAVING t1.version = MAX(t2.version) " .
                             "AND t1.body=''");

    $list = array();
    while(($result = $this->dbh->result($qid)))
    {
      $list[] = array($result[3], $result[0], $result[2],
                      $result[4], 0, $result[5]);
    }

    return $list;
  }

  // Return a list of information about a particular set of pages.
  function givenpages($names)
  {
    global $PgTbl;

    $list = array();
    foreach($names as $page)
    {
      $esc_page = addslashes($page);
      $qid = $this->dbh->query("SELECT time, author, username, LENGTH(body), " .
                               "comment FROM $PgTbl WHERE title='$esc_page' " .
                               "ORDER BY version DESC");

      if(!($result = $this->dbh->result($qid)))
        { continue; }

      $list[] = array($result[0], $page, $result[1], $result[2],
                      $result[3], $result[4]);
    }

    return $list;
  }

  // Expire old versions of pages.
  function maintain()
  {
    global $PgTbl, $RtTbl, $ExpireLen, $RatePeriod;

    $qid = $this->dbh->query("SELECT title, MAX(version) FROM $PgTbl " .
                             "GROUP BY title");

    if ($ExpireLen != 0) 
    {
      while(($result = $this->dbh->result($qid)))
      {
        $result[0] = addslashes($result[0]);
        $this->dbh->query("DELETE FROM $PgTbl WHERE title='$result[0]' AND " .
                          "(version < $result[1] OR body='') AND " .
                          "TO_DAYS(NOW()) - TO_DAYS(supercede) > $ExpireLen");
      }
    }

    if($RatePeriod)
    {
      $this->dbh->query("DELETE FROM $RtTbl " .
                        "WHERE ip NOT LIKE '%.*' " .
                        "AND TO_DAYS(NOW()) > TO_DAYS(time)");
    }
  }
}
?>
