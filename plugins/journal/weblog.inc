<?php

# Personal Weblog 1.0.0
#
# Copyright 2001,2002  Mark Pulford <mark@kyne.com.au>
# This file is subject to the terms and conditions of the GNU General Public
# License. Read the file COPYING found in this archive for details, or visit
# http://www.gnu.org/copyleft/gpl.html
#
# See http://www.kyne.com.au/~mark/software/weblog.php for the latest version.
#
# $Id: weblog.inc,v 1.1 2004/11/09 03:41:19 gpowers Exp $
#

global $session_user_id;

class DB_MySQL {
	var $dbh, $name;

	function query($sql)
	{
		# Must ensure correct DB is selected. If some other PHP
		# code has run it might have changed the db in between
		# Weblog() and insert().
		if (!mysql_select_db($this->name))
			return;

		return @mysql_query($sql, $this->dbh);
	}

	function fetch($r)
	{
		return mysql_fetch_array($r);
	}

	function lock($tables)
	{
		if (count($tables) < 1)
			return false;

		foreach ($tables as $t) {
			if ($q)
				$q .= ", ";
			$q .= "$t WRITE";
		}

		return $this->query("LOCK TABLES $q");
	}

	function unlock()
	{
		return $this->query("UNLOCK TABLES");
	}

	function affected_rows($r)
	{
		return mysql_affected_rows($this->dbh);
	}

	function num_rows($r)
	{
		return mysql_num_rows($r);
	}

	function error()
	{
		return mysql_error($this->dbh);
	}

	function connected()
	{
		return $this->dbh ? true : false;
	}

	function DB_MySQL($name, $host, $user, $pass)
	{
		$this->name = $name;
		$this->dbh = @mysql_connect($host, $user, $pass);

		# Ensure the database exists
		if (!mysql_select_db($name))
			return false;
	}
}

class DB_pg {
	var $dbh;
	var $row;

	function query($sql)
	{
		# Hack to convert a couple of MySQLisms into PostgreSQL
		if (eregi("^create table", $sql) or
		    eregi("^insert into vars", $sql))
			$sql = strtr($sql, array(
				"integer not null auto_increment" => "serial",
				"enum ('no', 'single', 'multiple')"
					=> "varchar(10)",
				"unix_timestamp()" => "'" . time() . "'"));

		# Re order LIMIT statements.
		if (eregi("^(select.+limit +)([^ ,]+)( *, *)([^ ]+)(.*)$",
			  $sql, $t))
			$sql = $t[1] . $t[4] . $t[3] . $t[2] . $t[5];

		$r = @pg_exec($this->dbh, $sql);
		$this->row[$r] = 0;
		return $r;
	}

	function fetch($r)
	{
		if ($this->row[$r] >=  pg_numrows($r))
			return;
		return pg_fetch_array($r, $this->row[$r]++);
	}

	function lock($tables)
	{
		return $this->query("BEGIN");
	}

	function unlock()
	{
		return $this->query("COMMIT");
	}

	function affected_rows($r)
	{
		return pg_cmdtuples($r);
	}

	function num_rows($r)
	{
		return pg_numrows($r);
	}

	function error()
	{
		return pg_errormessage($this->dbh);
	}

	function connected()
	{
		return $this->dbh ? true : false;
	}

	function DB_pg($name, $host, $user, $pass)
	{
		$this->row = array();
		$host = $host ? "host=$host " : "";
		$name = $name ? "dbname=$name " : "";
		$user = $user ? "user=$user " : "";
		$pass = $pass ? "password=$pass " : "";
		$this->dbh = @pg_connect("$host$name$user$pass");
	}
}

class Weblog {
	var $admin;
	var $db;
	var $dbvar;
	var $broken;
	var $valid_cgi;
	var $valid_topics;
	var $p;
	var $self;

	# Acceptable cgi variables (when prefix with "wl_")
	var $mode, $eid, $tid, $password, $name, $icon, $value, $title, $body;
	var $more, $update, $offset, $search, $topic, $delopt, $before, $start;

	# Print weblog HTML
	function insert()
	{
		if ($this->broken) {
			$this->print_error("Cannot insert weblog HTML, Weblog is not initialised");
			return;
		}
	
		echo $this->getvar("header") . "\n";

		$mode_fn = array(
			"more" => "entry_display",
			"archive" => "entry_archive",
			"edit setup" => "setup_edit",
			"save setup" => "setup_save",
			"edit entry" => "entry_edit",
			"preview entry" => "entry_preview",
			"save entry" => "entry_save",
			"delete entry" => "entry_delete",
			"delete topic" => "topic_delete",
			"change topic" => "topic_update",
			"add topic" => "topic_add",
			"edit topics" => "topic_edit",
			"login" => "login",
			"logout" => "logout"
		);

		# The configurable buttons have varying mode names
		$mode_fn[$this->getvar("search_text")] = "entry_search";
		$mode_fn[$this->getvar("reset_text")] = "entry_reset";

		if (!isset($this->mode))
			$this->entry_search();
		else if (isset($mode_fn[$this->mode]))
			$this->$mode_fn[$this->mode]();
		else
			$this->print_error("Unknown weblog mode");

		echo $this->getvar("footer") . "\n";
	}

	# Called to ensure the user is logged in, and also displays
	# a login screen where necessary.
	function admin($login = false)
	{
		if (!$this->admin) {
			// $this->admin(true);
			$login = true;
/*
			if ($login)
				echo "<h2>Login</h2>\n";
			
			if (isset($this->password))
				$this->print_error("Incorrect password");
			else if (!$login)
				$this->print_error("Not logged in");

			echo "<p><form action=\"{$this->self}\" method=\"post\">\n";
			$this->print_form_vars(array_diff($this->valid_cgi,
							  array("password")));
			echo "<input type=\"password\" name=\"wl_password\">\n";
			echo "<input type=\"submit\" value=\"Login\">\n";
			echo "</form></p>\n";
			$this->print_back();
*/
		}
		// return $this->admin;
		return true;
	}

