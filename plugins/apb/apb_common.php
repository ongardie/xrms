<?php

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename:    apb_common.php
// Authors:     L. Brandon Stone (lbstone.com)
//              Nathanial P. Hendler (retards.org)
//              Glenn Powers (glenn@net127.com) for XRMS
//
// This file should be included by anything that uses the apb bookmark
// system.  It makes the db connection.  It handles authentication.
// It sets up paths and urls.  It loads Classes.  It has the most
// commonly needed procedural functions.
//
//####################################################################

//////////////////////////////////////////////////////////////////////
// Security check.
//////////////////////////////////////////////////////////////////////

//if ($HTTP_COOKIE_VARS["APB_SETTINGS"]["template_path"] ||
//    $HTTP_POST_VARS["APB_SETTINGS"]["template_path"] ||
//    $HTTP_GET_VARS["APB_SETTINGS"]["template_path"])
//{ exit(); }
//
//if ($HTTP_COOKIE_VARS["APB_SETTINGS"]["apb_path"] ||
//    $HTTP_POST_VARS["APB_SETTINGS"]["apb_path"] ||
//    $HTTP_GET_VARS["APB_SETTINGS"]["apb_path"])
//{ exit(); }

//////////////////////////////////////////////////////////////////////
// Set variables.
//////////////////////////////////////////////////////////////////////

/*  Database Connection */
//  All database queries should use mysql_db_query($APB_SETTINGS['apb_database'], $query);
mysql_pconnect ($APB_SETTINGS['apb_host'], $APB_SETTINGS['apb_username'], $APB_SETTINGS['apb_password']);
//echo "[[[".$APB_SETTINGS['apb_username']."]]]";

/* class files */
include_once($APB_SETTINGS['apb_path'].'apb_bookmark_class.php');
include_once($APB_SETTINGS['apb_path'].'apb_group_class.php');
include_once($APB_SETTINGS['apb_path'].'apb_view_class.php');

/* Create some of our global variables */
$APB_SETTINGS['redirect_url']  = $APB_SETTINGS['apb_url'] . 'redirect.php';
$APB_SETTINGS['template_path'] = $APB_SETTINGS['apb_path'] . 'templates/';
$APB_SETTINGS['default_outer_file'] = 'default_outer.php';
$APB_SETTINGS['default_inner_file'] = 'default_inner.str';
// The default user_id that shows up for non-logged-in users is the lowest one.
// (v1.0.00 only supports one user, anyhow.)
$APB_SETTINGS['user_id'] = get_lowest_user_id();

/* Information about APB, please don't change this. */
$APB_SETTINGS['program_home_url'] = 'http://lbstone.com/apb/';
$APB_SETTINGS['version']          = '1.1.02';
$APB_SETTINGS['program_name']     = 'Active PHP Bookmarks';

// By default we allow the login button to be displayed. [LBS 20020211]
$APB_SETTINGS["allow_login"] = 1;

/* Authentication */
global $user_id;
$APB_SETTINGS['auth_user_id'] = $user_id;
$APB_SETTINGS['user_id']      = $user_id;

if ($APB_SETTINGS['auth_user_id'] && $edit_mode) {
    $APB_SETTINGS['edit_mode'] = 1;
}

//###################################################
// function apb_bookmark
//
// USE: $my_bookmark = apb_bookmark($constructor);
//
// $constructor:
//    can be the id of a bookmark (integer)
//     or
//    an result returned from mysql_fetch_assoc($result)
//
// This function keeps track of created bookmark
// objects in the $apb_bookmarks[] array.  If a bookmark
// object exists in the array, it returns it; otherwise
// it creates the objects, sticks it in the array, and
// returns the object to the caller.  This cuts down
// on uneeded SQL
//

function apb_bookmark ($constructor) {

    global $apb_bookmarks, $APB_SETTINGS;
    $APB_SETTINGS['log_start'] = "apb_bookmark()";

    if (is_array($constructor)) {
        $bookmark_id = $constructor['bookmark_id'];
        if (!$apb_bookmarks[$bookmark_id]) {
            $apb_bookmarks[$bookmark_id] = new Bookmark($bookmark_id);
        }
    } else {
        $bookmark_id = $constructor;
        if (!$apb_bookmarks[$bookmark_id]) {
            $apb_bookmarks[$bookmark_id] = new Bookmark($bookmark_id);
        }
    }

    $APB_SETTINGS['log_start'] = "";
    return $apb_bookmarks[$bookmark_id];
}


//###################################################
//
// apb_group =  apb_bookmark
// apb_group =~ s/bookmark/group/g
//
// i.e. see apb_bookmark
//

