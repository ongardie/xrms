#!/usr/bin/perl -w

# Copyright 2004 Uros Kositer, Agenda OpenSystems d.o.o. info@agenda.si
# Licensed under the GNU GPL

my $xrms_database   = 'xxxxxx';
my $xrms_user       = 'xxxxxx';
my $xrms_password   = 'xxxxxx';
my $sugar_database  = 'xxxxxx';
my $sugar_user      = 'xxxxxx';
my $sugar_password  = 'xxxxxx';

use DBI();
use strict;
use Date::Manip;

my $xrms_dbh = DBI->connect("DBI:mysql:database=$xrms_database;host=localhost",
		       $xrms_user, $xrms_password,
		       {'RaiseError' => 1});
my $sugar_dbh =
DBI->connect("DBI:mysql:database=$sugar_database;host=localhost",
		       $sugar_user, $sugar_password,
		       {'RaiseError' => 1}) or (die("No connection!"));

#
#	migrate users
#

$xrms_dbh->do('delete from users');

my $getUsers = $sugar_dbh->prepare(q{ SELECT * FROM users });

my $putUser = $xrms_dbh->prepare(q{
    INSERT INTO users (
		user_id, user_contact_id, role_id, username, password, last_name, 
		first_names, email, language, gmt_offset, last_hit, user_record_status 
	) VALUES (NULL,0,?,?,'NOPASSWORD',?,?,?,'english','1',NULL,?) 
});

my $role_id;
my $user_record_status;
my %userMap;

$getUsers->execute();

while ( my $user = $getUsers->fetchrow_hashref ) {
	if ( $user->{is_admin} eq "on" ) { $role_id = 2; } 
	else { $role_id = 1; };

	if ( $user->{status} eq "Active") { $user_record_status = "a" }
	else { $user_record_status = "d" }

	if (!$user->{first_name}) {$user->{first_name} = "-"};
	if (!$user->{email1}) {$user->{email1} = "-"};

    $putUser->execute( 
		  $role_id,
		  $user->{user_name},
		  $user->{last_name},
		  $user->{first_name},
		  $user->{email1},
		  $user_record_status
	);

	my $getUserId = $xrms_dbh->prepare(q{ SELECT LAST_INSERT_ID() as id; });
	$getUserId->execute();
	my $rowUserId = $getUserId->fetchrow_hashref;
	$userMap{$user->{id}} = $rowUserId->{id};
}

print ("Users migrated\n");

#
#	migrate companies	
#

$xrms_dbh->do('delete from companies');
$xrms_dbh->do('delete from addresses');

my $getCompanies = $sugar_dbh->prepare(q{ SELECT * FROM accounts WHERE deleted
= 0 });

my $putCompany = $xrms_dbh->prepare(q{
    INSERT INTO companies ( 
		company_id, user_id, company_source_id, industry_id, crm_status_id, rating_id,
account_status_id, company_name,
		company_code, legal_name, tax_id, profile, phone, phone2, fax, url, employees,
revenue, credit_limit, terms,
		entered_at, entered_by, last_modified_at, last_modified_by,
default_primary_address, default_billing_address,
		default_shipping_address, default_payment_address, custom1, custom2, custom3,
custom4, extref1, extref2, extref3
	) VALUES (
		NULL,?,1,1,1,1,1,?,
		?,?,'-','-',?,'-',?,?,?,?,'','',
		?,?,?,?,0,0,
		0,0,'','','','','','',''
	) 
});

my $putAddress = $xrms_dbh->prepare(q{
    INSERT INTO addresses ( 
		address_id, company_id, country_id, address_name, address_body, line1, line2,
city, province, postal_code,
		use_pretty_address, offset, daylight_savings_id, address_record_status
	) VALUES (
		NULL,?,189,?,'',?,'',?,'',?,
		'f',NULL,NULL,'a'
	) 
});

my %companyMap;

$getCompanies->execute();

