CREATE TABLE `cti_cisco_7960_config` (
`mac` VARCHAR( 100 ) NOT NULL ,
`ip` VARCHAR( 100 ) NOT NULL ,
`proxy1_address` VARCHAR( 100 ) NOT NULL ,
`line1_name` VARCHAR( 100 ) NOT NULL ,
`line1_displayname` VARCHAR( 100 ) NOT NULL ,
`line1_authname` VARCHAR( 100 ) NOT NULL ,
`line1_password` VARCHAR( 100 ) NOT NULL ,
`proxy2_address` VARCHAR( 100 ) NOT NULL ,
`line2_name` VARCHAR( 100 ) NOT NULL ,
`line2_displayname` VARCHAR( 100 ) NOT NULL ,
`line2_authname` VARCHAR( 100 ) NOT NULL ,
`line2_password` VARCHAR( 100 ) NOT NULL ,
`proxy3_address` VARCHAR( 100 ) NOT NULL ,
`line3_name` VARCHAR( 100 ) NOT NULL ,
`line3_displayname` VARCHAR( 100 ) NOT NULL ,
`line3_authname` VARCHAR( 100 ) NOT NULL ,
`line3_password` VARCHAR( 100 ) NOT NULL ,
`proxy4_address` VARCHAR( 100 ) NOT NULL ,
`line4_name` VARCHAR( 100 ) NOT NULL ,
`line4_displayname` VARCHAR( 100 ) NOT NULL ,
`line4_authname` VARCHAR( 100 ) NOT NULL ,
`line4_password` VARCHAR( 100 ) NOT NULL ,
`proxy5_address` VARCHAR( 100 ) NOT NULL ,
`line5_name` VARCHAR( 100 ) NOT NULL ,
`line5_displayname` VARCHAR( 100 ) NOT NULL ,
`line5_authname` VARCHAR( 100 ) NOT NULL ,
`line5_password` VARCHAR( 100 ) NOT NULL ,
`proxy6_address` VARCHAR( 100 ) NOT NULL ,
`line6_name` VARCHAR( 100 ) NOT NULL ,
`line6_displayname` VARCHAR( 100 ) NOT NULL ,
`line6_authname` VARCHAR( 100 ) NOT NULL ,
`line6_password` VARCHAR( 100 ) NOT NULL ,
`proxy_emergency` VARCHAR( 100 ) NOT NULL ,
`proxy_emergency_port` VARCHAR( 100 ) NOT NULL ,
`proxy_backup` VARCHAR( 100 ) NOT NULL ,
`proxy_backup_port` VARCHAR( 100 ) NOT NULL ,
`outbound_proxy` VARCHAR( 100 ) NOT NULL ,
`outbound_proxy_port` VARCHAR( 100 ) NOT NULL ,
`nat_enable` VARCHAR( 100 ) NOT NULL ,
`nat_address` VARCHAR( 100 ) NOT NULL ,
`voip_control_port` VARCHAR( 100 ) NOT NULL ,
`start_media_port` VARCHAR( 100 ) NOT NULL ,
`end_media_port` VARCHAR( 100 ) NOT NULL ,
`nat_received_processing` VARCHAR( 100 ) NOT NULL ,
`phone_label` VARCHAR( 100 ) NOT NULL ,
`time_zone` VARCHAR( 100 ) NOT NULL ,
`telnet_level` VARCHAR( 100 ) NOT NULL ,
`phone_prompt` VARCHAR( 100 ) NOT NULL ,
`phone_password` VARCHAR( 100 ) NOT NULL ,
`enable_vad` VARCHAR( 100 ) NOT NULL ,
`network_media_type` VARCHAR( 100 ) NOT NULL ,
`user_info` VARCHAR( 100 ) NOT NULL ,
`logo_url` VARCHAR( 100 ) NOT NULL ,
`messages_uri` VARCHAR( 100 ) NOT NULL ,
`user_id` INT( 11 ) NOT NULL ,
`entered_at` DATE NOT NULL ,
`entered_by` INT( 11 ) NOT NULL ,
`vm_email` VARCHAR( 100 ) NOT NULL ,
`vm_password` VARCHAR( 100 ) NOT NULL ,
'full_name' VARCHAR ( 100 ) NOT NULL,
`context` VARCHAR( 100 ) NOT NULL,
`extension` TINYINT( 6 ) NOT NULL
);