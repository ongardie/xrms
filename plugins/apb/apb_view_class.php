<?php

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: apb_view_class.php
// Authors:  L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-09-09 14:27     Starting on version 1.0 (NPH) (LBS)
//
//####################################################################

class View {

    /* PUBLIC VARIABLES */

    var $user_id;
    var $group_id;
    var $group_by;
    var $order_by;
    var $order_by_dir;

    var $limit = 5;

    var $date_format    = '%Y-%m-%d';
    var $time_format    = '%H:%i';
    var $on_date_format = '%Y-%m-%d';
    var $interval       = 'DAY';

    var $title_string = "Bookmarks";
    var $template;

    /* PRIVATE VARIABLES */

    var $inner_string;

    function View () {
    }

    function output () {
        global $APB_SETTINGS;

        $this->set_template_files();
        $inner .= $this->construct_inner();
        $inner .= $this->inner_string_output;
        //include($APB_SETTINGS['template_path'] . $this->outer_file);
        return $inner;
    }

    function construct_inner () {
        global $APB_SETTINGS;

        // Set some variables that build_query will need,
        // but we have to set here, so extended classes can
        // just use them, and not think about them (ie
        // most extended classes will want to overwrite build_query()
        // so we want to make it simple.

        if (! $this->user_id) {
            $this->user_id = $APB_SETTINGS['user_id'];
        }

        $this->private_sql = $this->is_private();

        // Done setting variables, now let's do the query...

        $query = $this->build_query();
        debug($query);
        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query)
            or error(mysql_error());
        while ($row = mysql_fetch_assoc($result)) {
            $this->add_row_to_inner($row);
        }
    }

    function build_query () {

        $select_sql  = "SELECT b.bookmark_id";
        $select_sql .= ", DATE_FORMAT(b.bookmark_creation_date, '$this->date_format') as b_date";
        $select_sql .= ", DATE_FORMAT(b.bookmark_creation_date, '$this->time_format') as b_time";

        $from_sql        = "FROM apb_bookmarks b";
        $where_sql       = "WHERE b.user_id = $this->user_id";
        $and_sql         = $this->get_and_sql();
        $and_private_sql = "AND $this->private_sql";

        if ($this->on_date) {
            if ( preg_match("/^h\./", $this->group_by) ) {
                $and_on_date_sql .= "AND DATE_FORMAT(h.hit_date, '$this->on_date_format') = '$this->on_date'";
            }
            elseif ( preg_match("/^g\./", $this->group_by) ) {
                $and_on_date_sql .= "AND DATE_FORMAT(g.group_creation_date, '$this->on_date_format') = '$this->on_date'";
            } else {
                $and_on_date_sql .= "AND DATE_FORMAT(b.bookmark_creation_date, '$this->on_date_format') = '$this->on_date'";
            }
        }

        if ($this->since_n_interval) {
            if ( preg_match("/^h\./", $this->group_by) ) {
                $and_since_sql .= "AND h.hit_date > DATE_SUB(NOW(), INTERVAL $this->since_n_interval $this->interval)";
            }
            elseif ( preg_match("/^g\./", $this->group_by) ) {
                $and_since_sql .= "AND g.group_creation_date > DATE_SUB(NOW(), INTERVAL $this->since_n_interval $this->interval)";
            } else {
                $and_since_sql .= "AND b.bookmark_creation_date > DATE_SUB(NOW(), INTERVAL $this->since_n_interval $this->interval)";
            }
        }

        if ($this->group_id)     { $and_group_sql = "AND b.group_id = $this->group_id"; }
        if ($this->group_by)     {
            if ( preg_match("/^h\./", $this->group_by) ) {
                $join_hits_sql = "LEFT JOIN apb_hits h ON (h.bookmark_id = b.bookmark_id)";
            }
            elseif ( preg_match("/^g\./", $this->group_by) ) {
                $join_groups_sql = "LEFT JOIN apb_groups g ON (g.group_id = b.group_id)";
            }
            $group_by_sql  = "GROUP BY $this->group_by";
        }
        if ($this->order_by)     {
            if ( preg_match("/^h\./", $this->order_by) ) {
                $join_hits_sql = "LEFT JOIN apb_hits h ON (h.bookmark_id = b.bookmark_id)";
            }
            $order_by_sql  = "ORDER BY $this->order_by";
            if ( preg_match("/total/", $this->order_by) ) {
                $select_sql .= ", count(*) as total";
            }
        }
        if ($this->order_by_dir) { $order_by_sql .= " $this->order_by_dir"; }
        if ($this->limit > 0)    { $limit_sql     = "LIMIT $this->limit"; }

        if ($join_hits_sql) {
            $select_sql .= ", DATE_FORMAT(h.hit_date, '$this->date_format') as h_date";
            $select_sql .= ", DATE_FORMAT(h.hit_date, '$this->time_format') as h_time";
        }

        $query = "
            $select_sql
            $from_sql
            $join_hits_sql
            $join_groups_sql
            $where_sql
            $and_private_sql
            $and_group_sql
            $and_sql
            $and_since_sql
            $and_on_date_sql
            AND b.bookmark_deleted != '1'
            $group_by_sql
            $order_by_sql
            $limit_sql
        ";

        #print "<p><pre>$query</pre><p>\n";

        return $query;
    }

    function is_private () {
        global $APB_SETTINGS;

        if ($APB_SETTINGS['auth_user_id'] && ($this->user_id == $APB_SETTINGS['auth_user_id'])) {
            $sql = "1 = 1";
        } else {
            $sql = "b.bookmark_private = 0";
        }
        return $sql;
    }

    function get_and_sql () {
        return "";
    }

    function add_row_to_inner ($row) {
        $this->create_inner_string();
        $b = apb_bookmark($row);
        $g = apb_group($b->group_id());
        eval("\$this->inner_string_output .= $this->inner_string;");
    }

    function create_inner_string () {
        global $APB_SETTINGS;

        if (! $this->inner_string ) {
            $path = $APB_SETTINGS['template_path'] . $this->inner_file;
            $this->inner_string = implode("", file($path));
        }
    }

    function set_template_files () {
        global $APB_SETTINGS;
        if (! $this->template) {
            $this->template = $APB_SETTINGS['template'];
        }
        $this->outer_file = $this->template . "_outer.php";
        $this->inner_file = $this->template . "_inner.str";
    }
}



