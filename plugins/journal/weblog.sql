create table journal_entries (
        user_id int(11) NOT NULL default '0',
	eid integer not null auto_increment primary key,
	title varchar(200) not null,
	tid integer not null,
	created integer not null,
	updated integer not null,
	body text not null,
	more text not null
);

create index updated_idx on journal_entries (updated);

create table journal_topics (
        user_id int(11) NOT NULL default '0',
	tid integer not null auto_increment primary key,
	name varchar(80) not null,
	icon varchar(128) not null
);

insert into journal_topics values (1, 1, 'General', 'gfx/tack.png');

create table journal_vars (
	name varchar(32) not null primary key,
	value text not null,
	help text not null,
	editable enum ('no', 'single', 'multiple') not null
);

insert into journal_vars values ('header','<table><tr><td>','Outputed at the beginning of the weblog', 'multiple');
insert into journal_vars values ('article',
'<p>@ICON@
<b>@TITLE@</b><br />
<font size="-1">@CREATED@</font>@UPDATED@
<p>@BODY@
@MORELINK@
@ADMIN@',
'<pre>This code is displayed for each entry on the main page.
The entry is subsituted into "article" via tokens:
@ID@       Entry ID number. 1 is the first entry created
@TOPICID@  Topic ID number, useful for creating links to topics
@TITLE@    Title field from entry
@CREATED@  Entry add time in human form
@UPDATED@  Updated text (see "update_text" variable) only used when the entry
           has been updated since creation and the "update time" checkbox was
	   set.
@ICON@     Insert a topic icon and link
@TOPIC@    Insert the topic name
@BODY@     Insert the entry body
@MORE@     Insert the entry "more" field (intented for the "more" variable).
@MORELINK@ This is subsituted with the "morelink" variable if there is a body,
           otherwise it is left blank.
@SELF@     This token represents the url to your weblog
@BACKLINK@ Substitute the contents of the "backlink" variable. Used to display
           a link back to the main page while maintaining current search.
@ADMIN@    THIS TOKEN IS REQUIRED. When logged in it is replaces by an entry
           admin menu, otherwise it is left blank.
Most tokens are optional, only include what you want on your page.</pre>', 'multiple');
insert into journal_vars values ('more', '<p>@ICON@
<b>@TITLE@</b><br />
<font size="-1">@CREATED@</font>@UPDATED@
<p>@BODY@
<p>@MORE@
@BACKLINK@
@ADMIN@','Used for displaying the "more" page. It supports the same
variables as "article".','multiple');
insert into journal_vars values ('morelink',
' <font size="-1"><a href="@URL@">more...</a></font>',
'This variable is subsituted into @MORELINK@ token when there is a body
available. "morelink" must use the @URL@ token to subsitute the correct
address into the link. Example: &lt;a href="@URL@"&gt;more...&lt;/a&gt;.',
'single');
insert into journal_vars values ('separator',
'</td></tr><tr><td><hr></td></tr><tr><td>',
'This code is outputed between every entry and the control box on the main
page', 'multiple');
insert into journal_vars values ('footer', '</td></tr></table>',
'The last code outputed by the weblog', 'multiple');
insert into journal_vars values ('max_articles', '5',
'The number of entries to display on a single page. Set to 0 to display all articles.', 'single');
insert into journal_vars values ('password', 'weblog', '', 'single');
insert into journal_vars values ('login_cookie', '',
'Most recent admin session cookie. May or may not have expired.', 'no');
insert into journal_vars values ('login_expire', '0',
'Login expiry time in unix time_t format.', 'no');
insert into journal_vars values ('next_html',
'<img src="gfx/next.png" width="16" height="16" border="0" />',
'This HTML will be used for any next page links', 'single');
insert into journal_vars values ('prev_html',
'<img src="gfx/prev.png" width="16" height="16" border="0" />',
'This HTML will be used for any previous page links', 'single');
insert into journal_vars values ('blank_html',
'<img src="gfx/blank.png" alt="" width="16" height="16" border="0" />',
'This variable will be inserted wherever next or prev icons/text is not
needed. It is useful when you are using tables and need to ensure the control
bar is centered properly. Otherwise you can leave this variable blank.',
'single');
insert into journal_vars values ('update_text',
' <font size="-1"><b>(Updated @DATE@)</b></font>',
'Use the token @DATE@ to represent the updated date. This text will be
subsitituted into the @UPDATED@ token in "article" and "more" variables -
provided the entry has had its timestamp updated since creation.',
'multiple');
insert into journal_vars values ('created_date', 'h:ia T, j M Y',
'Date format used by @CREATED@. See
<a href="http://www.php.net/manual/en/function.date.php">http://www.php.net/manual/en/function.date.php</a>
for more information.', 'single');
insert into journal_vars values ('updated_date', 'h:ia T, j M Y',
'Date format used by @UPDATED@. Same format as used by "created_date".',
'single');
insert into journal_vars values ('login_timeout', '1800',
'If no pages are accessed by the admin user within <i>login_timeout</i>
seconds the user is logged out. The minimum possible timeout is 1 minute.',
'single');
insert into journal_vars values ('topic_icon_attrs',
'width="32" height="32" border="0"',
'<tt>IMG</tt> tag attributes for the topic icons. Use <tt>border="0"</tt> to
prevent the browser from boxing the images. The <tt>src</tt> and <tt>alt</tt>
attributes are set automatically, don\'t configure them.', 'single');
insert into journal_vars values ('backlink',
'<p><a href="@URL@">back to journal</a></p>\n',
'This variable is used to display a link back to the main page while
maintaining the current search parameters. Use the token @URL@ in this
variable to represent the main page url. This variable is outputed at the
bottom of most admin pages and can be subsituted into the variable "more"
with the @BACKLINK@ token.', 'multiple');
insert into journal_vars values ('control', '
<center><table><tr><td>@PREV_HTML@</td><td>Search:<br /><form action="@SELF@" method="post">&nbsp;<input type="text" name="wl_search" value="@KEYWORDS@" /><br /><input type="submit" name="wl_mode" value="@SEARCH_TEXT@" />&nbsp;<input type="submit" name="wl_mode" value="@RESET_TEXT@" /></form></td><td>@NEXT_HTML@</td></tr></table></center>',
'<pre>This code is used to format the control bar at the bottom of the page.
The following tokens will be substited into this variable to create
the control bar:
@PREV_HTML@    These tokens represent the text/images used by the forward/back
@NEXT_HTML@       links. Refer to the relevent configuration variables.
@SELF@         This token represents the url to your weblog
@KEYWORDS@     sdfsd
@SEARCH_TEXT@  Represents "search_text" configuration variable
@RESET_TEXT@   Represents "reset_text" configuration variable
@ADMIN@        This will contain the admin bar when logged in, otherwise it
               is blank.</pre>', 'multiple');
insert into journal_vars values ('reset_text', 'Find all',
'Name of the button used to reset the weblog search to show everything,
loading the default main page.', 'single');
insert into journal_vars values ('search_text', 'Search',
'Name of the button used to search the weblog', 'single');
insert into journal_vars values ('topic_text', 'Topic:&nbsp;',
'Text to output just before the topic select box in the control bar. If you
want to add any HTML to the control bar, this is the place to do it.',
'multiple');
insert into journal_vars values ('last_modified', unix_timestamp(),
'Database last modified timestamp used for sending Last-Modified headers.
This variable is updated whenever an entry, topic or config variable is
added/changed/deleted. Set to -1 to disable sending Last-Modified headers.',
'single');
insert into journal_vars values ('time_adjust', '0',
'If you would like to show weblog times in a timezone different from the
server change this variable to the difference between your desired timezone
the server timezone. Ie "-4.5" will adjust the times back 4.5 hours from the server timezone. If you use this option will won\'t be able to use the "T" or "Z" formats with the "created_date" or "updated_date" variables since they will still reflect server time.', 'single');
insert into journal_vars values ('rss_title', '',
'This variable should be the title of your weblog used by RSS for syndication. Leave blank to disable RSS support.', 'single');
insert into journal_vars values ('rss_description', '',
'This variable contains the description of your weblog used by RSS.', 'multiple');
insert into journal_vars values ('paragraph_split', '',
'If this variable is non-blank the Weblog will split entries into separate HTML
paragraphs after a double-enter.', 'single');
insert into journal_vars values ('archive',
'<p>@ICON@ <a href="@SELF@?wl_mode=more&wl_eid=@ID@">@TITLE@</a></b><br />
<font size="-1">@CREATED@</font>@UPDATED@
', 
'This format is used to create the archive page listing all entries in
the weblog. It should be small and simple. No separator is used between
entries on this page. The tokens from "article" can be used.' , 'multiple');
