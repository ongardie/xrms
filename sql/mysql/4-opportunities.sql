-- 
-- 5-cases.sql
-- (sales) opportunities, as well as opportunity types, priorities, and categories
-- last modified 2003-09-27 by Chris Woofter
-- This software is licensed under the GNU General Public License.
-- 
-- Cases have types -- "telemarketing," "magazine ad," "holiday flyer," etc. -- as well as statuses, and one or more 
-- categories associated with them.  Modify my defaults as appropriate.
-- 

create table opportunities (
	opportunity_id												int not null primary key auto_increment,
	opportunity_status_id										int not null default 0,
	campaign_id													int,
	company_id													int not null default 0,
	contact_id													int not null default 0,
	user_id														int not null default 0,
	opportunity_title											varchar(100) not null default '',
	opportunity_description										text not null default '',
	next_step													varchar(100) not null default '',
	size														decimal(10,2) not null default 0,
	probability													int not null default 0,
	close_at													datetime,
	entered_at													datetime,
	entered_by													int not null default 0,
	last_modified_at											datetime,
	last_modified_by											int not null default 0,
	owned_at													datetime,
	owned_by													int not null default 0,
	closed_at													datetime,
	closed_by													int not null default 0,
	opportunity_record_status									char(1) default 'a'
);


create table opportunity_statuses (
	opportunity_status_id										int not null primary key auto_increment,
	opportunity_status_short_name								varchar(10) not null default '',
	opportunity_status_pretty_name								varchar(100) not null default '',
	opportunity_status_pretty_plural							varchar(100) not null default '',
	opportunity_status_display_html								varchar(100) not null default '',
	opportunity_status_record_status							char(1) not null default 'a'
);


insert into opportunity_statuses (opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values ('NEW', 'New', 'New', 'New');
insert into opportunity_statuses (opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values ('PRE', 'Preliminaries', 'Preliminaries', 'Preliminaries');
insert into opportunity_statuses (opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values ('DIS', 'Discussion', 'Discussion', 'Discussion');
insert into opportunity_statuses (opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values ('NEG', 'Negotiation', 'Negotiation', 'Negotiation');
insert into opportunity_statuses (opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values ('CLW', 'Closed/Won', 'Closed/Won', 'Closed/Won');
insert into opportunity_statuses (opportunity_status_short_name, opportunity_status_pretty_name, opportunity_status_pretty_plural, opportunity_status_display_html) values ('CLL', 'Closed/Lost', 'Closed/Lost', 'Closed/Lost');