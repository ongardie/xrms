-- 
-- where did each company come from?  how did they hear about us?  I like options like "trade show" and 
-- "advertisement", but you could just as easily use more specific items -- e.g., "June Telemarketing" -- to track how 
-- many leads are coming from each source.  These company sources are different from campaigns, which are only 
-- associated with opportunities.  Of course, if you don't have any need to track this information, you can just rename 
-- it and use the picklist to store another type of information entirely.
-- 

create table company_sources (
	company_source_id           	int not null primary key auto_increment,
	company_source_short_name	varchar(10) not null default '',
	company_source_pretty_name	varchar(100) not null default '',
	company_source_pretty_plural	varchar(100) not null default '',
	company_source_display_html	varchar(100) not null default '',
	company_source_record_status	char(1) default 'a',
	company_source_score_adjustment int not null
);

insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('OTH', 'Other', 'Other', 'Other');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('ADV', 'Advertisement', 'Advertisements', 'Advertisement');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('DM', 'Direct Mail', 'Direct Mail', 'Direct Mail');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('RAD', 'Radio', 'Radio', 'Radio');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('SE', 'Search Engine', 'Search Engines', 'Search Engine');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('SEM', 'Seminar', 'Seminars', 'Seminar');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('TEL', 'Telemarketing', 'Telemarketings', 'Telemarketing');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('TRD', 'Trade Show', 'Trade Shows', 'Trade Show');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('WEB', 'Web Site', 'Web Sites', 'Web Site');
insert into company_sources (company_source_short_name, company_source_pretty_name, company_source_pretty_plural, company_source_display_html) values ('WOM', 'Word of Mouth', 'Word of Mouth', 'Word of Mouth');


-- 
-- a user-readable list of industries -- by default I suggest things like "mining", "consulting", etc. But feel free 
-- to modify this for your particular needs... if you deal with restaurants exclusively, you might want to use values 
-- like "Mexican", "Thai", or "Caribbean".
-- 

create table industries (
	industry_id           		int not null primary key auto_increment,
	industry_short_name		varchar(10) not null default '',
	industry_pretty_name		varchar(100) not null default '',
	industry_pretty_plural		varchar(100) not null default '',
	industry_display_html		varchar(100) not null default '',
	industry_record_status		char(1) default 'a'
);


insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('OTH', 'Other', 'Other', 'Other');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('ADV', 'Advertising', 'Advertising', 'Advertising');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('ARCH', 'Architecture', 'Architecture', 'Architecture');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('CHEM', 'Chemicals', 'Chemicals', 'Chemicals');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('COM', 'Communications', 'Communications', 'Communications');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('COMP', 'Computers', 'Computers', 'Computers');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('CONST', 'Construction', 'Construction', 'Construction');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('CONS', 'Consulting', 'Consulting', 'Consulting');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('DIST', 'Distribution', 'Distribution', 'Distribution');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('EDU', 'Education', 'Education', 'Education');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('FIN', 'Finance', 'Finance', 'Finance');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('GOV', 'Government', 'Government', 'Government');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('HEAL', 'Healthcare', 'Healthcare', 'Healthcare');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('INS', 'Insurance', 'Insurance', 'Insurance');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('LEG', 'Legal', 'Legal', 'Legal');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('MAN', 'Manufacturing', 'Manufacturing', 'Manufacturing');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('NP', 'Non-Profit', 'Non-Profit', 'Non-Profit');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('RE', 'Real Estate', 'Real Estate', 'Real Estate');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('REST', 'Restaurant', 'Restaurant', 'Restaurant');
insert into industries (industry_short_name, industry_pretty_name, industry_pretty_plural, industry_display_html) values ('RET', 'Retail', 'Retail', 'Retail');



-- 
-- I think it's helpful to have some record of how good/reliable each company in your system is... just a quick "good", 
-- "fair", or "poor" is enough for me, but you might add "excellent" or "horrible" if you need more options.
-- 

create table ratings (
	rating_id			int not null primary key auto_increment,
	rating_short_name		varchar(10) not null default '',
	rating_pretty_name		varchar(100) not null default '',
	rating_pretty_plural		varchar (100) not null default '',
	rating_display_html		varchar(100) not null default '',
	rating_record_status		char(1) default 'a'
);

insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('N/A', 'N/A', 'N/A', '<font color=#999999><b>N/A</b></font>');
insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('Poor', 'Poor', 'Poor', '<font color=#cc0000><b>Poor</b></font>');
insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('Fair', 'Fair', 'Fair', '<font color=#ff9933><b>Fair</b></font>');
insert into ratings (rating_short_name, rating_pretty_name, rating_pretty_plural, rating_display_html) values ('Good', 'Good', 'Good', '<font color=#009900><b>Good</b></font>');


-- 
-- Some of you need to make sure that your customers have valid contracts, have paid their bills, aren't over their 
-- credit limits, etc.
-- 

create table account_statuses (
	account_status_id			int not null primary key auto_increment,
	account_status_short_name		varchar(10) not null default '',
	account_status_pretty_name		varchar(100) not null default '',
	account_status_pretty_plural		varchar(100) not null default '',
	account_status_display_html		varchar(100) not null default '',
	account_status_record_status		char(1) default 'a'
);

insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('N/A', 'N/A', 'N/A', '<font color=#999999><b>N/A</b></font>');
insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('Closed', 'Closed', 'Closed', '<font color=#cc0000><b>Closed</b></font>');
insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('Hold', 'Hold', 'Hold', '<font color=#ff9933><b>Hold</b></font>');
insert into account_statuses (account_status_short_name, account_status_pretty_name, account_status_pretty_plural, account_status_display_html) values ('Approved', 'Approved', 'Approved', '<font color=#009900><b>Approved</b></font>');


-- 
-- Companies can belong to zero or more of these types, but this table is here to represent high-level relationships 
-- with your organization: partner, vendor, customer, competitor, etc.  If you're just using XRMS to track customers, 
-- you won't have much need for these and they can safely be ignored.
-- 

create table company_types (
	company_type_id				int not null primary key auto_increment,
	company_type_short_name			varchar(10) not null default '',
	company_type_pretty_name		varchar(100) not null default '',
	company_type_pretty_plural		varchar(100) not null default '',
	company_type_display_html		varchar(100) not null default '',
	company_type_record_status		char(1) default 'a'
);

insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('CUST', 'Customer', 'Customers', 'Customer');
insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('VEND', 'Vendor', 'Vendors', 'Vendor');
insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('PART', 'Partner', 'Partners', 'Partner');
insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('COMP', 'Competitor', 'Competitors', 'Competitor');
insert into company_types (company_type_short_name, company_type_pretty_name, company_type_pretty_plural, company_type_display_html) values ('SPEC', 'Special', 'Special', 'Special');


-- 
-- one row per association between each company and each type
-- 

create table company_company_type_map (
	company_id				int not null default 0,
	company_type_id				int not null default 0
);

-- 
-- Did you just find out about this company, or is this an old, well-developed account?  I like traditional options here 
-- such as Lead, Prospect, Developed, etc.  Eventually we'll probably add a crm_status_transitions table to keep 
-- tabs on how well companies are moving along through the CRM process.
-- 

create table crm_statuses (
	crm_status_id				int not null primary key auto_increment,
	crm_status_short_name			varchar(10) not null default '',
	crm_status_pretty_name			varchar(100) not null default '',
	crm_status_pretty_plural		varchar(100) not null default '',
	crm_status_display_html			varchar(100) not null default '',
	crm_status_record_status		char(1) default 'a'
);

insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Lead', 'Lead', 'Leads', 'Lead');
insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Prospect', 'Prospect', 'Prospects', 'Prospect');
insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Qualified', 'Qualified', 'Qualified', 'Qualified');
insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Developed', 'Developed', 'Developed', 'Developed');
insert into crm_statuses (crm_status_short_name, crm_status_pretty_name, crm_status_pretty_plural, crm_status_display_html) values ('Closed', 'Closed', 'Closed', 'Closed');


-- this is the Big Daddy table of companies/organizations.  Lots of references to the above-mentioned tables, a few 
-- things that should probably be stored in other software (credit_limit, terms), and three "extref" columns to store 
-- keys to link these companies with their representations in other software for reporting/integration purposes.
-- 