function apb_group ($constructor) {
    global $apb_groups, $APB_SETTINGS;
    $APB_SETTINGS['log_start'] = "apb_group()";

    if (is_array($constructor)) {
        $group_id = $constructor['group_id'];
        if (!$apb_groups[$group_id]) {
            $apb_groups[$group_id] = new Group($group_id);
        }
    } else {
        $group_id = $constructor;
        if (!$apb_groups[$group_id]) {
            $apb_groups[$group_id] = new Group($group_id);
        }
    }

    $APB_SETTINGS['log_start'] = "";
    return $apb_groups[$group_id];
}

function return_username ($id) {
    global $APB_SETTINGS;

    $query = "SELECT last_name, first_names FROM users WHERE user_id = $id";
    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
    $row = mysql_fetch_assoc($result);
    $username =  $row['first_names'] . " " . $row['last_name'];
    return $username;
}

function directory_view ($group_id=0) {

    if ($group_id == 0) {
        global $APB_SETTINGS;

        $query = "
            SELECT g.group_id
              FROM apb_groups g
             WHERE g.group_parent_id = $group_id
               AND g.user_id = ".$APB_SETTINGS['user_id']."
          ORDER BY g.group_title
        ";
        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
        while ($row = mysql_fetch_assoc($result)) {

            $g = apb_group($row['group_id']);

            if ($g->number_of_child_groups() || $g->number_of_bookmarks()) {
                $lines .= "<b>".$g->link()."</b><br>\n";
            }

            $query = "
                SELECT g.group_id, count(*) as total
                  FROM apb_groups g
                       LEFT JOIN apb_bookmarks b ON (b.group_id = g.group_id)
                 WHERE g.group_parent_id = $g->id
                   AND g.user_id = ".$APB_SETTINGS['user_id']."
              GROUP BY g.group_id
              ORDER BY total DESC
                 LIMIT 3
            ";
            if ($result2 = mysql_db_query($APB_SETTINGS['apb_database'], $query)) {
                $temp_loop_count = 0;
                while ($row2= mysql_fetch_assoc($result2)) {
                    $child_group = apb_group($row2['group_id']);
                    if ($temp_loop_count > 0) { $lines .= ", "; }
                    $temp_loop_count++;
                    $lines .= $child_group->link();
                }
                if ($g->number_of_child_groups() > 3) { $lines .=  "..."; }
                $lines .= "<p>\n\n";
            }
        }
    } else {

        $g = apb_group($group_id);
        $g->print_group_path();
        $lines .= "<p>\n";

        if ($g->number_of_child_groups() > 0) {
            $lines .=  _("Categories") . "<p>\n";
            $g->print_group_children();
            $lines .=  "<p>\n";
        }

        if ($g->number_of_bookmarks() > 0) {
            $lines .=  _("Site Listings") . "<p>\n";
            $g->print_group_bookmarks();
        }

    }

    return $lines;
}

//###################################################
// function groups_dropdown
//
// prints a dropdown of all groups, with the ids as
// the <option> values and $name as the name used
// in the <select>
//

function groups_dropdown ($name, $selected_id='0', $null_name='', $dont_show=0) {

	global $APB_SETTINGS;

	print "<select name='$name'>\n";

    if ($null_name) {
        $lines .= "<option value='0'" . (($selected_id == 0) ? 'SELECTED' : '') . ">".$null_name."\n";
    }

	$query = "
		SELECT g.group_id, g.group_parent_id, g.group_title
		  FROM apb_groups g
		 WHERE g.group_parent_id = 0
           AND g.user_id = " . $APB_SETTINGS['user_id'] . "
           AND g.group_id != $dont_show
	  ORDER BY g.group_title
	";
	$result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

	while ($row = mysql_fetch_assoc($result))
	{
		$lines .=
            "<option value='" . $row['group_id'] . "'" .
            (($selected_id == $row['group_id']) ? ' SELECTED' : '') .
            "> " . $row['group_title']. "\n";
		do_group_dropdown_children($row[group_id], 3, $selected_id, $dont_show);
	}

    $lines .= "</select>\n\n";
    return $lines;
}

//###################################################
// function do_group_dropdown_children
//
// Used by groups_dropdown()
// This function is recursive.
//

function do_group_dropdown_children ($id, $count, $selected_id=0, $dont_show=0) {

	global $APB_SETTINGS;

    $query = "
        SELECT g.group_id, g.group_parent_id, g.group_title
		  FROM apb_groups g
		 WHERE g.group_parent_id = $id
           AND g.user_id = " . $APB_SETTINGS['user_id'] . "
           AND g.group_id != $dont_show
	  ORDER BY g.group_title
    ";

    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

    while ($row = mysql_fetch_assoc($result)) {

		$string = str_repeat("&nbsp;", $count);
		$string .= "- $row[group_title]";

	$lines .=	
            "<option value='" . $row[group_id] . "'" .
            (($selected_id == $row['group_id']) ? ' SELECTED' : '') .
            ">" . $string . "\n";

		$count += 3;
        do_group_dropdown_children($row[group_id], $count, $selected_id, $dont_show);
		$count -= 3;
    }

return $lines;
}



