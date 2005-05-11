#
# Table structure for table `cti_call_queue`
#

CREATE TABLE `cti_call_queue` (
`id` int(11) unsigned NOT NULL auto_increment,
`callerid` varchar(50) NOT NULL default '',
`callername` varchar(255) NOT NULL default '',
`extension` varchar(50) NOT NULL default '',
`start_ts` int(11) unsigned NOT NULL default '0',
`ack` tinyint(4) NOT NULL default '0',
PRIMARY KEY  (`id`)
);
