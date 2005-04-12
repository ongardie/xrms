$Id: README.txt,v 1.1 2005/04/12 20:45:10 gpowers Exp $

XRMS Tavi Plugin - REAMDE
-------------------------

Adaptation to XRMS Plugin by Glenn Powers <glenn@net127.com>

Please email me if you have any problems with this plugin.

A few files have been removed for added security and easy of maintenence.
See REMOVED.txt. More files may be removed, if needed.

To install the dbtables:
cd install
perl ./create-db.pl dbname dbuser dbpassword tavi_ [dbserver]

(the mysql scripts and config.php have been updated to use a table
prefix of tavi_)


WikkiTikkiTavi - README
-----------------------

  Copyright (C) 2000-2001 Scott Moonen and others.

  WikkiTikkiTavi is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

Comments are welcome.

[Information about general setup]

README.txt
-------------
README.txt is the file you are currently reading.  It contains general
information on WikkiTikkiTavi, including how the code is organized and
what is included with it.

INSTALL.txt
-------------
The INSTALL.txt file contains information and instructions on installing and
setting up your own Wiki using the WikkiTikkiTavi WikiEngine.

LICENSE.txt
-------------
The LICENSE.txt file contains a copy of the GNU General Public License,
which WikkiTikkiTavi is licensed under.

NEWS.txt
-------------
The NEWS.txt file contains important information, news, or events regarding
WikkiTikkiTavi.  This could include new release information, major new
features, or anything else the developers feel warrants mention.

ChangeLog.txt
-------------
ChangeLog.txt contains a date history of changes and updates made to
WikkiTikkiTavi.

TODO.txt
-------------
Contains info on where to find information on what to do in the future with
WikkiTikkiTavi, and some additional information on where to contribute with
patches, suggestions and bugs. 

index.php
-------------
The index.php file is the "default" page loaded when accessing a Wiki
run by WikkiTikkiTavi.

config.php
-------------
config.php is not present in the 'Tavi distribution.  It is created when
you install 'Tavi using the install/configure.pl script.  It contains
various vital configuration settings for the wiki.


contrib/
-------------
The contrib/ directory contains scripts, patches, and other add-ons or
extras that are not part of the "official" code base (and thus, not
maintained by us, nor supported by us) but might be useful to you.
Please note that these extras may not work, or may require tweaking on
your part to get them working with the latest releases of
WikkiTikkiTavi.

install/
-------------
The install/ directory contains scripts and utilities to aid with the
installation and setup of the WikkiTikkiTavi engine.  You can find a
database creation script, as well as conversion scripts to help you
upgrade your Wiki from an older release of 'Tavi.

lang/
-------------
This directory contains the nationalised user interface files required to get
the user interface in your own language. If you don't see your own language
within here, please translate and publish your translation on the web site,
that is on: http://tavi.sourceforge.net/TaviTranslation

fonts/
-------------
Related to the anti spam module, we use figlet fonts to provide an ascii image
which the reader are supposed to read. This directory contains these fonts. 
Please see http://www.figlet.org/ for more information on this subject.

action/  admin/  lib/  parse/  template/  tools/
------------------------------------------------
The rest of the directories are contains source code which usually isn't 
changed. That might be with the exception of the template/ directory where
users may provide his/her own templates for use with 'Tavi.