	# Returns a query string containing the current search
	# parameters.
	function search_string()
	{
		# PHP doesn't support "undef" like Perl hence the variable
		# hack..
		return $this->query_string(array("offset" => $undef,
						 "topic" => $undef,
						 "search" => $undef));
	}

	function query_string($arr)
	{
		$vars = array();
		foreach ($arr as $k => $v) {
			if (!isset($v))
				$v = $this->$k;
			$v = str_replace(" ", "+", htmlspecialchars($v));
			if ($v)
				array_push($vars, "wl_$k=$v");
		}

		return join("&", $vars);
	}

	function href_link($arr, $text)
	{
		$q = $this->query_string($arr);
		if ($q)
			$q = "?$q";
		$q = htmlspecialchars("{$this->self}$q");
		return "<a href=\"$q\">$text</a>";
	}

	function back_link()
	{
		$q = $this->search_string();
		if ($q)
			$q = "?$q";
		$q = htmlspecialchars("{$this->self}$q");
		$bl = $this->getvar("backlink");
		return str_replace("@URL@", $q, $bl);
	}

	# Print a link back to the main page while maintaining
	# current search parameters.
	function print_back()
	{
		echo $this->back_link();
	}

	# Reset the weblog last modified time.
	# Called whenever an entry/topic is added/changed/deleted or
	# settings are updated.
	function update_last_modified()
	{
		if ($this->getvar("last_modified") != -1)
			$this->setvar("last_modified", time());
	}

	# CGI mode: "change topic"
	# Uses: tid, name, icon
	function topic_update()
	{
		echo "<h2>Updating topic</h2>\n";

		if (!$this->admin())
			return;

		if (!$this->tid) {
			$this->print_error("No topic selected");
			$this->print_back();
			return;
		}

		if ($this->name)
			$terms = "name='{$this->name}'";
		if ($this->icon) {
			if ($terms)
				$terms .= ", ";
			$terms .= "icon='{$this->icon}'";
		}
		if (!$terms) {
			$this->print_error("At least one of icon or name must be set");
			$this->print_back();
			return;
		}

		$this->update_last_modified();

		$r = $this->dbquery("UPDATE {$this->p}topics SET $terms WHERE tid={$this->tid}");
		if (!$r)
			return;
		if ($this->db->affected_rows($r))
			echo "<p><b>Topic changed</b></p>\n";
		else
			echo "<p><b>Topic not changed</b></p>\n";

		$this->print_back();
	}

	# CGI mode: "add topic"
	# Uses: name, icon
	function topic_add()
	{
		echo "<h2>Adding topic</h2>\n";
                global $session_user_id;

		if (!$this->admin())
			return;

		if (!$this->name) {
			$this->print_error("Name must be set");
			$this->print_back();
			return;
		}

		$this->update_last_modified();

		$r = $this->dbquery("INSERT INTO {$this->p}topics (name, icon, user_id) VALUES ('" . addslashes($this->name) . "', '" . addslashes($this->icon) . "', '" . $session_user_id . "')");

		if (!$r)
			return;
		if ($this->db->affected_rows($r))
			echo "<p><b>Topic added</b></p>\n";
		else
			$this->print_error("Failed to add topic");

		$this->print_back();
	}

	# CGI mode: "delete topic"
	# Uses: tid, delopt
	function topic_delete()
	{
		echo "<h2>Deleting topic</h2>\n";

		if (!$this->admin())
			return;

		if (!$this->tid) {
			$this->print_error("No topic selected");
			$this->print_back();
			return;
		}

		if ($this->tid == $this->delopt) {
			$this->print_error("Cannot move entries into the topic about to be deleted!");
			$this->print_back();
			return;
		}

		# Verify "delopt" exists unless it is zero
		# 0 => use "tid" for a dummy lookup.
		$v = $this->delopt;
		if (!$v)
			$v = $this->tid;
		$tname = $this->topic_verify($v);
		if (!$tname)
			return;

		$this->update_last_modified();

		if ($this->delopt) {
			$q = "UPDATE {$this->p}entries SET tid={$this->delopt} WHERE tid={$this->tid}";
			echo "<p>Moving entries to $tname</p>\n";
		} else {
			$q = "DELETE FROM {$this->p}entries WHERE tid={$this->tid}";
			echo "<p>Deleting any existing entries for $tname\n";
		}

		if (!$this->dbquery($q))
			$this->dbunlock();
		else if (!$this->dbquery_unlock("DELETE FROM {$this->p}topics WHERE tid={$this->tid}", $aff))
			return;

		if ($aff)
			echo "<p><b>Topic deleted</b></p>\n";
		else
			$this->print_error("No topic found to delete");

		$this->print_back();
	}

	# Print hidden fields to maintain current cgi variable
	# values through html form
	function print_form_vars($cgi_names)
	{
		foreach ($cgi_names as $v) {
			if (isset($this->$v)) {
				if (is_array($this->$v)) {
					foreach ($this->$v as $k2 => $v2)
						echo "<input type=\"hidden\" name=\"wl_{$v}[$k2]\" value=\"" . htmlspecialchars($v2) . "\" />\n";
				} else {
					echo "<input type=\"hidden\" name=\"wl_$v\" value=\"" . htmlspecialchars($this->$v) . "\" />\n";
				}
			}
		}
	}

	# Maintain search parameters through html form
	function print_search_details()
	{
		$this->print_form_vars(array("offset", "topic", "search"));
		// $this->print_form_vars(array("offset", "search"));
	}

