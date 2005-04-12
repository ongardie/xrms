<?php
// $Id: db.php,v 1.1 2005/04/12 20:45:12 gpowers Exp $

// MySQL database abstractor.  It should be easy to port this to other
//   databases, such as PostgreSQL.
class WikiDB
{
  var $handle;

  function WikiDB($persistent, $server, $user, $pass, $database)
  {
    if($persistent)
      { $this->handle = mysql_pconnect($server, $user, $pass); }
    else
      { $this->handle = mysql_connect($server, $user, $pass); }

    if($this->handle <= 0)
      { die(LIB_ErrorDatabaseConnect); }

    if(mysql_select_db($database, $this->handle) == false)
      { die(LIB_ErrorDatabaseSelect); }
  }

  function query($text)
  {
    if(!($qid = mysql_query($text, $this->handle)))
      { die("<strong>".LIB_ErrorDatabaseQuery."</strong><p>$text</p>"); }
    return $qid;
  }

  function result($qid)
  {
    return mysql_fetch_row($qid);
  }
}
?>
