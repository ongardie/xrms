--
-- we don't actually use this table much yet, but we will soon
-- 

create table roles (
	role_id				int not null primary key auto_increment,
	role_short_name			varchar(10) not null default '',
	role_pretty_name		varchar(100) not null default '',
	role_pretty_plural		varchar(100) not null default '',
	role_display_html		varchar(100) not null default '',
	role_record_status		char(1) default 'a'
);

insert into roles (role_short_name, role_pretty_name, role_pretty_plural, role_display_html) values ('User', 'User', 'Users', 'User');
insert into roles (role_short_name, role_pretty_name, role_pretty_plural, role_display_html) values ('Admin', 'Admin', 'Admin', 'Admin');

--
-- typical users table
--

create table users (
	user_id				int not null primary key auto_increment,
	role_id				int not null default 0,
	username			varchar(100) not null default '',
	password			varchar(100) not null default '',
	last_name			varchar(100) not null default '',
	first_names			varchar(100) not null default '',
	email				varchar(100) not null default '',
	language			varchar(50) not null default 'english',
	gmt_offset			int not null default 0,
	last_hit			datetime,
	user_record_status		char(1) default 'a'
);

ALTER TABLE `users` ADD UNIQUE (`username` );

insert into users (role_id, username, password, last_name, first_names, email, language) values (1, 'user1', '24c9e15e52afc47c225b757e7bee1f9d', 'One', 'User', 'user1@somecompany.com', 'english');
