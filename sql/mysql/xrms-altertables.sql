# this file calls the non-destructive alter table commands
# this will be useful if you need to upgrade only part of your installation
# e.g. you've been loading from CVS, but don't have the newest version.

alter table companies add default_primary_address int not null;

alter table companies add custom1 varchar(255) not null;
alter table companies add custom2 varchar(255) not null;
alter table companies add custom3 varchar(255) not null;
alter table companies add custom4 varchar(255) not null;

alter table company_sources add company_source_score_adjustment int not null ;

alter table companies drop city;
alter table companies drop state;
alter table companies drop country;

alter table activities add company_id int not null;
alter table activities add contact_id int not null;

alter table contacts add address_id int not null;

alter table addresses add country_id int not null;
alter table addresses alter column country_id set default 1;
alter table addresses add line1 varchar(255) not null;
alter table addresses add line2 varchar(255) not null;
alter table addresses add city varchar(255) not null;
alter table addresses add province varchar(255) not null;
alter table addresses add postal_code varchar(255) not null;
alter table addresses add use_pretty_address char(1) not null;
alter table addresses alter column use_pretty_address set default 'f';