create table companies (
	company_id			int not null primary key auto_increment,
	user_id				int not null default 0,
	company_source_id		int not null default 0,
	industry_id			int not null default 0,
	crm_status_id			int not null default 0,
	rating_id			int not null default 0,
	account_status_id		int not null default 0,
	company_name			varchar(100) not null default '',
	company_code			varchar(10) not null default '',
	profile				text not null default '',
	phone				varchar(50) not null default '',
	phone2				varchar(50) not null default '',
	fax				varchar(50) not null default '',
	url				varchar(50) not null default '',
	employees			varchar(50) not null default '',
	revenue				varchar(50) not null default '',
	credit_limit			int not null default 0,
	terms				int not null default 0,
	entered_at			datetime,
	entered_by			int not null default 0,
	last_modified_at		datetime,
	last_modified_by		int not null default 0,
	default_primary_address		int not null default 0,
	default_billing_address		int not null default 0,
	default_shipping_address	int not null default 0,
	default_payment_address		int not null default 0,
	custom1				varchar(100) not null default '',
	custom2				varchar(100) not null default '',
	custom3				varchar(100) not null default '',
	custom4				varchar(100) not null default '',
	extref1				varchar(50) not null default '',
	extref2				varchar(50) not null default '',
	extref3				varchar(50) not null default '',
	company_record_status		char(1) default 'a'
);


-- each company can have one or more of these (one gets automatically added with the company) -- and you can select via 
-- radio button which one should be the default for billing, shipping, and payments.  I think this might be better as a 
-- "facilities" table, with contacts belonging to one facility, but for now this should be good enough.

create table addresses (
	address_id			int not null primary key auto_increment,
	company_id			int not null default 0,
	country_id			int not null default 1,
	address_name			varchar(100) not null default '',
	address_body			varchar(255) not null default '',
	line1				varchar(255) not null default '',
	line2				varchar(255) not null default '',
	city				varchar(255) not null default '',
	province			varchar(255) not null default '',
	postal_code			varchar(255) not null default '',
	use_pretty_address		char(1) not null default 'f',
	address_record_status		char(1) not null default 'a'
);

-- 
-- I could have made separate tables for titles ("President", "Marketing Director", etc.) and summaries 
-- ("Decision Maker", "Influencer", etc.) but constraining these often seems to just get in the way.  If you'd 
-- like to use specific values here, just come to some kind of agreement as to what they should be and have 
-- your employees use them consistently.
-- 

create table contacts (
	contact_id                      int not null primary key auto_increment,
	company_id                      int not null default 0,
	address_id                      int not null default 0,
    salutation                      varchar(20) not null default '',
	last_name                       varchar(100) not null default '',
	first_names                     varchar(100) not null default '',
	summary                         varchar(100) not null default '',
	title                           varchar(100) not null default '',
	description                     varchar(100) not null default '',
	email                           varchar(100) not null default '',
	email_status                    char(1) default 'a',
	work_phone                      varchar(50) not null default '',
	cell_phone                      varchar(50) not null default '',
	home_phone                      varchar(50) not null default '',
	fax                             varchar(50) not null default '',
	aol_name                        varchar(50) not null default '',
	yahoo_name                      varchar(50) not null default '',
	msn_name                        varchar(50) not null default '',
	interests                       varchar(50) not null default '',
	profile                         text not null default '',
	custom1                         varchar(50) not null default '',
	custom2                         varchar(50) not null default '',
	custom3                         varchar(50) not null default '',
	custom4                         varchar(50) not null default '',
	entered_at                      datetime,
	entered_by                      int not null default 0,
	last_modified_at                datetime,
	last_modified_by                int not null default 0,
	contact_record_status           char(1) not null default 'a'
);
-- 
-- for the bulk e-mail stuff, where you can store things like "Dear ##CONTACT_FIRST_NAMES## - " and the system will 
-- replace the ##CONTACT_FIRST_NAMES## token with the contact's actual first names
-- 

create table email_templates (
    email_template_id                   int not null primary key auto_increment,
    email_template_title                varchar(100) not null default '',
    email_template_body                 text not null default '',
    email_template_record_status        char(1) not null default 'a'
);

insert into email_templates (email_template_title, email_template_body) values ('Blank Template', '');
insert into email_templates (email_template_title, email_template_body) values ('Introduction', '');
insert into email_templates (email_template_title, email_template_body) values ('Sales Pitch', '');
insert into email_templates (email_template_title, email_template_body) values ('Thanks for Your Business', '');
insert into email_templates (email_template_title, email_template_body) values ('Customer Service Inquiry', '');


-- ---------------------------------------------------------------------------------------------------------------
-- If you'd like to have some sample data in your system (you can always remove it later), I recommend leaving the 
-- following lines in this file.  If the idea of having an "unclean" database bothers you, though, just erase them 
-- before you run it.
-- ---------------------------------------------------------------------------------------------------------------

