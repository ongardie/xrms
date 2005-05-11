CREATE TABLE cdr ( 
uniqueid varchar(32) NOT NULL default '', 
userfield varchar(255) NOT NULL default '', 
accountcode varchar(20) NOT NULL default '', 
src varchar(80) NOT NULL default '', 
dst varchar(80) NOT NULL default '', 
dcontext varchar(80) NOT NULL default '', 
clid varchar(80) NOT NULL default '', 
channel varchar(80) NOT NULL default '', 
dstchannel varchar(80) NOT NULL default '', 
lastapp varchar(80) NOT NULL default '', 
lastdata varchar(80) NOT NULL default '', 
calldate datetime NOT NULL default '0000-00-00 00:00:00', 
duration int(11) NOT NULL default '0', 
billsec int(11) NOT NULL default '0', 
disposition varchar(45) NOT NULL default '', 
amaflags int(11) NOT NULL default '0' 
); 