	# CGI mode: "edit topics"
	# Uses: none
	function topic_edit()
	{
		echo "<h2>Edit topics</h2>\n";
                global $session_user_id;

		if (!$this->admin())
			return;

		$tsel = $this->topic_selectbox("delopt", -1, "Delete");
		if (!$tsel)
			return;
		$r = $this->dbquery("SELECT * FROM {$this->p}topics WHERE user_id=$session_user_id");
		if (!$r)
			return;
		echo "<p><form action=\"{$this->self}\" method=\"post\">\n";
		$this->print_search_details();
		$r = $this->dbquery("SELECT * FROM {$this->p}topics where user_id = $session_user_id");
		echo "<tr><th>Id</th><th>Name</th>";
		echo "</tr>\n";
		while ($a = $this->dbfetch($r)) {
			echo "<tr>";
			echo "<td>$a[tid]</td>\n";
			echo "<td>$a[name]</td>\n";
			echo "<td><input type=\"radio\" name=\"wl_tid\" value=\"$a[tid]\" /></td>";
			echo "</tr>\n";
		}
		echo "<td colspan=\"3\" align=\"right\">Select none</td>\n";
		echo "<td><input type=\"radio\" name=\"wl_tid\" value=\"\" checked /></td>";
		echo "</table>\n";
		echo "<table><tr>\n";
		echo "<td>Name:</td><td><input type=\"text\" name=\"wl_name\" maxlength=\"80\" /></td>\n";
		echo "</tr><tr>\n";
		echo "</tr></table>\n";
		echo "<input type=\"submit\" name=\"wl_mode\" value=\"change topic\" />\n";
		echo "<input type=\"submit\" name=\"wl_mode\" value=\"add topic\" /><br />\n";
		echo "Move or delete entries currently in topic: ";
		echo $tsel;
		echo "<input type=\"submit\" name=\"wl_mode\" value=\"delete topic\" />\n";
		echo "</form></p>\n";
		$this->print_back();
	}

	# CGI mode: "save setup"
	# Uses: value
	function setup_save()
	{
		echo "<h2>Updating configuration</h2>\n";

		if (!$this->admin())
			return;

		foreach ($this->value as $k => $v)
			$this->setvar($k, $v);

		$this->update_last_modified();

		echo "<p><b>Configuration saved</b></p>\n";

		$this->print_back();
	}

	# CGI mode: "edit setup"
	# Uses: none
	function setup_edit()
	{
		echo "<h2>Edit weblog setup</h2>\n";

		if (!$this->admin())
			return;

		$r = $this->dbquery("SELECT * FROM {$this->p}vars WHERE editable!='no' ORDER BY name ASC");
		if (!$r)
			return;

		echo "<p><form action=\"{$this->self}\" method=\"post\">\n";
		$this->print_search_details();
		while ($a = $this->dbfetch($r)) {
			echo "<h2>$a[name]</h2>\n";
			if ($a[help])
				echo "<p>$a[help]</p>\n";
			echo "<p>";
			if ($a[editable] == "single") {
				echo "<input type=\"text\" name=\"wl_value[$a[name]]\" value=\"" . htmlspecialchars($a[value]) . "\" size=\"60\" />\n";
			} else {
				echo "<textarea name=\"wl_value[$a[name]]\" rows=\"5\" cols=\"60\" wrap=\"virtual\">" . htmlspecialchars($a[value]) . "</textarea>\n";
			}
			echo "</p>";
		}
		echo "<p><input type=\"submit\" name=\"wl_mode\" value=\"save setup\" /></p>\n";
		echo "</form></p>\n";
		$this->print_back();
	}

	# Load a weblog entry overriding current title, tid, body, more
	# values
	function entry_load($eid)
	{
		$r = $this->dbquery("SELECT * FROM {$this->p}entries WHERE eid=$eid");
		if (!$r)
			return false;
		if (!$this->db->num_rows($r)) {
			$this->print_error("The entry was not found");
			$this->print_back();
			return false;
		}

		$a = $this->dbfetch($r);
		foreach (array("title", "tid", "body", "more") as $v)
			$this->$v = $a[$v];

		return true;
	}

	# CGI mode: "edit entry"
	# Uses: eid, tid, title, body, more, update
	# Edit a DB entry. The entry can be given from CGI variables,
	# start blank, or be loaded from the DB.
	function entry_edit()
	{
		echo "<h2>Add Entry</h2>\n";

		if (!$this->admin())
			return;

		if (isset($this->eid) and !isset($this->tid))
			if (!$this->entry_load($this->eid))
				return;
		$tsel = $this->topic_selectbox("tid", $this->tid, "");
		if (!$tsel)
			return;

		echo "<p><form action=\"{$this->self}\" method=\"post\">\n";
		$this->print_search_details();
		if (isset($this->eid))
			echo "<input type=\"hidden\" name=\"wl_eid\" value=\"{$this->eid}\" />\n";
		echo "Title: <input type=\"text\" name=\"wl_title\" value=\"" . htmlspecialchars($this->title) . "\" maxlength=\"200\" size=\"60\" /><br />\n";
		// echo "Topic:&nbsp;";
		// echo $tsel;
		echo "<input type=\"hidden\" name=\"tid\" value=\"1\"";
		echo "<br />\n";
		echo "Summary:<br />\n";
		echo "<textarea name=\"wl_body\" rows=\"10\" cols=\"60\" wrap=\"virtual\">" . htmlspecialchars($this->body) . "</textarea><br />\n";
		echo "Details:<br />\n";
		echo "<textarea name=\"wl_more\" rows=\"10\" cols=\"60\" wrap=\"virtual\">" . htmlspecialchars($this->more) . "</textarea><br />\n";
		# Only offer "update" when editing an existing entry
		if (isset($this->eid)) {
			echo "<input type=\"checkbox\" name=\"wl_update\" value=\"yes\"";
			if ($this->update == "yes")
				echo " checked";
			echo " /> Update timestamp<br />\n";
		}
		echo "<input type=\"submit\" name=\"wl_mode\" value=\"save entry\" /><br />\n";
		echo "</form></p>\n";
		// $this->print_back();
	}

