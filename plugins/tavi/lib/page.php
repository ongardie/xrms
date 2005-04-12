<?php
// $Id: page.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

// Abstractor to read and write wiki pages.
class WikiPage
{
  var $name = '';                       // Name of page.
  var $dbname = '';                     // Name used in DB queries.
  var $text = '';                       // Page's text in wiki markup form.
  var $time = '';                       // Page's modification time.
  var $hostname = '';                   // Hostname of last editor.
  var $username = '';                   // Username of last editor.
  var $comment  = '';                   // Description of last edit.
  var $version = -1;                    // Version number of page.
  var $mutable = 1;                     // Whether page may be edited.
  var $exists = 0;                      // Whether page already exists.
  var $db;                              // Database object.

  function WikiPage($db_, $name_ = '')
  {
    $this->db = $db_;
    $this->name = $name_;
    $this->dbname = str_replace('\\', '\\\\', $name_);
    $this->dbname = str_replace('\'', '\\\'', $this->dbname);
  }

  // Check whether a page exists.
  // Returns: nonzero if page exists in database.

  function exists()
  {
    global $PgTbl;

    $qid = $this->db->query("SELECT MAX(version) FROM $PgTbl " .
                            "WHERE title='$this->dbname'");
    return !!(($result = $this->db->result($qid)) && $result[0]);
  }

  // Read in a page's contents.
  // Returns: contents of the page.

  function read()
  {
    global $PgTbl;

    $query = "SELECT title, time, author, body, mutable, version, " .
             "username, comment " .
             "FROM $PgTbl WHERE title = '$this->dbname' ";
    if($this->version != -1)
      { $query = $query . "AND version = '$this->version'"; }
    else
      { $query = $query . "ORDER BY version DESC"; }

    $qid = $this->db->query($query);

    if(!($result = $this->db->result($qid)))
      { return ""; }

    $this->time     = $result[1];
    $this->hostname = $result[2];
    $this->exists   = 1;
    $this->version  = $result[5];
    $this->mutable  = ($result[4] == 'on');
    $this->username = $result[6];
    $this->text     = $result[3];
    $this->comment  = $result[7];

    return $this->text;
  }

  // Write out a page's contents.
  // Note: caller is responsible for performing locking.
  // Note: it is assumed that the 'time' member actually contains the
  //       modification-time for the *previous* version.  It is expected that
  //       the previous version will have been read into the same object.
  //       Yes, this is a tiny kludge. :-)

  function write()
  {
    global $PgTbl;

    $this->db->query("INSERT INTO $PgTbl (title, version, time, supercede, " .
                     "mutable, username, author, comment, body) " .
                     "VALUES('$this->dbname', $this->version, NULL, NULL, '" .
                     ($this->mutable ? 'on' : 'off') . "', " .
                     "'$this->username', '$this->hostname', " .
                     "'$this->comment', '$this->text')");

    if($this->version > 1)
    {
      $this->db->query("UPDATE $PgTbl SET time='$this->time', " .
                       "supercede=NULL WHERE title='$this->dbname' " .
                       "AND version=" . ($this->version - 1));
    }
  }
}
?>
