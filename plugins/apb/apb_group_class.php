<?php

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: apb_group_class.php
// Author:   L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-09-05 00:18     Starting on version 1.0 (NPH) (LBS)
//
// This class is pretty easy, except for the constructor.  Look at
// the comments for apb_bookmark() in apb_common.php to get an idea of what
// is going on.
//
//####################################################################

class Group {

    var $id;
    var $parent_id;
    var $title;
    var $description;
    var $user_id;
    var $private;
    var $creation_date;

    function Group ($constructor) {

        if (is_array($constructor)) {
            // $constructor is an associate array
            // created by a db query, that contains
            // the bookmarks info so we'll use that
            // to populate the variables
            $this->id            = $constructor['group_id'];
            $this->parent_id     = $constructor['group_parent_id'];
            $this->title         = $constructor['group_title'];
            $this->description   = $constructor['group_description'];
            $this->user_id       = $constructor['user_id'];
            $this->private       = $constructor['group_private'];
            $this->creation_date = $constructor['group_creation_date'];
        } else {
            // $constructor is a bookmark id
            $this->load_vars($constructor);
        }

    }

    function id() {
        return $this->id;
    }

    function parent_id () {
        if (! $this->parent_id) {
            $this->load_vars($this->parent_id);
        }

        return $this->parent_id;
    }

    function title () {
        if (! $this->title) {
            $this->load_vars($this->id);
        }

        return $this->title;
    }

    function description () {
        if (! $this->description) {
            $this->load_vars($this->id);
        }

        return $this->description;
    }

    function creation_date () {
        if (! $this->creation_date) {
            $this->load_vars($this->id);
        }

        return $this->creation_date;
    }

    function private () {
        if (! $this->private) {
            $this->load_vars($this->id);
        }

        return $this->private;
    }

    function user_id () {
        if (! $this->user_id) {
            $this->load_vars($this->id);
        }

        return $this->user_id;
    }


    function link ($color = "") {
        global $APB_SETTINGS;

        $this->title() || $this->load_vars($this->id);

        // Start creating the link...
        $link = "<a href='";

        // If we're in "edit mode" display a different link than the normal one.
        if ($APB_SETTINGS['auth_user_id'] AND $APB_SETTINGS['edit_mode']) {
            $link .= $APB_SETTINGS['apb_url']."edit_group.php?id=".$this->id;
        } else {
            $link .= $APB_SETTINGS['view_group_path'] . "?id=" . $this->id;
        }

        // Finish with some user-friendly additions to the link...
        $link .= "' ".
            "onmouseover='window.status=\"". str_replace ("&quot;", "\\&quot;", $this->get_group_path()) . "\"; ".
            "return true;' onmouseout='window.status=\"\"; return true;' ".
            "title='". $this->get_group_path() ."'".
            ">";
        if ($color) { $link .= "<font color='".$color."'>"; }
        $link .= $this->title;
        if ($color) { $link .= "</font>"; }
        $link .= "</a>";

        return $link;
    }


    function load_vars ($id) {
        global $APB_SETTINGS;
        global $con;

        if (!$id) { return 0; }

        $sql = "SELECT * FROM apb_groups WHERE group_id = $id";
            $rst = $con->execute($sql);
            if (($rst) && (!$rst->EOF)) {

            $this->id            = $rst->fields['group_id'];
            $this->parent_id     = $rst->fields['group_parent_id'];
            $this->title         = htmlentities($rst->fields['group_title'], ENT_QUOTES);
            $this->description   = htmlentities($rst->fields['group_description'], ENT_QUOTES);
            $this->user_id       = $rst->fields['user_id'];
            $this->private       = $rst->fields['group_private'];
            $this->creation_date = $rst->fields['group_creation_date'];
        } else {
            db_error_handler ($con, $rst);
        }
    }



    function get_group_path () {

        global $APB_SETTINGS;
        global $con;

        $string   = $this->title();
        $group_id = $this->parent_id();

        while ($group_id > 0) {

            $sql = "
                SELECT g.group_id #, g.group_parent_id, g.group_title
                  FROM apb_groups g
                 WHERE g.group_id = $group_id
                   AND g.user_id = " . $APB_SETTINGS['user_id'] . "
            ";

            $rst = $con->execute($sql);
            if (($rst) && (!$rst->EOF)) {
                $g = apb_group($rst->fields['group_id']);
                $string = $g->title() . " :: " . $string;
                $group_id = $g->parent_id();
            } else {
                db_error_handler ($con, $rst);
            }
        }

        return $string;
    }