	# CGI mode: "preview entry"
	# Uses: eid, tid, title, body, more, update
	function entry_preview()
	{
		echo "<h2>Preview entry</h2>\n";

		if (!$this->admin())
			return;

		if ($this->eid)
			$r = $this->dbquery("SELECT created,updated,name,icon FROM {$this->p}entries,{$this->p}topics WHERE eid={$this->eid} AND {$this->p}topics.tid={$this->tid}");
		else
			$r = $this->dbquery("SELECT name,icon FROM {$this->p}topics WHERE tid={$this->tid}");
		if (!$r)
			return;
		$a = $this->dbfetch($r);

		# New entry? Set times. Otherwise check for update.
		$tm = time();
		if (!$a[created]) {
			$a[created] = $tm;
			$a[updated] = $tm;
		} else if ($this->update == "yes") {
			$a[updated] = $tm;
		}

		# Set remaining vars
		foreach (array("eid", "tid", "title", "body", "more") as $v)
			$a[$v] = $this->$v;

		echo $this->subst_tokens($this->getvar("more"), $a, false) . "\n";

		echo "<p><form action=\"{$this->self}\" method=\"post\">\n";
		$this->print_search_details();
		foreach (array("eid", "tid", "title", "body", "more", "update") as $v)
			if (isset($this->$v))
				echo "<input type=\"hidden\" name=\"wl_$v\" value=\"" . htmlspecialchars($this->$v) . "\" />\n";
		echo "<input type=\"submit\" name=\"wl_mode\" value=\"edit entry\" />\n";
		echo "<input type=\"submit\" name=\"wl_mode\" value=\"save entry\" />\n";
		echo "</form></p>\n";

		$this->print_back();
	}

	# CGI mode: "save entry"
	# Uses: eid, tid, title, body, more, update
	function entry_save()
	{
		echo "<h2>Saving entry</h2>\n";
                global $session_user_id;

		if (!$this->admin())
			return;

		// if (!$this->topic_verify($this->tid))
			// return;
	$tid = 1; 	
		$this->update_last_modified();

		$tm = time();
		if ($this->eid) {
			$q = "UPDATE {$this->p}entries SET title='" .
				addslashes($this->title) .
				"', tid={$this->tid}, body='" .
				addslashes($this->body) . "', more='" .
				addslashes($this->more) . "'";
			if ($this->update == "yes")
				$q .= ", updated=$tm";
			$q .= " WHERE eid={$this->eid}";
		} else {
			$q = "INSERT INTO {$this->p}entries (user_id, title, tid, created, updated, body, more) VALUES (" .
				$session_user_id
                                . ", '" . addslashes($this->title) .
				// "', {$this->tid}, $tm, $tm, '" .
				"', $tid, $tm, $tm, '" .
				addslashes($this->body) . "', '" .
				addslashes($this->more) . "')";
		}

		if (!$this->dbquery_unlock($q, $aff))
			return;

		if ($this->eid and !$aff) {
			echo "<p><b>Entry not changed</b></p>\n";
		} else {
			echo "<p><b>Entry saved</b></p>\n";

			# Call add/edit entry hook
			if (!$this->entry_save_local(!isset($this->eid), $this->title, $this->body, $this->more, $tm))
				$this->print_error("Local entry save hook failed");
		}

		$this->print_back();
	}

	# Hook to extend functionality when adding/updating an entry
	# $new is true when the entry has been added.
	function entry_save_local($new, $title, $body, $more, $tm)
	{
		return true;
	}

	# CGI mode: "delete entry"
	# Uses: eid
	function entry_delete()
	{
		echo "<h2>Deleting entry</h2>\n";

		if (!$this->admin())
			return;

		$this->update_last_modified();

		$r = $this->dbquery("DELETE FROM {$this->p}entries WHERE eid={$this->eid}");

		if (!$r)
			return;
		if ($this->db->affected_rows($r) == 1)
			echo "<p><b>Entry deleted</b></p>\n";
		else
			$this->print_error("No entry found to delete");

		$this->print_back();
	}

	# CGI mode: special ("reset_text" variable)
	# Uses: none
	function entry_reset()
	{
		unset($this->topic);
		unset($this->search);
		unset($this->offset);

		$this->entry_search();
	}

	# Returns a select query limiting entries based on CGI variables
	# and topic restriction. Further conditions can be appended.
	function select_query($colspec = "*")
	{
                global $session_user_id;
		$q = "SELECT $colspec FROM {$this->p}entries,{$this->p}topics WHERE {$this->p}entries.tid={$this->p}topics.tid AND {$this->p}entries.user_id=$session_user_id ";
		if (!$this->admin and $this->valid_topics) {
			$q .= " AND {$this->p}entries.tid IN (" .
				join(",", $this->valid_topics) . ")";
		}

		if (0 < $this->topic)
			$q .= " AND {$this->p}topics.tid={$this->topic}";
		if ($this->search) {
			$keywords = split(" +", $this->search);
			foreach ($keywords as $k) {
				$sk = addslashes($k);	# currently redundant
				$q .= " AND ({$this->p}entries.title LIKE '%$sk%' OR {$this->p}entries.body LIKE '%$sk%' OR {$this->p}entries.more LIKE '%$sk%')";
			}
		}

		return $q;
	}

	# Avert your eyes..
	function empty_query()
	{
		return "SELECT * FROM {$this->p}entries WHERE 0=1";
	}

