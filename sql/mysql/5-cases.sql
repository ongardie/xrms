-- 
-- 5-cases.sql
-- cases (support or service incidents), as well as case types, priorities, and categories
-- last modified 2003-09-27 by Chris Woofter
-- This software is licensed under the GNU General Public License.
-- 
-- Cases have types -- "telemarketing," "magazine ad," "holiday flyer," etc. -- as well as statuses, and one or more 
-- categories associated with them.  Modify my defaults as appropriate.
-- 

create table cases (
	case_id				int not null primary key auto_increment,
	case_type_id			int not null default 0,
	case_status_id			int not null default 0,
	case_priority_id		int not null default 0,
	company_id			int not null default 0,
	contact_id			int not null default 0,
	user_id				int not null default 0,
	priority			int not null default 0,
	case_title			varchar(100) not null default '',
	case_description		text not null default '',
	due_at				datetime,
	entered_at			datetime,
	entered_by			int not null default 0,
	last_modified_at		datetime,
	last_modified_by		int not null default 0,
	owned_at			datetime,
	owned_by			int not null default 0,
	closed_at			datetime,
	closed_by			int not null default 0,
	case_record_status		char(1) not null default 'a'
);


create table case_types (
	case_type_id			int not null primary key auto_increment,
	case_type_short_name		varchar(10) not null default '',
	case_type_pretty_name		varchar(100) not null default '',
	case_type_pretty_plural		varchar(100) not null default '',
	case_type_display_html		varchar(100) not null default '',
	case_type_record_status		char(1) not null default 'a'
);


create table case_statuses (
	case_status_id			int not null primary key auto_increment,
	sort_order			tinyint default '1' not null;
	status_open_indicator 		char( 1 ) default 'o' not null;
	case_status_short_name		varchar(10) not null default '',
	case_status_pretty_name		varchar(100) not null default '',
	case_status_pretty_plural	varchar(100) not null default '',
	case_status_display_html	varchar(100) not null default '',
	case_status_record_status	char(1) not null default 'a'
);


create table case_priorities (
	case_priority_id		int not null primary key auto_increment,
	case_priority_short_name	varchar(10) not null default '',
	case_priority_pretty_name	varchar(100) not null default '',
	case_priority_pretty_plural	varchar(100) not null default '',
	case_priority_display_html	varchar(100) not null default '',
	case_priority_record_status	char(1) not null default 'a'
);


insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (1, 'o', 'NEW', 'New', 'New', 'New');
insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (2, 'o', 'OPEN', 'Open', 'Open', 'Open');
insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (3, 'o', 'PRO', 'In Progress', 'In Progress', 'In Progress');
insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (4, 'c', 'FIN', 'Finished', 'Finished', 'Finished');
insert into case_statuses (sort_order, status_open_indicator, case_status_short_name, case_status_pretty_name, case_status_pretty_plural, case_status_display_html) values (5, 'c', 'CLO', 'Closed', 'Closed', 'Closed');

insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('CRIT', 'Critical', 'Critical', 'Critical');
insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('HIGH', 'High', 'High', 'High');
insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('MED', 'Medium', 'Medium', 'Medium');
insert into case_priorities (case_priority_short_name, case_priority_pretty_name, case_priority_pretty_plural, case_priority_display_html) values ('LOW', 'Low', 'Low', 'Low');

insert into case_types (case_type_short_name, case_type_pretty_name, case_type_pretty_plural, case_type_display_html) values ('HELP', 'Help Item', 'Help Items', 'Help Item');
insert into case_types (case_type_short_name, case_type_pretty_name, case_type_pretty_plural, case_type_display_html) values ('BUG', 'Bug', 'Bugs', 'Bug');
insert into case_types (case_type_short_name, case_type_pretty_name, case_type_pretty_plural, case_type_display_html) values ('RFE', 'Feature Request', 'Feature Requests', 'Feature Request');
