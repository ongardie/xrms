-- 
-- 7-activities.sql
-- activities, activity statuses, and activity types
-- last modified 2003-09-27 by Chris Woofter
-- This software is licensed under the GNU General Public License.
-- 
-- I've used default activity types like "call to," "call from," "e-mail to," "e-mail from," etc.  Using these, 
-- you'd probably end up writing things like "Introduction," "Sent Marketing Materials," or "Received Bank/Trade 
-- References" in the subject line for each activity.  If your organization has a more defined process for customer 
-- relationships, you might change these to reflect stages of that process.  For example, you might insert activity types 
-- like "Introduction," "Sent Marketing Materials," "Received Bank/Trade References," etc., in which case you could make 
-- the subject lines even more descriptive.

create table activity_types (
	activity_type_id											int not null primary key auto_increment,
	activity_type_short_name									varchar(10) not null default '',
	activity_type_pretty_name									varchar(100) not null default '',
	activity_type_pretty_plural									varchar(100) not null default '',
	activity_type_display_html									varchar(100) not null default '',
	activity_type_score_adjustment							    int not null default 0,
	activity_type_record_status									char(1) not null default 'a'
);

-- choose this batch

insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('CTO', 'call to', 'calls to', 'call to');
insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('CFR', 'call from', 'calls from', 'call from');
insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('ETO', 'e-mail to', 'e-mails to', 'e-mail to');
insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('EFR', 'e-mail from', 'e-mails from', 'e-mail from');
insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('FTO', 'fax to', 'faxes to', 'fax to');
insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('FFR', 'fax from', 'faxes from', 'fax from');

-- or comment out the lines above, uncomment the lines below, modify as appropriate, and insert something like this 
-- second batch

-- insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('MUL', 'mulching', 'mulching', 'mulching');
-- insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('WDG', 'weeding', 'weeding', 'weeding');
-- insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('MOW', 'mowing', 'mowing', 'mowing');
-- insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('EDG', 'edging', 'edging', 'edging');
-- insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('WAT', 'watering', 'watering', 'watering');
-- insert into activity_types (activity_type_short_name, activity_type_pretty_name, activity_type_pretty_plural, activity_type_display_html) values ('RAK', 'raking', 'raking', 'raking');


-- 
-- Activities are assumed to belong to exactly one company, contact, opportunity, or case, because I think (hope) this 
-- may be good enough.  The ideal data model should be more complicated, and an activity should probably be called 
-- something like "work effort,"  but I can't find a data model that seems right.
-- 

create table activities (
	activity_id			int not null primary key auto_increment,
	activity_type_id		int not null default 0,
	user_id				int not null default 0,
	on_what_table			varchar(100) not null default '',
	on_what_id			int not null default 0,
	activity_title			varchar(100) not null default '',
	activity_description		text not null default '',
	entered_at			datetime,
	entered_by			int not null default 0,
	scheduled_at			datetime,
	ends_at				datetime,
	completed_at			datetime,
	activity_status			char(1) default 'o',
	activity_record_status		char(1) default 'a'
);
