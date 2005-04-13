CREATE TABLE tavi_links (
page varchar(80) DEFAULT '' NOT NULL,
link varchar(80) DEFAULT '' NOT NULL,
count int(10) unsigned DEFAULT '0' NOT NULL,
PRIMARY KEY (page, link));

CREATE TABLE tavi_pages (
title varchar(80) DEFAULT '' NOT NULL,
version int(10) unsigned DEFAULT '1' NOT NULL,
time timestamp(14),
supercede timestamp(14),
mutable set('off', 'on') DEFAULT 'on' NOT NULL,
username varchar(80),
author varchar(80) DEFAULT '' NOT NULL,
comment varchar(80) DEFAULT '' NOT NULL,
body text,
PRIMARY KEY (title, version));

CREATE TABLE tavi_rate (
ip char(20) DEFAULT '' NOT NULL,
time timestamp(14),
viewLimit smallint(5) unsigned,
searchLimit smallint(5) unsigned,
editLimit smallint(5) unsigned,
PRIMARY KEY (ip));

CREATE TABLE tavi_interwiki (
prefix varchar(80) DEFAULT '' NOT NULL,
where_defined varchar(80) DEFAULT '' NOT NULL,
url varchar(255) DEFAULT '' NOT NULL,
PRIMARY KEY (prefix));

CREATE TABLE tavi_sisterwiki (
prefix varchar(80) DEFAULT '' NOT NULL,
where_defined varchar(80) DEFAULT '' NOT NULL,
url varchar(255) DEFAULT '' NOT NULL,
PRIMARY KEY (prefix));

CREATE TABLE tavi_remote_pages (
page varchar(80) DEFAULT '' NOT NULL,
site varchar(80) DEFAULT '' NOT NULL,
PRIMARY KEY (page, site));

