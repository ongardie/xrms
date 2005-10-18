-- phpMyAdmin SQL Dump
-- version 2.6.2
-- http://www.phpmyadmin.net
-- 
-- Host: localhost
-- Generation Time: Sep 17, 2005 at 10:04 AM
-- Server version: 4.1.11
-- PHP Version: 4.3.10-16
-- 
-- Database: `xrmsdevelkae`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `cf_data`
-- 
CREATE TABLE `cf_data` (
  `instance_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL default '0',
  `value` text NOT NULL,
  `record_status` char(1) NOT NULL default 'a',
  KEY `info_id` (`instance_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `cf_fields`
-- 
CREATE TABLE `cf_fields` (
  `field_id` int(11) NOT NULL auto_increment,
  `object_id` int(11) NOT NULL default '0',
  `field_label` varchar(50) NOT NULL default '',
  `field_type` enum('text','select','radio','checkbox','textarea') NOT NULL default 'text',
  `field_column` varchar(10) NOT NULL default '1',
  `field_order` smallint(6) NOT NULL default '0',
  `default_value` varchar(50) NOT NULL default '',
  `possible_values` varchar(100) NOT NULL default '',
  `record_status` char(1) NOT NULL default 'a',
  `display_in_sidebar` smallint(6) default NULL,
  PRIMARY KEY  (`field_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `cf_instances`
-- 
CREATE TABLE `cf_instances` (
  `instance_id` int(11) NOT NULL auto_increment,
  `object_id` int(11) NOT NULL default '0',
  `key_id` int(11) NOT NULL default '0',
  `subkey_id` int(11),
  `record_status` char(1) NOT NULL default 'a',
  PRIMARY KEY  (`instance_id`)
) ENGINE=MyISAM;

-- --------------------------------------------------------

-- 
-- Table structure for table `cf_objects`
-- 
CREATE TABLE `cf_objects` (
  `object_id` int(11) NOT NULL auto_increment,
  `object_name` text NOT NULL,
  `type_name` varchar(30) NOT NULL default '',
  `label_field_id` int(11) NOT NULL default '0',
  `record_status` char(1) NOT NULL default 'a',
  PRIMARY KEY  (`object_id`)
) ENGINE=MyISAM;

INSERT INTO `cf_objects` VALUES (1, 'Company Accounting', 'company_accounting', 0, 'a');
INSERT INTO `cf_objects` VALUES (2, 'Contact Accounting', 'contact_accounting', 0, 'a');

-- --------------------------------------------------------

-- 
-- Table structure for table `cf_types`
-- 
CREATE TABLE `cf_types` (
  `id` int(11) NOT NULL auto_increment,
  `record_status` char(1) NOT NULL default 'a',
  `type_name` varchar(30) NOT NULL default '',
  `display` enum('sidebar','inline','section') NOT NULL default 'sidebar',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

-- 
-- Dumping data for table `cf_types`
-- 

INSERT INTO `cf_types` VALUES (1, 'a', 'company_sidebar_bottom', 'sidebar');
INSERT INTO `cf_types` VALUES (2, 'a', 'contact_sidebar_top', 'sidebar');
INSERT INTO `cf_types` VALUES (3, 'a', 'contact_sidebar_bottom', 'sidebar');
INSERT INTO `cf_types` VALUES (4, 'a', 'company_content_bottom', 'section');
INSERT INTO `cf_types` VALUES (5, 'a', 'private_sidebar_bottom', 'sidebar');
INSERT INTO `cf_types` VALUES (6, 'a', 'company_accounting', 'inline');
INSERT INTO `cf_types` VALUES (7, 'a', 'contact_accounting', 'inline');
        
