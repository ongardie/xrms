######################################################################
Active PHP Bookmarks - lbstone.com/apb/
Plugin for XRMS - http://sourceforge.net/projects/xrms/

Filename:   README.txt
Authors:    L. Brandon Stone (lbstone.com)
            Nathanial P. Hendler (retards.org)
            Glenn Powers <glenn@net127.com>

2004-10-10  Removed "REQUIREMENTS" Section [GP]
2004-08-04  Added "THE BEGINNING OF THE APB XRMS PLUGIN" [GP]
2004-08-04  Added "THE END OF APB?" [GP]
2003-03-31  Changes made for 1.1.02. [LBS]
2002-02-11  Upgrade instructions almost added. [NPH]
2002-01-30  Rewritten for Version 1 release. [NPH]
2001-07-24  File created. [LBS]
######################################################################

 1. WHAT IS APB?
 1a. THE END OF APB?
 1b. THE BEGINNING OF THE APB XRMS PLUGIN
 3. QUICK INSTALL
 4. MORE INSTALL INSTRUCTIONS
 5. UPGRADING
 6. DOCUMENTATION
 7. SUPPORT
 8. LIMITATIONS / BUGS
 9. AVAILABLILTY
10. LICENSE
11. COPYRIGHT


1. WHAT IS APB?
---------------

Active PHP Bookmarks (APB) is a web-based program that allows you to store your
bookmarks and display them in many useful ways. It will sort your bookmarks with
usability in mind, keeping often-used bookmarks at your fingertips. It has a
bookmark search, private/public bookmarks, nested groups, usage rankings,
popularity sorting, and a quick add feature.


1a. THE END OF APB
------------------

http://lbstone.com/apb/
October 31st, 2003

I recently released the new version of Photoblogs.org, and some very exciting
and unexpected things are happening. It's kind of complicated, but now I have
to make some difficult decisions.

In short, I've made the choice to stop development of APB. There is a
possibility that I'll pick up the project again in the future, but I don't
know if that's in the cards.

The beauty of open source, though, means that someone will hopefully branch
APB with a new name and continue the project. I would be very happy to see
this happen. I realize that my inactivity has basically prevented this project
from moving forward, and I can't let that continue to happen.

If you're interested in branching APB, please feel free to use the forums to
help mobilize that effort.

I'd like to release the existing APB 2.0 code in it's currently unfinished
state, but I honestly don't know when/if I can find the time to do that. I'm
tremendously busy right now, and at this point I'm in a position where I need
to have a strong focus on other things. That's why I'd like to just leave this
completely in your hands. I hope you can understand.

I would like to say one last "thanks" to everyone, though. This was a great
learning experience for me. It's just time now for me to move on to other
things.

Cheers,

Brandon

1b. THE BEGINNING OF THE APB XRMS PLUGIN
----------------------------------------

I have been using Active PHP Bookmarks (APB) on and off for a number of years.
I've always found it very enjoyable to use and haven't found anything better.
I am now one of the developers of XRMS. There is a demand for a bookmark
plugin for my company and others have asked as well. So, I've taken APB and
started to integrate it into XRMS.

THIS IS A BRANCH OF APB.

Therefore, the code will NOT be released a patch, but only as a complete set
of scripts.

cheers,
glenn <glenn@net127.com>



3. QUICK INSTALL
----------------

1)  Unzip and untar apb-X.X.XX into the document root of your webserver.
    It will untar into a folder named apb-X.X.XX.

2)  Rename the apb directory from "apb-X.X.XX" to "bookmarks".  If you want it
    to be named something else, you'll have to edit the file apb.php.

3)  Use database_schema.sql to setup your database:

    bash$ mysql -u username -p database_name < database_schema.sql

4)  Go to http://yoursite.com/bookmarks/ and follow the instructions.


4. MORE INSTALL INSTRUCTIONS
----------------------------

The database_schema.sql contains the SQL to create your APB database.  All of
APB's database tables begin with apb_ so they can co-exist in an already created
database, or you can create a new database specificaly for APB.

If you don't know how to use database_schema.sql, here's an example of what
you'll want to run on the command line:

mysql -u username -p database_name < database_schema.sql

APB untars into the apb-X.X.XX/ directory.  If it isn't already in your web
server's document root, you'll need to put it there.

APB expects to be in a directory called bookmarks/  You can make it anything you
want, but you'll have to edit the apb.php file if you don't use "bookmarks".

You'll have to edit the first few lines of php in the apb.php to have the proper
database connection variables.

Go to http://yoursite.com/bookmarks/ and create a user.

The apb.php also contains user configurable variables that you may want to
change.


5. UPGRADING
------------

If you want to just do a quick patch from version 1.1.01, you only have to copy
the following listed files.  (If you're upgrading from an older version than
1.1.01, it is recommended that you update all the files.)

-   apb.php
-   apb_common.php
-   templates/head.php
-   templates/foot.php


6. DOCUMENTATION
----------------

http://lbstone.com/apb/


7. SUPPORT
----------

http://lbstone.com/apb/

XRMS Support: http://sourceforge.net/projects/xrms/


8. LIMITATIONS / BUGS
---------------------

APB Version 1.X.XX isn't fully ready for multiple users.  It is well on it's way,
but we still have a few things to do.


9. AVAILABLILTY
---------------

The latest version of APB is available at:

http://lbstone.com/apb/

The lastest version of XRMS and the APB XRMS plugin is available at:

http://sourceforge.net/projects/xrms/


10. LICENSE
-----------

APB is released under the 'GNU General Public License Version 2'.


11. COPYRIGHT
-------------

Copyright 2001-2003 by L. Brandon Stone (lbstone.com) and Nathanial P.
Hendler (retards.org), all rights reserved.

Additional code Copyright 2004 Glenn Powers <glenn@net127.com>

