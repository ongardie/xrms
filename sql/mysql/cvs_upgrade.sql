update activities set ends_at = scheduled_at where ends_at is null;
update activities set ends_at = scheduled_at where ends_at = " ";

CREATE TABLE `company_former_names` (
  `company_id` int(11) NOT NULL default '0',
  `namechange_at` datetime NOT NULL default '0000-00-00 00:00:00',
  `former_name` varchar(100) NOT NULL default '',
  `description` varchar(100) default NULL,
  KEY `company_id` (`company_id`)
) TYPE=MyISAM; 

CREATE TABLE `company_relationship` (
  `company_from_id` int(11) NOT NULL default '0',
  `relationship_type` varchar(100) NOT NULL default '',
  `company_to_id` int(11) NOT NULL default '0',
  `established_at` datetime NOT NULL default '0000-00-00 00:00:00',
  KEY `company_from_id` (`company_from_id`,`company_to_id`)
) TYPE=MyISAM; 


-- each company can have zero or more divisions.  This is probably not usefult for small companies, so its' use is optional.

create table company_division (
	division_id                      int not null primary key auto_increment,
	company_id                       int not null,
	user_id                          int not null default 0,
	company_source_id                int not null default 0,
	industry_id                      int not null default 0,
	division_name                    varchar(100) not null default '',
	description                      text not null default '',
	entered_at                       datetime,
	entered_by                       int not null default 0,
	last_modified_at                 datetime,
	last_modified_by                 int not null default 0,
	custom1                          varchar(100) not null default '',
	custom2                          varchar(100) not null default '',
	custom3                          varchar(100) not null default '',
	custom4                          varchar(100) not null default '',
	division_record_status           char(1) default 'a'
);

ALTER TABLE `contacts` ADD `division_id` INT NOT NULL AFTER `company_id` ;

ALTER TABLE `files` ADD `file_type` varchar(100) NOT NULL default '' AFTER `file_size` ;

-- add open status and sort order to case statuses and opportunity statuses
ALTER TABLE `opportunity_statuses` ADD `sort_order` TINYINT DEFAULT '1' NOT NULL AFTER `opportunity_status_id` ,
ADD `status_open_indicator` CHAR( 1 ) DEFAULT 'o' NOT NULL AFTER `sort_order` ;

update opportunity_statuses set sort_order = opportunity_status_id;

ALTER TABLE `case_statuses` ADD `sort_order` TINYINT DEFAULT '1' NOT NULL AFTER `case_status_id` ,
ADD `status_open_indicator` CHAR( 1 ) DEFAULT 'o' NOT NULL AFTER `sort_order` ;

update case_statuses set sort_order = case_status_id;
