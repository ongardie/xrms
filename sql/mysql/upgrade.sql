# source the alter table commands
# these can be used by people with newer versions of the database, 
# but maybe not everything, without doing any damage.
source xrms-altertables.sql

# now run the potentially more destructive commands

update companies set default_primary_address = default_billing_address;
update addresses set country_id = 1;
update contacts set address_id = 1;

create table countries (
	country_id			int not null primary key auto_increment,
	address_format_string_id	int not null default 1,
	country_name			varchar(100) not null default '',
	un_code				varchar(50) not null default '',
	iso_code1			varchar(50) not null default '',
	iso_code2			varchar(50) not null default '',
	iso_code3			varchar(50) not null default '',
	telephone_code			varchar(50) not null default '',
	country_record_status		char(1) not null default 'a'
);

create table address_format_strings (
	address_format_string_id		int not null primary key auto_increment,
	address_format_string			varchar(255),
	address_format_string_record_status	char(1) not null default 'a'
);

insert into countries (address_format_string_id, country_name, iso_code1, iso_code2, iso_code3, telephone_code) values (1, ' ', '', '', '', '');

insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Afghanistan', '004', 'AF', 'AFG', '93');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Albania', '008', 'AL', 'ALB', '355');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Algeria', '012', 'DZ', 'DZA', '213');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('American Samoa', '016', 'AS', 'ASM', '684');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Andorra', '020', 'AD', 'AND', '376');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Angola', '024', 'AO', 'AGO', '244');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Anguilla', '660', 'AI', 'AIA', '1 264');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Antarctica', '010', 'AQ', 'ATA', '672');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Antigua and Barbuda', '028', 'AG', 'ATG', '1 268');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Argentina', '032', 'AR', 'ARG', '54');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Armenia', '051', 'AM', 'ARM', '374');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Aruba', '533', 'AW', 'ABW', '297');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Australia', '036', 'AU', 'AUS', '61');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Austria', '040', 'AT', 'AUT', '43');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Azerbaijan', '031', 'AZ', 'AZE', '994');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bahamas', '044', 'BS', 'BHS', '1 242');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bahrain', '048', 'BH', 'BHR', '973');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bangladesh', '050', 'BD', 'BGD', '880');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Barbados', '052', 'BB', 'BRB', '1 246');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Belarus', '112', 'BY', 'BLR', '375');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Belgium', '056', 'BE', 'BEL', '32');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Belize', '084', 'BZ', 'BLZ', '501');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Benin', '204', 'BJ', 'BEN', '229');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bermuda', '060', 'BM', 'BMU', '1 441');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bhutan', '064', 'BT', 'BTN', '975');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bolivia', '068', 'BO', 'BOL', '591');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bosnia and Herzegovina', '070', 'BA', 'BIH', '387');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Botswana', '072', 'BW', 'BWA', '267');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Brazil', '076', 'BR', 'BRA', '55');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('British Virgin Islands', '092', 'VG', 'VGB', '1 284');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Brunei Darussalam', '096', 'BN', 'BRN', '673');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Bulgaria', '100', 'BG', 'BGR', '359');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Burkina Faso', '854', 'BF', 'BFA', '226');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Burundi', '108', 'BI', 'BDI', '257');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cambodia', '116', 'KH', 'KHM', '855');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cameroon', '120', 'CM', 'CMR', '237');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Canada', '124', 'CA', 'CAN', '1');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cape Verde', '132', 'CV', 'CPV', '238');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cayman Islands', '136', 'KY', 'CYM', '1 345');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Central African Republic', '140', 'CF', 'CAF', '236');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Chad', '148', 'TD', 'TCD', '235');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Chile', '152', 'CL', 'CHL', '56');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('China', '156', 'CN', 'CHN', '86');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Christmas Island', '162', 'CX', 'CXR', '61');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cocos (Keeling) Islands', '166', 'CC', 'CCK', '61');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Colombia', '170', 'CO', 'COL', '57');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Comoros', '174', 'KM', 'COM', '269');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Congo', '178', 'CG', 'COG', '242');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cook Islands', '184', 'CK', 'COK', '682');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Costa Rica', '188', 'CR', 'CRI', '506');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Côte d’Ivoire', '384', 'CI', 'CIV', '225');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Croatia', '191', 'HR', 'HRV', '385');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cuba', '192', 'CU', 'CUB', '53');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Cyprus', '196', 'CY', 'CYP', '357');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Czech Republic', '203', 'CZ', 'CZE', '420');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Democratic People''s Republic of Korea', '408', 'KP', 'PRK', '850');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Democratic Republic of the Congo', '180', 'CD', 'COD', '243');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Denmark', '208', 'DK', 'DNK', '45');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Djibouti', '262', 'DJ', 'DJI', '253');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Dominica', '212', 'DM', 'DMA', '1 767');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Dominican Republic', '214', 'DO', 'DOM', '1 809');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Ecuador', '218', 'EC', 'ECU', '593');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Egypt', '818', 'EG', 'EGY', '20');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('El Salvador', '222', 'SV', 'SLV', '503');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Equatorial Guinea', '226', 'GQ', 'GNQ', '240');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Eritrea', '232', 'ER', 'ERI', '291');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Estonia', '233', 'EE', 'EST', '372');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Ethiopia', '231', 'ET', 'ETH', '251');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Faeroe Islands', '234', 'FO', 'FRO', '298');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Falkland Islands (Malvinas)', '238', 'FK', 'FLK', '500');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Federated States of Micronesia', '583', 'FM', 'FSM', '691');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Fiji', '242', 'FJ', 'FJI', '679');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Finland', '246', 'FI', 'FIN', '358');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('France', '250', 'FR', 'FRA', '33');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('France, metropolitan', '249', 'FX', 'FXX', '33');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('French Guiana', '254', 'GF', 'GUF', '594');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('French Polynesia', '258', 'PF', 'PYF', '689');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Gabon', '266', 'GA', 'GAB', '241');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Gambia', '270', 'GM', 'GMB', '220');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Georgia', '268', 'GE', 'GEO', '995');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Germany', '276', 'DE', 'DEU', '49');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Ghana', '288', 'GH', 'GHA', '233');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Gibraltar', '292', 'GI', 'GIB', '350');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Greece', '300', 'GR', 'GRC', '30');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Greenland', '304', 'GL', 'GRL', '299');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Grenada', '308', 'GD', 'GRD', '1 473');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Guadeloupe', '312', 'GP', 'GLP', '590');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Guam', '316', 'GU', 'GUM', '1 671');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Guatemala', '320', 'GT', 'GTM', '502');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Guinea', '324', 'GN', 'GIN', '224');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Guinea-Bissau', '624', 'GW', 'GNB', '245');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Guyana', '328', 'GY', 'GUY', '592');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Haiti', '332', 'HT', 'HTI', '509');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Holy See', '336', 'VA', 'VAT', '39');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Honduras', '340', 'HN', 'HND', '504');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Hong Kong Special Administrative Region of China', '344', 'HK', 'HKG', '852');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Hungary', '348', 'HU', 'HUN', '36');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Iceland', '352', 'IS', 'ISL', '354');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('India', '356', 'IN', 'IND', '91');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Indonesia', '360', 'ID', 'IDN', '62');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Iran', '364', 'IR', 'IRN', '98');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Iraq', '368', 'IQ', 'IRQ', '964');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Ireland', '372', 'IE', 'IRL', '353');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Israel', '376', 'IL', 'ISR', '972');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Italy', '380', 'IT', 'ITA', '39');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Jamaica', '388', 'JM', 'JAM', '1 876');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Japan', '392', 'JP', 'JPN', '81');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Jordan', '400', 'JO', 'JOR', '962');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Kazakhstan', '398', 'KZ', 'KAZ', '7');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Kenya', '404', 'KE', 'KEN', '254');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Kiribati', '296', 'KI', 'KIR', '686');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Kuwait', '414', 'KW', 'KWT', '965');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Kyrgyzstan', '417', 'KG', 'KGZ', '996');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Lao People''s Democratic Republic', '418', 'LA', 'LAO', '856');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Latvia', '428', 'LV', 'LVA', '371');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Lebanon', '422', 'LB', 'LBN', '961');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Lesotho', '426', 'LS', 'LSO', '266');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Liberia', '430', 'LR', 'LBR', '231');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Libyan Arab Jamahiriya', '434', 'LY', 'LBY', '218');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Liechtenstein', '438', 'LI', 'LIE', '423');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Lithuania', '440', 'LT', 'LTU', '370');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Luxembourg', '442', 'LU', 'LUX', '352');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Macau', '446', 'MO', 'MAC', '853');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Madagascar', '450', 'MG', 'MDG', '261');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Malawi', '454', 'MW', 'MWI', '265');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Malaysia', '458', 'MY', 'MYS', '60');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Maldives', '462', 'MV', 'MDV', '960');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Mali', '466', 'ML', 'MLI', '223');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Malta', '470', 'MT', 'MLT', '356');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Marshall Islands', '584', 'MH', 'MHL', '692');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Martinique', '474', 'MQ', 'MTQ', '596');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Mauritania', '478', 'MR', 'MRT', '222');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Mauritius', '480', 'MU', 'MUS', '230');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Mayotte', '175', 'YT', 'MYT', '269');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Mexico', '484', 'MX', 'MEX', '52');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Monaco', '492', 'MC', 'MCO', '377');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Mongolia', '496', 'MN', 'MNG', '976');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Montserrat', '500', 'MS', 'MSR', '1 664');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Morocco', '504', 'MA', 'MAR', '212');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Mozambique', '508', 'MZ', 'MOZ', '258');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Myanmar', '104', 'MM', 'MMR', '95');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Namibia', '516', 'NA', 'NAM', '264');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Nauru', '520', 'NR', 'NRU', '674');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Nepal', '524', 'NP', 'NPL', '977');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Netherlands', '528', 'NL', 'NLD', '31');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Netherlands Antilles', '530', 'AN', 'ANT', '599');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('New Caledonia', '540', 'NC', 'NCL', '687');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('New Zealand', '554', 'NZ', 'NZL', '64');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Nicaragua', '558', 'NI', 'NIC', '505');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Niger', '562', 'NE', 'NER', '227');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Nigeria', '566', 'NG', 'NGA', '234');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Niue', '570', 'NU', 'NIU', '683');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Norfolk Island', '574', 'NF', 'NFK', '672');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Northern Mariana Islands', '580', 'MP', 'MNP', '1 670');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Norway', '578', 'NO', 'NOR', '47');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Oman', '512', 'OM', 'OMN', '968');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Pakistan', '586', 'PK', 'PAK', '92');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Palau', '585', 'PW', 'PLW', '680');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Panama', '591', 'PA', 'PAN', '507');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Papua New Guinea', '598', 'PG', 'PNG', '675');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Paraguay', '600', 'PY', 'PRY', '595');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Peru', '604', 'PE', 'PER', '51');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Philippines', '608', 'PH', 'PHL', '63');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Poland', '616', 'PL', 'POL', '48');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Portugal', '620', 'PT', 'PRT', '351');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Puerto Rico', '630', 'PR', 'PRI', '1 787');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Qatar', '634', 'QA', 'QAT', '974');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Republic of Korea', '410', 'KR', 'KOR', '82');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Republic of Moldova', '498', 'MD', 'MDA', '373');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Réunion', '638', 'RE', 'REU', '262');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Romania', '642', 'RO', 'ROM', '40');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Russian Federation', '643', 'RU', 'RUS', '7');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Rwanda', '646', 'RW', 'RWA', '250');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Saint Helena', '654', 'SH', 'SHN', '290');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Saint Kitts and Nevis', '659', 'KN', 'KNA', '1 869');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Saint Lucia', '662', 'LC', 'LCA', '1 758');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Saint Pierre and Miquelon', '666', 'PM', 'SPM', '508');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Saint Vincent and the Grenadines', '670', 'VC', 'VCT', '1 784');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Samoa', '882', 'WS', 'WSM', '685');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('San Marino', '674', 'SM', 'SMR', '378');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('São Tomé and Principe', '678', 'ST', 'STP', '239');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Saudi Arabia', '682', 'SA', 'SAU', '966');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Senegal', '686', 'SN', 'SEN', '221');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Seychelles', '690', 'SC', 'SYC', '248');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Sierra Leone', '694', 'SL', 'SLE', '232');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Singapore', '702', 'SG', 'SGP', '65');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Slovakia', '703', 'SK', 'SVK', '421');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Slovenia', '705', 'SI', 'SVN', '386');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Solomon Islands', '90', 'SB', 'SLB', '677');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Somalia', '706', 'SO', 'SOM', '252');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('South Africa', '710', 'ZA', 'ZAF', '27');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Spain', '724', 'ES', 'ESP', '34');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Sri Lanka', '144', 'LK', 'LKA', '94');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Sudan', '736', 'SD', 'SDN', '249');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Suriname', '740', 'SR', 'SUR', '597');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Swaziland', '748', 'SZ', 'SWZ', '268');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Sweden', '752', 'SE', 'SWE', '46');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Switzerland', '756', 'CH', 'CHE', '41');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Syrian Arab Republic', '760', 'SY', 'SYR', '963');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Taiwan', '158', 'TW', 'TWN', '886');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Tajikistan', '762', 'TJ', 'TJK', '7');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Thailand', '764', 'TH', 'THA', '66');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('The former Yugoslav Republic of Macedonia', '807', 'MK', 'MKD', '389');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Togo', '768', 'TG', 'TGO', '228');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Tonga', '776', 'TO', 'TON', '676');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Trinidad and Tobago', '780', 'TT', 'TTO', '1 868');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Tunisia', '788', 'TN', 'TUN', '216');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Turkey', '792', 'TR', 'TUR', '90');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Turkmenistan', '795', 'TM', 'TKM', '993');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Turks and Caicos Islands', '796', 'TC', 'TCA', '1 649');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Tuvalu', '798', 'TV', 'TUV', '688');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Uganda', '800', 'UG', 'UGA', '256');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Ukraine', '804', 'UA', 'UKR', '380');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('United Arab Emirates', '784', 'AE', 'ARE', '971');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('United Kingdom', '826', 'GB', 'GBR', '44');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('United Republic of Tanzania', '834', 'TZ', 'TZA', '255');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('United States', '840', 'US', 'USA', '1');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('United States Virgin Islands', '850', 'VI', 'VIR', '1 340');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Uruguay', '858', 'UY', 'URY', '598');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Uzbekistan', '860', 'UZ', 'UZB', '998');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Vanuatu', '548', 'VU', 'VUT', '678');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Venezuela', '862', 'VE', 'VEN', '58');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Viet Nam', '704', 'VN', 'VNM', '84');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Yemen', '887', 'YE', 'YEM', '967');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Yugoslavia', '891', 'YU', 'YUG', '381');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Zambia', '894', 'ZM', 'ZMB', '260');
insert into countries (country_name, un_code, iso_code2, iso_code3, telephone_code) values ('Zimbabwe', '716', 'ZW', 'ZWE', '263');

insert into address_format_strings (address_format_string) values ('$lines<br>$city, $province $postal_code<br>$country');