	# Returns or search string or NULL on a fatal error
	function generate_search()
	{
		if ($this->start) {
			$r = $this->dbquery($this->select_query() .
					    " AND eid={$this->start}");
			if (!$r)
				return;
			# If the entry was found use its timestamp as the
			# beginning of the log, otherwise don't limit it.
			if ($this->db->num_rows($r)) {
				$arr = $this->dbfetch($r);
				$this->before = $arr[updated];
			} else {
				return $this->empty_query();
			}
		}
		if ($this->before) {
			$r = $this->dbquery($this->select_query("count(*) AS offset") . " AND updated > {$this->before}");
			if (!$r)
				return;
			if ($this->db->num_rows($r)) {
				$arr = $this->dbfetch($r);
				$this->offset = $arr[offset];
			}
		}

		$q = $this->select_query() . " ORDER BY updated DESC";
		$off = $this->offset;
		$max = $this->getvar("max_articles");
		if (0 < $max) {
			if (!$off)
				$off = 0;

			# Search for an extra entry. Do not display the extra
			# entry, it is used to determine whether a next icon
			# is needed.
			$q .= " LIMIT $off, " . ($max+1);
		}

		return $q;
	}

	# CGI mode: special ("search_text" variable, default)
	# Uses: topic, offset, search, before, start
	function entry_search()
	{
		$sep = $this->getvar("separator");
		$max = $this->getvar("max_articles");

		$q = $this->generate_search();
		if (!$q)
			return;

		$r = $this->dbquery($q);
		if (!$r)
			return;
		$rows = $this->db->num_rows($r);

		if ($max > 0 and $rows > $max) {
			$next = $this->button_offset("next_html",
						     $this->offset + $max);
			$rows = $max;
		}

		if ($this->offset > 0)
			$prev = $this->button_offset("prev_html",
						     $this->offset - $max);

		if ($rows == 0) {
			echo "<b>No entry found.</b>\n";
			echo "$sep\n";
		} else {
			$this->print_entries("article", $r, $rows, "$sep\n");
		}

		$this->print_control($prev, $next);
	}

	function entry_archive()
	{
		$sep = $this->getvar("separator");
		# Temporarily disable limiting articles
		$this->dbvar[max_articles] = 0;

		$q = $this->generate_search();
		if (!$q)
			return;

		$r = $this->dbquery($q);
		if (!$r)
			return;
		$rows = $this->db->num_rows($r);

		if ($rows == 0) {
			echo "<b>No entries found.</b>\n";
			echo "$sep\n";
		} else {
			$this->print_entries("archive", $r, $rows, "\n");
		}
		echo "$sep\n";

		$this->print_control($prev, $next);
	}

	function entry_relative($time, $offset)
	{
		if ($offset < 0) {
			$cmp = ">";
			$dir = "ASC";
			$offset = abs($offset);
		} else if ($offset > 0) {
			$cmp = "<";
			$dir = "DESC";
		} else {
			return $id;
		}
		$offset--;

		$q = $this->select_query("eid") . " AND updated $cmp $time ORDER BY updated $dir LIMIT $offset,1";
		$r = $this->dbquery($q);

		if (!$this->db->num_rows($r))
			return;

		$arr = $this->dbfetch($r);
		return $arr[eid];
	}

	# CGI mode: "more"
	# Uses: eid
	function entry_display()
	{
		if (!$this->eid) {
			$this->print_error("Missing entry ID");
			$this->print_control(false, false);
			return;
		}
		$q = $this->select_query("*") . " AND eid={$this->eid}";
		$r = $this->dbquery($q);
		if (!$r)
			return;

		if (!$this->db->num_rows($r)) {
			echo "<b>No entry found.</b>\n";
		} else {
			$mf = $this->getvar("more");
			$arr = $this->dbfetch($r);
			echo $this->subst_tokens($mf, $arr) . "\n";
			$id = $this->entry_relative($arr[updated], -1);
			if ($id)
				$prev = $this->button_cycle("prev_html", $id);
			$id = $this->entry_relative($arr[updated], 1);
			if ($id)
				$next = $this->button_cycle("next_html", $id);
		}
		echo $this->getvar("separator") . "\n";

		$this->print_control($prev, $next);
	}

	# Print per entry admin menu maintaining search parameters
	function entry_print_admin($eid)
	{
		$q = $this->search_string();
		if ($q)
			$q = "&$q";
		$q = htmlspecialchars($q);
		$adm = "<br><a href=\"{$this->self}?wl_mode=edit+entry&amp;wl_eid=$eid$q\">[edit]</a>\n";
		$adm .= "<a href=\"{$this->self}?wl_mode=delete+entry&amp;wl_eid=$eid$q\">[delete]</a></p>\n";

		return $adm;
	}

	# Ensure all non string CGI variables are clean.
	# Strings are always slash escaped before passing to MySQL
	function sanitize_input()
	{
		# name, icon, title, body, more, value
		# - Used in db searches, but they are slash escaped
		# mode, password, update
		# - not used in db searches

		# Sanitize. remove non alphanumeric. shrink spaces.
		# remove prefix/suffix spaces.
		if (isset($this->search)) {
			$this->search = eregi_replace("[^a-z0-9 ]", "", $this->search);
			$this->search = eregi_replace(" +", " ", $this->search);
			$this->search = trim($this->search);
			# Only keep first 3 keywords
			# Do this here so the search box contains the updated
			# search string.
			if (ereg("([^ ]+ [^ ]+ [^ ]+) .*", $this->search, $reg)) {
				$this->search = $reg[1];
			}
		}
		# Ensure numeric CGI vars are valid.
		foreach (array("topic", "delopt", "tid", "eid", "offset",
			       "before", "start") as $v)
			if (isset($this->$v)) {
				$this->$v = eregi_replace("[^0-9]", "", $this->$v);
				# Ensure a blanked var will return 0 as a string
				if (!$this->$v)
					$this->$v = 0;
			}
	}

