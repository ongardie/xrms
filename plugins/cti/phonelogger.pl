#!/usr/bin/perl

# Phone Logger for XRMS -  interfaces XRMS with the Call Detail Records from a
# Panasonic KX-TD816 Digital Super Hybrid Phone System. It will probably also
# work with the KX-TD1232.

# copyright 2004 Glenn Powers <glenn@net127.com>
# Licensed Under the Open Software License v. 2.0

# this is NOT a web-based script. it should be moved out of this directory before use.
exit;

# default area code (prepended to 7-digit numbers)
$myNPA = "262";

use DBI;

# open the first serial port
open(SERIAL,"</dev/ttyS0");

while($line=<SERIAL>) {

# open the log file
open(LOG,">/var/log/phone.log");

# print line to log
print LOG "$line";

#close the log file
close(LOG);

# remove trailing newline
chop $line;

# skip headers
if ($line =~ /\-+/) {
    next;
};

if ($line =~ /^  Date     Time    Ext CO        Dial Number        Ring Duration  Acc code  CD/) {
    next
};

if ($line =~ /^AT/) {
    next
};

$line=~s/^(\d\d)\/(\d\d)\/(\d\d) (\d\d)\:(\d\d)(\w\w)   (\d\d\d) (\d\d) (.........................) (....) (........) (..) (..)/20\3-\1-\2,\4,\5,\6,\7,\8,\9,\10,\11,\12,\13/;

($date,$hh,$mm,$apm,$ext,$co,$dial,$ring,$duration,$acc,$cd)=split(",",$line);

# change HTML-like code:
$dial=~s/<I>/(Incoming) /g;

# remove spaces from dialed number
$dial=~s/ +//g;

if ($line =~ /^$/) {
    next
};


# format US phone numbers:
$dial=~s/^[^1](\d\d\d)(\d\d\d)(\d\d\d\d)$/\1-\2-\3/g;
$dial=~s/^1(\d\d\d)(\d\d\d)(\d\d\d\d)$/\1-\2-\3/g;
$dial=~s/^(\d\d\d)(\d\d\d\d)$/$myNPA-\1-\2/g;

# Convert to 24-hr time
if ($apm =~/PM/) {
    if ($hh < 12) {
        $hh=$hh+12;
    };
};

$time = "$hh:$mm";

# activity_id is set by auto_increment

# determine activity_type_id
$activity_type_id = 1; # "Call To"
if ($dial =~ /(Incoming)/) {
    $activity_type_id = 2; # "Call From"
};

# set XRMS user by extension number
# this should use the users table
$user[102]=1;
$user[101]=2;
$user[104]=3;
$user[113]=4;

# set default user
$user_id = $user[$ext];
if (!$user_id) {
    $user_id = "";
};

# determine company id
$company_id = 5; # set the default
if (($co eq /07/) || ($co eq /08/)) {
    $company_id = 3; # if line 7 or 8, log under company 3
};

# determine contact_id
$contact_id = 0;

# set on_what_table
$on_what_table = "";

# set on_what_id
$on_what_id = "";

# set on_what_status
$on_what_status = "";

# set activity_title
$activity_title = "Phone Log: " . $dial;

# set activity description
$activity_description = "DATE: $date
TIME: $time
EXTENSION: $ext
CO LINE: $co
DIAL NUMBER: $dial
RING TIME: $ring
DURATION: $duration
ACCOUNTING CODE: $acc
CD: $cd
";

# set entered_at time
($sec,$min,$hour,$mday,$mon,$year,$wday,$yday) = gmtime(time);
$mon++;
$year=$year+1900;
$entered_at = "$year-$mon-$mday $hour:$min:$sec";

# set entered_by
$entered_by = "";

# set scheduled_at
$scheduled_at = $date . " " . $time;

# set ends_at
$ends_at = $date . " " . $time;

# set completed_at
$completed_at = $date . " " . $time;

# set activity_stauts
$activity_status = "c"; # default to CLOSED status

# set activity_record_status
$activity_record_status = 'a';

# make insert statment
$insert = "INSERT INTO `activities` ( `activity_id` , `activity_type_id` , `user_id` , `company_id` , `contact_id` , `on_what_table` , `on_what_id` , `on_what_status` , `activity_title` , `activity_description` , `entered_at` , `entered_by` , `scheduled_at` , `ends_at` , `completed_at` , `activity_status` , `activity_record_status` ) VALUES ( '', '" . $activity_type_id . "', '" . $user_id . "', '" . $company_id . "', '" . $contact_id . "', '" . $on_what_table . "', '" . $on_what_id . "', '" . $on_what_status . "', '" . $activity_title . "', '" . $activity_description . "', '" . $entered_at . "', '" . $entered_by . "', '" . $scheduled_at . "' , '" . $ends_at . "' , '" . $completed_at . "' , '" . $activity_status . "', '" . $activity_record_status . "');";

# open database connection
$dbh = DBI->connect('DBI:mysql:xrms:216.145.239.247', 'phonelogger','kazaa') or die $DBI::errstr;

# prepare the insert statement
$cursor = $dbh->prepare($insert);

# do the insert
$cursor->execute;

# close the database connection
$dbh->disconnect;

# close the loop
};
close(SERIAL);
