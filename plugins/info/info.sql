# --------------------------------------------------------

#
# Table structure for table `info`
#

CREATE TABLE `info` (
  `info_id` int(11) NOT NULL default '0',
  `element_id` int(11) NOT NULL default '0',
  `value` text NOT NULL,
  `info_record_status` char(1) NOT NULL default 'a',
  KEY `server_id` (`info_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `info_element_definitions`
#
CREATE TABLE `info_element_definitions` (
  `element_id` int(11) NOT NULL auto_increment,
  `element_label` varchar(50) NOT NULL default '',
  `element_type` enum('text','select','radio','checkbox','textarea') NOT NULL default 'text',
  `element_column` varchar(10) NOT NULL default '1',
  `element_order` smallint(6) NOT NULL default '0',
  `element_default_value` varchar(50) NOT NULL default '',
  `element_possible_values` varchar(100) NOT NULL default '',
  `element_enabled` smallint(6) NOT NULL default '1',
  `info_type_id` int(11) NOT NULL default '0',
  `element_display_in_sidebar` smallint(6) NOT NULL default '0',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM AUTO_INCREMENT=2 ;

#
# Table structure for table `info_element_definitions`
#

INSERT INTO `info_element_definitions` VALUES (1, 'Name', 'text', '1', 0, '', '', 1, 0, 1);

# --------------------------------------------------------

#
# Table structure for table `info_map`
#
CREATE TABLE `info_map` (
  `info_id` int(11) NOT NULL auto_increment,
  `company_id` int(11) NOT NULL default '0',
  `contact_id` int(11) NOT NULL default '0',
  `on_what_table` varchar(100) NOT NULL default '',
  `on_what_id` int(11) NOT NULL default '0',
  `info_type_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`info_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `info_types`
#
CREATE TABLE `info_types` (
  `info_type_id` int(10) unsigned NOT NULL auto_increment,
  `info_type_name` varchar(48) NOT NULL default '',
  `from_what_table` varchar(24) NOT NULL default '',
  `to_what_table` varchar(24) NOT NULL default '',
  `from_what_text` varchar(32) NOT NULL default '',
  `to_what_text` varchar(32) NOT NULL default '',
  `info_type_record_status` char(1) NOT NULL default 'a',
  `pre_formatting` varchar(25) default NULL,
  `post_formatting` varchar(25) default NULL,
  `info_type_order` int(11) NOT NULL default '0',
  PRIMARY KEY  (`info_type_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

# --------------------------------------------------------

#
# Table structure for table `info_display_map`
#
CREATE TABLE `info_display_map` (
  `info_type_id` int(11) NOT NULL default '0',
  `display_on` varchar(100) NOT NULL default '',
  `record_status` char(1) NOT NULL default 'a',
  KEY `info_type_id` (`info_type_id`,`display_on`)
) TYPE=MyISAM;