	# Print $rows entries from the MySQL search result $r
	function print_entries($format, $r, $rows, $sep)
	{
		$af = $this->getvar($format);

		for ($i=0; $i<$rows; $i++) {
			$arr = $this->dbfetch($r);
			echo $this->subst_tokens($af, $arr) . "\n";
			echo $sep;
		}
	}

	function subst_paragraph($s)
	{
		if ($this->getvar("paragraph_split")) {
			$para = explode("\r\n\r\n", $s);
			$s = "";
			foreach ($para as $p) {
				$s .= "<p>$p</p>\r\n";
			}
		}

		return $s;
	}

	# Substitute an entry into a template via tokens
	function subst_tokens($format, $entry, $allow_admin = true)
	{
		# Create morelink for @MORE@
		if ($entry[more]) {
			$ml = $this->getvar("morelink");
			$q = $this->search_string();
			if ($q)
				$q = "&$q";
			$q = htmlspecialchars("{$this->self}?wl_mode=more&wl_eid=$entry[eid]$q");
			$ml = str_replace("@URL@", $q, $ml);
		} else {
			$ml = "";
		}

		$time_adj = floor($this->getvar("time_adjust") * 3600);

		if ($entry[created] != $entry[updated]) {
			$upd = $this->getvar("update_text");
			$upd = str_replace("@DATE@", date($this->getvar("updated_date"), $entry[updated] + $time_adj), $upd);
		} else {
			$upd = "";
		}

		if ($this->admin and $allow_admin)
			$al = $this->entry_print_admin($entry[eid]);
		else
			$al = "";

		$trans = array(
			"@TITLE@" => $entry[title],
			"@CREATED@" => date($this->getvar("created_date"), $entry[created] + $time_adj),
			"@UPDATED@" => $upd,
			"@ICON@" => "",
			"@TOPIC@" => $entry[name],
			"@BODY@" => $this->subst_paragraph($entry[body]),
			"@ID@" => $entry[eid],
			"@TOPICID@" => $entry[tid],
			"@MORELINK@" => $ml,
			"@BACKLINK@" => $this->back_link(),
			"@SELF@" => $this->self,
			"@MORE@" => $this->subst_paragraph($entry[more]),
			"@ADMIN@" => $al);

		# Substitute entry
		$s = strtr($format, $trans);

		return $this->subst_external($s);
	}

	function subst_external($s)
	{
		# Substitute any external text/links
		while (ereg("@REF@([^@]+)@", $s, $r)) {
			$text = $this->subst_local($r[1]);
			if (ereg("@REF@[^@]+@", $text)) {
				$this->print_error("The function subst_local() must not return @REF@ references, aborting");
				break;

			}
			$s = str_replace("@REF@$r[1]@", $text, $s);
		}

		return $s;
	}

	# Subclass and override this function to implement external links.
	# In your entries, any embeded "@REF@ref@" tokens will be replaced
	# by this function.
	function subst_local($ref)
	{
		return "<b>Not implemented</b>";
	}

	# Print a next/previous nav icon
	function button_offset($var, $off)
	{
		if ($off < 0)
			$off = 0;
		return $this->href_link(array("search" => $undef,
					      "topic" => $undef,
					      "offset" => $off),
					$this->getvar($var));
	}

	function button_cycle($var, $id)
	{
		return $this->href_link(array("mode" => "more", "eid" => $id,
					      "search" => $undef,
					      "topic" => $undef,
					      "offset" => $undef),
					$this->getvar($var));
	}

	# Show search box, topic select, ...
	function print_control($prev, $next)
	{
		if (!$prev)
			$prev = $this->getvar("blank_html");
		if (!$next)
			$next = $this->getvar("blank_html");
		$ts = $this->topic_selectbox("topic", $this->topic, "All");
		if (!$ts)
			return;
		if ($this->admin) {
			$q = $this->search_string();
			if ($q)
				$q = "&$q";
			$q = htmlspecialchars($q);
			$admin = "<a href=\"{$this->self}?wl_mode=edit+entry$q\">[add entry]</a>\n";
			$admin .= "<a href=\"{$this->self}?wl_mode=edit+topics$q\">[edit topics]</a>\n";
			$admin .= "<a href=\"{$this->self}?wl_mode=edit+setup$q\">[edit setup]</a>\n";
			$admin .= "<a href=\"{$this->self}?wl_mode=logout$q\">[log out]</a></p>\n";
		}

		$trans = array(	"@PREV_HTML@" => $prev,
				"@SELF@" => $this->self,
				"@TOPIC_BOX@" => $ts,
				"@KEYWORDS@" => htmlspecialchars($this->search),
				"@SEARCH_TEXT@" => $this->getvar("search_text"),
				"@RESET_TEXT@" => $this->getvar("reset_text"),
				"@NEXT_HTML@" => $next,
				"@ADMIN@" => $admin);

		echo strtr($this->getvar("control"), $trans);
	}

	function dbquery($sql)
	{
		$result = $this->db->query($sql);
		if ($result) {
			return $result;
		} else {
			$this->print_error("Query failed: " .
				htmlspecialchars($this->db->error()));
			$this->print_back();
			return false;
		}
	}

	# Ensure any magic quotes have been stripped.
	# Always use dbfetch() to get values from a MySQL query result
	function dbfetch($r)
	{
		return $this->mq_strip($this->db->fetch($r), "runtime");
	}

	# Lock the database
	function dblock()
	{
		return $this->db->lock(array("{$this->p}entries",
					     "{$this->p}topics",
					     "{$this->p}vars"));
	}