-- insert some companies
insert into companies (user_id, company_source_id, crm_status_id, industry_id, account_status_id, rating_id, company_name, company_code, profile, phone, phone2, fax, url, default_primary_address, default_billing_address, default_shipping_address, default_payment_address, credit_limit, terms, extref1, extref2, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 1, 2, 1, 4, 4, 'Bushwood Components', 'BUSH01', '(Bushwood Components is a fictitious company.)<p>This field can be used to hold a paragraph or two of text (either plain or <font color=blue><b>HTML</b></font>) about a company.', '(800) 555-2000', '(800) 555-2001', '(800) 555-2002', 'http://www.bushwood.com', 1, 1, 1, 1, 100000, 10, '10090', '10091', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);
insert into companies (user_id, company_source_id, crm_status_id, industry_id, account_status_id, rating_id, company_name, company_code, profile, phone, phone2, fax, url, default_primary_address, default_billing_address, default_shipping_address, default_payment_address, credit_limit, terms, extref1, extref2, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 2, 3, 2, 4, 4, 'Polymer Electronics', 'POLY01', '(Polymer Electronics is a fictitious company.)<p>This field can be used to hold a paragraph or two of text (either plain or <font color=blue><b>HTML</b></font>) about a company.', '(800) 555-3000', '(800) 555-3001', '(800) 555-3002', 'http://www.polymer.com', 2, 2, 2, 2, 200000, 20, '10092', '10093', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);
insert into companies (user_id, company_source_id, crm_status_id, industry_id, account_status_id, rating_id, company_name, company_code, profile, phone, phone2, fax, url, default_primary_address, default_billing_address, default_shipping_address, default_payment_address, credit_limit, terms, extref1, extref2, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 3, 4, 3, 4, 4, 'Callahan Manufacturing', 'CALL01', '(Callahan Manufacturing is a fictitious company.)<p>This field can be used to hold a paragraph or two of text (either plain or <font color=blue><b>HTML</b></font>) about a company.', '(800) 555-4000', '(800) 555-4001', '(800) 555-4002', 'http://www.callahan.com', 3, 3, 3, 3, 300000, 30, '10094', '10095', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);

-- insert the corresponding addresses
insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body) values (1, 1, 'Address 1', '3201 West Rolling Hills Circle', '', 'Ft. Lauderdale', 'FL', '33328', '3201 West Rolling Hills Circle\nFt. Lauderdale, FL 33328\nUSA');
insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body) values (2, 1, 'Address 2', '11 Platinum Drive', '', 'Los Angeles', 'CA', '90001', '11 Platinum Drive\nLos Angeles, CA 90001\nUSA');
insert into addresses (company_id, country_id, address_name, line1, line2, city, province, postal_code, address_body) values (3, 1, 'Address 3', '123 Main Street', 'Suite 100', 'Sandusky', 'OH', '44870', '123 Main Street\nSuite 100\nSandusky, OH 44870\nUSA');

-- insert contacts for the first company
insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 1, 'Webb', 'Ty', '1/2 owner', 'Account Manager', 'dad never liked us', 'twebb@bushwoodcc.com', '(555) 555-2100', 'twebb', 'twebb', 'twebb', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);
insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (1, 1, 'Spackler', 'Carl', 'do not call', 'Assistant Greenskeeper', 'to the bejeezus belt!', 'cspackler@bushwoodcc.com', '(555) 555-2200', 'cspackler', 'cspackler', 'cspackler', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);

-- insert contacts for the second company
insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (2, 2, 'Fufkin', 'Artie', '', 'Director', '', 'artie@polymer.com', '(555) 555-3100', 'artie', 'artie', 'artie', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);
insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (2, 2, 'Smalls', 'Derek', '', 'Bass', '', 'derek@polymer.com', '(555) 555-3200', 'derek', 'derek', 'derek', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);

-- insert contacts for the third company
insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (3, 3, 'Callahan', 'Tommy', 'works nights', 'President/CEO', '', 'tommy@callahan.com', '(555) 555-4100', 'tcallahan', 'tcallahan', 'tcallahan', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);
insert into contacts (company_id, address_id, last_name, first_names, summary, title, description, email, work_phone, aol_name, yahoo_name, msn_name, entered_at, entered_by, last_modified_at, last_modified_by) values (3, 3, 'Hayden', 'Richard', 'good contact', 'Buyer', 'All Lines', 'richard@callahan.com', '(555) 555-4200', 'rhayden', 'rhayden', 'rhayden', '2003-01-01 12:00', 1, '2003-01-01 12:00', 1);
