alter table companies add company_legal_name varchar(255) not null;
alter table companies add tax_id varchar(255) not null;

alter table contacts add salutation varchar(20) not null;
alter table contacts add gender char(1) not null;
alter table contacts add date_of_birth varchar(20);

alter table contacts alter column gender set default 'm';

update companies set company_legal_name = company_name;
update contacts set gender = 'm';