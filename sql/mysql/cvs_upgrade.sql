update activities set ends_at = scheduled_at where ends_at is null;

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