    function print_group_path () {

        global $APB_SETTINGS;
        global $con;

        $group_id = $this->parent_id();

        $string   =  " : " . $this->title;

        while ($group_id > 0) {

            $sql = "SELECT g.group_id #, g.group_parent_id, g.group_title
                  FROM apb_groups g
                 WHERE g.group_id = $group_id
                   AND g.user_id = " . $APB_SETTINGS['user_id'] . "";

            $rst = $con->execute($sql);
            if (($rst) && (!$rst->EOF)) {
                $g = apb_group($rst->fields['group_id']);
                $string =  " : " . $g->title . $string;
                $group_id = $g->parent_id();
            } else {
                db_error_handler ($con, $rst);
            }
        }

        return $string;
    }


    function print_group_children () {

        global $APB_SETTINGS;
        global $con;

        $group_id = $this->id();

        $query = "
            SELECT g.group_id
              FROM apb_groups g
             WHERE g.group_parent_id = $group_id
               AND g.user_id = " . $APB_SETTINGS['user_id'] . "
          ORDER BY g.group_title
        ";

        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

        echo "<ul>";
        while ($row = mysql_fetch_assoc($result)) {
            $g = apb_group($row['group_id']);
            $c = $g->number_of_bookmarks() + $g->number_of_child_groups();
            print "<li>" . $g->link() . " (" . $c . ")\n";
        }
        echo "</ul>";
    }


    function number_of_child_groups () {

        if (! $this->number_of_child_groups) {

            global $APB_SETTINGS;

            $group_id = $this->id();

            $query = "
                SELECT COUNT(g.group_id) as total
                  FROM apb_groups g
                 WHERE g.group_parent_id = $group_id
                   AND g.user_id = " . $APB_SETTINGS['user_id'] . "
            ";

            $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);


            if (($result) && (!$result->EOF)) {
                $row = mysql_fetch_assoc($result);
                $this->number_of_child_groups = $row['total'];
            }
        }

        return $this->number_of_child_groups;
    }


    function number_of_bookmarks() {

        global $APB_SETTINGS;

        if ($APB_SETTINGS['auth_user_id']) { $private_sql = ""; }
        else { $private_sql = "AND b.bookmark_private != 1"; }

        // Added the bookmark deleted bit, so that it returns a real number. [LBS 20020211]
        $query = "
            SELECT count(*) as total
              FROM apb_bookmarks b
             WHERE b.group_id = " . $this->id() . "
               AND b.bookmark_deleted != 1
               $private_sql
               AND b.user_id = " . $APB_SETTINGS['user_id'] . "
        ";
        #print "<p><pre>$query</pre><p>\n\n";

        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
        if (($result) && (!$result->EOF)) {
            $row = mysql_fetch_assoc($result);
            return $row['total'];
        } else {
            return 0;
        }
    }

    function print_group_bookmarks () {
        global $APB_SETTINGS;

        if (!$APB_SETTINGS['auth_user_id']) {
            $private_sql = "AND b.bookmark_private = 0";
        }

        $query = "
            SELECT b.bookmark_id
              FROM apb_bookmarks b
             WHERE b.group_id = " . $this->id() . "
               AND b.user_id = " . $APB_SETTINGS['user_id'] . "
               " . $private_sql . "
               AND b.bookmark_deleted != 1
          ORDER BY b.bookmark_title
        ";
        #print "<p><pre>$query</pre><p>\n\n";

        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

        echo "<ul>";
        while ($row = mysql_fetch_assoc($result)) {
            $b = apb_bookmark($row['bookmark_id']);
            print "<li>" . $b->link();
            if ($b->description()) { print " - " . $b->description(); }
            print "<br>\n";
        }
        echo "</ul>";

    }

    function return_child_groups () {
        global $APB_SETTINGS;

        if (! $this->child_groups_loop) {
            $this->group_list = array();
            array_push($this->group_list, $this->id);
            $this->child_groups_loop($this->id);
        }

        return $this->group_list;
    }

    function child_groups_loop ($id) {
        global $APB_SETTINGS;

        $query = "
            SELECT g.group_id, g.group_parent_id
              FROM apb_groups g
             WHERE g.user_id = " . $APB_SETTINGS['user_id'] . "
               AND g.group_parent_id = " . $id . "
        ";
        #print "<p><pre>$query</pre><p>\n\n";

        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

        while ($row = mysql_fetch_assoc($result)) {
            array_push($this->group_list, $row['group_id']);
            $this->child_groups_loop($row['group_id']);
        }
    }
}

?>