	# Unlock the database
	function dbunlock()
	{
		if (!$this->db->unlock()) {
			$this->print_error("CRITICAL! Failed to unlock tables");
			return false;
		}

		return true;
	}

	# Print an error message.
	# Exit if the error occured before the page was partially displayed
	function print_error($err)
	{
		$h = headers_sent();
	
		if ($err)
			$err = ": $err";
		if ($h) {
			echo "<p><b>ERROR</b>$err</p>\n";
		} else {
			echo "<html><head><title>Critical error</title></head>\n";
			echo "<body>";
			echo "<p><b>ERROR</b>$err</p>\n";
			echo "</body></html>\n";
			exit;
		}
	}

	# Verify the topic exists.
	# NOTE: It leaves the table locked!
	# Locking both entries and topics for write is overkill, but this
	# code does not get used much.
	function topic_verify($tid)
	{
                global $session_user_id;
		if (!$tid)
			return "";

		if (!$this->dblock())
			return "";

//		$r = $this->dbquery("SELECT tid,name FROM {$this->p}topics WHERE tid=$tid AND user_id=$session_user_id");
		$r = $this->dbquery("SELECT tid,name FROM {$this->p}topics WHERE tid=$tid");
		if (!$r or !$this->db->num_rows($r)) {
			$this->print_error("Topic not found");
			$this->print_back();
			$this->dbunlock();
			return "";
		}

		$a = $this->dbfetch($r);
		return $a[name];
	}

	function dbquery_unlock($sql, &$affected)
	{
		$r = $this->dbquery($sql);

		# Ugly, but if we do not we lose the information under MySQL
		$affected = $this->db->affected_rows($r);
		
		if (!$this->dbunlock())
			return false;

		return $r;
	}

	# Variables are assumed to exist and are required to run.
	function getvar($name)
	{
		if (isset($this->dbvar) and isset($this->dbvar[$name]))
			return $this->dbvar[$name];

		$this->print_error("The configuration variable $name was not found. You may need to insert this variable into the 'vars' table by using the data found in 'weblog.sql'.");
		exit;
	}

	# Update a weblog variable
	function setvar($name, $value)
	{
		if ($this->getvar($name) == $value)
			return true;

		$this->dbvar[$name] = $value;

		$q = "UPDATE {$this->p}vars SET value='" . addslashes($value) . "' WHERE name='" . addslashes($name) . "'";
		$r = $this->dbquery($q);

		if (!$r)
			return false;
		if ($this->db->affected_rows($r) != 1) {
			$this->print_error("Cannot set $name/$value");
			return false;
		}

		return true;
	}

	# Returns the form HTML necessary to select between topics
	function topic_selectbox($cgi, $t, $field)
	{
                global $session_user_id;
		# Restrict topics if requested.
		# Don't restrict admin users - it makes things difficult
		# when managing topics.
		if (!$this->admin and $this->valid_topics) {
			$w = "WHERE tid IN (" .
				join(",", $this->valid_topics) . ")";
		}
		$r = $this->dbquery("SELECT tid,name FROM {$this->p}topics $w WHERE user_id=$session_user_id ORDER BY name");
		if (!$r)
			return "";

		$html = "<select name=\"wl_$cgi\">\n";
		if ($field)
			$html .= "<option value=\"0\">$field</option>\n";
		while ($a = $this->dbfetch($r)) {
			$html .= "<option value=\"$a[tid]\"";
			if ($t == $a[tid])
				$html .= " selected=\"selected\"";
			$html .= ">$a[name]</option>\n";
		}
		$html .= "</select>";

		// return $html;
                $nothing = " ";
		return $nothing;
	}

	# CGI mode: "login"
	# Uses: none
	#
	# If already logged in show the main page
	function login()
	{
		if (!$this->admin(true))
			return;

		$this->entry_search();
	}

	# CGI mode: "logout"
	# Uses: none
	#
	# Logout and display main page.
	function logout()
	{
		$this->setvar("login_expire", -1);
		$this->admin = false;
		$this->entry_search();
	}


	# CGI mode: "rss"
	# Uses: same as "search"
	#
	# Generate RSS file with the current search parameters.
	function rss_print()
	{
		global $HTTP_SERVER_VARS;

		# Ignore RSS if it is not configured
		if (!$this->getvar("rss_title"))
			return;

		# Note if SCRIPT_URI doesn't exist this will need to be updated 
		# for https bound weblogs.
		if (isset($HTTP_SERVER_VARS["SCRIPT_URI"]))
			$self = $HTTP_SERVER_VARS["SCRIPT_URI"];
		else
			$self = "http://" . $HTTP_SERVER_VARS["SERVER_NAME"] .
				$this->self;

		$q = $this->generate_search();
		$r = $this->dbquery($q);

		header("Content-type: text/xml");

		echo "<?xml version=\"1.0\"?>\n";
		echo "<!DOCTYPE rss PUBLIC \"-//Netscape Communications//DTD RSS 0.91//EN\" \"http://my.netscape.com/publish/formats/rss-0.91.dtd\">\n";
		echo "<rss version=\"0.91\">\n";
		echo "<channel>\n";
		echo "<title>" . htmlspecialchars($this->getvar("rss_title")) .
		     "</title>\n";
		echo "<link>" . htmlspecialchars($self) . "</link>\n";
		echo "<description>" . $this->getvar("rss_description") .
			"</description>\n";
		echo "<language>en-us</language>\n";

		while($arr = $this->dbfetch($r)) {
			echo "<item><title>" . htmlspecialchars($this->subst_external($arr[title])) . "</title>\n";
			echo "<link>" . htmlspecialchars("$self?wl_start=$arr[eid]") . "</link>\n";
			echo "<description>" . htmlspecialchars($this->subst_external($arr[body])) . "</description></item>\n";
		}

		echo "</channel></rss>\n";

		exit;
	}

