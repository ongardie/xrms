# Database : `xrmstest`
# 

# --------------------------------------------------------

#
# Table structure for table `svrinfo`
#

CREATE TABLE `svrinfo` (
  `server_id` int(11) NOT NULL default '0',
  `element_id` int(11) NOT NULL default '0',
  `value` text NOT NULL,
  KEY `server_id` (`server_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `svrinfo_element_definitions`
#

CREATE TABLE `svrinfo_element_definitions` (
  `element_id` int(11) NOT NULL auto_increment,
  `element_label` varchar(50) NOT NULL default '',
  `element_type` enum('text','select','radio','checkbox','textarea') NOT NULL default 'text',
  `element_column` varchar(10) NOT NULL default '1',
  `element_order` smallint(6) NOT NULL default '0',
  `element_default_value` varchar(50) NOT NULL default '',
  `element_possible_values` varchar(100) NOT NULL default '',
  `element_enabled` smallint(6) NOT NULL default '1',
  PRIMARY KEY  (`element_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;

#
# Dumping data for table `svrinfo_element_definitions`
#

INSERT INTO `svrinfo_element_definitions` VALUES (1, 'Name', 'text', '1', 0, '', '', 1);

# --------------------------------------------------------

#
# Table structure for table `svrinfo_servers`
#

CREATE TABLE `svrinfo_servers` (
  `server_id` int(11) NOT NULL auto_increment,
  `company_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`server_id`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;
