#
# Table structure for table `dunfinder`
#

CREATE TABLE `dunfinder` (
  `state` char(2) NOT NULL default '',
  `city` varchar(100) NOT NULL default '',
  `npa` smallint(6) NOT NULL default '0',
  `access_number` varchar(12) NOT NULL default '',
  `pop_code` smallint(6) NOT NULL default '0',
  KEY `state` (`state`,`npa`)
) TYPE=MyISAM;
    
