create table recent_items (
	recent_item_id												int not null primary key auto_increment,
	user_id														int not null default 0,
	on_what_table												varchar(100) not null default '',
	on_what_id													int not null default 0,
	recent_item_timestamp										timestamp
);


create table audit_items (
	audit_item_id												int not null primary key auto_increment,
	user_id														int not null default 0,
	audit_item_type												varchar(50) default '',
	on_what_table												varchar(100) default '',
	on_what_id													varchar(10) default '',
	audit_item_timestamp										datetime,
	audit_item_record_status									char(1) default 'a'
);


create table files (
	file_id           											int not null primary key auto_increment,
	file_pretty_name											varchar(100) not null default '',
	file_description											text not null default '',
	file_filesystem_name										varchar(100) not null default '',
	file_size													int not null default 0,
	on_what_table												varchar(100) not null default '',
	on_what_id													int not null default 0,
	entered_at													datetime,
	entered_by													int not null default 0,
	file_record_status											char(1) default 'a'
);

create table notes (
	note_id           											int not null primary key auto_increment,
	note_description											text not null default '',
	on_what_table												varchar(100),
	on_what_id													int not null default 0,
	entered_at													datetime,
	entered_by													int not null default 0,
	note_record_status											char(1) not null default 'a'
);

create table categories (
	category_id													int not null primary key auto_increment,
	category_short_name											varchar(10) not null default '',
	category_pretty_name										varchar(100) not null default '',
	category_pretty_plural										varchar(100) not null default '',
	category_display_html										varchar(100) not null default '',
	category_record_status										char(1) not null default 'a'
);

create table category_scopes (
	category_scope_id											int not null primary key auto_increment,
	category_scope_short_name									varchar(10) not null default '',
	category_scope_pretty_name									varchar(100) not null default '',
	category_scope_pretty_plural								varchar(100) not null default '',
	category_scope_display_html									varchar(100) not null default '',
	on_what_table												varchar(100) not null default '',
	category_scope_record_status								char(1) default 'a'
);

create table category_category_scope_map (
	category_id													int not null default 0,
	category_scope_id											int not null default 0
);

create table entity_category_map (
	category_id													int not null default 0,
	on_what_table												varchar(100) not null default '',
	on_what_id													int not null default 0
);

insert into categories (category_short_name, category_pretty_name, category_pretty_plural, category_display_html) values ('TEST1', 'Test Category 1', 'Test Category 1', 'Test Category 1');
insert into categories (category_short_name, category_pretty_name, category_pretty_plural, category_display_html) values ('TEST2', 'Test Category 2', 'Test Category 2', 'Test Category 2');
insert into categories (category_short_name, category_pretty_name, category_pretty_plural, category_display_html) values ('TEST3', 'Test Category 3', 'Test Category 3', 'Test Category 3');

insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('COMP', 'Company', 'Companies', 'Company', 'companies');
insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('CONT', 'Contact', 'Contacts', 'Contact', 'contacts');
insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('OPP', 'Opportunity', 'Opportunities', 'Opportunity', 'opportunities');
insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('CASE', 'Case', 'Cases', 'Case', 'cases');
insert into category_scopes (category_scope_short_name, category_scope_pretty_name, category_scope_pretty_plural, category_scope_display_html, on_what_table) values ('CAMP', 'Campaign', 'Campaigns', 'Campaign', 'campaigns');

insert into category_category_scope_map (category_id, category_scope_id) values (1, 1);
insert into category_category_scope_map (category_id, category_scope_id) values (1, 2);
insert into category_category_scope_map (category_id, category_scope_id) values (1, 3);
insert into category_category_scope_map (category_id, category_scope_id) values (1, 4);
insert into category_category_scope_map (category_id, category_scope_id) values (1, 5);

insert into category_category_scope_map (category_id, category_scope_id) values (2, 1);
insert into category_category_scope_map (category_id, category_scope_id) values (2, 2);
insert into category_category_scope_map (category_id, category_scope_id) values (2, 3);
insert into category_category_scope_map (category_id, category_scope_id) values (2, 4);
insert into category_category_scope_map (category_id, category_scope_id) values (2, 5);

insert into category_category_scope_map (category_id, category_scope_id) values (3, 1);
insert into category_category_scope_map (category_id, category_scope_id) values (3, 2);
insert into category_category_scope_map (category_id, category_scope_id) values (3, 3);
insert into category_category_scope_map (category_id, category_scope_id) values (3, 4);
insert into category_category_scope_map (category_id, category_scope_id) values (3, 5);

