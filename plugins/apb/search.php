<?

//####################################################################
// Active PHP Bookmarks - lbstone.com/apb/
//
// Filename: search.php
// Authors:  L. Brandon Stone (lbstone.com)
//           Nathanial P. Hendler (retards.org)
//
// 2001-10-28 00:04     Starting on search for version 1.0 (NPH)
//
//####################################################################

include_once('apb.php');
apb_head();

$APB_SETTINGS['allow_edit_mode'] = 1;

// Clean up the data that's been passed to us [LBS 20020211].
$keywords = trim($keywords);
$keywords = preg_replace("/ +/", " ", $keywords);

$columns       = array('b.bookmark_url', 'b.bookmark_description', 'b.bookmark_title');
$group_columns = array('g.group_title', 'g.group_description');

$words = split(" ", $keywords, 8);
$total_bookmarks = get_number_of_bookmarks();

if ($APB_SETTINGS['auth_user_id']) {
    $private_sql = "";
} else {
    $private_sql = "AND b.bookmark_private = 0";
}


foreach ($words as $search_string) {

    // This doesn't do anything helpful yet...
    if (preg_match("/^-/", $search_string)) {
        print "<b>NOT</b> $search_string<br>\n";
    }

    /******************************/
    /* Look for Groups that Match */

    foreach ($group_columns as $column) {
        $query = "
            SELECT g.group_id, g.group_title
              FROM apb_groups g
             WHERE ($column LIKE '%$search_string%')
               AND g.user_id = " . $APB_SETTINGS['user_id'] . "
             #$private_sql
        ";

        #print "<p><pre>$query</pre><p>\n\n";

        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
        $total_rows = mysql_num_rows($result);

        while ($row = mysql_fetch_assoc($result)) {
            $mod = 1;
            #$group_results[$row[group_id]] += (( 2 * ( 100 - (($total_rows/$total_groups) * 100) ) ) / $mod);
            $group_results[$row[group_id]]++;
        }
    }

    /*********************************/
    /* Look for Bookmarks that Match */

    foreach ($columns as $column) {
        $query = "
            SELECT b.bookmark_id, b.bookmark_description, b.bookmark_url, g.group_title
              FROM apb_bookmarks b
                   LEFT JOIN apb_groups g ON (g.group_id = b.group_id)
             WHERE ($column LIKE '%$search_string%')
               AND b.user_id = " . $APB_SETTINGS['user_id'] . "
               AND b.bookmark_deleted != 1
             $private_sql
        ";

        #print "<p><pre>$query</pre><p>\n\n";

        $result = mysql_db_query($APB_SETTINGS['apb_database'], $query);
        $total_rows = mysql_num_rows($result);

        while ($row = mysql_fetch_assoc($result)) {
            if ($column == 'b.bookmark_url') {
                $mod = 1.5;
            } else {
                $mod = 1;
            }
            $results[$row[bookmark_id]] += (( 2 * ( 100 - (($total_rows/$total_bookmarks) * 100) ) ) / $mod);
        }
    }
}

$keyword = htmlspecialchars(stripslashes($keyword));
$number_of_results = count($results) + count($group_results);

print "<h2>Search Results</h2>";
if ($number_of_results == 1) { $s = ""; }
else { $s = "s"; }

// Added all the entities stripslashes stuff to the search results. [LBS 20020211]
print "<p><b>$number_of_results</b> result".$s." for \"<b>".htmlentities(stripslashes($keywords))."</b>\"";

?>
   <!-- Search Box -->

   <p>
   <form>
   <input type='hidden' name='action' value='search'>
   <input name='keywords' value="<?php echo htmlentities(stripslashes($keywords)) ?>">
   <input type='submit' name='Submit' value='Search'>
   </form>
<?

print "<p><table align='center' cellpadding='0' cellspacing='0' border='0'><tr><td>\n";

if ($group_results) {

    print "<p><b>Group Matches</b></p>\n\n";

    echo "<ul>";
	while(list($id, $score) = each ($group_results)) {
        $g = apb_group($id);
        print "<li>";
        #print $g->link();
        #print " (Home :: " . $g->get_group_path() . ")";
        $g->print_group_path();

        if ($g->description()) {
            print " - ".$g->description();
        }
        print "\n";
	}
    echo "</ul>";

}

if ($results) {
	arsort($results);
	reset($results);

    print "<p><b>Site Matches</b></p>\n\n";

    echo "<ul>";
	while(list($id, $score) = each ($results)) {
        $b = apb_bookmark($id);
        $g = apb_group($b->group_id());
        print "<li>";
        #print "<tt>[$score]</tt> ";
        print $b->link() . " <font size='1'>(" . $g->link() . ")</font> ";
        if ($b->description()) {
            print " - ".$b->description();
        }
        print "\n";
	}
    echo "</ul>";
}

print "</td></tr></table>\n\n";

apb_foot();

?>
