<?php

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: apb_bookmark_class.php
// Author:   L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-09-05 00:08     Starting on version 1.0 (NPH) (LBS)
//
// This class is pretty easy, except for the constructor.  Look at
// the comments for apb_bookmark() in apb_common.php to get an idea of what
// is going on.
//
//####################################################################

class Bookmark {

    var $id;
    var $group_id;
    var $title;
    var $url;
    var $description;
    var $creation_date;
    var $private;
    var $user_id;

    function Bookmark ($constructor) {

        if (is_array($constructor)) {
            // $constructor is an associate array
            // created by a db query, that contains
            // the bookmarks info so we'll use that
            // to populate the variables

            #debug("Constructing bookmark from array");

            $this->id            = $constructor['bookmark_id'];
            $this->group_id      = $constructor['group_id'];
            $this->title         = $constructor['bookmark_title'];
            $this->url           = $constructor['bookmark_url'];
            $this->description   = $constructor['bookmark_description'];
            $this->creation_date = $constructor['bookmark_creation_date'];
            $this->private       = $constructor['bookmark_private'];
            $this->user_id       = $constructor['user_id'];
        } else {
            // $constructor is a bookmark id
            #debug("Constructing bookmark from integer");
            $this->load_vars($constructor);
        }

    }

    function id () {
        return $this->id;
    }

    function group_id () {
        if (! $this->group_id) {
            #debug("No group_id, loading vars");
            $this->load_vars($this->id);
        }

        return $this->group_id;
    }

    function title () {
        if (! $this->title) {
            #debug("No title, loading vars");
            $this->load_vars($this->id);
        }

        return $this->title;
    }

    function url () {
        if (! $this->url) {
            #debug("No url, loading vars");
            $this->load_vars($this->id);
        }

        return $this->url;
    }

    function description () {
        if (! $this->description) {
            #debug("No description, loading vars");
            $this->load_vars($this->id);
        }

        return $this->description;
    }

    function creation_date () {
        if (! $this->creation_date) {
            #debug("No creation_date, loading vars");
            $this->load_vars($this->id);
        }

        return $this->creation_date;
    }

    function private () {
        if (! $this->private) {
            #debug("No private, loading vars");
            $this->load_vars($this->id);
        }

        return $this->private;
    }

    function user_id () {
        if (! $this->user_id) {
            #debug("No user_id, loading vars");
            $this->load_vars($this->id);
        }

        return $this->user_id;
    }

    function link () {
        global $APB_SETTINGS;

        $this->url()   || $this->load_vars($this->id);
        $this->title() || $this->load_vars($this->id);

        // If we're in "edit mode" go to a different link than the normal one.
        if ($APB_SETTINGS['auth_user_id'] AND $APB_SETTINGS['edit_mode']) {
            $url = $APB_SETTINGS['apb_url']."add_bookmark.php?id=";
            // Show an icon to let you know if something is private.
            if ($this->private) { $private_icon = " <img src='images/private.gif' alt='Private' title='Private'>"; }
        } else {
            $url = $APB_SETTINGS['redirect_url']."?id=";
        }

        // Create the entire link.
        $link = "<a href='".
            $url.
            $this->id.
            "' ".
            "onmouseover='window.status=\"". $this->url . "\"; ".
            "return true;' onmouseout='window.status=\"\"; return true;' ".
            "title='". $this->description . (($this->description) ? "\n" : "") . $this->url ."' ".
//            "target='_blank'".  // Uncomment this line to make links pop out into a new window.
            ">".
            $this->title.
            "</a>".
            $private_icon;

        return $link;
    }


    function load_vars ($id) {
        global $APB_SETTINGS;
        global $con;



        $sql = "SELECT * FROM apb_bookmarks WHERE bookmark_id = $id";
        $rst = $con->execute($sql);

      if (($rst) && (!$rst -> EOF)) {
        $this->id            = $rst->fiedls['bookmark_id'];
        $this->group_id      = $rst->fiedls['group_id'];
        $this->title         = htmlentities($rst->fields['bookmark_title'], ENT_QUOTES);
        $this->url           = $rst->fiedls['bookmark_url'];
        // Clean up blank bookmark bug. [LBS 20020211]
        if (!$this->url) { $this->url = "https://"; }
        // Clean up blank bookmark bug. [LBS 20020211]
        if (!$this->title) { $this->title = $this->url; }
        // If there's no protocol given, assume http:// at the front. [LBS 20020301]
//        if (!preg_match ("/^[a-zA-Z]+\:/", $this->url)) { $this->url = "http://".$this->url; }
        // Bug-fix to previous line of code... this way internal links, such
        // as "/documents/foo.txt", won't get an "http://" added to them work. [LBS 20020306]
        if (!preg_match ("/^[a-zA-Z]+\:/", $this->url) && preg_match ("/^[^\/]+\./", $this->url)) { $this->url = "http://".$this->url; }
        $this->description   = htmlentities($rst->fields['bookmark_description'], ENT_QUOTES);
        $this->creation_date = $rst->fields['bookmark_creation_date'];
        $this->private       = $rst->fields['bookmark_private'];
        $this->user_id       = $rst->fields['user_id'];
      }
    }

}

?>