function top_groups($group_count=5, $bookmark_count=5, $since_n_interval=7) {
    global $APB_SETTINGS;

    // Get a list of top level groups.
    $query = "
        SELECT g.group_id
          FROM apb_groups g
         WHERE g.group_parent_id = '0'
           AND g.user_id = " . $APB_SETTINGS['user_id'] . "
    ";

    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);

    $top_level_group_list = array();

    while ($row = mysql_fetch_assoc($result)) {
        $g = apb_group($row['group_id']);
        $child_groups = $g->return_child_groups();
        $group_list = join(", ", $child_groups);

        if ($APB_SETTINGS['auth_user_id'] && ($APB_SETTINGS['user_id'] == $APB_SETTINGS['auth_user_id'])) {
            $private_sql = "1 = 1";
        } else {
            $private_sql = "b.bookmark_private = 0";
        }

        // Get total hits for each top level group for a period of time.
        $query2 = "
            SELECT count(*) AS total
              FROM apb_hits h, apb_bookmarks b
             WHERE h.bookmark_id = b.bookmark_id
               AND $private_sql
               AND b.bookmark_deleted != 1
               AND h.user_id = " . $APB_SETTINGS['user_id'] . "
               AND b.group_id IN ($group_list)
               AND h.hit_date > DATE_SUB(NOW(), INTERVAL $since_n_interval DAY)
        ";

        $result2 = mysql_db_query($APB_SETTINGS['apb_database'], $query2);
        $row2 = mysql_fetch_array($result2);
        $top_level_group_list[$row['group_id']] = $row2['total'];
    }

    arsort($top_level_group_list);

    $c = 0;
    while (list ($gid, $hits) = each ($top_level_group_list)) {
        if ($count == $group_count) {
            break;
        }
        if ($hits > 0) {
            $g = apb_group($gid);
            $v = new TopInHierarchyView();
            $v->group_list = join(", ", $g->return_child_groups());
            $v->title_string = $g->link("FFFFFF");
            $v->template = 'home_topingroup';
            $v->limit = $bookmark_count;
            $v->since_n_interval = $since_n_interval;
            $lines .= $v->output();
        }
        $count++;
    }
    return $lines;
}


function get_number_of_users() {
    global $APB_SETTINGS;

    $query = "
        SELECT count(*) as total
          FROM apb_users u
    ";

    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
    $row = mysql_fetch_assoc($result);
    return $row['total'];
}


function get_number_of_bookmarks() {
    global $APB_SETTINGS;

    // Altered this query to check for bookmark_deleted [LBS 20020211]
    $query = "
        SELECT count(*) as total
          FROM apb_bookmarks b
         WHERE b.user_id = " . $APB_SETTINGS['user_id'] . " AND
               bookmark_deleted = 0
    ";
//echo "<pre>$query</pre>";
    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
    $row = mysql_fetch_assoc($result);
    return $row['total'];
}

// Tells you the number of existing groups for a particular user. [LBS 20020211]
function get_number_of_groups() {
    global $APB_SETTINGS;

    $query = "
        SELECT count(*) as total
          FROM apb_groups g
         WHERE g.user_id = " . $APB_SETTINGS['user_id'] . " AND
               group_deleted = 0
    ";
//echo "<pre>$query</pre>";
    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
    $row = mysql_fetch_assoc($result);
    return $row['total'];
}

function apb_foot() {
    global $APB_SETTINGS;
    global $cookie_username;
    global $QUERY_STRING;
    global $id;
    global $action;
    global $keywords;
    global $edit_mode;
    global $date;

    include($APB_SETTINGS['template_path'] . 'foot.php');
}

function debug ($string, $level = 3) {
    global $APB_SETTINGS;
    if ($APB_SETTINGS['debug'] >= $level) {
        $debug_string = "<p><b>DEBUG: </b>";
        $debug_string .= "$string</p>\n";
        print $debug_string;
    }
}

function error ($string) {
    global $ABP_SETTINGS;

    $error_string = "ERROR: ";
    if ($APB_SETTINGS['log_start']) {
        $error_string .= $APB_SETTINGS['log_start'] . ": ";
    }
    $error_string .= "$string\n";

    print $error_string;

    if ($ABP_SETTINGS['log_path']) {
        $fp = fopen($ABP_SETTINGS['log_path'], 'a');
        fwrite($fp, $error_string);
        fclose($fp);
    }

}

function get_lowest_user_id()
{
    global $APB_SETTINGS;

    $query = "
        SELECT min(user_id) as min_user_id
        FROM users
        GROUP BY user_id
    ";
    $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
    $row = mysql_fetch_assoc($result);

    $output = $row['min_user_id'];

    // We're assuming that if there's no user_id returned then 0 is a good
    // "no user" number. [LBS 20020211]
    if (!$output) { $output = 0; }

    return $output;
}

?>