while ( my $company = $get->fetchrow_hashref ) {

	if (!$company->{fax_phone}) {$company->{fax_phone} = "-"};
	if (!$company->{employees}) {$company->{employees} = 0};
	if (!$company->{annual_revenue}) {$company->{annual_revenue} = 0};

    $putCompany->execute( 
		  $userMap{$company->{assigned_user_id}},$company->{name},
		  $company->{name}, $company->{name}, $company->{phone_office},
$company->{fax_phone}, $company->{website}, $company->{employees},
$company->{annual_revenue},
		  $company->{date_entered}, $userMap{$company->{assigned_user_id}},
$company->{date_modified}, $userMap{$company->{modified_user_id}},
	);

	my $getCompanyId = $xrms_dbh->prepare(q{ SELECT LAST_INSERT_ID() as id; });
	$getCompanyId->execute();
	my $rowCompanyId = $getCompanyId->fetchrow_hashref;
	$companyMap{$company->{id}} = $rowCompanyId->{id};

	if (!$company->{billing_address_street}) {$company->{billing_address_street}
= "-"};
	if (!$company->{billing_address_city}) {$company->{billing_address_city} =
"-"};
	if (!$company->{billing_address_postalcode}) {$company->{billing_address_postalcode}
= "-"};

	$putAddress->execute (
		$companyMap{$company->{id}}, 
		'Billing address', 
		$company->{billing_address_street}, 
		$company->{billing_address_city}, 
		$company->{billing_address_postalcode}
	);

	my $getAddressId = $xrms_dbh->prepare(q{ SELECT LAST_INSERT_ID() as id; });
	$getAddressId->execute();
	my $rowAddressId = $getAddressId->fetchrow_hashref;

	$xrms_dbh->do("UPDATE companies SET default_primary_address = '$rowAddressId->{id}',
default_billing_address = '$rowAddressId->{id}' WHERE company_id =
$companyMap{$company->{id}}");

}

print ("Companies migrated\n");

#
#	contacts
#

$xrms_dbh->do('delete from contacts');

my $getContacts = $sugar_dbh->prepare(q{
	SELECT c.*, ac.account_id FROM contacts c, accounts_contacts ac WHERE ac.contact_id
= c.id AND c.deleted = 0
});


$putContact = $xrms_dbh->prepare(q{
    INSERT INTO contacts ( 
		contact_id, company_id, division_id, address_id, salutation, last_name,
first_names, gender,
		date_of_birth, summary, title, description, email, email_status, work_phone,
cell_phone,
		home_phone, fax, aol_name, yahoo_name, msn_name, interests, profile, custom1,
custom2,
		custom3, custom4, entered_at, entered_by, last_modified_at, last_modified_by,
		contact_record_status
	) VALUES (
		NULL,?,0,0,?,?,?,'u',
		?,'',?,?,?,'a',?,?,
		?,?,'','','','','','','',
		'','',?,?,?,?,'a'
	) 
});

$getContacts->execute();

my %contactMap;

while ( my $contact = $get->fetchrow_hashref ) {
	if ($companyMap{$contact->{account_id}}) {
	    $putContact->execute( 
			$companyMap{$contact->{account_id}}, $contact->{salutation},
$contact->{last_name}, $contact->{first_name},
			$contact->{birthdate}, $contact->{title}, $contact->{description},
$contact->{email1}, $contact->{phone_work}, $contact->{phone_mobile},
			$contact->{phone_home}, $contact->{phone_fax}, 
			$contact->{date_entered}, $userMap{$contact->{assigned_user_id}},
$contact->{date_modified}, $userMap{$contact->{modified_user_id}}
		);

		my $getContactId = $xrms_dbh->prepare(q{ SELECT LAST_INSERT_ID() as id; });
		$getContactId->execute();
		my $rowContactId = $getContactId->fetchrow_hashref;
		$contactMap{$contact->{id}} = $rowContactId->{id};

		if (!$contact->{primary_address_street}) {$contact->{primary_address_street}
= "-"};
		if (!$contact->{primary_address_city}) {$contact->{primary_address_city} =
"-"};
		if (!$contact->{primary_address_postalcode})
{$contact->{primary_address_postalcode} = "-"};

		$putAddress->execute (
			$contactMap{$contact->{id}}, 
			'Primary address', 
			$contact->{primary_address_street}, 
			$contact->{primary_address_city}, 
			$contact->{primary_address_postalcode}
		);

		my $getAddressId = $xrms_dbh->prepare(q{ SELECT LAST_INSERT_ID() as id; });
		$getAddressId->execute();
		my $rowAddressId = $getAddressId->fetchrow_hashref;

		$xrms_dbh->do("UPDATE contacts SET address_id = '$rowAddressId->{id}'");
	}

}
print ("Contacts migrated\n");

#
#	calls
#

$xrms_dbh->do('delete from activities');

my $getCalls = $sugar_dbh->prepare(q{ SELECT * FROM calls WHERE deleted = 0 });

my $getContact = $sugar_dbh->prepare(q{
	SELECT cc.contact_id, ac.account_id FROM calls_contacts cc, accounts_contacts
ac WHERE cc.call_id = ? AND cc.contact_id = ac.contact_id
});

my $getContact1 = $sugar_dbh->prepare(q{
	SELECT contact_id, account_id FROM accounts_contacts WHERE account_id = ? AND
deleted = 0
});

my $putCall = $xrms_dbh->prepare(q{
    INSERT INTO activities ( 
		activity_id, activity_type_id, user_id, company_id, contact_id, on_what_table,
on_what_id, on_what_status,
		activity_title, activity_description, entered_at, entered_by, scheduled_at,
ends_at, completed_at,
		activity_status, activity_record_status
	) VALUES (
		NULL,?,?,?,?,0,0,0,
		?,?,?,?,?,?,?,
		?,?
	) 
});

my $activity_type_id;
my $ends_at;
my $ars;
my $oc;
my $controw;

$getCalls->execute();

while ( my $call = $getCalls->fetchrow_hashref ) {

	my $compid = 0;
	my $contid = 0;

	if ($call->{direction} eq "Outbound") { $activity_type_id = 1; }
	else { $activity_type_id = 2; }

	if ($call->{status} eq "Held") { 
		$ends_at = UnixDate (DateCalc($call->{date_start}." ".$call->{time_start},
"+ ".$call->{duration_hours}."hours ".$call->{duration_minutes}."minutes"),"%Y-%m-%d
%H:%M:%S");
		$oc = "c";
	} else { 
		$ends_at = ""; 
		$oc = "o";
	}

	if ($call->{deleted}) { $ars = "d"; }
	else { $ars = "a"; };

	$getContact->execute($call->{id});
	if ($getContact->rows) {
		$controw = $getContact->fetchrow_hashref;
		$contid = $contactMap{$controw->{contact_id}};
		$compid = $companyMap{$controw->{account_id}};
	} elsif ($call->{parent_type} eq "Accounts") {
		$getContact1->execute($call->{parent_id});
		if ($getContact1->rows) {
			$controw = $getContact1->fetchrow_hashref;
			$contid = $contactMap{$controw->{contact_id}};
			$compid = $companyMap{$controw->{account_id}};
		}	
	}

	if ($compid && $contid) {
    	$put->execute( 
			$activity_type_id, $userMap{$call->{assigned_user_id}}, $compid, $contid, 
			$call->{name}, $call->{description}, $call->{date_entered},
$userMap{$call->{assigned_user_id}}, $call->{date_start}." ".$call->{time_start},
$ends_at, $ends_at, $oc, $ars
		);
	};
}

print ("Calls migrated\n");

#
#	e-mails
#

$get = $sugar_dbh->prepare(q{ SELECT * FROM emails WHERE deleted = 0 });
$get->execute();

$cget = $sugar_dbh->prepare(q{
	SELECT ec.contact_id, ac.account_id FROM emails_contacts ec, accounts_contacts
ac WHERE ec.email_id = ? AND ec.contact_id = ac.contact_id
});

$acget = $sugar_dbh->prepare(q{
	SELECT contact_id, account_id FROM accounts_contacts WHERE account_id = ? AND
deleted = 0
});

while ( my $row = $get->fetchrow_hashref ) {

	my $compid = 0;
	my $contid = 0;
	my $usrid = 0;

	$activity_type_id = 4;
	$oc = "c";

	if ($row->{deleted}) { $ars = "d"; }
	else { $ars = "a"; };

	$usrid = $userMap{$row->{assigned_user_id}};

	$cget->execute($row->{id});
	if ($cget->rows) {
		$controw = $cget->fetchrow_hashref;
		$contid = $contactMap{$controw->{contact_id}};
		$compid = $companyMap{$controw->{account_id}};
	} elsif ($row->{parent_type} eq "Accounts") {
		$acget->execute($row->{parent_id});
		if ($acget->rows) {
			$controw = $acget->fetchrow_hashref;
			$contid = $contactMap{$controw->{contact_id}};
			$compid = $companyMap{$controw->{account_id}};
		}	
	}

	if ($compid && $contid && $usrid) {
	    $put->execute( 
			$activity_type_id, $usrid, $compid, $contid, 
			$row->{name}, $row->{description}, $row->{date_entered}, $usrid,
$row->{date_start}." ".$row->{time_start}, $row->{date_start}."
".$row->{time_start},$row->{date_start}." ".$row->{time_start}, $oc, $ars
		);
	}
}

print ("Vnesel emaile\n");

#
#	notes
#

$get = $sugar_dbh->prepare(q{
	SELECT * FROM notes WHERE deleted = 0
});
$get->execute();

$acget = $sugar_dbh->prepare(q{
	SELECT contact_id, account_id FROM accounts_contacts WHERE account_id = ? AND
deleted = 0
});

while ( my $row = $get->fetchrow_hashref ) {

	my $compid = 0;
	my $contid = 0;

	$activity_type_id = 9;
	$oc = "c";
	$ars = "a";

	if ($row->{parent_type} eq "Accounts") {
		$acget->execute($row->{parent_id});
		if ($acget->rows) {
			$controw = $acget->fetchrow_hashref;
			$contid = $contactMap{$controw->{contact_id}};
			$compid = $companyMap{$controw->{account_id}};
		}	
	}
	
	print ("$compid $contid\n");
	if ($compid && $contid) {
	    $put->execute( 
			$activity_type_id, $userMap{1}, $compid, $contid, 
			$row->{name}, $row->{description}, $row->{date_entered}, $userMap{1},
$row->{date_entered}, $row->{date_entered}, $row->{date_entered}, $oc, $ars
		);
	}
}

print("Vnesel notes\n");

#
#	tasks
#

$get = $sugar_dbh->prepare(q{
	SELECT * FROM tasks WHERE deleted = 0
});
$get->execute();

$cget = $sugar_dbh->prepare(q{
	SELECT contact_id, account_id FROM accounts_contacts WHERE contact_id = ?
});

$acget = $sugar_dbh->prepare(q{
	SELECT contact_id, account_id FROM accounts_contacts WHERE account_id = ? AND
deleted = 0
});

while ( my $row = $get->fetchrow_hashref ) {

	my $compid = 0;
	my $contid = 0;
	my $usrid = 0;

	$activity_type_id = 9;

	if ($row->{status} eq "Completed") { 
		$oc = "c";
	} else { 
		$oc = "o";
	}

	if ($row->{deleted}) { $ars = "d"; }
	else { $ars = "a"; };

	$usrid = $userMap{$row->{assigned_user_id}};

	$cget->execute($row->{contact_id});
	if ($cget->rows) {
		$controw = $cget->fetchrow_hashref;
		$contid = $contactMap{$controw->{contact_id}};
		$compid = $companyMap{$controw->{account_id}};
	} elsif ($row->{parent_type} eq "Accounts") {
		$acget->execute($row->{parent_id});
		if ($acget->rows) {
			$controw = $acget->fetchrow_hashref;
			$contid = $contactMap{$controw->{contact_id}};
			$compid = $companyMap{$controw->{account_id}};
		}	
	}
	if ($compid && $contid && $usrid) {
	    $put->execute( 
			$activity_type_id, $usrid, $compid, $contid, 
			$row->{name}, $row->{description}, $row->{date_entered}, $usrid,
$row->{date_entered}, $row->{date_entered}, $row->{date_entered}, $oc, $ars
		);
	}
}
print ("Vnesel tasks\n");


sub IsANumber {

	my $var = $_[0];
             
	if ( ($var =~ /(^[0-9])/) && !($var =~ /([a-zA-Z])/) && ($var ne '') ) { return(1)
}
	else { return(0) }
}

