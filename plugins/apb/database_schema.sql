# MySQL dump 7.1
#
# Host: localhost    Database: apb
#--------------------------------------------------------
# Server version	3.23.42-log

#
# Table structure for table 'apb_bookmarks'
#
CREATE TABLE apb_bookmarks (
  bookmark_id int(11) unsigned NOT NULL auto_increment,
  group_id int(10) unsigned DEFAULT '0' NOT NULL,
  bookmark_title varchar(255) DEFAULT '' NOT NULL,
  bookmark_url varchar(255) DEFAULT '' NOT NULL,
  bookmark_description varchar(255),
  bookmark_creation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  bookmark_private char(1) DEFAULT '0' NOT NULL,
  bookmark_last_hit_date datetime,
  user_id int(10) unsigned DEFAULT '0' NOT NULL,
  bookmark_deleted char(1) DEFAULT '0' NOT NULL,
  PRIMARY KEY (bookmark_id),
  KEY group_id (group_id),
  KEY user_id (user_id)
);

#
# Table structure for table 'apb_config'
#
CREATE TABLE apb_config (
  user_id int(10) unsigned DEFAULT '0' NOT NULL,
  title varchar(30),
  PRIMARY KEY (user_id)
);

#
# Table structure for table 'apb_groups'
#
CREATE TABLE apb_groups (
  group_id int(10) unsigned NOT NULL auto_increment,
  group_parent_id int(10) unsigned DEFAULT '0' NOT NULL,
  group_title varchar(100) DEFAULT '' NOT NULL,
  group_description varchar(255),
  user_id int(10) unsigned DEFAULT '0' NOT NULL,
  group_private char(1) DEFAULT '0' NOT NULL,
  group_creation_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  group_deleted char(1) DEFAULT '0' NOT NULL,
  PRIMARY KEY (group_id),
  UNIQUE user_id (user_id,group_title),
  KEY parent_id (group_parent_id),
  KEY name_of_group (group_title),
  KEY group_title (group_title),
  KEY group_description (group_description)
);

#
# Table structure for table 'apb_hits'
#
CREATE TABLE apb_hits (
  bookmark_id int(10) unsigned DEFAULT '0' NOT NULL,
  user_id int(10) unsigned DEFAULT '0' NOT NULL,
  hit_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  hit_ip char(15) DEFAULT '' NOT NULL,
  KEY bookmark_id (bookmark_id),
  KEY user_id (user_id),
  KEY hit_date (hit_date)
);

#
# Table structure for table 'apb_users'
#
CREATE TABLE apb_users (
  user_id int(10) unsigned NOT NULL auto_increment,
  username varchar(20) DEFAULT '' NOT NULL,
  password varchar(20),
  PRIMARY KEY (user_id),
  UNIQUE username (username)
);