class MostUsedView extends View {
    function MostUsedView () {
        $this->group_by     = 'h.bookmark_id';
        $this->order_by     = 'total';
        $this->order_by_dir = 'DESC';
        $this->title_string = 'Most Used';
    }
}

class MostUsedByVisitorsView extends MostUsedView {
    function get_and_sql () {
        $this->title_string = "Most Used by Visitors";
        return "AND h.user_id = 0\n";
    }
}

class MostUsedByUserView extends MostUsedView {
    function get_and_sql () {
        $this->title_string = "Most Used by " . return_username($this->user_id);
        return "AND h.user_id = $this->user_id\n";
    }
}

class RecentAddedView extends View {
    function RecentAddedView () {
        $this->order_by     = 'b.bookmark_creation_date';
        $this->order_by_dir = 'DESC';
        $this->title_string = 'Recently Added';
    }
}

class RecentUsedView extends View {
    function RecentUsedView () {
        $this->order_by     = 'h.hit_date';
        $this->order_by_dir = 'DESC';
        $this->title_string = 'Recently Used';
    }
}

class RecentUsedUserView extends RecentUsedView {
    function get_and_sql () {
        $this->title_string = 'Recently Used by ' . return_username($this->user_id);
        return "AND h.user_id = $this->user_id";
    }
}

class RecentUsedVisitorsView extends RecentUsedView {
    function get_and_sql () {
        $this->title_string = 'Recently Used by Visitors';
        return "AND h.user_id != $this->user_id";
    }
}


class TopInGroupView extends MostUsedView {
    function get_and_sql () {
        
        global $APB_SETTINGS;
        
        $this->order_by = "total DESC, h.hit_date";

        $string .=  "
            AND b.group_id = $this->group_id
            AND h.user_id = ".$APB_SETTINGS['user_id']."
        ";
        return $string;
    }
}


class TopInHierarchyView extends MostUsedView {
    function get_and_sql () {
        
        global $APB_SETTINGS;
        
        $this->order_by = "total DESC, h.hit_date";

        $string .=  "
            AND b.group_id IN (". $this->group_list . ")
            AND h.user_id = ".$APB_SETTINGS['user_id']."
        ";
        return $string;
    }
}



?>
