-- 
-- 6-campaigns.sql
-- marketing campaign types, campaign statuses, and categories
-- last modified 2003-09-27 by Chris Woofter
-- This software is licensed under the GNU General Public License.
-- 
-- Campaigns have campaign types -- "telemarketing," "magazine ad," "holiday flyer," etc. -- as well as statuses, and one 
-- or more categories associated with them.  Modify my defaults as appropriate.
-- 

create table campaign_types (
	campaign_type_id											int not null primary key auto_increment,
	campaign_type_short_name									varchar(10) not null default '',
	campaign_type_pretty_name									varchar(100) not null default '',
	campaign_type_pretty_plural									varchar(100) not null default '',
	campaign_type_display_html									varchar(100) not null default '',
	campaign_type_record_status									char(3) not null default 'a'
);

create table campaign_statuses (
	campaign_status_id											int not null primary key auto_increment,
	campaign_status_short_name									varchar(10) not null default '',
	campaign_status_pretty_name									varchar(100) not null default '',
	campaign_status_pretty_plural								varchar(100) not null default '',
	campaign_status_display_html								varchar(100) not null default '',
	campaign_status_record_status								char(3) not null default 'a'
);

create table campaigns (
	campaign_id													int not null primary key auto_increment,
	campaign_type_id											int not null default 0,
	campaign_status_id											int not null default 0,
	user_id														int not null default 0,
	campaign_title												varchar(100) not null default '',
	campaign_description										text not null default '',
	starts_at													datetime,
	ends_at														datetime,
	cost														decimal(8,2) not null default 0.01,
	entered_at													datetime,
	entered_by													int not null default 0,
	last_modified_at											datetime,
	last_modified_by											int not null default 0,
	campaign_record_status										char(1) not null default 'a'
);

insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('OTH', 'Other', 'Other', 'Other');
insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('EML', 'E-Mail', 'E-Mail', 'E-Mail');
insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('TEL', 'Phone', 'Phone', 'Phone');
insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('MAIL', 'Mail', 'Mail', 'Mail');
insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('EVT', 'Event', 'Event', 'Event');
insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('MAG', 'Magazine', 'Magazine', 'Magazine');
insert into campaign_types (campaign_type_short_name, campaign_type_pretty_name, campaign_type_pretty_plural, campaign_type_display_html) values ('TV', 'Television', 'Television', 'Television');


insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('NEW', 'New', 'New', 'New');
insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('PLAN', 'Planning', 'Planning', 'Planning');
insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('ACT', 'Active', 'Active', 'Active');
insert into campaign_statuses (campaign_status_short_name, campaign_status_pretty_name, campaign_status_pretty_plural, campaign_status_display_html) values ('CLO', 'Closed', 'Closed', 'Closed');
