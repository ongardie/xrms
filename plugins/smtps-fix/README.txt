Patch files to make SMTPs work in XRMS on a user-specific basis

Copyright by Randy Martinsen.  For assistance send e-mail to randym56@hotmail.com.

This is not an actual plugin - it is a patch to Core XRMS, and you must follow these instructions to make the patch work properly

1. Run sqlupdate.php to add fields in users table
2. Install and activate mcrypt in PHP (download here: http://sourceforge.net/projects/mcrypt/ )
3. Copy the following files to the respective folders on XRMS
	users/* to /admin/users/*
	SMTPs/* to /include/classes/SMTPs/*
	email/* to /email/*

4. Within XRMS - select Administration | Users, and add the STMP information into each user's record.

NOTE: A user with the name "admin", may contain an SMTP address that will be the default used if no SMTP information is found in a user's record - this should not be used generally, because SPAM blockers will identify that the SMTP address is different from the e-mail address, and most e-mails will be blocked by the intended recipient, but XRMS will continue to function normally.