	# Returns true if currently logged in via a cookie
	function current_login($name)
	{
		global $HTTP_COOKIE_VARS;

		$t = $this->getvar("login_expire");
		if ($t < time())
			return false;

		// if ($HTTP_COOKIE_VARS[$name] != $this->getvar("login_cookie"))
		//	return false;

		return true;
	}

	# Cache DB vars to prevent many queries
	function load_db_vars()
	{
		$r = $this->dbquery("SELECT name,value FROM {$this->p}vars");
		if (!$r or !$this->db->num_rows($r))
			$this->print_error("No configuration data found. Ensure the 'vars' table and been created and filled with data from ther 'weblog.sql' data file.");

		while ($a = $this->dbfetch($r))
			$this->dbvar[$a[name]] = $a[value];
	}

	# Strip magic quotes escapes from a string
	function mq_strip_escape($v)
	{
		if (ini_get("magic_quotes_sybase") == 1)
			return ereg_replace("''", "'", $v);
		else
			return stripslashes($v);
	}

	# Strip magic quotes if they exist, depending on the data source
	# Valid types: "gpc", "runtime"
	function mq_strip($cv, $mq = "gpc")
	{
		if (!isset($cv))
			return $cv;

		$fn = "get_magic_quotes_$mq";
		if (!$fn())
			return $cv;

		if (!is_array($cv))
			return $this->mq_strip_escape($cv);

		foreach ($cv as $k => $v)
			$new[$k] = $this->mq_strip_escape($v);

		return $new;
	}

	# Store any GET/POST variables in the class.
	# POST has precedence over GET
	function load_cgi_vars()
	{
		global $HTTP_GET_VARS, $HTTP_POST_VARS;

		$arr = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS);

		foreach ($arr as $k => $v)
			if (ereg("^wl_(.+)", $k, $r) and in_array($r[1], $this->valid_cgi))
				$this->$r[1] = $this->mq_strip($v);
	}

	# Start a login session and send/update browser cookie
	function enter_session($sessname, $sess)
	{
		$exp = time() + $this->getvar("login_timeout");
		setcookie($sessname, $sess, $exp);
		$this->setvar("login_cookie", $sess);
		$this->setvar("login_expire", $exp);
		$this->admin = true;
	}

	# The Weblog class must be instantiated at the top of the page
	# before any data is sent. This way it can modify the HTTP headers.
	#
	# Checks for a valid new or current login and updates the session cookie
	# and expiry.
	# Sets the class variable $admin to true on a successful login.
	function Weblog($dbname, $dbhost, $dbuser, $dbpass, $tarr = "",
			$prefix = "", $dbtype = "mysql")
	{
		global $HTTP_SERVER_VARS;

		$this->p = $prefix;
		$this->self = $HTTP_SERVER_VARS["PHP_SELF"];

		# Ensure topic restriction array has the correct format
		if ($tarr != "" and !is_array($tarr))
			$this->print_error("Restricted topic list must be an array or left blank");
		if (is_array($tarr)) {
			foreach ($tarr as $v) {
				if (!ereg("^[0-9]+$", $v))
					$this->print_error("Restricted topic array must only contain numeric topic IDs");
			}
			$this->valid_topics = $tarr;
		}

		$this->valid_cgi = array(
			"mode", "topic", "search", "offset", "before", "start",
			"eid", "tid", "name", "icon", "value",
			"title", "body", "more",
			"password", "update", "delopt");

		if (headers_sent()) {
			$this->print_error("The Weblog constructor must be called before the headers have been sent");
			$this->broken = 1;
			return;
		}

		# Strip E_NOTICE to prevent broken property error messages
		error_reporting(error_reporting() & ~E_NOTICE);

		switch ($dbtype) {
		case "mysql":
			$this->db = new DB_MySQL($dbname, $dbhost, $dbuser, $dbpass);
			break;
		case "postgresql":
			$this->db = new DB_pg($dbname, $dbhost, $dbuser, $dbpass);
			break;
		default:
			$this->print_error("Unknown database type '$dbtype'. Check Weblog() arguments.");
		}

		if (!$this->db->connected())
			$this->print_error("Unable to connect to the database. Check Weblog() arguments.");

		$this->load_cgi_vars();
		$this->sanitize_input();
		$this->load_db_vars();

		# Code when must be run before any data is sent to the client.
		switch ($this->mode) {
		case "rss":
			# rss_print() will not return (unless disabled)
			$this->rss_print();
			break;
		case "edit setup":
		case "edit topics":
		case "edit entry":
			# Disable caching
			$this->dbvar[last_modified] = -1;
			break;
		}

		# Generate session key name unique to dbname/prefix
		$ses = "PW" . substr(md5($dbname . $prefix), 0, 5);

		# Check for login status
		if ($this->current_login($ses))
			$this->enter_session($ses, $this->getvar("login_cookie"));
		else if ($this->password == $this->getvar("password"))
			$this->enter_session($ses, $this->gen_session_key());
		else
			$this->admin = false;

		$lm = $this->getvar("last_modified");
		if ($lm == -1) {
			header("Cache-control: private, no-cache");  
			header("Expires: " .
			       gmdate("D, d M Y H:i:s", 0) . " GMT");
			header("Pragma: no-cache");			
		} else {
			header("Last-Modified: " .
			       gmdate("D, d M Y H:i:s", $lm) . " GMT");
		}
	}

	# Attempt to generate a unique session cookie value
	function gen_session_key()
	{
		mt_srand((double)microtime() * 1000000);
		return mt_rand() . mt_rand() . mt_rand();
	}
}

?>
