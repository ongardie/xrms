CREATE TABLE `time_register` (
  `time_register_id` int(11) NOT NULL auto_increment,
  `time` datetime NOT NULL,
  `host` varchar(255) NULL,
  `operator` varchar(255) NULL,
  `remote` varchar(255) NULL,
  `refid` varchar(255) NULL,
  `st` varchar(255) NULL,
  `t` char(1) NULL,
  `when` int(11) NULL,
  `poll` int(11) NULL,
  `reach` int(11) NULL,
  `delay` decimal(11,3) NULL,
  `offset` decimal(11,3) NULL,
  `jitter` decimal(11,3) NULL,
  `entered_at` datetime NULL,
  `entered_by` int(11) NULL,
  `register_record_status` char(1) NULL,
  PRIMARY KEY  (`time_register_id`),
  KEY(`time`)
);
